<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8" />
    <title>Centro Cinofilo - Home</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <style>
        /* Reset base */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html, body {
            height: 100%;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: white;
            overflow-x: hidden;
        }

     body {
    background: url('locandina per centro cinofilo.jpg') no-repeat center center fixed;
    background-size: cover;
    background-color: red; /* colore di debug */
    position: relative;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}
        /* Overlay scuro per leggibilità */
        body::before {
            content: "";
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0, 0, 0, 0.55);
            z-index: 0;
        }

        header {
            position: relative;
            z-index: 2;
            text-align: center;
            padding: 60px 20px 20px;
        }

        header h1 {
            font-size: 3.5rem;
            animation: fadeZoomIn 1.5s ease forwards;
            opacity: 0;
            transform: scale(0.8);
            margin-bottom: 10px;
        }

        header p {
            font-size: 1.5rem;
            opacity: 0;
            animation: fadeIn 2s ease forwards;
            animation-delay: 1s;
        }

        .container {
            position: relative;
            z-index: 2;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
            text-align: center;
        }

        .container h2 {
            font-size: 2rem;
            margin-bottom: 40px;
            text-shadow: 0 0 10px rgba(0,0,0,0.7);
        }

        .btn {
            display: inline-block;
            margin: 10px;
            padding: 14px 36px;
            font-size: 1.2rem;
            font-weight: 600;
            color: white;
            background: #3498db;
            border-radius: 12px;
            text-decoration: none;
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.5);
            transition: background-color 0.3s ease, transform 0.2s ease;
            user-select: none;
        }

        .btn:hover, .btn:focus {
            background: #2980b9;
            transform: translateY(-3px);
        }

        footer {
            position: relative;
            z-index: 2;
            text-align: center;
            padding: 20px;
            font-size: 1rem;
            background: rgba(0, 0, 0, 0.35);
            color: #ddd;
            user-select: none;
        }

        /* Animazioni */
        @keyframes fadeZoomIn {
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        @keyframes fadeIn {
            to {
                opacity: 1;
            }
        }

        /* Responsive */
        @media (max-width: 600px) {
            header h1 {
                font-size: 2.5rem;
            }

            header p {
                font-size: 1.2rem;
            }

            .container h2 {
                font-size: 1.5rem;
            }

            .btn {
                width: 80%;
                font-size: 1.1rem;
                padding: 14px 0;
            }
        }
    </style>
</head>
<body>

    <header>
        <h1>Centro Cinofilo</h1>
        <p>Benvenuto nella nostra webapp</p>
    </header>

    <div class="container">
        <h2>Accedi o Registrati per continuare</h2>
        <a href="login.php" class="btn" tabindex="1">Login</a>
        <a href="register.php" class="btn" tabindex="2">Registrati</a>
    </div>

    <footer>
        &copy; <?= date("Y") ?> Centro Cinofilo. Tutti i diritti riservati.
    </footer>

</body>
</html>
