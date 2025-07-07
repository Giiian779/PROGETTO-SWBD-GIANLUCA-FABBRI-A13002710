<?php
function verificaCredenziali($conn, $email, $password) {
    $stmt = $conn->prepare("SELECT * FROM utenti WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $risultato = $stmt->get_result();

    if ($risultato->num_rows === 1) {
        $utente = $risultato->fetch_assoc();
        if (password_verify($password, $utente['password'])) {
            return $utente; // login ok, ritorna dati utente
        } else {
            return false; // password errata
        }
    }
    return false; // utente non trovato
}
