<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
/* login 

/* Style all input fields */
input {
  width: 100%;
  padding: 12px;
  border: 1px solid #ccc;
  border-radius: 4px;
  box-sizing: border-box;
  margin-top: 6px;
  margin-bottom: 16px;
}

/* Style the submit button */
input[type=submit] {
  background-color: #04AA6D;
  color: white;
}

/* Style the container for inputs */
.container {
  background-color: #f1f1f1;
  padding: 20px;
}

/* The message box is shown when the user clicks on the password field */
#message {
  display:none;
  background: #f1f1f1;
  color: #000;
  position: relative;
  padding: 20px;
  margin-top: 10px;
}

#message p {
  padding: 10px 35px;
  font-size: 18px;
}

/* Add a green text color and a checkmark when the requirements are right */
.valid {
  color: green;
}

.valid:before {
  position: relative;
  left: -35px;
  content: "✔";
}

/* Add a red text color and an "x" when the requirements are wrong */
.invalid {
  color: red;
}

.invalid:before {
  position: relative;
  left: -35px;
  content: "✖";
}
</style>
</head>
<body>
<center><h1>ADMIN</h1></center>

<div class="container">
  <form method="POST" action="">
    <label for="usrname">Username</label>
    <input type="text" id="usrname" name="usrname" required>

    <label for="psw">Password</label>
    <input type="password" id="psw" name="psw"
           pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"
           title="Must contain at least one number, one uppercase and lowercase letter, and at least 8 characters"
           required>

    <input type="submit" value="Submit">
  </form>
</div>

<div id="message" style="display:none;">
  <h3>Password must contain the following:</h3>
  <p id="letter" class="invalid">A <b>lowercase</b> letter</p>
  <p id="capital" class="invalid">A <b>capital (uppercase)</b> letter</p>
  <p id="number" class="invalid">A <b>number</b></p>
  <p id="length" class="invalid">Minimum <b>8 characters</b></p>
</div>

<?php
// ================= LOGIN LOGIC ===================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $host = "localhost";
    $user = "root";
    $password = "";
    $dbname = "wordpress";

    $conn = new mysqli($host, $user, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Values from form
    $inputUser = $_POST['usrname'];
    $inputPass = $_POST['psw'];

    // Query using your real column names
    $sql = "SELECT * FROM admin_bd 
            WHERE `COL 1` = '$inputUser' AND `COL 2` = '$inputPass'";

    $result = $conn->query($sql);

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $role = $row["COL 3"];

        if ($role == 1) {
            header("Location: admin1.php");
            exit;
        }
        else if ($role == 2) {
            header("Location: admin2.php");
            exit;
        }
    } 
    else {
        echo "<p style='color:red; font-weight:bold;'>❌ Incorrect username or password</p>";
    }

    $conn->close();
}
?>

<script>
var myInput = document.getElementById("psw");
var letter = document.getElementById("letter");
var capital = document.getElementById("capital");
var number = document.getElementById("number");
var length = document.getElementById("length");

// Show message box on focus
myInput.onfocus = function() {
  document.getElementById("message").style.display = "block";
}

// Hide message box on blur
myInput.onblur = function() {
  document.getElementById("message").style.display = "none";
}

// Validate password
myInput.onkeyup = function() {
  let lower = /[a-z]/g;
  let upper = /[A-Z]/g;
  let nums = /[0-9]/g;

  // Lowercase
  if (myInput.value.match(lower)) {
    letter.classList.add("valid"); letter.classList.remove("invalid");
  } else {
    letter.classList.add("invalid"); letter.classList.remove("valid");
  }

  // Uppercase
  if (myInput.value.match(upper)) {
    capital.classList.add("valid"); capital.classList.remove("invalid");
  } else {
    capital.classList.add("invalid"); capital.classList.remove("valid");
  }

  // Numbers
  if (myInput.value.match(nums)) {
    number.classList.add("valid"); number.classList.remove("invalid");
  } else {
    number.classList.add("invalid"); number.classList.remove("valid");
  }

  // Length
  if (myInput.value.length >= 8) {
    length.classList.add("valid"); length.classList.remove("invalid");
  } else {
    length.classList.add("invalid"); length.classList.remove("valid");
  }
}
</script>
</body>

</html>
