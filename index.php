<?php
  require_once('isloggedin.php');
  require_once('utils.php')
?>
<!DOCTYPE html>
<html>
<head>
    <title>Brandregard - Kanban</title>
    <link rel="stylesheet" type="text/css" href="css/screen.css" />
    <link type="text/css" href="css/smoothness/jquery-ui-1.8.23.custom.css" rel="stylesheet" />
    <script type="text/javascript" src="js/jquery-1.8.0.min.js"></script>
    <script type="text/javascript" src="js/jquery-ui-1.8.23.custom.min.js"></script>
    <script src="js/jquery.qtip.min.js" type="text/javascript"></script>
    <script src="js/kanban.js"></script>

    <script type="text/javascript" charset="utf-8">
        <?
        include_once 'settings.php';
        $jsSettings = array('ciUrl' => $ciUrl);
        echo "project_name = '$codebaseMainProject';\n";
        echo "account_name = '$codebaseAccount';\n";
        ?>
    </script>
</head>
<body id="body">
    <div id="overview"></div>
    <div id="statuses">
		<div id="open"></div>
    <div id="closed">
      <div id="spacer" class="status">
      </div>
    </div>
  </div>

  <?php include('new_ticket.php'); ?>

</body>
</html>
