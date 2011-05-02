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
	* Text for the 'value' field
	*
	* @var string
	*/
	protected static $text = 'true';

	/**
	* Is the checkbox checked?
	*
	* @return boolean
	*/
	public function isChecked() {
		return (bool)$this->getValue();
	}

	/**
	* Is the checkbox checked by default?
	*
	* @return boolean
	*/
	public function isDefaultChecked() {
		return (bool)$this->getDefaultValue();
	}

	/**
	* Is the posted checkbox checked?
	*
	* @return boolean
	*/
	public function isPostedChecked() {
		return (bool)$this->getPostedValue();
	}

	/**
	* Check the checkbox by default
	*
	* @param boolean $checked
	* @return FORM_CHECKBOX
	*/
	public function setDefaultChecked($checked) {
		$this->setDefaultValue($checked ? self::$text : "");
		return $this;
	}

	/**
	* Set the posted checkbox to checked
	*
	* @param boolean $checked
	* @return FORM_CHECKBOX
	*/
	public function setPostedChecked($checked) {
		$this->setPostedValue($checked ? self::$text : "");
		return $this;
	}



	/*****************
	 **  RENDERING  **
	 *****************/

	/**
	 * Render the checkbox element
	 *
	 * @param array $languages
	 * @return string
	 */
	public function fieldHTML(array $languages) {
		$this->setFieldAttribute('value', self::$text);

		if ($this->isChecked()) {
			$this->setFieldAttribute('checked', 'checked');
		}

		return parent::fieldHTML($languages);
	}

}