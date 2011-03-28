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
	* The radio's 'value' field
	*
	* @var string
	*/
	protected $text;

	/**
	* Constructor
	*
	* @param FORM_RADIO_LIST $parent
	* @param string $name
	* @param string $text
	* @param string|array $labels
	* @return FORM_RADIO
	*/
	public function __construct(&$parent, $name, $text, $labels=null) {
		parent::__construct($parent, $name, $labels);
		$this->text = $text;
	}

	/**
	 * Render the radio element
	 *
	 * @param array $languages
	 * @return string
	 */
	public function render_field(array $languages) {
		$this->setFieldAttribute('value', $this->text);
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
