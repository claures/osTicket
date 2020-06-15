<?php

class MXVPTools{

    //curl Functions
    static function initCurl($url,$headers){
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_HEADER, 0);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,0);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,0);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch,CURLOPT_TIMEOUT, 10);
        curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
        return $ch;
    }

    static function curlExec($ch){
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

    static function doGet($url,$headers){
        $ch = self::initCurl($url,$headers);
        return self::curlExec($ch);
    }

    static function doPost($url,$postData,$headers){
        $ch = self::initCurl($url,$headers);
        curl_setopt($ch,CURLOPT_POST, 1);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$postData);
        return self::curlExec($ch);
    }

    static function replaceVariables($postEntry, $info){
        $url = HOST_PATH.'scp/tickets.php?id='.$info['mxvp_ticketID'];
        $search = array('%%USERMAIL%%','%%NAME%%','%%SUBJECT%%','%%BODY%%','%%TICKETID%%','%%TICKETNO%%','%%LINK%%','%%DEP%%');
        $replace = array($info['email'],$info['name'],$info['field.20'],$info['body']->body,$info['mxvp_ticketID'],$info['mxvp_ticketNO'],$url,$info['mxvp_depName']);
        return str_replace($search, $replace, $postEntry);
    }

}