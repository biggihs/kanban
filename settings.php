<?php
require_once('isloggedin.php');

$codebaseAccount = 'transmit';
$codebaseUser    = $_SESSION['user'];
$codebaseApikey  = $_SESSION['apikey'];
//$codebaseMainProject = 'londoncru';
$codebaseMainProject = 'webweka-doodle';

$ciUrl = 'http://localhost';
