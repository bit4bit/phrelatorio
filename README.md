# phrelatorio

build reports using opendocument editor.

see **examples** for how to build reports.

~~~php

use Phrelatorio\OpenDocument as Relatorio;

$doc = Relatorio::loadXML('myods.fods');
$doc->saveXML('mynewods.fods', ...context...);

~~~

# Thanks

* [PHPTAL](https://phptal.org/)

# Inspired by

* [relatorio](https://hg.tryton.org/relatorio/file/tip/README)
