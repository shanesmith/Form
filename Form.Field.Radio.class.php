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
	 * The radio's shared name
	 *
	 * @var string
	 */
	protected $radio_name;

	/**
	 * Constructor
	 *
	 * @param FORM_FIELDSET $parent
	 * @param string $name
	 * @param string $text
	 * @param string|array $labels
	 * @return FORM_RADIO
	 */
	public function __construct(&$parent, $radio_name, $unique_name, $text, $labels=null, $default_checked=false) {
		parent::__construct($parent, $unique_name, $labels);
		$this->text = $text;
		$this->radio_name = $radio_name;
		$this->setDefaultChecked($default_checked);
	}

	/**
	 * Get the name of the radio list that this belongs to
	 *
	 * @return string
	 */
	public function getRadioName() {
		return $this->radio_name;
	}

	/**
	 * Get the text
	 *
	 * @return string
	 */
	public function getText() {
		return $this->text;
	}

	/**
	 * Is this radio checked?
	 *
	 * @return boolean
	 */
	public function isChecked() {
		return $this->form()->getRadioCheckedText($this->getRadioName()) == $this->getText();
	}

	/**
	 * Is this radio checked by default?
	 *
	 * @return boolean
	 */
	public function isDefaultChecked() {
		return $this->form()->getRadioDefaultCheckedText($this->getRadioName()) == $this->getText();
	}

	/**
	 * Is the posted radio checked?
	 *
	 * @return boolean
	 */
	public function isPostedChecked() {
		return $this->form()->getRadioPostedCheckedText($this->getRadioName()) == $this->getText();
	}

	/**
	 * Set the radio checked by default
	 *
	 * @param boolean $checked
	 * @return FORM_RADIO
	 */
	public function setDefaultChecked() {
		$this->form()->setRadioDefaultChecked($this->getRadioName(), $this->getText());
		return $this;
	}

	/**
	 * Set the posted radio checked
	 *
	 * @param boolean $checked
	 * @return FORM_RADIO
	 */
	public function setPostedChecked() {
		$this->form()->setRadioPostedChecked($this->getRadioName(), $this->getText());
		return $this;
	}

	/**
	 * Render the radio element
	 *
	 * @param array $languages
	 * @return string
	 */
	public function fieldHTML(array $languages) {
		$this->setFieldAttribute('value', $this->text);

		if ($this->isChecked()) {
			$this->setFieldAttribute('checked', 'checked');
		}

		$attributes = $this->getFieldAttributesString(array(
			 'type' => $this->type(),
			 'name' => $this->getRadioName(),
		));

		return "<input {$attributes} />";
	}

}
