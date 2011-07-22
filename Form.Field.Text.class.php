<?php
require_once dirname(__FILE__) . "/Form.class.php";

/**
 *
 * FORM_TEXT
 *
 *
 * An HTML textfield
 *
 */
class FORM_TEXT extends FORM_FIELD {

	/**
	 * Set this field's max length attribute
	 *
	 * @param int $len
	 * @return FORM_TEXT
	 */
	public function setFieldMaxLength($len) {
		return $this->setFieldAttribute('maxlength', $len);
	}

	/**
	 * Render the text element
	 *
	 * @param array $languages
	 * @return string
	 */
	public function fieldHTML(array $languages) {
		$this->setFieldAttribute('value', $this->getValue());
		return parent::fieldHTML($languages);
	}

}
