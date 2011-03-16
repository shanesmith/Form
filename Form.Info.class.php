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
	* @returns array
	*/
	public function getTexts() {
		return $this->texts;
	}

	/**
	* Sets this info's texts
	*
	* @param string|array $texts
	* @returns FORM_INFO
	*/
	public function setTexts($texts) {
		$this->process_languaged_argument($this->texts, $texts);
		return $this;
	}

	/**
	* A default renderer for info box
	*
	* Simplified version found in FORM_FIELD
	*
	* @param FORM_INFO $element
	* @param array $languages
	* @returns string
	*/
	public static function _default_renderer($element, array $languages) {
		$attributes = $element->getAttributesArray();

		$attributes['class'] .= " form-element-container form-info-container";

		$attributes = self::attr2str($attributes);

		$labels = $element->getLabels();

		$texts = $element->getTexts();


		$str = "<div {$attributes}>";

		$str .= "\t<label class='form-element-label form-info-label'>\n";

		foreach ($languages as $lang) {
			$str .= "\t\t<span class='form-info-label-{$lang}'>{$labels[$lang]}</span>\n";
		}

		$str .= "\t</label>\n";

		$str .= "\t<div class='form-info'>";

		foreach ($languages as $lang) {
			$str .= "\t\t<span class='form-info-{$lang}'>{$texts[$lang]}</span>\n";
		}

		$str .= "\t</div>\n";

		$str .= "</div>\n";

		return $str;
	}

}
