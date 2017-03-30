<?php
$exclude_ips = array(
  'abc',
  '127.0.0.1',
  '127.0.0.1',
  '1.53.194.96'
);

function validate($ip) {
  return filter_var($ip, FILTER_VALIDATE_IP);
}

$filtered_ip = array_filter(array_unique($exclude_ips), 'validate');
$exclude_ips = array();

function _push($ip) {
  global $exclude_ips;
  array_push($exclude_ips, $ip);
}

array_map('_push', $filtered_ip);

print_r($exclude_ips);

// ===== Output =====
// Array
// (
//     [0] => 127.0.0.1
//     [1] => 1.53.194.96
// )
