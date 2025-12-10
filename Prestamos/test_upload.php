<?php
$target = __DIR__ . "/uploads/test.txt";

if (!file_exists("uploads")) {
    echo "No existe la carpeta uploads<br>";
}

if (!is_writable("uploads")) {
    echo "La carpeta uploads NO tiene permisos de escritura<br>";
} else {
    echo "La carpeta uploads SI tiene permisos de escritura<br>";
}

file_put_contents($target, "prueba");

if (file_exists($target)) {
    echo "Se pudo crear archivo correctamente ✔️";
} else {
    echo "No se pudo crear archivo ❌";
}
?>
