<?php
//Start session
session_start();
ini_set("display_errors","1");
ERROR_REPORTING(E_ALL);
//Then we retrieve the posted values for user and password.
$user = $_POST['user'];
$pass = $_POST['pass'];
$hashpass = md5($pass);

//Users defined in a SQLite database
$db = new PDO('sqlite:users.sqlite');
$result = $db->query("SELECT COUNT(*) AS count,api_key FROM users WHERE username = '$user' AND password = '$hashpass'");
$rows = $result->fetchAll();
if (count($rows) > 0) {
	 //If user and pass match any of the defined users
  $_SESSION['loggedin'] = true;
  $_SESSION['apikey'] = $rows[0]["api_key"];
	 header("Location: index.php");
};
 
//If the session variable is not true, exit to exit page.
if(!$_SESSION['loggedin']){
    header("Location: login.html");
    exit;
};
?>
