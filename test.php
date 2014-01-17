<?php
error_reporting(-1);//report all errors
$invalid_utf8_char = chr(193);

ini_set('display_errors', 1);//display errors to standard output
var_dump(json_encode($invalid_utf8_char));
var_dump(error_get_last());//nothing

ini_set('display_errors', 0);//do not display errors to standard output
var_dump(json_encode($invalid_utf8_char));
var_dump(error_get_last());// json_encode(): Invalid UTF-8 sequence in argument
?>