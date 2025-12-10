<?php
header("Content-Type: application/json");
require_once __DIR__ . "/../../backend/connection.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Método no permitido"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$dni = trim($data["dni"] ?? "");

if (!$dni) {
    echo json_encode(["success" => false, "message" => "DNI es obligatorio"]);
    exit;
}

$dni = preg_replace('/\D/', '', $dni);

if (strlen($dni) < 7 || strlen($dni) > 8) {
    echo json_encode(["success" => false, "message" => "El DNI debe tener entre 7 y 8 dígitos"]);
    exit;
}

// Convertir DNI a formato CUIT (00 + DNI + 0)
$dni_padded = str_pad($dni, 8, '0', STR_PAD_LEFT);
$cuit = "00{$dni_padded}0";

// URL del WS
$wsdl = "http://padronbcra.dyndns.info:9999/aconsultapaws.aspx?wsdl";

// Verificar si el servicio responde
$headers = @get_headers($wsdl);
if (!$headers || strpos($headers[0], "200") === false) {
    echo json_encode([
        "success" => false,
        "message" => "El servicio de validación no está disponible"
    ]);
    exit;
}

$options = [
    "trace" => 1,
    "exceptions" => true,
    "cache_wsdl" => WSDL_CACHE_NONE,
];

try {
    $client = new SoapClient($wsdl, $options);

    // XML correcto
    $xml = '
    <GX xmlns="GX">
        <Usuario>P.LIDER</Usuario>
        <Clave>P.LIDER</Clave>
        <Version>1</Version>
        <Pa_Tipo>CUIT</Pa_Tipo>
        <CUIT>'.$cuit.'</CUIT>
    </GX>';

    $params = [
        "Parametros" => "[PA_CUIT][".$xml."]"
    ];

    $response = $client->__soapCall("Execute", [ $params ]);

    // Log para depuración
    // file_put_contents("debug_dni.txt", print_r($response, true));

    if (!isset($response->Bcra_rpa)) {
        echo json_encode([
            "success" => false,
            "message" => "Respuesta inválida del servicio"
        ]);
        exit;
    }

    $r = $response->Bcra_rpa;
    $casos = intval($r->Casos ?? 0);

    if ($casos === 0) {
        echo json_encode([
            "success" => false,
            "message" => "No se encontró información para este DNI"
        ]);
        exit;
    }

    if ($casos === 1) {
        $item = $r->Datos->Item;
        echo json_encode([
            "success" => true,
            "casos" => 1,
            "cuil" => $item->PaCUIT ?? "",
            "nombre" => $item->PaDenom ?? ""
        ]);
        exit;
    }

    // Multiples resultados
    $lista = [];
    foreach ($r->Datos->Item as $item) {
        $lista[] = [
            "cuil" => $item->PaCUIT ?? "",
            "nombre" => $item->PaDenom ?? ""
        ];
    }

    echo json_encode([
        "success" => true,
        "casos" => count($lista),
        "opciones" => $lista
    ]);
    exit;

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "No se pudo validar el DNI",
        "error" => $e->getMessage()
    ]);
    exit;
}
?>
