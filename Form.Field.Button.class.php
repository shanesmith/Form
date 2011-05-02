<?php
require_once dirname(__FILE__) . "/Form.class.php";

/**
*
* FORM_BUTTON
*
*
* An HTML button
*
*/
class FORM_BUTTON extends FORM_FIELD {

	/**
	* Render the button field, adding value attribute equal to labels.
	*
	* @param array $languages
	* @return string
	*/
	public function fieldHTML(array $languages) {
		$value = array();

		foreach ($languages as $lang) {
			$value[] = $this->labels[$lang];
		}

		$value = implode(" // ", $value);

		$this->setFieldAttribute('value', $value);
		return parent::fieldHTML($languages);
	}

}


/**
*
* FORM_SUBMIT_BUTTON
*
*
* An HTML submit button
*
*/
class FORM_SUBMIT_BUTTON extends FORM_BUTTON {

}


/**
*
* FORM_RESET_BUTTON
*
*
* An HTML reset button
*
*/
class FORM_RESET_BUTTON extends FORM_BUTTON {

}
