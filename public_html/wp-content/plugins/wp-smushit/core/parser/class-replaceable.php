<?php

namespace Smush\Core\Parser;

interface Replaceable {
	public function get_original();

	public function get_updated();

	public function get_position();

	public function has_updates();
}
