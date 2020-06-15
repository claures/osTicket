<?php
//DO NOT REMOVE VERSION OR PATH COMMENT. (Path is based on the OST Root)
//PATH:./
//VERSION:0.0

/**
 * Incomming webhook api for the MIXvoip Superhandler
 */
require_once('client.inc.php');

if(!SuperhandlerPlugin::$apiEnabled){
    die("API not enabled");
}

if(isset($_REQUEST['apiKey'])){
    if(!(isset(SuperhandlerPlugin::$apiKey) && SuperhandlerPlugin::$apiKey == $_REQUEST['apiKey'])){
        die("Invalid API key");
    }
}

if(!isset($_REQUEST['action'])){
    die('No Action defined');
}

echo 'Welcome';

//Back to the plugin
Signal::send('mxvp_apicalled',$data);