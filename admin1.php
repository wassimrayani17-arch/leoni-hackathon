<?php
// ============ CONNECT TO DATABASE ============
$host = "localhost";
$user = "root";
$password = "";
$dbname = "wordpress"; // Your DB name

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Collect messages for display in HTML
$messages = [];

// ================================================
// =============== ADD NEW ROW LOGIC ==============
// ================================================
if (isset($_POST['add_row'])) {

    // Escape all 11 inputs
    $col2  = $conn->real_escape_string($_POST['col2']);
    $col3  = $conn->real_escape_string($_POST['col3']);
    $col4  = $conn->real_escape_string($_POST['col4']);
    $col5  = $conn->real_escape_string($_POST['col5']);
    $col6  = $conn->real_escape_string($_POST['col6']);
    $col7  = $conn->real_escape_string($_POST['col7']);
    $col8  = $conn->real_escape_string($_POST['col8']);
    $col9  = $conn->real_escape_string($_POST['col9']);
    $col10 = $conn->real_escape_string($_POST['col10']);
    $col11 = $conn->real_escape_string($_POST['col11']);
    $col12 = $conn->real_escape_string($_POST['col12']);

    $sql = "INSERT INTO `base_de_donn__es_`
            (`COL 2`, `COL 3`, `COL 4`, `COL 5`, `COL 6`, `COL 7`, `COL 8`, `COL 9`, `COL 10`, `COL 11`, `COL 12`)
            VALUES
            ('$col2', '$col3', '$col4', '$col5', '$col6', '$col7', '$col8', '$col9', '$col10', '$col11', '$col12')";

    if ($conn->query($sql) === TRUE) {
        $messages[] = ['type' => 'success', 'text' => "New row added successfully!"];
    } else {
        $messages[] = ['type' => 'error', 'text' => "Error adding row: " . $conn->error];
    }
}

// =================================================
// ======== DELETE ROW BY 'NOM ET PRENOM' ==========
// =================================================
if (isset($_POST['delete_row'])) {

    $nameToDelete = $conn->real_escape_string($_POST['name_to_delete']);

    $sql = "DELETE FROM `base_de_donn__es_` WHERE `COL 2` = '$nameToDelete'";

    if ($conn->query($sql) === TRUE) {
        if ($conn->affected_rows > 0) {
            $messages[] = [
                'type' => 'success',
                'text' => "Row with name '$nameToDelete' deleted successfully!"
            ];
        } else {
            $messages[] = [
                'type' => 'warning',
                'text' => "No row found with that name."
            ];
        }
    } else {
        $messages[] = ['type' => 'error', 'text' => "Error deleting row: " . $conn->error];
    }
}

