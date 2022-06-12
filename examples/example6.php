<?php

require_once __DIR__ . "/../vendor/autoload.php";

$doc = Phrelatorio\OpenDocument::loadXML('example6.fods');
$doc->saveXML('./out_example6.fods', ['items' => [['GUIX'], ['PARABOLA'], ['TRISQUEL']]]);
