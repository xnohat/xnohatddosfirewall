<?php

echo "\n\t---------------------------------\n
	\tXNOHAT DDoS FIREWALL\n
	\tVersion: 1.1\n
	\txnohat@gmail.com\n
	-----------------------------------\n
	";

$serverip = '125.212.250.72';
$yourip = '1.53.194.96';
$exclude_ips = array($serverip,
					$yourip,

					'1.53.194.96',

				);

$timewindow = 60; //unit: second - a splited interval time that requests are grouped in. For example one minute have 5000 requests
$threshold = 5000; //threshold requests number per $timewindow
$ipblocklist = array();

while(true){

	exec('iptables -F BLOCKEDIP'); //flush chain rules
	exec("iptables-save | grep -v -- '-j BLOCKEDIP' | iptables-restore"); // delete all rules related to chain
	exec('iptables -X BLOCKEDIP'); //delete chain
	exec('iptables -N BLOCKEDIP'); //create chain
	exec("iptables -A BLOCKEDIP -j LOG --log-level 4 --log-prefix 'blockedip'"); //LOG any packets go to this BLOCKEDIP chain
	exec('iptables -A BLOCKEDIP -j DROP'); //DROP any packets go to this BLOCKEDIP chain

	//copy('access_log.db','access_log_for_analysis.db');

	$db = new SQLite3('access_log.db');
	//$db = new SQLite3(':memory:');
	$db->query("PRAGMA synchronous = OFF");
	$db->query("PRAGMA journal_mode = MEMORY");
	$db->query("PRAGMA busy_timeout = 300000");

	//$res_count_request = $db->query('SELECT remote_ip, count(remote_ip) AS request_num FROM accesslog GROUP BY remote_ip ORDER BY request_num DESC'); //load all request in database is VERY SLOW
	$res_count_request = $db->query('SELECT remote_ip, count(remote_ip) AS request_num FROM accesslog WHERE request_time BETWEEN "'.@date("Y-m-d H:i:s", time()-$timewindow).'" AND "'.@date("Y-m-d H:i:s", time()).'" GROUP BY remote_ip ORDER BY request_num DESC');
	while ($row = $res_count_request->fetchArray()) {
	    //print_r($row);
	    if($row['request_num'] >= $threshold AND !in_array($row['remote_ip'],$exclude_ips)){
	    	
	    	exec('iptables -A INPUT -s '.$row['remote_ip'].' -j BLOCKEDIP');

	    	echo 'BLOCKED IP: '.$row['remote_ip'].' (REQ_NUM: '.$row['request_num'].")\n";
	    	file_put_contents('blockedip.log',$row['remote_ip'].'-'.$row['request_num']."\n",FILE_APPEND);
	    	
	    	$ipblocklist[] = $row['remote_ip'];
	    }
	}

	$db->close();

	echo "SLEEP IN $timewindow seconds\n";
	sleep($timewindow);

}

?>