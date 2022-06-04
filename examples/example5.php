<?php

require_once __DIR__ . "/../vendor/autoload.php";

$doc = Phrelatorio\OpenDocument::loadXML('example5.fods');
$doc->saveXML('./out_example5.fods', ['show' => false, 'title' => 'HOLA', 'items' => [['GUIX'], ['PARABOLA'], ['TRISQUEL']]]);
