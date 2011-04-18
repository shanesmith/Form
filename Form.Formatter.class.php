<?php
require_once dirname(__FILE__) . "/Form.class.php";

class FORM_FORMATTER {

	public static $trim = array("FORM_FORMATTER", "_trim");

	/**
	* Returns the trimmed string, according to arguments
	*
	* Default charlist is taken from php manual
	*
	* @param string $value
	* @param FORM_FIELD $element
	* @param boolean $left
	* @param boolean $right
	* @param string $charlist
	* @return string
	*/
	public static function _trim($value, $element, $left=true, $right=true, $charlist=null) {
		if (empty($charlist)) {
			$charlist = " \t\n\r\0\x0B";
		}

		if ($left && $right) {
			return trim($value, $charlist);
		}
		elseif ($left) {
			return ltrim($value, $charlist);
		}
		elseif ($right) {
			return rtrim($value, $charlist);
		}
		else {
			return $value;
		}
	}

}
