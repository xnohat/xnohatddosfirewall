<?php
defined('BASEPATH') OR exit('Access denied.');

/* EDIT HERE */
define('LOG_FILE', '/var/log/nginx/access.log');

define('SERVER_IP', '125.212.250.72');
define('YOUR_IP', '1.53.194.96');

define('TIME_WINDOW', 60); // unit: second - a splited interval time that requests are grouped in. For example one minute have 5000 requests
define('THRESHOLD', 1000); // threshold requests number per $timewindow
define('SLEEP_TIME', 10); // unit: second - sleep between log check

$exclude_ips = array(
  SERVER_IP,
  YOUR_IP,
  
  // Add more here...
);

?>