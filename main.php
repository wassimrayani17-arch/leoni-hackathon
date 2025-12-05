<?php
session_start();

$loginError = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $host     = "localhost";
    $user     = "root";
    $password = "";
    $dbname   = "wordpress";

    $conn = new mysqli($host, $user, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Values from form
    $inputUser = trim($_POST['usrname'] ?? "");
    $inputPass = trim($_POST['psw'] ?? "");

    // Safer query with prepared statement
    $stmt = $conn->prepare(
        "SELECT `COL 1`, `COL 2`, `COL 3`
         FROM `admin_bd`
         WHERE `COL 1` = ? AND `COL 2` = ?
         LIMIT 1"
    );
    $stmt->bind_param("ss", $inputUser, $inputPass);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $role = $row["COL 3"];

        if ($role == 1) {
            header("Location: admin1.php");
            exit;
        } elseif ($role == 2) {
            header("Location: admin2.php");
            exit;
        } else {
            $loginError = "Rôle administrateur inconnu.";
        }
    } else {
        $loginError = "Identifiant ou mot de passe incorrect.";
    }

    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Portail Administrateur LEONI</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- main.css must be in the same folder as main.php -->
    <link rel="stylesheet" href="assets/css/main.css">
</head>
<body>
<div class="page-wrapper">
    <div class="login-card">
        <h1 class="login-title">Espace Administrateur</h1>
        <p class="login-subtitle">Connectez-vous pour gérer la plateforme sociale digitale.</p>

        <?php if (!empty($loginError)): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($loginError, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" class="login-form">
            <label for="usrname" class="field-label">Nom d'utilisateur</label>
            <input type="text" id="usrname" name="usrname" class="input-field" required>

            <label for="psw" class="field-label">Mot de passe</label>
            <input
                type="password"
                id="psw"
                name="psw"
                class="input-field"
                pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"
                title="Au moins un chiffre, une lettre majuscule, une lettre minuscule et 8 caractères minimum."
                required
            >

            <button type="submit" class="btn-primary">Se connecter</button>
        </form>

        <div id="message" class="password-box">
            <h3>Le mot de passe doit contenir&nbsp;:</h3>
            <p id="letter" class="invalid">Une lettre <b>minuscule</b></p>
            <p id="capital" class="invalid">Une lettre <b>majuscule</b></p>
            <p id="number" class="invalid">Un <b>chiffre</b></p>
            <p id="length" class="invalid">Au minimum <b>8 caractères</b></p>
        </div>
    </div>
</div>

<script>
const myInput = document.getElementById("psw");
const letter  = document.getElementById("letter");
const capital = document.getElementById("capital");
const number  = document.getElementById("number");
const length  = document.getElementById("length");

// Show message box on focus
myInput.onfocus = function () {
    document.getElementById("message").style.display = "block";
};

// Hide on blur
myInput.onblur = function () {
    document.getElementById("message").style.display = "none";
};

// Validate password
myInput.onkeyup = function () {
    const lower = /[a-z]/g;
    const upper = /[A-Z]/g;
    const nums  = /[0-9]/g;

    // Lowercase
    if (myInput.value.match(lower)) {
        letter.classList.add("valid");
        letter.classList.remove("invalid");
    } else {
        letter.classList.add("invalid");
        letter.classList.remove("valid");
    }

    // Uppercase
    if (myInput.value.match(upper)) {
        capital.classList.add("valid");
        capital.classList.remove("invalid");
    } else {
        capital.classList.add("invalid");
        capital.classList.remove("valid");
    }

    // Numbers
    if (myInput.value.match(nums)) {
        number.classList.add("valid");
        number.classList.remove("invalid");
    } else {
        number.classList.add("invalid");
        number.classList.remove("valid");
    }

    // Length
    if (myInput.value.length >= 8) {
        length.classList.add("valid");
        length.classList.remove("invalid");
    } else {
        length.classList.add("invalid");
        length.classList.remove("valid");
    }
};
</script>
</body>
</html>
