<?php

require_once __DIR__ . "/../vendor/autoload.php";

$doc = Phrelatorio\OpenDocument::loadFlatODT('example2.fods');
$doc->save('./out_example2.fods', ['title' => 'HOLA', 'items' => ['GUIX', 'PARABOLA', 'TRISQUEL']]);
