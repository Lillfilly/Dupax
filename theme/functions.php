<?php
function getTitle($title){
    global $dupax;
    return $title . (isset($dupax['title_append']) ? $dupax['title_append'] : NULL);
}
