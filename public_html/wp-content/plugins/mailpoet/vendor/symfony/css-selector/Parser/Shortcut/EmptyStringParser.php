<?php
namespace Symfony\Component\CssSelector\Parser\Shortcut;
if (!defined('ABSPATH')) exit;
use Symfony\Component\CssSelector\Node\ElementNode;
use Symfony\Component\CssSelector\Node\SelectorNode;
use Symfony\Component\CssSelector\Parser\ParserInterface;
class EmptyStringParser implements ParserInterface
{
 public function parse(string $source): array
 {
 // Matches an empty string
 if ('' == $source) {
 return [new SelectorNode(new ElementNode(null, '*'))];
 }
 return [];
 }
}
