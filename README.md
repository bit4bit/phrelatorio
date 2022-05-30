# phrelatorio

build opendocument reports.

see **examples** for how to build reports.

~~~php

use Phrelatorio\OpenDocument as Phrelatorio;

$doc = Phrelatorio::loadXML('myods.fods');
$doc->saveXML('mynewods.fods', ...context...);

~~~