// =================================================
// =============== IMPORT CSV LOGIC ================
// =================================================
if (isset($_POST['upload_csv'])) {

    if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
        $messages[] = ['type' => 'error', 'text' => "Erreur lors de l'upload du fichier CSV."];
    } else {
        $tmpName = $_FILES['csv_file']['tmp_name'];

        if (($handle = fopen($tmpName, "r")) !== false) {

            $rowIndex     = 0;
            $insertedRows = 0;

            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                $rowIndex++;

                // Skip header line if present
                if ($rowIndex === 1 && !is_numeric($data[0])) {
                    continue;
                }

                // Expect at least 11 columns
                if (count($data) < 11) {
                    continue;
                }

                // Escape each column
                $col2  = $conn->real_escape_string($data[0]);
                $col3  = $conn->real_escape_string($data[1]);
                $col4  = $conn->real_escape_string($data[2]);
                $col5  = $conn->real_escape_string($data[3]);
                $col6  = $conn->real_escape_string($data[4]);
                $col7  = $conn->real_escape_string($data[5]);
                $col8  = $conn->real_escape_string($data[6]);
                $col9  = $conn->real_escape_string($data[7]);
                $col10 = $conn->real_escape_string($data[8]);
                $col11 = $conn->real_escape_string($data[9]);
                $col12 = $conn->real_escape_string($data[10]);

                $sql = "INSERT INTO `base_de_donn__es_`
                        (`COL 2`, `COL 3`, `COL 4`, `COL 5`, `COL 6`, `COL 7`, `COL 8`, `COL 9`, `COL 10`, `COL 11`, `COL 12`)
                        VALUES
                        ('$col2', '$col3', '$col4', '$col5', '$col6', '$col7', '$col8', '$col9', '$col10', '$col11', '$col12')";

                if ($conn->query($sql) === TRUE) {
                    $insertedRows++;
                }
            }

            fclose($handle);

            if ($insertedRows > 0) {
                $messages[] = ['type' => 'success', 'text' => "$insertedRows lignes importées depuis le CSV."];
            } else {
                $messages[] = ['type' => 'warning', 'text' => "Aucune ligne insérée depuis le CSV (format vide ou incorrect)."];
            }
        } else {
            $messages[] = ['type' => 'error', 'text' => "Impossible de lire le fichier CSV."];
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin 1 Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="assets/css/admin1.css">
</head>
<body>
<div class="page-wrapper">
    <header class="topbar">
        <h1 class="topbar-title">Admin 1 – Gestion des Employés</h1>
        <p class="topbar-subtitle">Ajoutez, importez ou supprimez des collaborateurs de la base.</p>
    </header>

    <?php foreach ($messages as $msg): ?>
        <div class="alert alert-<?= htmlspecialchars($msg['type']); ?>">
            <?= htmlspecialchars($msg['text'], ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endforeach; ?>

    <main class="admin-layout">
        <br>
        <!-- ========== IMPORT CSV (FIRST) ========== -->
        <section class="admin-column">
            <h2 class="section-title">Importer un CSV</h2>
            <p class="helper-text">
                Le fichier CSV doit contenir 11 colonnes : Nom et prénom, Ancienneté, Absence, Avance,
                Déduction 1–5, Salary range, Email.
            </p>

            <form method="POST" enctype="multipart/form-data" class="admin-form">
                <div class="field-group">
                    <label class="field-label">Fichier CSV</label>
                    <input type="file" name="csv_file" accept=".csv" class="input-field file-input" required>
                </div>

                <button type="submit" name="upload_csv" class="btn btn-secondary">Ajouter CSV</button>
            </form>
        </section>

        <!-- ========== ADD ROW (SECOND) ========== -->
        <section class="admin-column">
            <h2 class="section-title">Ajouter un employé (manuel)</h2>

            <form method="POST" class="admin-form">
                <div class="field-group">
                    <label class="field-label">Nom et Prénom</label>
                    <input type="text" name="col2" class="input-field" placeholder="NOM ET PRENOM" required>
                </div>

                <div class="field-group">
                    <label class="field-label">Ancienneté (AN)</label>
                    <input type="text" name="col3" class="input-field" placeholder="ANC / AN" required>
                </div>

                <div class="field-group">
                    <label class="field-label">Absence (Jours)</label>
                    <input type="text" name="col4" class="input-field" placeholder="ABS / JOUR" required>
                </div>

                <div class="field-group">
                    <label class="field-label">Avance</label>
                    <input type="text" name="col5" class="input-field" placeholder="AVANCE" required>
                </div>

                <div class="field-grid">
                    <div class="field-group">
                        <label class="field-label">Déduction 1</label>
                        <input type="text" name="col6" class="input-field" placeholder="déduction 1" required>
                    </div>
                    <div class="field-group">
                        <label class="field-label">Déduction 2</label>
                        <input type="text" name="col7" class="input-field" placeholder="déduction 2" required>
                    </div>
                    <div class="field-group">
                        <label class="field-label">Déduction 3</label>
                        <input type="text" name="col8" class="input-field" placeholder="déduction 3" required>
                    </div>
                    <div class="field-group">
                        <label class="field-label">Déduction 4</label>
                        <input type="text" name="col9" class="input-field" placeholder="déduction 4" required>
                    </div>
                    <div class="field-group">
                        <label class="field-label">Déduction 5</label>
                        <input type="text" name="col10" class="input-field" placeholder="déduction 5" required>
                    </div>
                </div>

                <div class="field-group">
                    <label class="field-label">Salary Range</label>
                    <input type="text" name="col11" class="input-field" placeholder="SALARY RANGE" required>
                </div>

                <div class="field-group">
                    <label class="field-label">E‑mail (optionnel)</label>
                    <input type="email" name="col12" class="input-field" placeholder="e-mail (optional)">
                </div>

                <button type="submit" name="add_row" class="btn btn-primary">Ajouter la ligne</button>
            </form>
        </section>

        <!-- ========== DELETE ROW (THIRD) ========== -->
        <section class="admin-column">
            <h2 class="section-title">Supprimer un employé</h2>
            <p class="helper-text">Suppression par <b>NOM ET PRENOM</b> (COL 2) dans la base.</p>

            <form method="POST" class="admin-form">
                <div class="field-group">
                    <label class="field-label">Nom et Prénom</label>
                    <input type="text" name="name_to_delete" class="input-field" placeholder="Entrer NOM ET PRENOM" required>
                </div>

                <button type="submit" name="delete_row" class="btn btn-danger">Supprimer la ligne</button>
            </form>
        </section>

        <!-- ========== PARAMÈTRES (FRONT-END ONLY) ========== -->
        <section class="admin-column">
            <h2 class="section-title">Paramètres globals</h2>


            <div class="admin-form">
                <div class="field-group">
                    <label class="field-label">Max Absence</label>
                    <input
                        type="number"
                        class="input-field"
                        value="5"
                        min="0"
                    >
                </div>

                <div class="field-group">
                    <label class="field-label">Min Ancienneté</label>
                    <input
                        type="number"
                        class="input-field"
                        value="3"
                        min="0"
                    >
                </div>
            </div>
        </section>

    </main>

</div>
</body>
</html>
