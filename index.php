<?php

    $upload_urls = $_GET['upload'];
    $log_ids = array();
    $log_json_objects = array();
    foreach($upload_urls as $log_url) {
        $upload_url_parts = explode('/', $log_url);
        $upload_url_id = explode('#', end($upload_url_parts))[0];
        array_push($log_ids, $upload_url_id);
    }
    foreach($log_ids as $id) {
        $log_json_obj = file_get_contents("http://logs.tf/json/$id");
        array_push($log_json_objects, $log_json_obj);
    }
    //all json data from every log is now stored in $log_json_objects

    // TODO:                                       //
    /* Combine all the data into one log file here */
    //                                             //

    $API_KEY = '_API_KEY_';
    $UPLOAD_URL = 'http://logs.tf/upload';
    $_title, $_map, $_key, $_logfile, $_uploader;

    //multipart/form-data POST
    $_title = '_TITLE_';
    $_map = '_MAP_FROM_JSON_DATA_';
    $_key = $API_KEY;
    $_logfile = '_GENERATED_LOG_FILE_';
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

?>
