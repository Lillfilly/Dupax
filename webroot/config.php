<?php
error_reporting(-1);
ini_set('display_errors', 1);
ini_set('output_buffering', 0);

define('DUPAX_INSTALL_PATH', __DIR__ . '/..');
define('DUPAX_THEME_PATH', DUPAX_INSTALL_PATH . '/theme/render.php');

include(DUPAX_INSTALL_PATH . '/src/bootstrap.php');

session_name(preg_replace('/[^a-z\d]/i', '', __DIR__));
session_start();

$dupax = array();

$dupax['lang'] = 'sv';
$dupax['title'] = "Dupax";
$dupax['title_append'] = '';
$dupax['database'] = null;

$dupax['favicon'] = null;
$dupax['stylesheets'] = array('css/style.css');

$dupax['modernizr'] = 'js/modernizr.js';
$dupax['jquery'] = '//ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js';
$dupax['javascript_include'] = array();

$dupax['google_analytics'] = NULL;

$menu = array(
    'home' => array('text'=>'Home', 'url'=>'index.php')
);
$menu = generateMenu($menu);
$dupax['header'] = <<<EOD
<h1>Dupax</h1>
{$menu}
<hr />
EOD;
$dupax['footer'] = <<<EOD
EOD;
