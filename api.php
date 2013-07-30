<?php

require_once('isloggedin.php');
include_once 'settings.php';
include_once 'codebasehq.inc.php';

$cb = new CodeBaseHQAPI($codebaseAccount, $codebaseUser, $codebaseApikey);

$what = $_GET['f'];

if ($what == 'categories') {
    $categories = $cb->get_categories($codebaseMainProject);
    echo json_encode($categories);
}

if ($what == 'milestones') {
    $milestones = $cb->get_milestones($codebaseMainProject);
    echo json_encode($milestones);
}

if ($what == 'statuses') {
    $statuses = $cb->get_statuses($codebaseMainProject);
    echo json_encode($statuses);
}

if ($what == 'priorities') {
    $priorities = $cb->get_priorities($codebaseMainProject);
    echo json_encode($priorities);    
}

if ($what == 'users') {
    $users = $cb->get_users($codebaseMainProject);
    foreach($users->user as $user) {
        $f = "email-address";
        $user->hash = md5(strtolower(trim($user->$f)));
    }
    echo json_encode($users);
}

if ($what == 'tickets') {
    $status = isset($_GET['s']) ? $_GET['s'] : 'open';
    if($_GET['q'])
      $tickets = $cb->search_tickets($codebaseMainProject, sprintf('sort:priority status:%s milestone:"%s"', $status, $_GET['q']), $_GET['p']);
    else
      $tickets = $cb->search_tickets($codebaseMainProject, sprintf('sort:priority status:%s', $status), $_GET['p']);
    echo json_encode($tickets);
}

if ($what == 'create_ticket'){
    $summary       = $_GET['summary'];
    $description   = $_GET['description'];
    $ticket_type   = $_GET['ticket_type'];
    $assignee_id   = $_GET['assignee_id'];
    $category_id   = $_GET['category_id'];
    $priority_id   = $_GET['priority_id'];
    $status_id     = $_GET['status_id'];
    $milestone_id  = $_GET['milestone_id'];

    //I want to figure out who this user is. Not parse the reporter_id.
//    $user_id =$cb->get_current_user_id($codebaseMainProject,$api_key);

    $resp = $cb->create_new_ticket( $codebaseMainProject,
                                    $summary,
                                    $description,
                                    $ticket_type,
                                    $assignee_id,
                                    $category_id,
                                    $priority_id,
                                    $status_id,
                                    $milestone_id);
    echo json_encode($resp);
}

if ($what == 'update_ticket'){
    $ticket = isset($_GET['ticket_id']) ? $_GET['ticket_id'] : '-1';
    $status = isset($_GET['status_id']) ? $_GET['status_id'] : '-1';
    if(($ticket == -1)||($status == -1))
      json_encode(array('success'=>false));
    else
    {
      $cb->change_ticket_status($codebaseMainProject, $ticket, $status);
    }
}

if ($what == 'assign_ticket'){
    $ticket = isset($_GET['ticket_id']) ? $_GET['ticket_id'] : '-1';
    $user = isset($_GET['user_id']) ? $_GET['user_id'] : '-1';
    if(($ticket_id == -1)||($user_id == -1))
      json_encode(array('success'=>false));
    else
    {
      $cb->change_ticket_assign($codebaseMainProject, $ticket,$user);
    }
}
