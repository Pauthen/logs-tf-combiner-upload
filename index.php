<?php

    /* Get json data for logs from urls first */

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
