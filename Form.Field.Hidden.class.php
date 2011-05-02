<?php
require_once dirname(__FILE__) . "/Form.class.php";

/**
*
* FORM_HIDDEN
*
*
* An HTML hiden field
*
*/
class FORM_HIDDEN extends FORM_FIELD {

	/**
	 * Render the hidden element
	 *
	 * @param array $languages
	 * @return string
	 */
	public function fieldHTML(array $languages) {
		$this->setFieldAttribute('value', $this->getValue());
		return parent::fieldHTML($languages);
	}

}
