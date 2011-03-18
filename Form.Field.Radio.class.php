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
	 * Render the radio element
	 *
	 * @param array $languages
	 * @return string
	 */
	public function render_field(array $languages) {
		$this->setFieldAttribute('value', $this->value);
		return parent::render_field($languages);
	}

	/**
	* Override for phpdoc change of return type...
	*
	* @return FORM_RADIO_LIST
	*/
	public function parent() {
		return parent::parent();
	}

}
