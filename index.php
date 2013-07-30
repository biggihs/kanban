<?php
  require_once('isloggedin.php');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Brandregard - Kanban</title>
    <link rel="stylesheet" type="text/css" href="css/screen.css" />
    <link type="text/css" href="css/smoothness/jquery-ui-1.8.23.custom.css" rel="stylesheet" />
    <link type="text/css" href="css/jquery.jui_dropdown.css" rel="stylesheet" />
    <script type="text/javascript" src="js/jquery-1.8.0.min.js"></script>
    <script type="text/javascript" src="js/jquery-ui-1.8.23.custom.min.js"></script>
    <script type="text/javascript" src="js/jquery-jui_dropdown.min.js"></script>
    <script src="js/jquery.qtip.min.js" type="text/javascript"></script>
    <script src="js/kanban.js"></script>

    <script type="text/javascript" charset="utf-8">
        <?
        include_once 'settings.php';
        $jsSettings = array('ciUrl' => $ciUrl);
        echo "project_name = '$codebaseMainProject';\n";
        echo "account_name = '$codebaseAccount';\n";

        if($_GET['milestone-numb'])
          $_SESSION['milestone-numb'] = $_GET['milestone-numb'];
  
        if(!$_SESSION['milestone-numb'])
          echo "current_milestone_id = 0;\n";
        else
          echo "current_milestone_id = ".$_SESSION['milestone-numb'].";\n";

        echo "if(current_milestone_id == -1){";
        echo "current_milestone_id = 0;";
        echo "all_milestones_selected = true;";
        echo "}else{all_milestones_selected = false}";
        ?>
    </script>
</head>
<body id="body">
    <div id="overview"></div>
    <div id="milestone_selection">
      <h4>Current Milestone : <select></select></h4>
    </div>
    <div id="statuses">
		<div id="open"></div>
    <div id="closed">
      <div id="spacer" class="status">
      </div>
    </div>
  </div>

  <?php include('new_ticket.php'); ?>

  <script>
    $(document).ready(function(){
      $('#milestone_selection').on('change','select',function(){
        milestone_numb = $(this).find(':selected').attr('milestone-numb')
        window.location = "?milestone-numb="+milestone_numb
      })
    })
  </script>
</body>
</html>
