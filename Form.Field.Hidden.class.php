<?php
require_once dirname(__FILE__) . "/Form.class.php";

/**
*
* FORM_HIDDEN
*
*
* An HTML hiden field
*
*/
class FORM_HIDDEN extends FORM_FIELD {

	/**
	* Type of Form Element
	*
	* @var string
	*/
	static private $type = 'hidden';

	/**
	 * Return the element's type
	 *
	 * @return string
	 */
	public function type() { return self::$type; }

}
