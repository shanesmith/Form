<?php
require_once dirname(__FILE__) . "/Form.class.php";

/**
 *
 * FORM_CHECKBOX
 *
 *
 * An HTML checkbox
 *
 */
class FORM_CHECKBOX extends FORM_FIELD {

	/**
	* The radio's value field
	*
	* @var string
	*/
	protected $value;

	/**
	 * Type of Form Element
	 *
	 * @var string
	 */
	static protected $type = 'checkbox';

	/**
	* Constructor
	*
	* @param FORM_FIELDSET $parent
	* @param string $name
	* @param string|array $labels
	* @param string $value
	* @return FORM_CHECKBOX
	*/
	public function __construct(&$parent, $name, $labels=null, $value='true') {
		parent::__construct($parent, $name, $labels);
		$this->value = $value;
	}

	/**
	 * Return the element's type
	 *
	 * @return string
	 */
	public function type() { return self::$type; }

	/**
	 * Render the checkbox element
	 *
	 * @param array $languages
	 * @return string
	 */
	public function render_field(array $languages) {
		$this->setFieldAttribute('value', $this->value);
		return parent::render_field($languages);
	}

}