<?php
$host = 'localhost';
$user = 'root';
$pass = ''; // Metti la tua password se ce n'è una

// Usa database di test se APP_ENV è impostato a 'testing'
$dbname = getenv('APP_ENV') === 'testing' ? 'centro_cinofilo_test' : 'centro_cinofilo';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

return $conn;
