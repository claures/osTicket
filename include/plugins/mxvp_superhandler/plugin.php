<?php
set_include_path(get_include_path().PATH_SEPARATOR.dirname(__file__).'/include');
return array(
    'id' => 'mixvoip:superhandler',
    'version' => '1.0',
    'name' => 'Mixvoip Adaptions for osTicket',
    'author' => 'support@mixvoip.com',
    'description' => 'Mixvoip Adaptions for osTicket',
    'url' => 'http://mixvoip.com',
    'plugin' => 'class.SuperhandlerPlugin.php:SuperhandlerPlugin'
);