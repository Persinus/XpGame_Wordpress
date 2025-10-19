<?php

namespace Smush\Core\LCP;

class LCP_Data_Store_Home extends LCP_Data_Store_Option {
	const TYPE = 'home';

	public function __construct() {
		parent::__construct( self::TYPE );
	}
}
