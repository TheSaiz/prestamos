<?php
/**
 * =========================================================
 * ENVÍO A CRM – CLON EXACTO API ORIGINAL (PROD)
 * Archivo: /system/api/send_riesgo_api.php
 * =========================================================
 */

function formatearCUIL(string $cuil): string
{
    $n = preg_replace('/\D+/', '', $cuil);

    if (strlen($n) === 11) {
        return substr($n, 0, 2) . '-' . substr($n, 2, 8) . '-' . substr($n, 10, 1);
    }

    return $cuil;
}

function enviarDatosRiesgo(int $chatId): bool
{
    global $pdo;

    if (!$pdo) {
        require_once __DIR__ . "/../backend/connection.php";
    }

    // =====================================================
    // LOCK ANTI DUPLICADO
    // =====================================================
    $stmt = $pdo->prepare("
        UPDATE chats
        SET api_enviado = 2
        WHERE id = ?
          AND api_enviado = 0
    ");
    $stmt->execute([$chatId]);

    if ($stmt->rowCount() !== 1) {
        return false;
    }

    // =====================================================
    // OBTENER DATOS
    // =====================================================
    $stmt = $pdo->prepare("
        SELECT
            c.cuil_validado,
            c.nombre_validado,
            c.banco,
            c.situacion_laboral,
            u.email,
            u.telefono
        FROM chats c
        INNER JOIN usuarios u ON u.id = c.cliente_id
        WHERE c.id = ?
        LIMIT 1
    ");
    $stmt->execute([$chatId]);
    $d = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$d) {
        $pdo->prepare("UPDATE chats SET api_enviado = 0 WHERE id = ?")->execute([$chatId]);
        throw new Exception("Chat no encontrado");
    }

    // =====================================================
    // VALIDACIONES OBLIGATORIAS
    // =====================================================
    $requeridos = [
        'cuil_validado',
        'nombre_validado',
        'email',
        'telefono',
        'banco',
        'situacion_laboral'
    ];

    foreach ($requeridos as $campo) {
        if (empty($d[$campo])) {
            $pdo->prepare("UPDATE chats SET api_enviado = 0 WHERE id = ?")->execute([$chatId]);
            throw new Exception("Falta dato obligatorio: {$campo}");
        }
    }

    // =====================================================
    // TELÉFONO (MISMO CRITERIO FORM ORIGINAL)
    // =====================================================
    $telefono = preg_replace('/\D+/', '', $d['telefono']);

    if (strlen($telefono) < 8) {
        $pdo->prepare("UPDATE chats SET api_enviado = 0 WHERE id = ?")->execute([$chatId]);
        throw new Exception("Teléfono inválido");
    }

    $areacode = substr($telefono, 0, strlen($telefono) - 8);
    $phone    = substr($telefono, -8);

    // =====================================================
    // MAPEO LOCALIDAD
    // =====================================================
    $codigos = [
        "11"   => "Ciudad Autónoma de Buenos Aires y Gran Buenos Aires",
        "220"  => "San Miguel - Buenos Aires",
        "221"  => "La Plata - Buenos Aires",
        "223"  => "Mar del Plata - Buenos Aires",
        "230"  => "Campana - Buenos Aires",
        "236"  => "Junín - Buenos Aires",
        "237"  => "Moreno - Buenos Aires",
        "249"  => "Tandil - Buenos Aires",
        "261"  => "Mendoza - Mendoza",
        "351"  => "Córdoba - Córdoba",
        "341"  => "Rosario - Santa Fe",
        "376"  => "Posadas - Misiones",
        "379"  => "Corrientes - Corrientes",
        "381"  => "San Miguel de Tucumán - Tucumán",
        "387"  => "Salta - Salta",
        "388"  => "San Salvador de Jujuy - Jujuy",
        "2901" => "Ushuaia - Tierra del Fuego",
    ];

    $localidad = $codigos[$areacode] ?? "Localidad desconocida";

    // =====================================================
    // MAPEO BANCO (ID → TEXTO CRM)
    // =====================================================
    $mapBancos = require __DIR__ . "/../config/bancos_crm.php";

    $bankId   = (int)$d['banco'];
    $bankText = $mapBancos[$bankId] ?? '';

    if ($bankText === '') {
        $pdo->prepare("UPDATE chats SET api_enviado = 0 WHERE id = ?")->execute([$chatId]);
        throw new Exception("Banco inválido o no mapeado para CRM");
    }

    // =====================================================
    // SOAP – CLON EXACTO FORM ORIGINAL
    // =====================================================
    $wsdl = "http://plider.dyndns.info:8080/riesgohook1_plider_ws.aspx?wsdl";

    $client = new SoapClient($wsdl, [
        'trace'      => 1,
        'exceptions' => true,
        'cache_wsdl' => WSDL_CACHE_NONE
    ]);

    $params = [
        'D_usucod'            => '998',
        'D_name'              => $d['nombre_validado'],
        'D_lastname'          => '',
        'D_email'             => $d['email'],
        'D_dni'               => formatearCUIL($d['cuil_validado']),
        'D_areacode'          => (string)$areacode,
        'D_phone'             => (string)$phone,
        'D_bank'              => $bankText, // ✅ TEXTO EXACTO PARA CRM
        'D_typeofincome'      => $d['situacion_laboral'],
        'D_amount'            => 0,
        'D_amountdestination' => '',
        'D_urgency'           => 'quietly',
        'D_saleschannel'      => 'Web',
        'D_version'           => 'plider',
        'D_campaign'          => 'plider',
        'D_provincia'         => $localidad,
        'D_parametros'        => ''
    ];

    try {
        $response = $client->Execute($params);

        // =================================================
        // LOG SOAP REAL
        // =================================================
        file_put_contents(__DIR__ . "/SOAP_REQUEST.xml", $client->__getLastRequest());
        file_put_contents(__DIR__ . "/SOAP_RESPONSE.xml", $client->__getLastResponse());

        // =================================================
        // MARCAR COMO ENVIADO
        // =================================================
        $pdo->prepare("
            UPDATE chats
            SET api_enviado = 1,
                api_respuesta = ?
            WHERE id = ?
        ")->execute([
            json_encode($response, JSON_UNESCAPED_UNICODE),
            $chatId
        ]);

        return true;

    } catch (SoapFault $e) {
        $pdo->prepare("UPDATE chats SET api_enviado = 0 WHERE id = ?")->execute([$chatId]);
        throw new Exception("SOAP ERROR: " . $e->getMessage());
    }
}
