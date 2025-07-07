<?php
$host = '127.0.0.1'; // oppure 'localhost'
$dbname = 'centro_cinofilo';
$username = 'root'; // o altro utente
$password = '';     // XAMPP di solito ha password vuota

try {
    $conn = new mysqli($host, $username, $password, $dbname);
    if ($conn->connect_error) {
        throw new Exception("Connessione fallita: " . $conn->connect_error);
    }
} catch (Exception $e) {
    die($e->getMessage());
}
?>
