<?php
require_once dirname(__FILE__) . "/Form.class.php";

/**
 *
 * FORM_INFO
 *
 *
 * An area to write informational text
 *
 */
class FORM_INFO extends FORM_ELEMENT {

	/**
	 * The info's text
	 *
	 * @var array
	 */
	protected $texts = array();

	/**
	 * Constructor
	 *
	 * @param FORM_FIELDSET $parent
	 * @param string $name
	 * @param array|string $labels
	 * @param array|string $texts
	 * @return FORM_INFO
	 */
	public function __construct(&$parent, $name, $labels=null, $texts=array()) {
		parent::__construct($parent, $name, $labels);
		if (!empty($texts)) $this->setTexts($texts);
	}

	/**
	 * Return texts
	 *
	 * @return array
	 */
	public function getTexts() {
		return $this->texts;
	}

	/**
	 * Returns the text for the given language
	 *
	 * @param string $lang
	 * @return string
	 */
	public function getTextByLang($lang) {
		return $this->texts[$lang];
	}

	/**
	 * Sets this info's texts
	 *
	 * @param string|array $texts
	 * @return FORM_INFO
	 */
	public function setTexts($texts) {
		$this->process_languaged_argument($this->texts, $texts);
		return $this;
	}

}
