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
