<?php
include("config.php");
$source = new CSource(array('secure_dir' => '..', 'base_dir' => '..'));
$dupax['stylesheets'][] = "css/source.css";
$dupax['title'] = 'Visa källkod';
$dupax['main'] = "<h2>Visa källkod</h2>\n" . $source->view();
include(DUPAX_THEME_PATH);