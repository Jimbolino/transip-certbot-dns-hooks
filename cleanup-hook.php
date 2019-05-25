#!/usr/bin/php
<?php
require __DIR__.'/vendor/autoload.php';
$hook = new Hook();
$hook->cleanup($_SERVER['CERTBOT_DOMAIN'], $_SERVER['CERTBOT_VALIDATION']);
