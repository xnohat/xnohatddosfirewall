<?php

main();

register_shutdown_function( "fatal_handler" );

function main(){
    $logfile = '/var/log/nginx/access.log';

    unlink('access_log.db');

    $db = new SQLite3('access_log.db');
    //$db = new SQLite3(':memory:');
    $db->query("PRAGMA synchronous = OFF");
    $db->query("PRAGMA journal_mode = MEMORY");
    $db->query("PRAGMA busy_timeout = 300000");

    //Check log table exist or not
    $res_check_table_exist = $db->query("SELECT * FROM sqlite_master WHERE name = 'accesslog' and type='table' ");
    if(!$res_check_table_exist->fetchArray()){ // Not have table accesslog
        //echo "Table Not exist";
        //Create Table accesslog
        $db->exec('CREATE TABLE accesslog (remote_ip varchar(255), request_time NUMERIC)');
        $db->exec("CREATE INDEX accesslog_index ON accesslog(remote_ip,request_time)");
    	echo "Table accesslog has been created \r\n";
    }

    //Follow Log
    $size = filesize($logfile); //set to current file size to move disk read cursor to end of file

    while (true) {

        clearstatcache();
        $currentSize = filesize($logfile);
        if ($size == $currentSize) {
            usleep(100);
            continue;
        }

        $fh = fopen($logfile, "r");
        fseek($fh, $size);

        while ($line = fgets($fh)) {

            // process the line read.
            if($line <> ''){            
                //-----Clear wasted character-----
                $clear_char = array('[',']');
                $line = str_replace($clear_char,'',$line); //strip special chars

                //-----Parse Log Line-----
                $arr_log_line = explode(' ',$line);
                //var_dump($arr_log_line);continue;
                $remote_ip = $arr_log_line[0];
                $request_time = @date("Y-m-d H:i:s", @strtotime(str_replace('/', '-', substr_replace($arr_log_line[3], ' ', -9,1)))); //original nginx time look like 05/Nov/2016:01:35:24 , remember change for apache , SQLite format must look like 2016-11-05 01:35:24
                $db->exec('INSERT INTO accesslog (remote_ip, request_time) VALUES ("'.$remote_ip.'","'.$request_time.'")'); //insert request to DB
                //echo $remote_ip.' - '.$request_time."\r\n";
                echo $line;
            }

        }

        fclose($fh);
        $size = $currentSize;
    }

    $db->close();
}
//function for fatal error case
function fatal_handler() {
  $errfile = "unknown file";
  $errstr  = "shutdown";
  $errno   = E_CORE_ERROR;
  $errline = 0;

  $error = error_get_last();

  if( $error !== NULL) {
    $errno   = $error["type"];
    $errfile = $error["file"];
    $errline = $error["line"];
    $errstr  = $error["message"];

    main();
  }
} 

?>