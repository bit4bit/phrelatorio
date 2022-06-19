<?php

require_once __DIR__ . "/../vendor/autoload.php";

$doc = Phrelatorio\OpenDocument::loadFlatODT('example4.fods');
$doc->save('./out_example4.fods', ['title' => 'HOLA', 'items' => [['GUIX'], ['PARABOLA'], ['TRISQUEL']]]);
