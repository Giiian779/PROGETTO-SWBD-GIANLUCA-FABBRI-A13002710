<?php
session_start();
require_once 'config.php';

// Controllo autenticazione
if (!isset($_SESSION['utente_id']) || $_SESSION['ruolo'] !== 'utente') {
    header("Location: login.php");
    exit;
}

$utente_id = $_SESSION['utente_id'];
$messaggio = '';
$errore = '';

// Aggiunta cane
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['azione'] === 'aggiungi_cane') {
    $nome = trim($_POST['nome']);
    $eta = intval($_POST['eta']);
    $razza = trim($_POST['razza']);

    if ($nome === '') {
        $errore = "Il nome del cane è obbligatorio.";
    } else {
        $stmt = $conn->prepare("INSERT INTO cani (nome, eta, razza, proprietario_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sisi", $nome, $eta, $razza, $utente_id);
        if ($stmt->execute()) {
            $messaggio = "Cane aggiunto con successo.";
        } else {
            $errore = "Errore nell'aggiunta del cane.";
        }
        $stmt->close();
    }
}

// Eliminazione cane
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['azione'] === 'elimina_cane') {
    $cane_id = intval($_POST['cane_id']);
    $stmt = $conn->prepare("DELETE FROM cani WHERE id = ? AND proprietario_id = ?");
    $stmt->bind_param("ii", $cane_id, $utente_id);
    if ($stmt->execute()) {
        $messaggio = "Cane eliminato con successo.";
    } else {
        $errore = "Errore durante l'eliminazione.";
    }
    $stmt->close();
}

// Richiesta di iscrizione
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['azione'] === 'richiedi_iscrizione') {
    $corso_id = intval($_POST['corso_id']);
    $cane_id = intval($_POST['cane_id']);

    $stmt = $conn->prepare("SELECT id FROM cani WHERE id = ? AND proprietario_id = ?");
    $stmt->bind_param("ii", $cane_id, $utente_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        $errore = "Cane non valido.";
    } else {
        $stmt->close();

        // Controlla se esiste già una richiesta o iscrizione
        $stmt2 = $conn->prepare("SELECT id FROM iscrizioni WHERE cane_id = ? AND corso_id = ?");
        $stmt2->bind_param("ii", $cane_id, $corso_id);
        $stmt2->execute();
        $stmt2->store_result();

        if ($stmt2->num_rows > 0) {
            $errore = "Hai già fatto richiesta per questo corso.";
        } else {
            $stato = 'in_attesa';
            $stmt3 = $conn->prepare("INSERT INTO iscrizioni (cane_id, corso_id, stato) VALUES (?, ?, ?)");
            $stmt3->bind_param("iis", $cane_id, $corso_id, $stato);
            if ($stmt3->execute()) {
                $messaggio = "Richiesta di iscrizione inviata.";
            } else {
                $errore = "Errore nell'invio della richiesta.";
            }
            $stmt3->close();
        }
        $stmt2->close();
    }
}

// Recupera i cani
$stmt = $conn->prepare("SELECT id, nome, razza, eta FROM cani WHERE proprietario_id = ?");
$stmt->bind_param("i", $utente_id);
$stmt->execute();
$cani = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Recupera corsi con giorni associati
$corsi = [];
$corsi_result = $conn->query("SELECT id, nome, descrizione, fascia_oraria FROM corsi");

while ($corso = $corsi_result->fetch_assoc()) {
    $corso_id = $corso['id'];

    // Recupera i giorni dalla tabella giorni_corso
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

    $corsi[] = $corso;
}

