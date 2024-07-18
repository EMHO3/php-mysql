<?php $host="localhost";
$bd="nombre de la DB";
$user="usuario";
$contrasena="contraseña de su DB ";
try {
    $conexion=new PDO("mysql:host=$host;dbname=$bd",$user,$contrasena);
} catch (Exception $ex) {
    echo $ex->getMessage();
}?>
