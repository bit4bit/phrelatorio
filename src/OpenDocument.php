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

use PHPTAL;

class OpenDocument
{
    private $doc;

    public function asXML(array $context): string
    {
        return $this->execute($context);
    }

    public function saveXML(string $pathxml, array $context): void
    {
        $out = $this->execute($context);
        \file_put_contents($pathxml, $out);
    }
    
    public static function loadXML(string $pathxml): OpenDocument
    {
        $xmlstr = \file_get_contents($pathxml);
        if ($xmlstr === false) {
            throw new \InvalidArgumentException("can't read file {$pathxml}");
        }

        return new self($xmlstr);
    }

    public static function fromString(string $xmlstr): OpenDocument
    {
        return new self($xmlstr);
    }

    public function __construct(string $xmlstr) {
        $this->doc = \UXML\UXML::fromString($xmlstr);

        // namespace para PHPTAL
        $this->doc->element()->ownerDocument->createElementNs('http://tal', 'tal');
        
        $this->talTransform();
    }

    private function sanitazeXML(string $xml): string
    {
        // MACHETE eliminar etiquetas temporales y reformatear xml
        $out = preg_replace('|</?.*phrelatorio.*>\n*|', '', $xml);

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($out);
        return $dom->saveXML();
    }

    private function extractTal($elem) {
        $href = urldecode($elem->element()->getAttribute('xlink:href'));
        if (preg_match('|phrelatorio://(?<talAction>[^ ]+) *(?<talArgument>.*)?$|', $href, $matchs) != 1) {
            throw new \Exception("invalid href ${href}: {$elem}");
        }
        $talAction = $matchs['talAction'];
        $talArgument = @$matchs['talArgument'];

        return [$talAction, $talArgument];
    }
    
    private function talTransform()
    {
        $elements = $this->doc->getAll('//text:a[starts-with(@*["href"=local-name()], "phrelatorio://")]');

        // argumentos para uso posterior
        $talArguments = [];
        $beginsFor = [];
        $endsFor = [];
        foreach ($elements as $elem) {
            [$talAction, $talArgument] = $this->extractTal($elem);
            
            $cell = $elem->parent()->parent();
            $talArguments[spl_object_hash($cell)] = $talArgument;
            
            switch($talAction) {
                
            case 'content':
                // a/p/container
                $elem->parent()->element()->setAttribute('tal:content', $talArgument);
                break;
            case 'for':
                $beginsFor[] = $cell;
                break;
            case '/for':
                $endsFor[] = $cell;
                break;
            }

            $elem->remove();
        }

        if (count($beginsFor) != count($endsFor)) {
            throw new \Exception('every open for must be close with /for');
        }

        // </a>..<a>
        foreach($endsFor as $endFor) {
            $beginFor = array_pop($beginsFor);

            // algoritmo extraido de relatorio
            $parentBeginFor = $beginFor->parent();
            $parentEndFor = $endFor->parent();
            $parentCommon = null;
            
            $levelsBeforeException = 5;
            while($levelsBeforeException -= 1 > 0) {
                // ubicamos el pariente en comun
                $idParentBegin = spl_object_hash($parentBeginFor);
                $idParentEnd = spl_object_hash($parentEndFor);
                if ($idParentBegin == $idParentEnd) {
                    $parentCommon = $parentBeginFor;
                    break;
                }

                $parentBeginFor = $parentBeginFor->parent();

                $parentEndFor = $parentEndFor->parent();
            }
            if ($parentCommon === null) {
                throw new \Exception("fails to found a common ancestor for ${beginFor} and ${endFor}");
            }

            // TODO adicionar tag para repetir elementos
            $parentCommonTag = $parentCommon->element()->localName;
            $talArgument = $talArguments[spl_object_hash($beginFor)];
            switch($parentCommonTag) {
            case 'table-row':
                $beginTableRow = $beginFor->get('./ancestor::table:table-row');
                $phrelatorio = $beginTableRow->add('tal:phrelatorio', '');
                $phrelatorio->element()->setAttribute('tal:repeat', trim($talArgument,'"'));
                
                $this->moveBetweenToNewParent($beginFor, $endFor, $phrelatorio);
                $beginFor->remove();
                $endFor->remove();
                break;
            case 'table':
                $beginTable = $beginFor->get('./ancestor::table:table');
                $phrelatorio = $beginTable->add('tal:phrelatorio', '');
                $phrelatorio->element()->setAttribute('tal:repeat', trim($talArgument,'"'));

                $beginTableRow = $beginFor->get('./ancestor::table:table-row');
                $endTableRow = $endFor->get('./ancestor::table:table-row');
                $this->moveBetweenToNewParent($beginTableRow, $endTableRow, $phrelatorio);

                $beginTableRow->remove();
                $endTableRow->remove();
                break;
            default:
                throw new \Exception("not known how to repeat for {$parentCommon}");
            }
        }
    }

    private function moveBetweenToNewParent($begin, $end, $parent): void
    {
        $beginDom = $begin->element();
        $endDom = $end->element();
        $parentDom = $parent->element();

        $fromBeginNextSibling = $beginDom->nextSibling;
        while( $fromBeginNextSibling !== null && spl_object_hash($fromBeginNextSibling) != spl_object_hash($endDom)) {
            $parentDom->appendChild($fromBeginNextSibling);
            $fromBeginNextSibling = $beginDom->nextSibling;
        }
    }

    private function execute(array $context): string
    {
        $tal = PHPTAL::create();
        $tal->setSource($this->doc->asXML());

        // TODO poblamos phtal, no tiene otra manera?
        foreach($context as $k => $v) {
            $tal->{$k} = $v;
        }

        $out = (string) $tal->execute();

        return $this->sanitazeXML($out);
    }
}
