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
	public function render_field(array $languages) {
		$value = array();

		foreach ($languages as $lang) {
			$value[] = $this->labels[$lang];
		}

		$value = implode(" // ", $value);

		$this->setFieldAttribute('value', $value);
		return parent::render_field($languages);
	}

	/**
	* Renderer for a button
	*
	* Overrides FORM_FIELD to remove label tags
	*
	* @param FORM_BUTTON $element
	* @param array $languages
	* @returns string
	*/
	public static function _div_renderer($element, array $languages) {
		$type = $element->type();

		$name = $element->name();

		$attributes = $element->getAttributesArray();

		$attributes['class'] .= " form-element-container form-field-container form-field-container-{$type} form-element-name-{$name}";

		$attributes = self::attr2str($attributes);

		$labels = $element->getLabels();

		$field = $element->render_field($languages);


		$str = "<div {$attributes}>";

		$str .= "\t<div class='form-field form-field-{$type}'>{$field}</div>\n";

		$str .= "</div>\n";

		return $str;
	}

	/**
	* A renderer for buttons as table rows
	*
	* Overrides FORM_FIELD to remove label tags
	*
	* @param FORM_BUTTON $element
	* @param array $languages
	* @return string
	*/
	public static function _table_renderer($element, array $languages) {
		if ($element->parent()->type() == "form") {
			return self::_div_renderer($element, $languages);
		}

		$type = $element->type();

		$name = $element->name();

		$labels = $element->getLabels();

		$field = $element->render_field($languages);

		$attributes = $element->getAttributesArray();

		$attributes['class'] .= " form-element-container form-field-container form-field-container-{$type} form-element-name-{$name}";

		$attributes = self::attr2str($attributes);


		$str = "<tr {$attributes}>\n";

		$str .= "\t<td colspan='2' class='form-field form-field-{$type}'>{$field}</td>\n";

		$str .= "</tr>";

		return $str;
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
