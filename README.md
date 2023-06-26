# phrelatorio

build reports using opendocument editor (like libreoffice/abiwork/gnumeric).

see **examples** for how to build reports.

~~~php

use Phrelatorio\OpenDocument as Relatorio;

$doc = Relatorio::loadFlatODT('myods.fods');
$doc->save('mynewods.fods', ...context...);

~~~

# Limitation

- only support Flat OpenDocument

# Todo

- [x] allow php expressions
- [ ] inject images
- [ ] work with .odt and .ods

# Contributing

1. `rake dev:up`
2. `rake dev:init`
3. `rake tdd`
4. `rake dev:down`

# Thanks

* [PHPTAL](https://phptal.org/)

# Inspired by

* [relatorio](https://hg.tryton.org/relatorio/file/tip/README)
