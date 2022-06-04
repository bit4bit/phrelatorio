<?php

require_once __DIR__ . "/../vendor/autoload.php";

$doc = Phrelatorio\OpenDocument::loadXML('example4.fods');
$doc->saveXML('./out_example4.fods', ['title' => 'HOLA', 'items' => [['GUIX'], ['PARABOLA'], ['TRISQUEL']]]);
