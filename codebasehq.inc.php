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
    }

    function _get_request($full_path, $timeout = 300) {
        $url = $this->base_url() . $full_path;
        $cache_file = $this->cache_dir .'/'. sha1($url);
        if (!file_exists($cache_file) || ((time() - @filemtime($cache_file)) > $timeout)) {
            $process = curl_init($url);
            curl_setopt($process, CURLOPT_HTTPHEADER, array('Content-Type: application/xml', 'Accept: application/xml'));
            curl_setopt($process, CURLOPT_HEADER, 0);
            curl_setopt($process, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($process, CURLOPT_USERPWD, $this->account .'/'. $this->user_name . ":" . $this->api_key);
            curl_setopt($process, CURLOPT_TIMEOUT, 30);
            //curl_setopt($process, CURLOPT_POST, 1);
            //curl_setopt($process, CURLOPT_POSTFIELDS, $payloadName);
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
    
    function _parse_request($content) {
        return simplexml_load_string($content);
    }
    
    function _perform_request($full_path, $timeout = 300,$payload=false,$send=false) {
        if(!$send)
          $content = $this->_get_request($full_path, $timeout);
        else
        {
          $content = $this->_send_request($full_path, $payload, $timeout);
          return $content;
        }
        return $this->_parse_request($this->_get_request($full_path));
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

    function search_tickets($project, $query, $page = 1) {
        $params = 'query='. urlencode($query);
        if ($page > 1) {
            $params .= '&page='. $page;
        }
        return $this->_perform_request(sprintf('/%s/tickets?%s', $project, $params));        
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
