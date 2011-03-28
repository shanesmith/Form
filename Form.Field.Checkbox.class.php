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
	 * Render the checkbox element
	 *
	 * @param array $languages
	 * @return string
	 */
	public function render_field(array $languages) {
		$this->setFieldAttribute('value', 'true');
		return parent::render_field($languages);
	}

}