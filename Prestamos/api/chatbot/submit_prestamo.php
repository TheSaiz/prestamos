<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
require_once __DIR__ . "/../../backend/connection.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Método no permitido"]);
    exit;
}

$chat_id = intval($_POST["chat_id"] ?? 0);

if (!$chat_id) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "chat_id es obligatorio"]);
    exit;
}

try {
    // Obtener datos del chat
    $stmt = $pdo->prepare("
        SELECT 
            c.cuil_validado,
            c.nombre_validado,
            c.situacion_laboral,
            c.banco,
            cl.email,
            cl.telefono
        FROM chats c
        INNER JOIN clientes cl ON c.cliente_id = cl.id
        WHERE c.id = ?
    ");
    $stmt->execute([$chat_id]);
    $data = $stmt->fetch();

    if (!$data) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Chat no encontrado"]);
        exit;
    }

    // Validar que todos los datos necesarios estén presentes
    $required = ['cuil_validado', 'nombre_validado', 'situacion_laboral', 'banco', 'email', 'telefono'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            echo json_encode([
                "success" => false,
                "message" => "Faltan datos obligatorios: $field"
            ]);
            exit;
        }
    }

    // Separar código de área y teléfono
    $telefono_completo = preg_replace('/\D/', '', $data['telefono']);
    
    if (strlen($telefono_completo) === 10) {
        // Primeros 2-4 dígitos = área, resto = teléfono
        if (substr($telefono_completo, 0, 2) === '11') {
            $areacode = '11';
            $phone = substr($telefono_completo, 2);
        } else if (in_array(substr($telefono_completo, 0, 3), ['220', '221', '223', '230', '236'])) {
            $areacode = substr($telefono_completo, 0, 3);
            $phone = substr($telefono_completo, 3);
        } else if (in_array(substr($telefono_completo, 0, 4), ['2202', '2221', '2223'])) {
            $areacode = substr($telefono_completo, 0, 4);
            $phone = substr($telefono_completo, 4);
        } else {
            $areacode = substr($telefono_completo, 0, 3);
            $phone = substr($telefono_completo, 3);
        }
    } else {
        echo json_encode([
            "success" => false,
            "message" => "El teléfono debe tener 10 dígitos (código de área + número)"
        ]);
        exit;
    }

    // Mapear banco a ID numérico (simplificado)
    $bancos_map = [
        'Banco Nación' => 11,
        'Banco Provincia' => 14,
        'Banco Galicia' => 7,
        'Banco Santander' => 72,
        'Banco Macro' => 285,
        'Banco BBVA' => 17,
        'Banco Credicoop' => 191,
        'Banco Supervielle' => 27,
        'Banco Patagonia' => 34,
        'Banco Hipotecario' => 44,
        'Otro banco' => 2
    ];
    
    $bank_id = $bancos_map[$data['banco']] ?? 2;

    // Mapear código de área a localidad
    $localidades = [
        '11' => 'Ciudad Autónoma de Buenos Aires y Gran Buenos Aires',
        '220' => 'San Miguel - Buenos Aires',
        '221' => 'La Plata - Buenos Aires',
        '223' => 'Mar del Plata - Buenos Aires',
        // ... (agregar más según necesites)
    ];
    $localidad = $localidades[$areacode] ?? 'Argentina';

    // Conectar al SOAP API de riesgo
    $wsdl = "http://plider.dyndns.info:8080/riesgohook1_plider_ws.aspx?wsdl";
    $options = [
        'trace' => 1,
        'exceptions' => true,
        'cache_wsdl' => WSDL_CACHE_NONE,
        'connection_timeout' => 30
    ];

    $client = new SoapClient($wsdl, $options);

    $params = [
        'D_usucod' => '998',
        'D_name' => $data['nombre_validado'],
        'D_lastname' => '',
        'D_email' => $data['email'],
        'D_dni' => $data['cuil_validado'],
        'D_areacode' => $areacode,
        'D_phone' => $phone,
        'D_bank' => $bank_id,
        'D_typeofincome' => $data['situacion_laboral'],
        'D_amount' => 0,
        'D_amountdestination' => '',
        'D_urgency' => 'quietly',
        'D_saleschannel' => 'Chatbot',
        'D_version' => 'plider',
        'D_campaign' => 'plider',
        'D_provincia' => $localidad,
        'D_parametros' => ''
    ];

    $response = $client->Execute($params);

    // Procesar respuesta
    if (isset($response->Respuesta->P_OK) && strtoupper($response->Respuesta->P_OK) === 'S') {
        $mensaje = strtoupper(trim($response->Respuesta->P_Msj));

        $respuestas = [
            'CARGA REPETIDA' => [
                'status' => 'pendiente',
                'titulo' => 'Solicitud Duplicada',
                'mensaje' => 'Ya te registraste en los últimos 30 días. Si ya estás hablando con un asesor por WhatsApp seguí comunicándote por allí.',
                'whatsapp' => null
            ],
            'RECHAZO SISTEMA' => [
                'status' => 'rechazado',
                'titulo' => 'Solicitud Rechazada',
                'mensaje' => 'Por el momento no estarías calificando para un crédito. Podés volver a consultar sin compromiso en 90 días.',
                'whatsapp' => null
            ],
            'HABERES' => [
                'status' => 'ok',
                'titulo' => '¡Solicitud Recibida!',
                'mensaje' => 'Te registraste con éxito. Un asesor nuestro te estará contactando por WhatsApp.',
                'whatsapp' => '543764584920'
            ],
            'ANSES' => [
                'status' => 'ok',
                'titulo' => '¡Solicitud Recibida!',
                'mensaje' => 'Te registraste con éxito. Un asesor nuestro te estará contactando por WhatsApp.',
                'whatsapp' => '543764584963'
            ],
            'ANSES (VIP)' => [
                'status' => 'ok',
                'titulo' => '¡Solicitud Recibida!',
                'mensaje' => 'Te registraste con éxito. Un asesor nuestro te estará contactando por WhatsApp.',
                'whatsapp' => '543764584963'
            ],
            'EMPLEADO PUBLICO' => [
                'status' => 'ok',
                'titulo' => '¡Solicitud Recibida!',
                'mensaje' => 'Te registraste con éxito. Un asesor nuestro te estará contactando por WhatsApp.',
                'whatsapp' => null
            ],
            'EMPLEADO PUBLICO (VIP)' => [
                'status' => 'ok',
                'titulo' => '¡Solicitud Recibida!',
                'mensaje' => 'Te registraste con éxito. Un asesor nuestro te estará contactando por WhatsApp.',
                'whatsapp' => null
            ],
            'AUH' => [
                'status' => 'ok',
                'titulo' => '¡Solicitud Recibida!',
                'mensaje' => 'Te registraste con éxito. Un asesor nuestro te estará contactando por WhatsApp.',
                'whatsapp' => null
            ],
            'EMPLEADO PRIVADO' => [
                'status' => 'ok',
                'titulo' => '¡Solicitud Recibida!',
                'mensaje' => 'Te registraste con éxito. Un asesor nuestro te estará contactando por WhatsApp.',
                'whatsapp' => null
            ]
        ];

        $resultado = $respuestas[$mensaje] ?? [
            'status' => 'ok',
            'titulo' => 'Solicitud Procesada',
            'mensaje' => 'Solicitud recibida correctamente.',
            'whatsapp' => null
        ];

        // Actualizar estado del chat
        $stmt = $pdo->prepare("UPDATE chats SET estado = 'finalizado' WHERE id = ?");
        $stmt->execute([$chat_id]);

        echo json_encode([
            "success" => true,
            "data" => $resultado
        ]);

    } else {
        echo json_encode([
            "success" => false,
            "message" => "Solicitud rechazada por la API de riesgo."
        ]);
    }

} catch (SoapFault $e) {
    error_log("Error SOAP submit_prestamo: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Error al conectar con el servicio de préstamos"
    ]);
} catch (Exception $e) {
    error_log("Error submit_prestamo: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Error inesperado al procesar la solicitud"
    ]);
}
?>