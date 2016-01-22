<?php

namespace Bekey\Bank\LemonWay;

class LemonWayError{

    function __construct($code, $msg) {
		$this->CODE = $code;
		$this->MSG = $msg;
	}

}
