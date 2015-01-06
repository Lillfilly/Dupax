<?php
function generateMenu($items, $class = NULL) {
    $html = "<nav" . (isset($class) ? " class=\"{$class}\"" : "") . ">\n";
    foreach($items as $item){
	$selected = (basename($_SERVER['PHP_SELF']) == basename($item['url']) ? "class=\"selected\"" : NULL);
	$html .= "<a href='{$item['url']}' {$selected}>{$item['text']}</a>\n";
    }
    $html .= "</nav>\n";
    return $html;
}
function dump($a, $return = false){
    $text = '<pre>' . htmlentities(print_r($a, true)) . '</pre>';
    if($return)
	return $text;
    else
	echo $text;
}
function myExHandler($e){
    echo "Dupax: Uncaught exception: <p>" . $e->getMessage() . "</p><pre>" . $e->getTraceAsString() . "</pre>";
}
/**
 * Use the current querystring as base, modify it according to $options and return the modified query string.
 *
 * @param array $options to set/change.
 * @param string $prepend this to the resulting query string
 * @return string with an updated query string.
 */
function getQueryString($options=array(), $prepend='?') {
    // parse query string into array
    $query = array();
    parse_str($_SERVER['QUERY_STRING'], $query);

    // Modify the existing query string with new options
    $query = array_merge($query, $options);

    // Return the modified querystring
    return $prepend . htmlentities(http_build_query($query));
}
/**
 * Create a slug of a string, to be used as url.
 *
 * @param string $str the string to format as slug.
 * @returns str the formatted slug. 
 */
function slugify($str) {
  $str = mb_strtolower(trim($str));
  $str = str_replace(array('å','ä','ö'), array('a','a','o'), $str);
  $str = preg_replace('/[^a-z0-9-]/', '-', $str);
  $str = trim(preg_replace('/-+/', '-', $str), '-');
  return $str;
}

function myAutoloader($class){
    $path = DUPAX_INSTALL_PATH . "/src/{$class}/{$class}.php";
    if(is_file($path)){
	include($path);
    }
    else{
	throw new Exception("Classfile '{$class}' does not exist.");
    }
}

set_exception_handler('myExHandler');
spl_autoload_register('myAutoloader');