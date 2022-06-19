<?php

/**
 * This file is part of the bit4bit/phrelatorio library
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) Jovany Leandro G.C <bit4bit@riseup.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

declare(strict_types=1);

namespace Phrelatorio;

class OpenDocument
{
    private $template;

    public function save(string $pathxml, array $context): void
    {
        $out = $this->template->execute($context);
        \file_put_contents($pathxml, $out);
    }
    
    public static function loadFlatODT(string $pathxml): OpenDocument
    {
        $xmlstr = \file_get_contents($pathxml);
        if ($xmlstr === false) {
            throw new \InvalidArgumentException("can't read file {$pathxml}");
        }

        return new static($xmlstr);
    }

    private function __construct(string $xmlstr) {
        $this->template = OpenDocument\Template::fromString($xmlstr);
    }
}
