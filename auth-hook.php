#!/usr/bin/php
<?php
require __DIR__.'/vendor/autoload.php';
$hook = new Hook();
$hook->challenge($_SERVER['CERTBOT_DOMAIN'], $_SERVER['CERTBOT_VALIDATION']);

