<?php
require_once dirname(__FILE__) . "/Form.class.php";

/**
 * FORM_FIELD
 *
 *
 * A base class to represent a generic field.
 *
 */
abstract class FORM_FIELD extends FORM_ELEMENT {

	/**
	* An array of field attributes for rendering
	* with name and value pairs matching
	* attribute names and values
	*
	* @var array
	*/
	protected $field_attributes = array();


	/*************************
	 **  GETTERS / SETTERS  **
	 *************************/

	/**
	* Returns all of the element's field attributes in an array
	*
	* @return array
	*/
	public function getFieldAttributesArray() {
		return $this->field_attributes;
	}

	/**
	* Sets all field attributes in the given array
	*
	* @param array $attributes
	* @return FORM_ELEMENT
	*/
	public function setFieldAttributesArray(array $attributes) {
		$this->field_attributes = array_merge($this->field_attributes, $attributes);
		return $this;
	}

	/**
	* Get the specified field attribute
	*
	* @param string $key
	* @returns string
	*/
	public function getFieldAttribute($key) {
		return $this->field_attributes[$key];
	}

	/**
	* Set the specified field attribute to a given value
	*
	* @param string $key
	* @param string $value
	* @returns FORM_ELEMENT
	*/
	public function setFieldAttribute($key, $value) {
		$this->field_attributes[$key] = $value;
		return $this;
	}

	/**
	* Remove all field attributes
	*
	* @return FORM_ELEMENT
	*/
	public function resetFieldAttributes() {
		$this->field_attributes = array();
		return $this;
	}

	/**
	* Get the element's field class attribute
	*
	* @returns string
	*/
	public function getFieldClass() {
		return $this->getFieldAttribute('class');
	}

	/**
	* Appends a class to the element's field class attribute
	*
	* @param string $class
	* @return FORM_ELEMENT
	*/
	public function addFieldClass($class) {
		return $this->setFieldAttribute('class', $this->getFieldClass() . " " . $class);
	}

	/**
	* Remove the given class from the element field
	*
	* @param string $class
	* @return FORM_ELEMENT
	*/
	public function removeFieldClass($class) {
		$class = preg_quote($class);
		return $this->setFieldAttribute('class', preg_replace("/\\b{$class}\\b/i", '', $this->getFieldClass()));
	}

	/**
	* Get the element's ID attribute
	*
	* @return string
	*/
	public function getFieldID() {
		return $this->getFieldAttribute('id');
	}

	/**
	* Sets the element's ID attribute
	*
	* @param string $id
	* @return FORM_ELEMENT
	*/
	public function setFieldID($id) {
		return $this->setFieldAttribute('id', $id);
	}


	/*****************
	 **  RENDERING  **
	 *****************/

	/*
	 * Abstract functions setStaticRenderer(), getStaticRenderer(), resolveRenderer() and render()
	 * from FORM_ELEMENT are deferred to subclasses of FORM_FIELD
	 */


	/**
	* Render this element's field
	*
	* @return string
	*/
	public function render_field() {
		$attributes = array_merge(
			$this->getFieldAttributesArray(),
			array(
				'type' => $this->type(),
				'name' => $this->name(),
			)
		);

		$attributes = self::attr2str($attributes);

		return "<input {$attributes} />";
	}


	/**
	* A default renderer for fields
	*
	* @param FORM_FIELD $fieldset
	* @param string $elements
	*/
	public static function _default_renderer($element, array $languages) {
		$type = $element->type();

		$attributes = $element->getAttributesArray();

		$attributes['class'] .= " form-element-container form-field-container form-field-container-{$type}";

		$attributes = self::attr2str($attributes);


		$original_field_id = $element->getFieldID();

		if (empty($original_field_id)) {
			$field_id = uniqid('form-id-');
			$element->setFieldID($field_id);
		} else {
			$field_id = $original_field_id;
		}

		$labels = $element->getLabels();


		$str = "<div {$attributes}>";

		$str .= "\t<label class='form-element-label form-field-label form-field-label-{$type}' for='{$field_id}'>\n";

		foreach ($languages as $lang) {
			$str .= "\t\t<span class='form-field-label-{$lang} form-field-label-{$type}-{$lang}'>{$labels[$lang]}</span>\n";
		}

		$str .= "\t</label>\n";

		$type = $element->type();
		$field = $element->render_field();
		$str .= "\t<div class='form-field form-field-{$type}'>{$field}</div>\n";

		$str .= "</div>\n";

		$element->setFieldID($original_field_id);

		return $str;
	}

}
