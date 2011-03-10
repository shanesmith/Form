<?php
require_once dirname(__FILE__) . "/Form.class.php";

/**
 *
 * FORM_RADIO
 *
 *
 * An HTML radio
 *
 */
class FORM_RADIO extends FORM_FIELD {

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
	static protected $type = 'radio';

	/**
	* Constructor
	*
	* @param FORM_RADIO_LIST $parent
	* @param string $name
	* @param string $value
	* @param string|array $labels
	* @return FORM_RADIO
	*/
	public function __construct(&$parent, $name, $value, $labels=null) {
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
	 * Render the radio element
	 *
	 * @param array $languages
	 * @return string
	 */
	public function render_field(array $languages) {
		$this->setFieldAttribute('value', $this->value);
		return parent::render_field($languages);
	}

}
