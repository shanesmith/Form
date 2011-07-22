<?php
require_once dirname(__FILE__) . "/Form.class.php";

class FORM_VALIDATOR {

	/*****************
	 **  CALLABLES  **
	 *****************/

	/**
	 * Negates a given validator
	 *
	 * @var array
	 */
	public static $not = array("FORM_VALIDATOR", "_not");

	/**
	 * Value must be a valid email
	 *
	 * @var array
	 */
	public static $email = array("FORM_VALIDATOR", '_email');

	/**
	 * Value must be between min and max values (inclusive)
	 *
	 * @var array
	 */
	public static $range = array("FORM_VALIDATOR", "_range");

	/**
	 * Value must not be more than max
	 *
	 * @var array
	 */
	public static $max = array("FORM_VALIDATOR", "_max");

	/**
	 * Value must not be less than min
	 *
	 * @var array
	 */
	public static $min = array("FORM_VALIDATOR", "_min");

	/**
	 * Value must be an integer (a whole number)
	 *
	 * @var array
	 */
	public static $integer = array("FORM_VALIDATOR", "_integer");

	/**
	 * Value must be a decimal number with at least min_decimal decimal positions
	 * and no more than max_decimal.
	 *
	 * If max_decimal is null then there is no upper limit.
	 *
	 * @var array
	 */
	public static $decimal = array("FORM_VALIDATOR", "_decimal");

	/**
	 * Value must be a number (integer or decimal)
	 *
	 * @var array
	 */
	public static $number = array("FORM_VALIDATOR", "_number");

	/**
	 * Value must be made of only alphanumeric characters (and whitespaces if allow_whitespaces)
	 *
	 * @var array
	 */
	public static $alphanum = array("FORM_VALIDATOR", "_alphanum");

	/**
	 * Value must be made of only alphabetic characters (and whitespaces if allow_whitespace)
	 *
	 * @var array
	 */
	public static $alpha = array("FORM_VALIDATOR", "_alpha");

	/**
	 * Value must be between min_length and max_length characters long
	 *
	 * @var array
	 */
	public static $length = array( "FORM_VALIDATOR", "_length" );

	/**
	 * Value must be no more than max_length characters long
	 *
	 * @var array
	 */
	public static $maxlength = array( "FORM_VALIDATOR", "_maxlength" );

	/**
	 * Value must be no less than min_length characters long
	 *
	 * @var array
	 */
	public static $minlength = array( "FORM_VALIDATOR", "_minlength" );

	/**
	 * Value must match given regex
	 *
	 * @var array
	 */
	public static $regex = array( "FORM_VALIDATOR", "_regex" );

	/**
	 * Value must be between min_lines and max_lines number of lines (inclusive)
	 *
	 * @var array
	 */
	public static $lines = array( "FORM_VALIDATOR", "_lines" );

	/**
	 * Value must be no more than max_lines number of lines
	 *
	 * @var array
	 */
	public static $maxlines = array( "FORM_VALIDATOR", "_maxlines" );

	/**
	 * Value must be no less than min_lines number of lines
	 *
	 * @var array
	 */
	public static $minlines = array( "FORM_VALIDATOR", "_minlines" );

	/**
	 * Value must be a valid postal code (seperating space optional)
	 *
	 * @var array
	 */
	public static $postalcode = array("FORM_VALIDATOR", "_postalcode");

	/**
	 * Value must be on of the entries in the given array
	 *
	 * @var array
	 */
	public static $isoneof = array("FORM_VALIDATOR", "_isoneof");


	/*****************
	 **  FUNCTIONS  **
	 *****************/

	/**
	 * Negate a given validator
	 *
	 * @param string $value
	 * @param FORM_FIELD $field
	 * @param callable $validator
	 * @param array $arguments
	 * @return bool
	 */
	public static function _not($value, $field, $validator, $arguments=array()) {
		return !(bool)call_user_func_array($validator, array_merge(array($value, $field), $arguments));
	}

	/**
	 * Value must be a valid email
	 *
	 * @param string $value
	 * @param FORM_FIELD $field
	 * @return bool
	 */
	public static function _email($value, $field) {
		$regex = '/^[A-Za-z0-9._%-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4}$/';
		return (bool)preg_match($regex, $value);
	}

	/**
	 * Value must be between min and max values (inclusive)
	 *
	 * @param string $value
	 * @param FORM_FIELD $field
	 * @param float $min
	 * @param float $max
	 * @return bool
	 */
	public static function _range($value, $field, $min, $max) {
		return self::_min($value, $field, $min) && self::_max($value, $field, $max);
	}

	/**
	 * Value must not be more than max
	 *
	 * @param string $value
	 * @param FORM_FIELD $field
	 * @param float $max
	 * @return bool
	 */
	public static function _max($value, $field, $max) {
		return $value <= $max;
	}

	/**
	 * Value must not be less than min
	 *
	 * @param string $value
	 * @param FORM_FIELD $field
	 * @param float $min
	 * @return bool
	 */
	public static function _min($value, $field, $min) {
		return $value >= $min;
	}

