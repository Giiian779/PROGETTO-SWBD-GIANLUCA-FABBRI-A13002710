<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['utente_id']) || $_SESSION['ruolo'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Gestione eliminazione utente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['elimina_utente_email'])) {
    $email_da_eliminare = $conn->real_escape_string($_POST['elimina_utente_email']);
    
    // Evita di eliminare te stesso o utenti admin
    if ($email_da_eliminare !== $_SESSION['email'] && $email_da_eliminare !== '') {
        $check = $conn->query("SELECT ruolo FROM utenti WHERE email = '$email_da_eliminare'")->fetch_assoc();
        if ($check && $check['ruolo'] !== 'admin') {
            $conn->query("DELETE FROM utenti WHERE email = '$email_da_eliminare'");
        }
    }

    // Redirect per evitare doppia sottomissione
    header("Location: reports.php");
    exit;
}

// Query utenti
$utenti = $conn->query("SELECT nome, cognome, email, ruolo FROM utenti ORDER BY ruolo, cognome, nome");

// Query corsi e partecipazioni
$corsi_query = "
    SELECT c.id AS corso_id, c.nome AS nome_corso, c.fascia_eta, c.fascia_oraria,
           u.nome AS nome_istruttore, u.cognome AS cognome_istruttore,
           ca.nome AS nome_cane, ca.razza, ca.eta, p.nome AS nome_proprietario, p.cognome AS cognome_proprietario
    FROM corsi c
    LEFT JOIN utenti u ON c.istruttore_id = u.id
    LEFT JOIN iscrizioni i ON c.id = i.corso_id AND i.stato = 'accettata'
    LEFT JOIN cani ca ON i.cane_id = ca.id
    LEFT JOIN utenti p ON ca.proprietario_id = p.id
    ORDER BY c.nome, ca.nome
";
$corsi_result = $conn->query($corsi_query);

// Organizza i dati dei corsi
$corsi_dati = [];
while ($row = $corsi_result->fetch_assoc()) {
    $corso_id = $row['corso_id'];
    if (!isset($corsi_dati[$corso_id])) {
        $corsi_dati[$corso_id] = [
            'nome_corso' => $row['nome_corso'],
            'fascia_eta' => $row['fascia_eta'],
            'fascia_oraria' => $row['fascia_oraria'],
            'istruttore' => $row['nome_istruttore'] . ' ' . $row['cognome_istruttore'],
            'cani' => []
        ];
    }
    if ($row['nome_cane']) {
        $corsi_dati[$corso_id]['cani'][] = [
            'nome' => $row['nome_cane'],
            'razza' => $row['razza'],
            'eta' => $row['eta'],
            'proprietario' => $row['nome_proprietario'] . ' ' . $row['cognome_proprietario']
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Report Admin</title>
    <style>
        body {
            font-family: "Segoe UI", sans-serif;
            background: #f4f6f8;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 1100px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            margin-bottom: 25px;
            color: #333;
        }

        .tabs {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }

        .tab {
            padding: 12px 20px;
            cursor: pointer;
            background: #e9ecef;
            border-radius: 8px 8px 0 0;
            margin: 0 5px;
            font-weight: bold;
            transition: background 0.3s;
        }

        .tab.active {
            background: #007bff;
            color: white;
        }

        .tab-content {
            display: none;
            animation: fadeIn 0.3s ease-in-out;
        }

        .tab-content.active {
            display: block;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th, td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f8f9fa;
            text-align: left;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        .back-button {
            display: inline-block;
            margin-bottom: 15px;
            padding: 10px 15px;
            background-color: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        button.delete-btn {
            background:#dc3545;
            color:white;
            border:none;
            padding:5px 10px;
            border-radius:5px;
            cursor:pointer;
        }

        button.delete-btn:hover {
            background:#b02a37;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="container">
        <a class="back-button" href="admin_dashboard.php">← Torna alla Dashboard</a>
        <h1>Report del Centro Cinofilo</h1>

        <div class="tabs">
            <div class="tab active" data-tab="utenti">Utenti Registrati</div>
            <div class="tab" data-tab="corsi">Corsi e Cani</div>
        </div>

        <div class="tab-content active" id="utenti">
            <table>
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Cognome</th>
                        <th>Email</th>
                        <th>Ruolo</th>
                        <th>Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($u = $utenti->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($u['nome']) ?></td>
                            <td><?= htmlspecialchars($u['cognome']) ?></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td><?= htmlspecialchars($u['ruolo']) ?></td>
                            <td>
                                <?php if ($u['ruolo'] !== 'admin'): ?>
                                    <form method="POST" onsubmit="return confirm('Sei sicuro di voler eliminare questo utente?');" style="margin:0;">
                                        <input type="hidden" name="elimina_utente_email" value="<?= htmlspecialchars($u['email']) ?>">
                                        <button type="submit" class="delete-btn">Elimina</button>
                                    </form>
                                <?php else: ?>
                                    <em>Non eliminabile</em>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="tab-content" id="corsi">
            <?php foreach ($corsi_dati as $corso): ?>
                <h3><?= htmlspecialchars($corso['nome_corso']) ?> (<?= htmlspecialchars($corso['fascia_eta']) ?> - <?= htmlspecialchars($corso['fascia_oraria']) ?>)</h3>
                <p><strong>Istruttore:</strong> <?= htmlspecialchars($corso['istruttore']) ?></p>
                <table>
                    <thead>
                        <tr>
                            <th>Nome Cane</th>
                            <th>Razza</th>
                            <th>Età</th>
                            <th>Proprietario</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($corso['cani']) > 0): ?>
                            <?php foreach ($corso['cani'] as $cane): ?>
                                <tr>
                                    <td><?= htmlspecialchars($cane['nome']) ?></td>
                                    <td><?= htmlspecialchars($cane['razza']) ?></td>
                                    <td><?= htmlspecialchars($cane['eta']) ?> anni</td>
                                    <td><?= htmlspecialchars($cane['proprietario']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4">Nessun cane iscritto a questo corso.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <br>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        const tabs = document.querySelectorAll('.tab');
        const contents = document.querySelectorAll('.tab-content');

        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                tabs.forEach(t => t.classList.remove('active'));
                contents.forEach(c => c.classList.remove('active'));
                tab.classList.add('active');
                document.getElementById(tab.dataset.tab).classList.add('active');
            });
        });
    </script>
</body>
</html>
