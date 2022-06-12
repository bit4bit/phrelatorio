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
    const PHPTAL_ENABLE_BLOCKS = ['repeat', 'condition'];
    const PHPTAL_INLINE = ['content'];

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
        if (preg_match('|phrelatorio://(?<talOpened>/?)(?<talAction>[^ ]+) *(?<talArgument>.*)?$|', $href, $matchs) != 1) {
            throw new \Exception("invalid href ${href}: {$elem}");
        }

        $talAction = $matchs['talAction'];
        $talArgument = @$matchs['talArgument'];
        $talOpened = @$matchs['talOpened'] != '/';
        return [$talOpened, $talAction, $talArgument];
    }
    
    private function talTransform()
    {
        $elements = $this->doc->getAll('//text:a[starts-with(@*["href"=local-name()], "phrelatorio://")]');

        // argumentos para uso posterior
        $talArguments = [];
        $beginsBlock = [];
        $blocks = []; # begin => end

        // construimos la relacion begin => end,
        // usando beginsBlock como una pila
        foreach ($elements as $elem) {
            [$talOpened, $talAction, $talArgument] = $this->extractTal($elem);
            
            $cell = $elem->parent()->parent();
            $talArguments[spl_object_hash($cell)] = $talArgument;

            if (in_array($talAction, self::PHPTAL_INLINE)) {
                $inTable = $elem->parent()->parent()->element()->localName == 'table-cell';

                if ($inTable) {
                    $elem->parent()->element()->setAttribute("tal:{$talAction}", $talArgument);
                } else {
                    // invertimos xlink:a/text:a 
                    $span = $elem->get('./text:span');
                    $span->element()->setAttribute("tal:{$talAction}", $talArgument);
                    $elem->parent()->element()->insertBefore($span->element(), $elem->element());
                }

                $elem->remove();
            } else if (in_array($talAction, self::PHPTAL_ENABLE_BLOCKS)) {
                if ($talOpened) {
                    \array_push($beginsBlock, $cell);
                } else {
                    $beginBlock = \array_pop($beginsBlock);
                    $endBlock = $cell;
                    $parentCommon = $this->findParentCommon($beginBlock, $endBlock);
                    if ($parentCommon === null) {
                        throw new \Exception("can't detect common parent");
                    }
                    $blocks[] = [$talAction, $parentCommon, $beginBlock, $endBlock];
                }
            }

            $elem->remove();
        }

        // </a>..<a>
        foreach($blocks as $match) {
            [$talAction, $parentCommon, $beginBlock, $endBlock] = $match;
            // TODO adicionar tag para repetir elementos
            $parentCommonTag = $parentCommon->element()->localName;
            $talArgument = $talArguments[spl_object_hash($beginBlock)];

            switch($parentCommonTag) {
            case 'table-row':
                $phrelatorio = \UXML\UXML::newInstance('tal:phrelatorio', null, [], $this->doc->element()->ownerDocument);
                $phrelatorio->element()->setAttribute("tal:{$talAction}", trim($talArgument,'"'));

                $beginBlock->parent()->element()->insertBefore($phrelatorio->element(), $beginBlock->element());
                
                $this->moveBetweenToNewParent($beginBlock, $endBlock, $phrelatorio);
                $beginBlock->remove();
                $endBlock->remove();
                break;
            case 'table':
                $phrelatorio = \UXML\UXML::newInstance('tal:phrelatorio', null, [], $this->doc->element()->ownerDocument);
                $phrelatorio->element()->setAttribute("tal:{$talAction}", trim($talArgument,'"'));

                $beginBlock->parent()->parent()->element()->insertBefore($phrelatorio->element(), $beginBlock->parent()->element());

                $beginTableRow = $beginBlock->get('./ancestor::table:table-row');
                $endTableRow = $endBlock->get('./ancestor::table:table-row');
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

    private function objectHash(&$obj): string
    {
        return spl_object_hash($obj);
    }

    private function findParentCommon($beginFor, $endFor) {
        // algoritmo extraido de relatorio
        $parentBeginFor = $beginFor->parent();
        $parentEndFor = $endFor->parent();
        $parentCommon = null;
        
        $levelsBeforeGiveOut = 3;
        while($levelsBeforeGiveOut -= 1 > 0) {
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

        return $parentCommon;
    }
}
