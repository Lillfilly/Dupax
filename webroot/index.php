<?php
include(__DIR__ . '/config.php');
$dupax['title'] = "Dupax";
$dupax['main'] = <<<EOD
<h2>Dupax</h2>
<p>
    This webpage is powered by Dupax
</p>
EOD;
include(DUPAX_THEME_PATH);