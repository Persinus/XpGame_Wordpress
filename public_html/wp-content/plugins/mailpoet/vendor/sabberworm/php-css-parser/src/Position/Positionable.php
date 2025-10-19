<?php
declare(strict_types=1);
namespace Sabberworm\CSS\Position;
if (!defined('ABSPATH')) exit;
interface Positionable
{
 public function getLineNumber();
 public function getLineNo();
 public function getColumnNumber();
 public function getColNo();
 public function setPosition($lineNumber, $columnNumber = null);
}
