<?php
session_start();
require_once 'config.php'; // connessione al DB

$errore = '';
$successo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $cognome = trim($_POST['cognome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    // Validazioni base
    if (!$nome || !$cognome || !$email || !$password || !$password_confirm) {
        $errore = "Tutti i campi sono obbligatori.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errore = "Email non valida.";
    } elseif ($password !== $password_confirm) {
        $errore = "Le password non coincidono.";
    } else {
        // Verifica se email già registrata
        $stmt = $conn->prepare("SELECT id FROM utenti WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errore = "Email già registrata.";
        } else {
            // Inserimento nuovo utente con ruolo 'utente'
            $hash_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt->close();

            $stmt = $conn->prepare("INSERT INTO utenti (nome, cognome, email, password, ruolo) VALUES (?, ?, ?, ?, 'utente')");
            $stmt->bind_param("ssss", $nome, $cognome, $email, $hash_password);
            if ($stmt->execute()) {
                $successo = "Registrazione completata con successo! Puoi effettuare il login.";
            } else {
                $errore = "Errore durante la registrazione: " . $stmt->error;
            }
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8" />
    <title>Registrazione - Centro Cinofilo</title>
    <style>
        /* Reset */
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #7ec8e3, #b3d4fc);
            height: 100vh;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .contenitore {
            background-color: white;
            padding: 40px 30px;
            border-radius: 12px;
            box-shadow: 0 12px 30px rgba(0,0,0,0.12);
            width: 100%;
            max-width: 420px;
            text-align: center;
            animation: fadeInScale 0.5s ease forwards;
        }

        @keyframes fadeInScale {
            from {
                opacity: 0;
                transform: scale(0.85);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        h2 {
            margin-bottom: 30px;
            font-weight: 700;
            color: #34495e;
        }

        label {
            display: block;
            text-align: left;
            margin-bottom: 6px;
            font-weight: 600;
            color: #34495e;
            font-size: 0.95rem;
            margin-top: 15px;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            border-radius: 8px;
            border: 1.8px solid #ddd;
            font-size: 1rem;
            transition: border-color 0.3s;
            outline-offset: 2px;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus {
            border-color: #3498db;
            outline: none;
            box-shadow: 0 0 8px rgba(52, 152, 219, 0.3);
        }

        button {
            margin-top: 30px;
            width: 100%;
            background-color: #3498db;
            color: white;
            font-size: 1.15rem;
            padding: 14px 0;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            transition: background-color 0.3s, box-shadow 0.3s;
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.4);
        }

        button:hover {
            background-color: #2980b9;
            box-shadow: 0 6px 18px rgba(41, 128, 185, 0.6);
        }

        .errore {
            color: #e74c3c;
            margin-bottom: 20px;
            font-weight: 700;
            font-size: 0.95rem;
            user-select: none;
        }

        .successo {
            color: #27ae60;
            margin-bottom: 20px;
            font-weight: 700;
            font-size: 0.95rem;
            user-select: none;
        }

        p.link-home {
            margin-top: 22px;
            font-size: 0.9rem;
            color: #34495e;
        }

        p.link-home a {
            color: #3498db;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s;
        }

        p.link-home a:hover {
            color: #2980b9;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="contenitore">
        <h2>Registrazione Utente</h2>

        <?php if ($errore): ?>
            <p class="errore"><?= htmlspecialchars($errore) ?></p>
        <?php endif; ?>
        <?php if ($successo): ?>
            <p class="successo"><?= htmlspecialchars($successo) ?></p>
        <?php endif; ?>

        <form method="POST" action="">
            <label for="nome">Nome:</label>
            <input type="text" name="nome" id="nome" required value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>" autofocus>

            <label for="cognome">Cognome:</label>
            <input type="text" name="cognome" id="cognome" required value="<?= htmlspecialchars($_POST['cognome'] ?? '') ?>">

            <label for="email">Email:</label>
            <input type="email" name="email" id="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">

            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required>

            <label for="password_confirm">Conferma Password:</label>
            <input type="password" name="password_confirm" id="password_confirm" required>

            <button type="submit">Registrati</button>
        </form>

        <p class="link-home">Hai già un account? <a href="login.php">Accedi qui</a></p>
    </div>
</body>
</html>
