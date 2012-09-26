<?php

require_once('isloggedin.php');

$pass = $_GET["password"];
if (isset($pass)) {
    $hashpass = md5($pass);
	echo "<p><b>Hashed Password: </b><i>".$hashpass."</i></p>";
	echo "<p>Insert this into the <i>password</i> field in the <i>users</i> table in the <i>users.sqlite</i> database</p>";
}
else
	echo "<b>Clear text password value required in url: </b><i>generate_password.php?password=password123</i>";
?>
