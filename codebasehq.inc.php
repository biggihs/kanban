<?php
require_once('isloggedin.php');

class CodebaseHQAPI {
    function __construct($account, $user_name, $api_key) {
        $this->account = $account;
        $this->user_name = $user_name;
        $this->api_key = $api_key;
        $this->cache_dir = './cache'; #no slash!
    }
    
    function base_url() {
        return 'http://api3.codebasehq.com';
    }

    function _send_request($full_path, $payload, $timeout = 300){
      $url = $this->base_url() . $full_path;
      $cache_file = $this->cache_dir .'/'. sha1($url);
      if (!file_exists($cache_file) || ((time() - @filemtime($cache_file)) > $timeout)) {
        $process = curl_init($url);
//        curl_setopt($process, CURLOPT_HTTPHEADER, array('Content-Type: application/xml', 'Accept: */*'));
        curl_setopt($process, CURLOPT_HEADER, 0);
        curl_setopt($process, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($process, CURLOPT_USERPWD, $this->account .'/'. $this->user_name . ":" . $this->api_key);
        curl_setopt($process, CURLOPT_TIMEOUT, 30);
        curl_setopt($process, CURLOPT_POST, TRUE);
        curl_setopt($process, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
        $return = curl_exec($process);
        $f = fopen($cache_file, 'w');
        fwrite($f, $return);
        fclose($f);
      } else {
        $return = file_get_contents($cache_file);
      }
      return $return;
    }

    function _get_request($full_path, $timeout = 300,$many_pages = false){
        $url = $this->base_url() . $full_path;
        $cache_file = $this->cache_dir .'/'. sha1($url);
        if (!file_exists($cache_file) || ((time() - @filemtime($cache_file)) > $timeout)) {
            $return = "";
            if(strpos($url,'?'))
              $url_with_page_key = $url . "&page=PAGENUMBER";
            else
              $url_with_page_key = $url . "?page=PAGENUMBER";
            for($i=1;$i<=10;$i++) //get maximum 10 pages
            {
                $url_with_page = str_replace("PAGENUMBER",$i,$url_with_page_key);
                $process = curl_init($url_with_page);
                curl_setopt($process, CURLOPT_HTTPHEADER, array('Content-Type: application/xml', 'Accept: application/xml'));
                curl_setopt($process, CURLOPT_HEADER, 0);
                curl_setopt($process, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                curl_setopt($process, CURLOPT_USERPWD, $this->account .'/'. $this->user_name . ":" . $this->api_key);
                curl_setopt($process, CURLOPT_TIMEOUT, 30);
                //curl_setopt($process, CURLOPT_POST, 1);
                //curl_setopt($process, CURLOPT_POSTFIELDS, $payloadName);
                curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
                $content = curl_exec($process);
                $response = curl_getinfo($process);

                if($response["http_code"] == 404)
                  break; //There are no more pages if there is a 404 response
                elseif($response["http_code"] == 200)
                {
                  if(!$many_pages)
                  {
                    $return = simplexml_load_string($content);
                    break;
                  }
                  else
                  {
                    $xml = simplexml_load_string($content);
                    if($i == 1)
                      $return = $xml;
                    else
                    {
                      foreach($xml->ticket as $ticket)
                      {
                        $node = $return->addChild($ticket->getName());
                        foreach($ticket->children() as $child)
                          $node->addChild($child->getName(),$child);
                      }
                    }
                  }
                }
            }

            $f = fopen($cache_file, 'w');
            fwrite($f, $return);
            fclose($f);
        } else {
            $return = file_get_contents($cache_file);
        }
        return $return;
    }
    
    function _parse_request($content) {
        return simplexml_load_string($content);
    }
    
    function _perform_request($full_path, $timeout = 300,$payload=false,$send=false,$many_pages=false){
      if(!$send)
      {
          //$content = $this->_get_request($full_path, $timeout);
          return $this->_get_request($full_path,$timeout,$many_pages);
      }
      else
      {
          $content = $this->_send_request($full_path, $payload, $timeout);
          return $content;
      }
    }
    
    function get_statuses($project) {
        return $this->_perform_request(sprintf('/%s/tickets/statuses', $project), 86400);
    }

    function get_priorities($project) {
        return $this->_perform_request(sprintf('/%s/tickets/priorities', $project), 86400);
    }

    function get_categories($project) {
        return $this->_perform_request(sprintf('/%s/tickets/categories', $project), 86400);
    }

    function get_milestones($project) {
        return $this->_perform_request(sprintf('/%s/milestones', $project), 86400);
    }

    function get_users($project) {
        return $this->_perform_request(sprintf('/%s/assignments', $project), 86400);
    }

    function search_tickets($project, $query) {
        $params = 'query='. urlencode($query);
        return $this->_perform_request(sprintf('/%s/tickets?%s', $project, $params),86400,false,false,true);
    }

    function get_current_user_id($project,$api_key)
    {
      if(!$_SESSION['current_user_id'])
      {
        $users = $this->_perform_request(sprintf('/users', $project));
        $id = -1;
        foreach($users->user as $user)
        {
          $a = (array)$user;
          if($a['api-key'] == $_SESSION['apikey'])
          {
            $id = $a['id'];
            $_SESSION['current_user_id'] = $id;
            break;
          }
        }
        return $id;
      }
      else
      {
        return $_SESSION['current_user_id'];
      }
    }

    function create_new_ticket($project,$summary,$description,$ticket_type,$assignee_id,$category_id,$priority_id,$status_id,$milestone_id)
    {
      $payload = array(
            'ticket[summary]'=>$summary,
        #'ticket[description]'=>"<![CDATA[$description]]",
        'ticket[description]'=>"<![CDATA[$description]]",
        'ticket[ticket_type]'=>$ticket_type,
        'ticket[reporter_id]'=>$this->get_current_user_id($project,$api_key),
        'ticket[assignee_id]'=>$assignee_id,
        'ticket[category_id]'=>$category_id,
        'ticket[priority_id]'=>$priority_id,
          'ticket[status_id]'=>$status_id,
       'ticket[milestone_id]'=>$milestone_id,
      );
      return $this->_perform_request(sprintf('/%s/tickets', $project), 86400,$payload ,true);
    }


    function change_ticket_status($project, $ticket, $status)
    {
      $payload = array( 
        'ticket_note[changes][status_id]'=>$status,
        'ticket_note[content]'=>'Update From Kanban!',
      );
      return $this->_perform_request(sprintf('/%s/tickets/%s/notes', $project,$ticket), 86400,$payload ,true);
    }
    function change_ticket_assign($project, $ticket, $user)
    {
      $payload = array( 
        'ticket_note[changes][assignee_id]'=>$user,
        'ticket_note[content]'=>'Update From Kanban!',
      );
      return $this->_perform_request(sprintf('/%s/tickets/%s/notes', $project,$ticket), 86400,$payload ,true);
    }
}
