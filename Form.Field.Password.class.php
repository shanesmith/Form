<?php
require_once dirname(__FILE__) . "/Form.class.php";

/**
*
* FORM_PASSWORD
*
*
* An HTML password field
*
*/
class FORM_PASSWORD extends FORM_FIELD {

	/**
	 * Render the password element
	 *
	 * @param array $languages
	 * @return string
	 */
	public function render_field(array $languages) {
		// password fields should not display anything.....
		return parent::render_field($languages);
	}

}