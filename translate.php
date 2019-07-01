<?php
require_once ('googleTranslate/vendor/autoload.php');
use \Statickidz\GoogleTranslate;

$source = 'en';
$target = 'it';
$text = $argv[1];

$trans = new GoogleTranslate();
$result = $trans->translate($source, $target, $text);

file_put_contents("traduzione.txt", $result);

?>
