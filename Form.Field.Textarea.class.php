<?php
require_once dirname(__FILE__) . "/Form.class.php";

/**
 *
 * FORM_TEXTAREA
 *
 *
 * An HTML textarea
 *
 */
class FORM_TEXTAREA extends FORM_FIELD {

	/**
	 * Render the textarea field, overwitting basic input tag renderer.
	 *
	 * @param array $languages
	 * @return string
	 */
	public function fieldHTML(array $languages) {
		$attributes = $this->getFieldAttributesString(array(
			'name' => $this->name(),
		));

		$value = $this->getValue();

		return "<textarea {$attributes}>{$value}</textarea>";
	}

}
