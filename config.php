<?php
defined('BASEPATH') OR exit('Access denied.');

/* EDIT HERE */
define('LOG_FILE', '/var/log/nginx/access.log');
define('DB_FILE', BASEPATH . 'access_log.db');

define('SERVER_IP', '125.212.250.72');
define('YOUR_IP', '1.53.194.96'); // Go to canyouseeme.org to get your IP

define('TIME_WINDOW', 60); // unit: second - a splited interval time that requests are grouped in. For example one minute have 5000 requests
define('THRESHOLD', 1000); // threshold requests number per $timewindow
define('SLEEP_TIME', 10); // unit: second - sleep between log check

// Whitelist
$exclude_ips = array(
  SERVER_IP,
  YOUR_IP,
  // Add more IP address here...
);

/* DO NOT EDIT HERE */
function validate($ip) {
  return filter_var($ip, FILTER_VALIDATE_IP);
}

if ( ! validate(SERVER_IP) OR ! validate(YOUR_IP)) {
  exit('Error: Please check config.php and run again.');
}

// Remove duplicate and invalid IP
$filtered_ip = array_filter(array_unique($exclude_ips), 'validate');

// Override the original array (run test.php to see how it works)
$exclude_ips = array();

function _push($ip) {
  global $exclude_ips;
  array_push($exclude_ips, $ip);
}

array_map('_push', $filtered_ip);
