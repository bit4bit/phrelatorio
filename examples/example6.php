<?php

require_once __DIR__ . "/../vendor/autoload.php";

$doc = Phrelatorio\OpenDocument::loadFlatODT('example6.fods');
$doc->save('./out_example6.fods', ['items' => [['GUIX'], ['PARABOLA'], ['TRISQUEL']]]);
