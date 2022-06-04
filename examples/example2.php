<?php

require_once __DIR__ . "/../vendor/autoload.php";

$doc = Phrelatorio\OpenDocument::loadXML('example2.fods');
$doc->saveXML('./out_example2.fods', ['title' => 'HOLA', 'items' => ['GUIX', 'PARABOLA', 'TRISQUEL']]);
