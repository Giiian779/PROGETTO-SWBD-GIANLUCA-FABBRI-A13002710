<?php
session_start();
require_once 'config.php'; // connessione al DB

$errore = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Cerca l'utente nel DB
    $stmt = $conn->prepare("SELECT * FROM utenti WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $risultato = $stmt->get_result();

    if ($risultato->num_rows === 1) {
        $utente = $risultato->fetch_assoc();
        if (password_verify($password, $utente['password'])) {
            // Salva dati in sessione
            $_SESSION['utente_id'] = $utente['id'];
            $_SESSION['ruolo'] = $utente['ruolo'];
            $_SESSION['nome'] = $utente['nome'];

            // Reindirizzamento in base al ruolo
            if ($utente['ruolo'] === 'admin') {
                header('Location: admin/admin_dashboard.php');
            } elseif ($utente['ruolo'] === 'istruttore') {
                header('Location: admin/dashboard_istruttori.php');
            } else {
                header('Location: area_utenti.php');
            }
            exit;
        } else {
            $errore = "Password errata.";
        }
    } else {
        $errore = "Utente non trovato.";
    }

    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8" />
    <title>Login - Centro Cinofilo</title>
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
            max-width: 400px;
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
            margin-bottom: 25px;
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
        }

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

        input[type="email"]:focus,
        input[type="password"]:focus {
            border-color: #3498db;
            outline: none;
            box-shadow: 0 0 8px rgba(52, 152, 219, 0.3);
        }

        button {
            margin-top: 25px;
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

        p.link-home {
            margin-top: 18px;
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
        <h2>Login Centro Cinofilo</h2>
        <?php if ($errore): ?>
            <p class="errore"><?= htmlspecialchars($errore) ?></p>
        <?php endif; ?>
        <form method="POST" action="">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required autocomplete="username" autofocus>

            <label for="password" style="margin-top:20px;">Password:</label>
            <input type="password" id="password" name="password" required autocomplete="current-password">

            <button type="submit">Accedi</button>
        </form>
        <p class="link-home">
            <a href="index.php">Torna alla home</a>
        </p>
    </div>
</body>
</html>
