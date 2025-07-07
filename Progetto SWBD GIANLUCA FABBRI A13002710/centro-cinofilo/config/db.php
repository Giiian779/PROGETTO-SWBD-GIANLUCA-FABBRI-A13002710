<?php
// Se è definita la costante TESTING, carica config_test, altrimenti config normale
if (defined('TESTING') && TESTING === true) {
    $config = require __DIR__ . '/config_test.php';
} else {
    $config = require __DIR__ . '/config.php';
}

// Crea connessione mysqli
$conn = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);

// Controlla connessione
if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}