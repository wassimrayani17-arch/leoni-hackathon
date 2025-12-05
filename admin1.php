<?php
// ============ CONNECT TO DATABASE ============
$host = "localhost";
$user = "root";
$password = "";
$dbname = "wordpress";

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ================= ADD ROW LOGIC ===================
if (isset($_POST['add_row'])) {

    // COL 1 is NOT inserted (as you requested)
    $col2 = $_POST['col2'];
    $col3 = $_POST['col3'];
    $col4 = $_POST['col4'];
    $col5 = $_POST['col5'];
    $col6 = $_POST['col6'];
    $col7 = $_POST['col7'];
    $col8 = $_POST['col8'];
    $col9 = $_POST['col9'];
    $col10 = $_POST['col10'];
    $col11 = $_POST['col11'];
    $col12 = $_POST['col12'];

    // Insert into table (COL 2 → COL 12)
    $sql = "
        INSERT INTO `base_de_donn__es_`
        (`COL 2`, `COL 3`, `COL 4`, `COL 5`, `COL 6`, `COL 7`, `COL 8`, `COL 9`, `COL 10`, `COL 11`, `COL 12`)
        VALUES
        ('$col2', '$col3', '$col4', '$col5', '$col6', '$col7', '$col8', '$col9', '$col10', '$col11', '$col12')
    ";

    if ($conn->query($sql) === TRUE) {
        echo "<p style='color:green; font-weight:bold;'>✔ New row added successfully!</p>";
    } else {
        echo "<p style='color:red; font-weight:bold;'>❌ Error adding row: " . $conn->error . "</p>";
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

<h2>Add New Row</h2>

<form method="POST">

    <label>COL 2:</label><br>
    <input type="text" name="col2" required><br><br>

    <label>COL 3:</label><br>
    <input type="text" name="col3" required><br><br>

    <label>COL 4:</label><br>
    <input type="text" name="col4" required><br><br>

    <label>COL 5:</label><br>
    <input type="text" name="col5" required><br><br>

    <label>COL 6:</label><br>
    <input type="text" name="col6" required><br><br>

    <label>COL 7:</label><br>
    <input type="text" name="col7" required><br><br>

    <label>COL 8:</label><br>
    <input type="text" name="col8" required><br><br>

    <label>COL 9:</label><br>
    <input type="text" name="col9" required><br><br>

    <label>COL 10:</label><br>
    <input type="text" name="col10" required><br><br>

    <label>COL 11:</label><br>
    <input type="text" name="col11" required><br><br>

    <label>COL 12:</label><br>
    <input type="text" name="col12" required><br><br>

    <button type="submit" name="add_row">Add Row</button>
</form>

</body>
</html>
