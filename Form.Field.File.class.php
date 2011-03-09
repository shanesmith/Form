<?php
require_once dirname(__FILE__) . "/Form.class.php";

/**
*
* FORM_FILE
*
*
* An HTML file input
*
*/
class FORM_FILE extends FORM_FIELD {

	/**
	* Type of Form Element
	*
	* @var string
	*/
	static private $type = 'file';

	/**
	 * Return the element's type
	 *
	 * @return string
	 */
	public function type() { return self::$type; }

}