// Recupera iscrizioni in attesa o rifiutate
$stmt = $conn->prepare("
    SELECT i.id, c.nome AS cane_nome, co.nome AS corso_nome, co.fascia_oraria, i.stato, i.testo_notifica, co.id AS corso_id
    FROM iscrizioni i
    JOIN cani c ON i.cane_id = c.id
    JOIN corsi co ON i.corso_id = co.id
    WHERE c.proprietario_id = ?
    ORDER BY FIELD(i.stato, 'in_attesa', 'rifiutata', 'accettata'), i.id DESC
");
$stmt->bind_param("i", $utente_id);
$stmt->execute();
$iscrizioni_raw = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Per mostrare anche i giorni nei corsi delle iscrizioni:
$iscrizioni = [];
foreach ($iscrizioni_raw as $r) {
    // Recupera giorni del corso per ogni iscrizione
    $stmt_giorni = $conn->prepare("SELECT giorno FROM giorni_corso WHERE corso_id = ?");
    $stmt_giorni->bind_param("i", $r['corso_id']);
    $stmt_giorni->execute();
    $giorni_result = $stmt_giorni->get_result();

    $giorni = [];
    while ($row = $giorni_result->fetch_assoc()) {
        $giorni[] = $row['giorno'];
    }
    $stmt_giorni->close();

    $r['giorni'] = $giorni;
    $iscrizioni[] = $r;
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Area Utente</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f2f4f8;
            margin: 0;
            padding: 40px;
            color: #333;
        }

        h1 {
            text-align: center;
            margin-bottom: 30px;
            color: #222;
        }

        .nav {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 30px;
        }

        .nav button {
            padding: 10px 20px;
            font-size: 15px;
            background-color: #007bff;
            border: none;
            color: white;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .nav button.active,
        .nav button:hover {
            background-color: #0056b3;
        }

        .box {
            background: #fff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
            margin-bottom: 40px;
        }

        h2, h3 {
            margin-top: 0;
            color: #0056b3;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 10px 12px;
            text-align: left;
        }

        th {
            background-color: #f1f1f1;
            font-weight: 600;
        }

        input, select {
            width: 100%;
            padding: 8px 10px;
            margin: 6px 0;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 14px;
        }

        button {
            padding: 10px 16px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            font-size: 14px;
        }

        button:hover {
            background-color: #0056b3;
        }

        .btn-danger {
            background-color: #dc3545;
        }

        .btn-danger:hover {
            background-color: #a71d2a;
        }

        .success {
            color: green;
            text-align: center;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .error {
            color: red;
            text-align: center;
            font-weight: bold;
            margin-bottom: 10px;
        }

        form.inline {
            display: inline;
        }

        .hidden {
            display: none;
        }

        a {
            color: #007bff;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .text-right {
            text-align: right;
        }

        em {
            color: #777;
        }
    </style>
</head>
<body>

<h1>Area Utente</h1>


<form method="POST" action="logout.php" style="text-align: right; margin-bottom: 20px;">
    <button type="submit" style="background-color:#dc3545; color:#fff; border:none; padding:8px 16px; border-radius:6px; cursor:pointer;">
        Logout
    </button>
</form>

<?php if ($messaggio): ?><p class="success"><?= htmlspecialchars($messaggio) ?></p><?php endif; ?>
<?php if ($errore): ?><p class="error"><?= htmlspecialchars($errore) ?></p><?php endif; ?>

<!-- NAVIGATION BUTTONS -->
<div class="nav">
    <button class="tab-button active" data-target="gestione-cani">I tuoi cani</button>
    <button class="tab-button" data-target="corsi">Iscrizione corsi</button>
    <button class="tab-button" data-target="richieste">Le tue richieste</button>
</div>

<!-- GESTIONE CANI -->
<div class="box section" id="gestione-cani">
    <h2>I tuoi cani</h2>
    <?php if ($cani): ?>
        <table>
            <tr><th>Nome</th><th>Razza</th><th>Età</th><th>Azioni</th></tr>
            <?php foreach ($cani as $cane): ?>
                <tr>
                    <td><?= htmlspecialchars($cane['nome']) ?></td>
                    <td><?= htmlspecialchars($cane['razza']) ?></td>
                    <td><?= htmlspecialchars($cane['eta']) ?></td>
                    <td>
                        <form method="POST" onsubmit="return confirm('Eliminare questo cane?');" class="inline">
                            <input type="hidden" name="azione" value="elimina_cane">
                            <input type="hidden" name="cane_id" value="<?= $cane['id'] ?>">
                            <button type="submit" class="btn-danger">Elimina</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>Non hai ancora aggiunto cani.</p>
    <?php endif; ?>

    <h3>Aggiungi un nuovo cane</h3>
    <form method="POST">
        <input type="hidden" name="azione" value="aggiungi_cane">
        <label>Nome: <input type="text" name="nome" required></label><br>
        <label>Età: <input type="number" name="eta" min="0" required></label><br>
        <label>Razza: <input type="text" name="razza" required></label><br>
        <button type="submit">Aggiungi Cane</button>
    </form>
</div>

<!-- ISCRIZIONE CORSI -->
<div class="box section hidden" id="corsi">
    <h2>Corsi disponibili</h2>
    <?php if ($corsi): ?>
        <table>
            <tr><th>Nome</th><th>Descrizione</th><th>Giorni</th><th>Fascia Oraria</th><th>Iscrizione</th></tr>
            <?php foreach ($corsi as $corso): ?>
                <tr>
                    <td><?= htmlspecialchars($corso['nome']) ?></td>
                    <td><?= htmlspecialchars($corso['descrizione']) ?></td>
                    <td><?= htmlspecialchars(implode(', ', $corso['giorni'])) ?></td>
                    <td><?= htmlspecialchars($corso['fascia_oraria']) ?></td>
                    <td>
                        <?php if ($cani): ?>
                            <form method="POST" onsubmit="return confirm('Confermi la richiesta di iscrizione?');">
                                <input type="hidden" name="azione" value="richiedi_iscrizione">
                                <input type="hidden" name="corso_id" value="<?= $corso['id'] ?>">
                                <select name="cane_id" required>
    <option value="" disabled selected>Seleziona il cane</option>
    <?php
    // Trova cani non ancora iscritti né in attesa per questo corso
    $corso_id = $corso['id'];
    // Prepara un array con gli id cani da mostrare
    $cani_validi = [];

    // Query per ottenere cane_id iscritti o in attesa a questo corso
    $stmt_cani_iscritti = $conn->prepare("
        SELECT cane_id FROM iscrizioni
        WHERE corso_id = ? AND stato IN ('in_attesa', 'accettata')
    ");
    $stmt_cani_iscritti->bind_param("i", $corso_id);
    $stmt_cani_iscritti->execute();
    $result_cani_iscritti = $stmt_cani_iscritti->get_result();

    $cani_non_validi = [];
    while ($row = $result_cani_iscritti->fetch_assoc()) {
        $cani_non_validi[] = $row['cane_id'];
    }
    $stmt_cani_iscritti->close();

    foreach ($cani as $cane) {
        if (!in_array($cane['id'], $cani_non_validi)) {
            $cani_validi[] = $cane;
        }
    }

    foreach ($cani_validi as $cane_valido):
    ?>
        <option value="<?= $cane_valido['id'] ?>"><?= htmlspecialchars($cane_valido['nome']) ?></option>
    <?php endforeach; ?>
</select>

                                <button type="submit">Richiedi iscrizione</button>
                            </form>
                        <?php else: ?>
                            <em>Aggiungi prima un cane per iscriverti</em>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>Non ci sono corsi disponibili al momento.</p>
    <?php endif; ?>
</div>

<!-- RICHIESTE ISCRIZIONE -->
<div class="box section hidden" id="richieste">
    <h2>Le tue richieste di iscrizione</h2>
    <?php if ($iscrizioni): ?>
        <table>
            <tr>
                <th>Cane</th>
                <th>Corso</th>
                <th>Giorni</th>
                <th>Fascia Oraria</th>
                <th>Stato</th>
                <th>Notifica</th>
            </tr>
            <?php foreach ($iscrizioni as $iscrizione): ?>
                <tr>
                    <td><?= htmlspecialchars($iscrizione['cane_nome']) ?></td>
                    <td><?= htmlspecialchars($iscrizione['corso_nome']) ?></td>
                    <td><?= htmlspecialchars(implode(', ', $iscrizione['giorni'])) ?></td>
                    <td><?= htmlspecialchars($iscrizione['fascia_oraria']) ?></td>
                    <td>
                        <?php
                            $stato = $iscrizione['stato'];
                            if ($stato === 'in_attesa') echo "In attesa";
                            elseif ($stato === 'accettata') echo "Accettata";
                            elseif ($stato === 'rifiutata') echo "Rifiutata";
                            else echo htmlspecialchars($stato);
                        ?>
                    </td>
                    <td><?= nl2br(htmlspecialchars($iscrizione['testo_notifica'] ?: '-')) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>Non hai richieste di iscrizione.</p>
    <?php endif; ?>
</div>

<script>
    const buttons = document.querySelectorAll('.tab-button');
    const sections = document.querySelectorAll('.section');

    buttons.forEach(button => {
        button.addEventListener('click', () => {
            const target = button.dataset.target;

            buttons.forEach(btn => btn.classList.remove('active'));
            sections.forEach(section => section.classList.add('hidden'));

            document.getElementById(target).classList.remove('hidden');
            button.classList.add('active');
        });
    });
</script>

</body>
</html>
