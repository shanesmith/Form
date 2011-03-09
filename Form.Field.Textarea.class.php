<?php
require_once dirname(__FILE__) . "/Form.class.php";

/**
*
* FORM_TEXTAREA
*
*
* An HTML textarea
*
*/
class FORM_TEXTAREA extends FORM_FIELD {

	/**
	* Type of Form Element
	*
	* @var string
	*/
	static private $type = 'textarea';

	/**
	 * Return the element's type
	 *
	 * @return string
	 */
	public function type() { return self::$type; }

	/**
	* Render the textarea field, overwitting basic input tag renderer.
	*
	* @param array $languages
	* @return string
	*/
	public function render_field(array $languages) {
		$attributes = $this->getFieldAttributesString(array(
			'name' => $this->name(),
		));

		return "<textarea {$attributes}></textarea>";
	}

}
