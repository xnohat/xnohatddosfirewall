<?php
define('BASEPATH', __DIR__ . DIRECTORY_SEPARATOR);
require_once BASEPATH . 'config.php';

main();

function main() {
  
  register_shutdown_function('fatal_handler');
  
  echo "\n\t---------------------------------\n
    \tXNOHAT DDoS FIREWALL\n
    \tVersion: 1.1\n
    \txnohat@gmail.com\n
    -----------------------------------\n
    ";
  
  $ipblocklist = array();
  
  //exec('iptables -F BLOCKEDIP'); //flush chain rules
  //exec("iptables-save | grep -v -- '-j BLOCKEDIP' | iptables-restore"); // delete all rules related to chain
  //exec('iptables -X BLOCKEDIP'); //delete chain
  exec('iptables -N BLOCKEDIP'); //create chain
  exec('iptables -A BLOCKEDIP -j LOG --log-level 4 --log-prefix \'blockedip\''); //LOG any packets go to this BLOCKEDIP chain
  exec('iptables -A BLOCKEDIP -j DROP'); //DROP any packets go to this BLOCKEDIP chain
  
  while (true) {
    
    try {
      
      //copy(DB_FILE,'access_log_for_analysis.db');
      
      $db = new SQLite3(DB_FILE);
      //$db = new SQLite3(':memory:');
      $db->query('PRAGMA synchronous = OFF');
      $db->query('PRAGMA journal_mode = MEMORY');
      $db->query('PRAGMA busy_timeout = 300000');
      
      //$res_count_request = $db->query('SELECT remote_ip, count(remote_ip) AS request_num FROM accesslog GROUP BY remote_ip ORDER BY request_num DESC'); //load all request in database is VERY SLOW
      $res_count_request = $db->query('SELECT remote_ip, count(remote_ip) AS request_num FROM accesslog WHERE request_time BETWEEN "' . @date("Y-m-d H:i:s", time() - TIME_WINDOW) . '" AND "' . @date("Y-m-d H:i:s", time()) . '" GROUP BY remote_ip ORDER BY request_num DESC');
      while ($row = $res_count_request->fetchArray()) {
        //print_r($row);
        if ($row['request_num'] >= THRESHOLD AND !in_array($row['remote_ip'], $exclude_ips)) {
          
          exec('iptables -A INPUT -s ' . $row['remote_ip'] . ' -j BLOCKEDIP');
          
          echo 'BLOCKED IP: ' . $row['remote_ip'] . ' (REQ_NUM: ' . $row['request_num'] . '/' . TIME_WINDOW . " seconds)\n";
          file_put_contents('blockedip.log', $row['remote_ip'] . '-' . $row['request_num'] . "\n", FILE_APPEND);
          file_put_contents('manualblockip.sh', 'iptables -A INPUT -s ' . $row['remote_ip'] . ' -j BLOCKEDIP' . "\n", FILE_APPEND);
          
          $ipblocklist[] = $row['remote_ip'];
        }
      }
      
      removeDuplicateIptablesRules();
      
      $db->close();
      
      echo 'SLEEP IN ' . SLEEP_TIME . "seconds\n";
      sleep(SLEEP_TIME);
      
    }
    catch (Exception $error) {
      //do nothing
    }
    
  }
  
}

function removeDuplicateIptablesRules() {
  exec('iptables-save', $arr_iptables_save_output);
  foreach ($arr_iptables_save_output as $k => $v) {
    if (($kt = array_search($v, $arr_iptables_save_output)) !== FALSE AND $k != $kt AND strpos($v, '-A') !== FALSE) {
      unset($arr_iptables_save_output[$kt]);
    }
  }
  
  $removed_duplicate_iptables_rules = implode("\n", $arr_iptables_save_output);
  file_put_contents('removed_duplicate_iptables_rules.txt', $removed_duplicate_iptables_rules);
  exec('iptables -F'); // Clear all rules
  exec('iptables-restore < removed_duplicate_iptables_rules.txt');
  echo "Removed Duplicate Rules from IPTables\n";
}

//function for fatal error case
function fatal_handler() {
  $errfile = 'unknown file';
  $errstr  = 'shutdown';
  $errno   = E_CORE_ERROR;
  $errline = 0;
  
  $error = error_get_last();
  
  if (!is_null($error)) {
    $errno   = $error['type'];
    $errfile = $error['file'];
    $errline = $error['line'];
    $errstr  = $error['message'];
    
    main();
  }
}

?>