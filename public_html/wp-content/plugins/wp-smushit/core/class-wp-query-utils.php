<?php

namespace Smush\Core;

class WP_Query_Utils {
	public function get_queried_object_id() {
		return get_queried_object_id();
	}

	public function is_singular() {
		return is_singular();
	}

	public function is_front_page() {
		return is_front_page();
	}

	public function is_home() {
		return is_home();
	}
}
