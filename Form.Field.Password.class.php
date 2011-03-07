<?php
require_once dirname(__FILE__) . "/Form.class.php";

/**
*
* FORM_PASSWORD
*
*
* An HTML password field
*
*/
class FORM_PASSWORD extends FORM_FIELD {

	/**
	* Type of Form Element
	*
	* @var string
	*/
	static private $type = 'password';

	/**
	 * Return the element's type
	 *
	 * @return string
	 */
	public function type() { return self::$type; }

}