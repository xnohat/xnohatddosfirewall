<?php
defined('BASEPATH') OR exit('Access denied.');

$logfile = '/var/log/nginx/access.log';

$serverip    = '125.212.250.72';
$yourip      = '1.53.194.96';
$exclude_ips = array(
  $serverip,
  $yourip,
  '1.53.194.96'
);

$timewindow = 60; //unit: second - a splited interval time that requests are grouped in. For example one minute have 5000 requests
$threshold  = 1000; //threshold requests number per $timewindow
$sleeptime = 10; //unit: second - sleep between log check

?>