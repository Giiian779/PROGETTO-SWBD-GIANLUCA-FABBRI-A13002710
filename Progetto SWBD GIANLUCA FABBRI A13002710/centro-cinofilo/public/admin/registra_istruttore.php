<?php
session_start();
require_once '../config.php';

// Accesso consentito solo agli admin
if (!isset($_SESSION['utente_id']) || $_SESSION['ruolo'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$messaggio = '';
$errore = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $cognome = trim($_POST['cognome']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $ruolo = 'istruttore';

    if ($nome && $cognome && $email && $password) {
        $stmt_check = $conn->prepare("SELECT id FROM utenti WHERE email = ?");
        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $errore = "⚠️ Email già in uso.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO utenti (nome, cognome, email, password, ruolo) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $nome, $cognome, $email, $hash, $ruolo);

            if ($stmt->execute()) {
                $messaggio = "✅ Istruttore registrato con successo.";
            } else {
                $errore = "❌ Errore durante la registrazione: " . $stmt->error;
            }
            $stmt->close();
        }
        $stmt_check->close();
    } else {
        $errore = "⚠️ Tutti i campi sono obbligatori.";
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Registra Istruttore</title>
    <style>
        body {
            background: #f5f7fa;
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            padding: 40px;
        }

        .container {
            max-width: 480px;
            background: #fff;
            padding: 30px;
            margin: auto;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            color: #333;
        }

        form {
            margin-top: 20px;
        }

        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
            color: #555;
        }

        input {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-sizing: border-box;
        }

        button {
            margin-top: 25px;
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            border: none;
            color: white;
            font-size: 16px;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #0056b3;
        }

        .messaggio, .errore {
            text-align: center;
            margin-top: 15px;
            padding: 10px;
            border-radius: 6px;
        }

        .messaggio {
            background-color: #d4edda;
            color: #155724;
        }

        .errore {
            background-color: #f8d7da;
            color: #721c24;
        }

        a {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #007bff;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

    </style>
</head>
<body>

<div class="container">
    <h2>Registrazione Nuovo Istruttore</h2>

    <?php if ($messaggio): ?>
        <div class="messaggio"><?= htmlspecialchars($messaggio) ?></div>
    <?php endif; ?>

    <?php if ($errore): ?>
        <div class="errore"><?= htmlspecialchars($errore) ?></div>
    <?php endif; ?>

    <form method="POST">
        <label for="nome">Nome</label>
        <input type="text" id="nome" name="nome" required>

        <label for="cognome">Cognome</label>
        <input type="text" id="cognome" name="cognome" required>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" required>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>

        <button type="submit">Registra Istruttore</button>
    </form>

    <a href="admin_dashboard.php">← Torna alla dashboard</a>
</div>

</body>
</html>
