<?php
    /*
        php?upload[]=<log url>&upload[]=<log url>&title=<log title>&map=<map name>&api=<api key>
    */
    header("Access-Control-Allow-Origin: *");
    function getIP() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        }
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }

    $USER_IP = getIP();

    $upload_urls = $_POST['upload'];
    $log_ids = array();
    foreach($upload_urls as $log_url) {
        $upload_url_parts = explode('/', $log_url);
        $upload_url_id_array = explode('#', end($upload_url_parts));
	$upload_url_id = $upload_url_id_array[0];
        array_push($log_ids, $upload_url_id);
    }

    $storage_dir = str_replace(array('.', ':'), '-' , $USER_IP) . '/';
    $log_files = array();
    if(!file_exists(substr($storage_dir, 0, -1))) {
        mkdir(substr($storage_dir, 0, -1));
    }
    foreach($log_ids as $id) {
        $log_zip_dir = $storage_dir . $id . '_log.zip';
        file_put_contents($log_zip_dir, fopen('http://logs.tf/logs/log_' . $id . '.log.zip', 'r'));
        //EXAMPLE: ./255-255-255-0/1234567_log.zip
        $log_zip = new ZipArchive;
        $log_zip->open($log_zip_dir);
        $log_zip->extractTo($storage_dir);
        $log_zip->close();
        array_push($log_files, $storage_dir . 'log_' . $id . '.log');
    }
    //array for log file directories is stored in $log_files

    $final_log = fopen($storage_dir . 'LOG_FINAL.log', 'a+');
	foreach($log_files as $f) {
		$l = file_get_contents($f) . "\n";
		fwrite($final_log, $l);
	}
	fclose($final_log);

    $UPLOAD_URL = 'http://logs.tf/upload';
    $_title; $_map; $_key; $_logfile; $_uploader;

    $_title = $_POST['title'];
    $_map = $_POST['map'];
    $_key = $_POST['api'];
    $_logfile = curl_file_create($storage_dir . 'LOG_FINAL.log');  //'@' . $storage_dir . 'LOG_FINAL.log'
    $_uploader = "Sharky Log Combiner v0.1";

    $post = array(
        'title' => $_title,
        'map' => $_map,
        'key' => $_key,
        'logfile' => $_logfile,
        'uploader' => $_uploader
    );

    $ch = curl_init( $UPLOAD_URL );
    curl_setopt( $ch, CURLOPT_POST, 1);
    curl_setopt( $ch, CURLOPT_POSTFIELDS, $post);
    curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt( $ch, CURLOPT_HEADER, 0);
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);

    $response = curl_exec( $ch );
    /*
        JSON object containing:
        - (bool) success
        - (str) error
        - (int) log_id
        - (str) url
    */
    echo($response);

    $ffiles = glob($storage_dir . '*');
    foreach($ffiles as $ffile) {
       unlink($ffile);
    }

?>
