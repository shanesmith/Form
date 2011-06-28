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
	 * Constructor.
	 *
	 * @param FORM_FIELDSET $parent
	 * @param string $name
	 * @param string $default
	 * @return FORM_HIDDEN
	 */
	public function __construct(&$parent, $name, $default=null) {
		parent::__construct($parent, $name, null, $default);
	}

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
