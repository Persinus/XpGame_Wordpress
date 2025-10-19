<?php
namespace Symfony\Component\CssSelector\Parser;
if (!defined('ABSPATH')) exit;
use Symfony\Component\CssSelector\Node\SelectorNode;
interface ParserInterface
{
 public function parse(string $source): array;
}
