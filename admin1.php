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


// ================================================
// =============== ADD NEW ROW LOGIC ===============
// ================================================
if (isset($_POST['add_row'])) {

    // Escape all 11 inputs
    $col2 = $conn->real_escape_string($_POST['col2']);
    $col3 = $conn->real_escape_string($_POST['col3']);
    $col4 = $conn->real_escape_string($_POST['col4']);
    $col5 = $conn->real_escape_string($_POST['col5']);
    $col6 = $conn->real_escape_string($_POST['col6']);
    $col7 = $conn->real_escape_string($_POST['col7']);
    $col8 = $conn->real_escape_string($_POST['col8']);
    $col9 = $conn->real_escape_string($_POST['col9']);
    $col10 = $conn->real_escape_string($_POST['col10']);
    $col11 = $conn->real_escape_string($_POST['col11']);
    $col12 = $conn->real_escape_string($_POST['col12']);

    // Insert (ignoring COL 1 — auto increment or ID)
    $sql = "INSERT INTO `base_de_donn__es_`
            (`COL 2`, `COL 3`, `COL 4`, `COL 5`, `COL 6`, `COL 7`, `COL 8`, `COL 9`, `COL 10`, `COL 11`, `COL 12`)
            VALUES
            ('$col2', '$col3', '$col4', '$col5', '$col6', '$col7', '$col8', '$col9', '$col10', '$col11', '$col12')";

    if ($conn->query($sql) === TRUE) {
        echo "<p style='color:green; font-weight:bold;'>✔ New row added successfully!</p>";
    } else {
        echo "<p style='color:red; font-weight:bold;'>❌ Error adding row: " . $conn->error . "</p>";
    }
}



// =================================================
// ======== DELETE ROW BY 'NOM ET PRENOM' ==========
// =================================================
if (isset($_POST['delete_row'])) {

    $nameToDelete = $conn->real_escape_string($_POST['name_to_delete']);

    // Column NOM ET PRENOM = COL 2
    $sql = "DELETE FROM `base_de_donn__es_` WHERE `COL 2` = '$nameToDelete'";

    if ($conn->query($sql) === TRUE) {
        if ($conn->affected_rows > 0) {
            echo "<p style='color:green; font-weight:bold;'>✔ Row with name '$nameToDelete' deleted successfully!</p>";
        } else {
            echo "<p style='color:red; font-weight:bold;'>⚠ No row found with that name.</p>";
        }
    } else {
        echo "<p style='color:red; font-weight:bold;'>❌ Error deleting row: " . $conn->error . "</p>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin 1 Panel</title>
</head>
<body>
<h1>Welcome Admin 1</h1>


<!-- =========================================== -->
<!-- =========== ADD ROW FORM ================== -->
<!-- =========================================== -->
<h2>Add New Row</h2>

<form method="POST">
    <input type="text" name="col2" placeholder="NOM ET PRENOM" required><br>
    <input type="text" name="col3" placeholder="ANC / AN" required><br>
    <input type="text" name="col4" placeholder="ABS / JOUR" required><br>
    <input type="text" name="col5" placeholder="AVANCE" required><br>
    <input type="text" name="col6" placeholder="deduction 1" required><br>
    <input type="text" name="col7" placeholder="deduction 2" required><br>
    <input type="text" name="col8" placeholder="deduction 3" required><br>
    <input type="text" name="col9" placeholder="deduction 4" required><br>
    <input type="text" name="col10" placeholder="deduction 5" required><br>
    <input type="text" name="col11" placeholder="SALARY RANGE" required><br>
    <input type="text" name="col12" placeholder="e-mail (optional)" ><br><br>

    <button type="submit" name="add_row">Add Row</button>
</form>



<!-- =========================================== -->
<!-- =========== DELETE ROW FORM =============== -->
<!-- =========================================== -->
<h2>Delete Row</h2>

<form method="POST">
    <input type="text" name="name_to_delete" placeholder="Enter NOM ET PRENOM" required>
    <button type="submit" name="delete_row">Delete Row</button>
</form>

</body>
</html>
