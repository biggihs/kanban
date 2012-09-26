<?php
  require_once('isloggedin.php');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Brandregard - Kanban</title>
    <script src="js/jquery.min.js"></script>
    <script src="js/jquery-ui-1.8.23.custom.min.js"></script>
    <script src="js/jquery.qtip.min.js" type="text/javascript"></script>
    <script src="js/kanban.js"></script>
    <link rel="stylesheet" type="text/css" href="css/screen.css" />
    <script type="text/javascript" charset="utf-8">
        <?
        include_once 'settings.php';
        $jsSettings = array('ciUrl' => $ciUrl);
        echo "project_name = '$codebaseMainProject';\n";
        echo "account_name = '$codebaseAccount';\n";
        ?>
    </script>
</head>
<body>
    <div id="overview"></div>
    <div id="statuses">
		<div id="open"></div>
		<div id="closed"></div>
	</div>
</body>
</html>
