<?php
require_once('isloggedin.php');

$codebaseAccount = 'transmit';
$codebaseUser    = $_SESSION['user'];
$codebaseApikey  = $_SESSION['apikey'];
$codebaseMainProject = '';

$ciUrl = 'http://localhost';
