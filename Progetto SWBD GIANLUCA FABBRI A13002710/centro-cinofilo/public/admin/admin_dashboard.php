<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['utente_id']) || $_SESSION['ruolo'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$messaggio_corso = '';
$errore_corso = '';
$messaggio_assegnazione = '';
$errore_assegnazione = '';

// Funzione per validare i giorni
function giorni_validi($giorni) {
    $validi = ["Lunedì","Martedì","Mercoledì","Giovedì","Venerdì","Sabato","Domenica"];
    foreach ($giorni as $g) {
        if (!in_array($g, $validi)) return false;
    }
    return true;
}

// Aggiunta corso
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aggiungi_corso'])) {
    $nome = trim($_POST['nome']);
    $descrizione = trim($_POST['descrizione']);
    $fascia_eta = trim($_POST['fascia_eta']);
    $fascia_oraria = trim($_POST['fascia_oraria']);
    $istruttore_id = intval($_POST['istruttore_id']);
    $giorni = $_POST['giorni'] ?? [];

    if ($nome && $descrizione && $fascia_eta && $fascia_oraria && !empty($giorni) && giorni_validi($giorni)) {
        $stmt = $conn->prepare("INSERT INTO corsi (nome, descrizione, fascia_eta, fascia_oraria, istruttore_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssi", $nome, $descrizione, $fascia_eta, $fascia_oraria, $istruttore_id);
        if ($stmt->execute()) {
            $corso_id = $stmt->insert_id;
            $stmt->close();

            $stmt_giorni = $conn->prepare("INSERT INTO giorni_corso (corso_id, giorno) VALUES (?, ?)");
            foreach ($giorni as $giorno) {
                $stmt_giorni->bind_param("is", $corso_id, $giorno);
                $stmt_giorni->execute();
            }
            $stmt_giorni->close();

            $messaggio_corso = "Corso aggiunto con successo.";
        } else {
            $errore_corso = "Errore nell'aggiunta del corso: " . $stmt->error;
        }
    } else {
        $errore_corso = "Compila tutti i campi e seleziona almeno un giorno valido.";
    }
}

// Assegnazione istruttore
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assegna_istruttore'])) {
    $corso_id = intval($_POST['corso_id']);
    $istruttore_id = intval($_POST['istruttore_id']);

    $stmt = $conn->prepare("UPDATE corsi SET istruttore_id = ? WHERE id = ?");
    $stmt->bind_param("ii", $istruttore_id, $corso_id);
    if ($stmt->execute()) {
        $messaggio_assegnazione = "Istruttore assegnato con successo.";
    } else {
        $errore_assegnazione = "Errore: " . $stmt->error;
    }
    $stmt->close();
}

// Eliminazione corso
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['elimina_corso_id'])) {
    $corso_id = intval($_POST['elimina_corso_id']);

    // Elimina i giorni associati al corso
    $conn->query("DELETE FROM giorni_corso WHERE corso_id = $corso_id");

    // Elimina il corso
    $conn->query("DELETE FROM corsi WHERE id = $corso_id");
}

// Caricamento dati istruttori e corsi
$istruttori = [];
$sql = "SELECT * FROM utenti WHERE ruolo = 'istruttore'";
$result = $conn->query($sql);

while ($istruttore = $result->fetch_assoc()) {
    $istruttore_id = $istruttore['id'];
    $istruttore['corsi'] = [];

    $stmt = $conn->prepare("SELECT c.id, c.nome, c.fascia_eta, c.fascia_oraria FROM corsi c WHERE c.istruttore_id = ?");
    $stmt->bind_param("i", $istruttore_id);
    $stmt->execute();
    $corsi_result = $stmt->get_result();

    while ($corso = $corsi_result->fetch_assoc()) {
        $giorni_corso = [];
        $stmt_g = $conn->prepare("SELECT giorno FROM giorni_corso WHERE corso_id = ?");
        $stmt_g->bind_param("i", $corso['id']);
        $stmt_g->execute();
        $res_g = $stmt_g->get_result();
        while ($g = $res_g->fetch_assoc()) {
            $giorni_corso[] = $g['giorno'];
        }
        $stmt_g->close();
        $corso['giorni'] = $giorni_corso;

        $istruttore['corsi'][] = $corso;
    }

    $stmt->close();
    $istruttori[] = $istruttore;
}