	/**
	 * Value must be an integer (a whole number)
	 *
	 * @param string $value
	 * @param FORM_FIELD $field
	 * @return bool
	 */
	public static function _integer($value, $field) {
		return (bool)preg_match('/^-?[0-9]+$/', $value);
	}

	/**
	 * Value must be a decimal number with at least min_decimal decimal positions
	 * and no more than max_decimal.
	 *
	 * If max_decimal is null then there is no upper limit.
	 *
	 * @param string $value
	 * @param FORM_FIELD $field
	 * @param int $min_decimal
	 * @param int $max_decimal
	 * @return bool
	 */
	public static function _decimal($value, $field, $min_decimal = 1, $max_decimal=null) {
		$regex = '/^-?[0-9]+\.[0-9]{' . $min_decimal . ',' . $max_decimal . '}$/';
		return (bool)preg_match($regex, $value);
	}

	/**
	 * Value must be a number (integer or decimal)
	 *
	 * @param string $value
	 * @param FORM_FIELD $field
	 * @return bool
	 */
	public static function _number($value, $field) {
		return (bool)preg_match('/^-?[0-9]+(\.[0-9]+)?$/', $value);
	}

	/**
	 * Value must be made of only alphanumeric characters (and whitespaces if allow_whitespaces)
	 *
	 * @param string $value
	 * @param FORM_FIELD $field
	 * @param bool $allow_whitespaces
	 * @return bool
	 */
	public static function _alphanum($value, $field, $allow_whitespaces = true) {
		$regex = $allow_whitespaces ? '/^[A-Za-z0-9\s]+$/' : '/^[A-Za-z0-9]+$/';
		return (bool)preg_match($regex, $value);
	}

	/**
	 * Value must be made of only alphabetic characters (and whitespaces if allow_whitespace)
	 *
	 * @param string $value
	 * @param FORM_FIELD $field
	 * @param bool $allow_whitespaces
	 * @return bool
	 */
	public static function _alpha($value, $field, $allow_whitespaces=true) {
		$regex = $allow_whitespaces ? '/^[A-Za-z\s]+$/' : '/^[A-Za-z]+$/';
		return (bool)preg_match($regex, $value);
	}

	/**
	 * Value must be between min_length and max_length characters long
	 *
	 * @param string $value
	 * @param FORM_FIELD $field
	 * @param int $min_length
	 * @param int $max_length
	 * @return bool
	 */
	public static function _length($value, $field, $min_length, $max_length) {
		return strlen($value) >= $min_length && strlen($value) <= $max_length;
	}

	/**
	 * Value must be no more than max_length characters long
	 *
	 * @param string $value
	 * @param FORM_FIELD $field
	 * @param int $max_length
	 * @return bool
	 */
	public static function _maxlength($value, $field, $max_length) {
		return strlen($value) <= $max_length;
	}

	/**
	 * Value must be no less than min_length characters long
	 *
	 * @param string $value
	 * @param FORM_FIELD $field
	 * @param int $min_length
	 * @return bool
	 */
	public static function _minlength($value, $field, $min_length) {
		return strlen($value) >= $min_length;
	}

	/**
	 * Value must match given regex
	 *
	 * @param string $value
	 * @param FORM_FIELD $field
	 * @param string $regex
	 * @return bool
	 */
	public static function _regex($value, $field, $regex) {
		return (bool)preg_match($regex, $value);
	}

	/**
	 * Value must be between min_lines and max_lines number of lines (inclusive)
	 *
	 * @param string $value
	 * @param FORM_FIELD $field
	 * @param int $min_lines
	 * @param int $max_lines
	 * @return bool
	 */
	public static function _lines($value, $field, $min_lines, $max_lines) {
		$num_lines = substr_count($value, "\n") + 1;
		return $num_lines >= $min_lines && $num_lines <= $max_lines;
	}

	/**
	 * Value must be no more than max_lines number of lines
	 *
	 * @param string $value
	 * @param FORM_FIELD $field
	 * @param int $max_lines
	 * @return bool
	 */
	public static function _maxlines($value, $field, $max_lines) {
		$num_lines = substr_count($value, "\n") + 1;
		return $num_lines <= $max_lines;
	}

	/**
	 * Value must be no less than min_lines number of lines
	 *
	 * @param string $value
	 * @param FORM_FIELD $field
	 * @param int $min_lines
	 * @return bool
	 */
	public static function _minlines($value, $field, $min_lines) {
		$num_lines = substr_count($value, "\n") + 1;
		return $num_lines >= $min_lines;
	}

	/**
	 * Value must be a valid postal code (seperating space optional)
	 *
	 * @param string $value
	 * @param FORM_FIELD $field
	 * @return bool
	 */
	public static function _postalcode($value, $field) {
		$regex = '/^[A-Za-z][0-9][A-Za-z] ?[0-9][A-Za-z][0-9]$/';
		return (bool)preg_match($regex, $value);
	}

	/**
	 * Value must be on of the entries in the given array
	 *
	 * @param string $value
	 * @param FORM_FIELD $field
	 * @param array $array
	 * @return bool
	 */
	public static function _isoneof($value, $field, array $array) {
		return in_array($value, $array);
	}

}
