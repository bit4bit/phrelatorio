<?php

require_once __DIR__ . "/../vendor/autoload.php";

$doc = Phrelatorio\OpenDocument::loadXML('example3.fods');
$doc->saveXML('./out_example3.fods', ['title' => 'HOLA', 'items' => ['GUIX', 'PARABOLA', 'TRISQUEL']]);
