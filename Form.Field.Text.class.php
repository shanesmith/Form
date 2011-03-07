<?php
require_once dirname(__FILE__) . "/Form.class.php";

/**
*
* FORM_TEXT
*
*
* An HTML textfield
*
*/
class FORM_TEXT extends FORM_FIELD {

	/**
	* Type of Form Element
	*
	* @var string
	*/
	static private $type = 'text';

	/**
	 * Return the element's type
	 *
	 * @return string
	 */
	public function type() { return self::$type; }

}
