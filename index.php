<?php

    $_title, $_map, $_key, $_logfile, $_uploader;
    //multipart/form-data POST
    $API_KEY = '04b3e6f3772ea9669c25de77015a9b59';
    $UPLOAD_URL = 'http://logs.tf/upload';
    $upload_data = array('title' => $_title, 'map' => $_map, 'key' => $_key, 'logfile' => $_logfile, 'uploader' => $_uploader);
    $upload_options = array(
        'http' => array(
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($upload_data)
        )
    );
    $upload_context  = stream_context_create($upload_options);
    $result = file_get_contents($UPLOAD_URL, false, $upload_context);
    if ($result === FALSE) { /* Handle error */ }
    var_dump($result);

?>
