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
	* @return string
	*/
	public function render_field() {
		$attributes = array_merge(
			$this->getFieldAttributesArray(),
			array(
				'type' => $this->type(),
				'name' => $this->name(),
			)
		);

		$attributes = self::attr2str($attributes);

		return "<textarea {$attributes}></textarea>";
	}

}