$corsi = $conn->query("SELECT id, nome FROM corsi");
$istruttori_opzioni = $conn->query("SELECT id, nome, cognome FROM utenti WHERE ruolo = 'istruttore'");
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f9fafb;
            padding: 30px;
            color: #333;
        }
        .contenitore {
            max-width: 1200px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        h1, h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .bottone {
            display: inline-block;
            padding: 10px 18px;
            margin: 5px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            transition: background 0.2s;
        }
        .bottone:hover {
            background-color: #0056b3;
        }
        .form-box {
            background-color: #f1f1f1;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 30px;
        }
        label {
            display: block;
            margin-top: 15px;
            font-weight: 600;
        }
        input[type=text], select, textarea {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border-radius: 6px;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }
        button {
            margin-top: 20px;
            padding: 10px 18px;
            background-color: #28a745;
            border: none;
            color: white;
            border-radius: 6px;
            cursor: pointer;
        }
        button:hover {
            background-color: #218838;
        }
        .messaggio {
            color: green;
            text-align: center;
            font-weight: bold;
        }
        .errore {
            color: red;
            text-align: center;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
        }
        th, td {
            padding: 15px;
            border-bottom: 1px solid #ddd;
            text-align: left;
            vertical-align: top;
        }
        th {
            background-color: #f5f5f5;
        }
        .checkbox-group label {
            display: inline-block;
            margin-right: 15px;
            margin-top: 10px;
        }
        .btn-elimina {
            background-color: #dc3545;
            color: white;
            padding: 8px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 8px;
        }
        .btn-elimina:hover {
            background-color: #c82333;
        }
        ul {
            padding-left: 18px;
            margin: 0;
        }
    </style>
    <script>
        function confermaEliminazione() {
            return confirm('Sei sicuro di voler eliminare questo elemento?');
        }
    </script>
</head>
<body>

<div class="contenitore">
    <h1>Dashboard Amministratore</h1>

    <div style="text-align:right; margin-bottom:20px;">
        <a class="bottone" href="registra_istruttore.php">+ Aggiungi Istruttore</a>
        <a class="bottone" href="reports.php" style="background-color:#17a2b8;">📊 Report</a>
        <a class="bottone" href="../logout.php" style="background-color:#6c757d;">Logout</a>
    </div>

    <div class="form-box">
        <h2>Crea Nuovo Corso</h2>
        <?php if ($messaggio_corso): ?><p class="messaggio"><?= htmlspecialchars($messaggio_corso) ?></p><?php endif; ?>
        <?php if ($errore_corso): ?><p class="errore"><?= htmlspecialchars($errore_corso) ?></p><?php endif; ?>

                <form method="POST" action="">
            <label for="nome">Nome Corso</label>
            <input type="text" id="nome" name="nome" required>

            <label for="descrizione">Descrizione</label>
            <textarea id="descrizione" name="descrizione" rows="3" required></textarea>

            <label for="fascia_eta">Fascia Età</label>
            <input type="text" id="fascia_eta" name="fascia_eta" placeholder="Es. 2-5 anni" required>

            <label for="fascia_oraria">Fascia Oraria</label>
            <input type="text" id="fascia_oraria" name="fascia_oraria" placeholder="Es. 15:00-17:00" required>

            <label for="istruttore_id">Assegna Istruttore</label>
            <select id="istruttore_id" name="istruttore_id" required>
                <option value="" disabled selected>Seleziona un istruttore</option>
                <?php while ($istruttore_op = $istruttori_opzioni->fetch_assoc()): ?>
                    <option value="<?= $istruttore_op['id'] ?>"><?= htmlspecialchars($istruttore_op['nome'] . ' ' . $istruttore_op['cognome']) ?></option>
                <?php endwhile; ?>
            </select>

            <label>Giorni della settimana</label>
            <div class="checkbox-group">
                <?php 
                $giorni_settimana = ["Lunedì","Martedì","Mercoledì","Giovedì","Venerdì","Sabato","Domenica"];
                foreach ($giorni_settimana as $giorno): ?>
                    <label>
                        <input type="checkbox" name="giorni[]" value="<?= $giorno ?>"> <?= $giorno ?>
                    </label>
                <?php endforeach; ?>
            </div>

            <button type="submit" name="aggiungi_corso">Aggiungi Corso</button>
        </form>
    </div>

   <div class="form-box">
    <h2>Elenco Istruttori e Corsi</h2>

    <table>
        <thead>
            <tr>
                <th>Istruttore</th>
                <th>Corsi</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($istruttori as $istruttore): ?>
            <tr>
                <td><?= htmlspecialchars($istruttore['nome'] . ' ' . $istruttore['cognome']) ?></td>
                <td>
                    <?php if (count($istruttore['corsi']) === 0): ?>
                        <em>Nessun corso assegnato</em>
                    <?php else: ?>
                        <ul>
                        <?php foreach ($istruttore['corsi'] as $corso): ?>
                            <li>
                                <strong><?= htmlspecialchars($corso['nome']) ?></strong><br>
                                Fascia età: <?= htmlspecialchars($corso['fascia_eta']) ?><br>
                                Orario: <?= htmlspecialchars($corso['fascia_oraria']) ?><br>
                                Giorni: <?= htmlspecialchars(implode(", ", $corso['giorni'])) ?>
                                <form method="POST" style="display:inline-block; margin-left:10px;" onsubmit="return confermaEliminazione();">
                                    <input type="hidden" name="elimina_corso_id" value="<?= $corso['id'] ?>">
                                    <button type="submit" class="btn-elimina" title="Elimina Corso">🗑️</button>
                                </form>
                            </li>
                        <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>

