<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['utente_id']) || $_SESSION['ruolo'] !== 'istruttore') {
    header("Location: ../login.php");
    exit;
}

$istruttore_id = $_SESSION['utente_id'];

// Azioni
if (isset($_POST['accetta_iscrizione'])) {
    $iscrizione_id = intval($_POST['accetta_iscrizione']);
    $conn->query("UPDATE iscrizioni SET stato = 'accettata' WHERE id = $iscrizione_id");
}

if (isset($_POST['rifiuta_iscrizione'])) {
    $iscrizione_id = intval($_POST['rifiuta_iscrizione']);
    $motivo = trim($_POST['motivo_rifiuto']) ?: 'Richiesta rifiutata.';
    $stmt = $conn->prepare("UPDATE iscrizioni SET stato = 'rifiutata', testo_notifica = ? WHERE id = ?");
    $stmt->bind_param("si", $motivo, $iscrizione_id);
    $stmt->execute();
    $stmt->close();
}

if (isset($_POST['rimuovi_iscrizione'])) {
    $iscrizione_id = intval($_POST['rimuovi_iscrizione']);
    $conn->query("DELETE FROM iscrizioni WHERE id = $iscrizione_id");
}

// Recupera corsi
$stmt = $conn->prepare("SELECT id, nome, fascia_oraria FROM corsi WHERE istruttore_id = ?");
$stmt->bind_param("i", $istruttore_id);
$stmt->execute();
$corsi_result = $stmt->get_result();
$corsi = [];

while ($corso = $corsi_result->fetch_assoc()) {
    $corso_id = $corso['id'];

    $stmt_giorni = $conn->prepare("SELECT giorno FROM giorni_corso WHERE corso_id = ?");
    $stmt_giorni->bind_param("i", $corso_id);
    $stmt_giorni->execute();
    $giorni_result = $stmt_giorni->get_result();
    $giorni = [];
    while ($row = $giorni_result->fetch_assoc()) {
        $giorni[] = $row['giorno'];
    }
    $stmt_giorni->close();

    $corso['giorni'] = $giorni;

    // Richieste
    $stmt_richieste = $conn->prepare("
        SELECT i.id AS iscrizione_id, c.nome, c.razza, c.eta
        FROM iscrizioni i
        JOIN cani c ON i.cane_id = c.id
        WHERE i.corso_id = ? AND i.stato = 'in_attesa'
    ");
    $stmt_richieste->bind_param("i", $corso_id);
    $stmt_richieste->execute();
    $corso['richieste'] = $stmt_richieste->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt_richieste->close();

    // Iscritti
    $stmt_cani = $conn->prepare("
        SELECT i.id AS iscrizione_id, c.nome, c.razza
        FROM iscrizioni i
        JOIN cani c ON i.cane_id = c.id
        WHERE i.corso_id = ? AND i.stato = 'accettata'
    ");
    $stmt_cani->bind_param("i", $corso_id);
    $stmt_cani->execute();
    $corso['cani_iscritti'] = $stmt_cani->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt_cani->close();

    $corsi[] = $corso;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Istruttore</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f4f6f8;
            margin: 0;
            padding: 20px;
        }

        .contenitore {
            max-width: 1000px;
            margin: auto;
            background: #fff;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        h1 {
            text-align: center;
            color: #333;
        }

        .corso {
            margin-top: 30px;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }

        .corso h3 {
            margin-bottom: 10px;
            color: #007bff;
        }

        h4 {
            margin-bottom: 10px;
            color: #555;
        }

        ul {
            list-style: none;
            padding: 0;
        }

        li {
            background: #f9f9f9;
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 10px;
        }

        form.inline {
            display: inline-block;
            margin-left: 10px;
        }

        input[type="text"] {
            padding: 6px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        button {
            padding: 6px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button[type="submit"] {
            background-color: #28a745;
            color: white;
        }

        button.rifiuta {
            background-color: #dc3545;
        }

        button.rimuovi {
            background-color: #ffc107;
            color: #000;
        }

        a {
            color: #007bff;
            text-decoration: none;
        }

        .motivo-container {
            display: none;
            margin-top: 6px;
        }
    </style>
    <script>
        function toggleMotivo(id) {
            var container = document.getElementById('motivo_' + id);
            container.style.display = container.style.display === 'none' ? 'block' : 'none';
        }
    </script>
</head>
<body>
<div class="contenitore">
    <h1>Benvenuto, <?= htmlspecialchars($_SESSION['nome']) ?></h1>
    <h2>I tuoi corsi assegnati</h2>

    <?php if (count($corsi) === 0): ?>
        <p>Non ti è stato ancora assegnato nessun corso.</p>
    <?php else: ?>
        <?php foreach ($corsi as $corso): ?>
            <div class="corso">
                <h3>
                    <?= htmlspecialchars($corso['nome']) ?>
                    (<?= htmlspecialchars(implode(", ", $corso['giorni'])) ?> - <?= htmlspecialchars($corso['fascia_oraria']) ?>)
                </h3>

                <h4>Richieste in attesa:</h4>
                <?php if (count($corso['richieste']) === 0): ?>
                    <p><em>Nessuna richiesta in attesa.</em></p>
                <?php else: ?>
                    <ul>
                        <?php foreach ($corso['richieste'] as $richiesta): ?>
                            <li>
                                <?= htmlspecialchars($richiesta['nome']) ?> (<?= htmlspecialchars($richiesta['razza']) ?> - <?= htmlspecialchars($richiesta['eta']) ?> anni)
                                <form class="inline" method="POST">
                                    <input type="hidden" name="accetta_iscrizione" value="<?= $richiesta['iscrizione_id'] ?>">
                                    <button type="submit">Accetta</button>
                                </form>
                                <button type="button" class="rifiuta" onclick="toggleMotivo(<?= $richiesta['iscrizione_id'] ?>)">Rifiuta</button>

                                <div class="motivo-container" id="motivo_<?= $richiesta['iscrizione_id'] ?>">
                                    <form method="POST" style="margin-top:10px;">
                                        <input type="hidden" name="rifiuta_iscrizione" value="<?= $richiesta['iscrizione_id'] ?>">
                                        <input type="text" name="motivo_rifiuto" placeholder="Motivo del rifiuto" required>
                                        <button type="submit" class="rifiuta">Invia Rifiuto</button>
                                    </form>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <h4>Cani iscritti:</h4>
                <?php if (count($corso['cani_iscritti']) === 0): ?>
                    <p><em>Nessun cane iscritto al momento.</em></p>
                <?php else: ?>
                    <ul>
                        <?php foreach ($corso['cani_iscritti'] as $cane): ?>
                            <li>
                                <?= htmlspecialchars($cane['nome']) ?> (<?= htmlspecialchars($cane['razza']) ?>)
                                <form class="inline" method="POST" onsubmit="return confirm('Vuoi davvero rimuovere questo cane dal corso?');">
                                    <input type="hidden" name="rimuovi_iscrizione" value="<?= $cane['iscrizione_id'] ?>">
                                    <button type="submit" class="rimuovi">Rimuovi</button>
                                </form>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <p style="text-align: right; margin-top: 30px;"><a href="../logout.php">Logout</a></p>
</div>
</body>
</html>
