<?php
    /*
        php?upload[]=<log url>&upload[]=<log url>&title=<log title>&map=<map name>&api=<api key>
    */
    header("Access-Control-Allow-Origin: *");
    error_reporting(0);
    function url_exists($url) {
        if (!$fp = curl_init($url)) return false;
        return true;
    }
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

	if(!isset($_POST['api'])) {
		exit('{"error": "Missing api field.", "success": false}');
	}
	if(!isset($_POST['title'])) {
		exit('{"error": "Missing title field.", "success": false}');
	}
	if(!isset($_POST['map'])) {
		exit('{"error": "Missing map field.", "success": false}');
	}
	if(!isset($_POST['upload'])) {
		exit('{"error": "Missing upload[] field.", "success": false}');
	}

    $upload_urls = $_POST['upload'];
	if(!is_array($upload_urls)) {
		exit('{"error": "Upload[] field must contain an array of valid urls.", "success": false}');
	}
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
	if(!url_exists('http://logs.tf/logs/log_' . $id . '.log.zip')) {
		exit('{"error": "Invalid log url submitted.", "success": false}');
	}
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

    $post = array(
        'title' => $_POST['title'],
        'map' => $_POST['map'],
        'key' => $_POST['api'],
        'logfile' => curl_file_create($storage_dir . 'LOG_FINAL.log'),
        'uploader' => "Sharky's Logify v1.3"
    );

    $ch = curl_init( $UPLOAD_URL );
	$ch_set = array(
		CURLOPT_POST => 1,
		CURLOPT_POSTFIELDS => $post,
		CURLOPT_FOLLOWLOCATION => 1,
		CURLOPT_HEADER => 0,
		CURLOPT_RETURNTRANSFER => 1
	);
	curl_setopt_array($ch, $ch_set);
    $response = curl_exec( $ch );
    if(!$response) {
		exit('{"error": "Log does not exist!", "success": false}');
    }
    echo($response);

    $ffiles = glob($storage_dir . '*');
    foreach($ffiles as $ffile) {
       unlink($ffile);
    }

?>
