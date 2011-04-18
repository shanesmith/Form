<?php
require_once dirname(__FILE__) . "/Form.class.php";

class FORM_VALIDATOR {

	public static $required = array("FORM_VALIDATOR", '_required');
	public static $email 		= array("FORM_VALIDATOR", '_email');


	public static function _required($value, $field) {
		return !empty($value);
	}

	public static function _email($value, $field) {
		$regex = "/[a-z0-9._%-]+@[a-z0-9.-]+\\.[a-z]{2,4}/i";

		return preg_match($regex, $value);
	}

}
