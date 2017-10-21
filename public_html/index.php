<?php
//print_r($_GET);exit;
// Pfade
ini_set('display_errors', 0);
//error_reporting(E_ALL);
$project = str_replace($_SERVER['DOCUMENT_ROOT'], '', str_replace("\\", "/",__DIR__));
(!isset($_SERVER['HTTPS']) OR $_SERVER['HTTPS'] == 'off') ? $protocol = 'http://' : $protocol = 'https://';
$protocol = 'https://';
$protocol = (!isset($_SERVER['HTTPS']) OR $_SERVER['HTTPS'] == 'off') ? $protocol = 'http://' : $protocol = 'https://';
define('HTTP_ROOT', $protocol.$_SERVER['HTTP_HOST'].$project.'/');


// Initialisierung
require_once '../core/init.php';