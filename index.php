<?php
    /*
        php?upload[]=<@file>&upload[]=<@file>&title=<log title>&map=<map name>&api=<api key>
    */
    public static function deleteDir($dirPath) {
        if (! is_dir($dirPath)) {
            throw new InvalidArgumentException("$dirPath must be a directory");
        }
        if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
            $dirPath .= '/';
        }
        $files = glob($dirPath . '*', GLOB_MARK);
        foreach ($files as $file) {
            if (is_dir($file)) {
                self::deleteDir($file);
            } else {
                unlink($file);
            }
        }
        rmdir($dirPath);
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

    $upload_urls = $_GET['upload'];
    $log_ids = array();
    foreach($upload_urls as $log_url) {
        $upload_url_parts = explode('/', $log_url);
        $upload_url_id = explode('#', end($upload_url_parts))[0];
        array_push($log_ids, $upload_url_id);
    }

    $storage_dir = str_replace(array('.', ':'), '-' , $USER_IP) . '/';
    $log_files = array();
    mkdir(substr($storage_dir, 0, -1));
    foreach($log_ids as $id) {
        $log_zip_dir = $storage_dir . $id . '_log.zip';
        file_put_contents($log_zip_dir, fopen('http://logs.tf/logs/log_' . $id . '.log.zip'));
        //EXAMPLE: ./255-255-255-0/1234567_log.zip
        $log_zip = new ZipArchive;
        $log_zip->open($log_zip_dir);
        $log_zip->extractTo($storage_dir);
        $log_zip->close();
        array_push($log_files, $storage_dir . 'log_' . $id . '.log');
    }
    //array for log file directories is stored in $log_files

    // TODO:                           //
    /* Combine both log files into one */
    //                                 //

    $API_KEY = $_GET['api'];
    $UPLOAD_URL = 'http://logs.tf/upload';
    $_title, $_map, $_key, $_logfile, $_uploader;

    $_title = $_GET['title'];
    $_map = $_GET['map'];
    $_key = $API_KEY;
    $_logfile = '@' . $storage_dir . 'LOG_FINAL.log';
    $_uploader = "Sharky Log Combiner v0.1";
    $upload_data = array(
        'title' => $_title,
        'map' => $_map,
        'key' => $_key,
        'logfile' => $_logfile,
        'uploader' => $_uploader
    );
    $upload_options = array(
        'http' => array(
            'header'  => "Content-type: multipart/form-data",
            'method'  => 'POST',
            'content' => http_build_query($upload_data)
        )
    );
    $upload_context  = stream_context_create($upload_options);
    $result = file_get_contents($UPLOAD_URL, false, $upload_context);
    /*
        JSON object containing:
        - (bool) success
        - (str) error
        - (int) log_id
        - (str) url
    */

    deleteDir($storage_dir);

?>
