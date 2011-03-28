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
	* Is this radio checked?
	*
	* @return boolean
	*/
	public function isChecked() {
		return $this->parent()->getValue() == $this->text;
	}

	/**
	* Is this radio checked by default?
	*
	* @return boolean
	*/
	public function isDefaultChecked() {
		return $this->parent()->getDefaultValue() == $this->text;
	}

	/**
	* Is the posted radio checked?
	*
	* @return boolean
	*/
	public function isPostedChecked() {
		return $this->parent()->getPostedValue() == $this->text;
	}

	/**
	* Set the radio checked by default
	*
	* @param boolean $checked
	* @return FORM_RADIO
	*/
	public function setDefaultChecked($checked) {
		$this->setDefault($checked ? self::$text : "");
		return $this;
	}

	/**
	* Set the posted radio checked
	*
	* @param boolean $checked
	* @return FORM_RADIO
	*/
	public function setPostedChecked($checked) {
		$this->setPostedChecked($checked ? self::$text : "");
		return $this;
	}

	/**
	 * Render the radio element
	 *
	 * @param array $languages
	 * @return string
	 */
	public function render_field(array $languages) {
		$this->setFieldAttribute('value', $this->text);

		if ($this->isChecked()) {
			$this->setFieldAttribute('checked', 'checked');
		}

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
