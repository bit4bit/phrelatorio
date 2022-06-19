<?php

require_once __DIR__ . "/../vendor/autoload.php";

$doc = Phrelatorio\OpenDocument::loadFlatODT('example1.fods');
$doc->save('./out_example1.fods', ['title' => 'HOLA', 'items' => ['GUIX', 'PARABOLA', 'TRISQUEL']]);
