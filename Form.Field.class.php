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

	/**
	* Values
	*
	* @TODO override in Radio, Button, Checkbox, File?
	*
	* @var string
	*/
	protected $default_value, $posted_value;

	/**
	* An array of validators to be run on validate()
	*
	* Each element is an array with the keys 'func', 'args' and 'msg'
	*
	* @var array
	*/
	protected $validators = array();


	/*******************
	 **  CONSTRUCTOR  **
	 *******************/

	/**
	* Field constructor
	*
	* @param FORM_FIELDSET $parent
	* @param string $name
	* @param string $label
	* @param string $default
	* @return FORM_FIELD
	*/
	public function __construct(&$parent, $name, $labels=null, $default=null) {
		$this->setDefaultValue($default);
		parent::__construct($parent, $name, $labels);
	}


	/**************
	 **  VALUES  **
	 **************/

	 /**
	 * Get the posted value, or if none get default
	 *
	 * @return string
	 */
	 public function getValue() {
		 if (isset($this->posted_value)) {
			 return $this->posted_value;
		 } else {
			 return $this->default_value;
		 }
	 }

	 /**
	 * Return the default value
	 *
	 * @return string
	 */
	public function getDefaultValue() {
		return $this->default_value;
	}

	/**
	* Return the posted value
	*
	* @return string
	*/
	public function getPostedValue() {
		return $this->posted_value;
	}

	/**
	* Set a default value
	*
	* @param string $value
	* @return FORM_FIELD
	*/
	public function setDefaultValue($value) {
		$this->default_value = $value;
		return $this;
	}

	/**
	* Set the posted value
	*
	* @param string $value
	* @return FORM_FIELD
	*/
	public function setPostedValue($value) {
		$this->posted_value = $value;
		return $this;
	}


	/******************
	 **  VALIDATORS  **
	 ******************/

	 /**
	 * Add a validator to this field, including optional arguments array and error message
	 *
	 * @param callable $validator
	 * @param array $args
	 * @param string $message
	 */
	public function addValidator($validator, $args=array(), $message=null) {
		$this->validators[] = array(
			'func' => $validator,
			'args' => $args,
			'msg'  => $message
		);
		return $this;
	}

	/**
	* Remove all validators
	*
	* @return FORM_FIELD
	*/
	public function clearValidators() {
		$this->validators = array();
		return $this;
	}

	/**
	* Return an array of all validators.
	*
	* Each element is an array with the keys 'func', 'args' and 'msg'
	*
	* @return array
	*/
	public function getAllValidators() {
		return $this->validators;
	}

	/**
	* Run all validators
	*
	* Returns true if all validators return cleanly, false otherwise.
	*
	* Call getMessages() for more details on the errors, if any.
	*
	* @return boolean
	*/
	public function validate() {
		$this->clearError();

		$value = $this->getValue();

		foreach ($this->getAllValidators() as $validator) {
			if (!is_callable($validator['func'])) {
				$validator_str = !is_string($validator['func']) ? var_export($validator['func'], true) : $validator['func'];
				throw new FormInvalidValidator("Validator is not callable: {$validator_str}", $this, $validator);
			}

			$arguments = array_merge(array($value, $this), (array)$validator['args']);

			$valid = call_user_func_array($validator['func'], $arguments);

			if (!$valid) {
				$this->setError($validator['msg']);
				return false;
			}

		}

		return true;
	}

	/**
	* Return the language-keyed array of error messages, if any were set by validate()
	*
	* @return array
	*/
	public function getError() {
		$name = $this->name();
		return $this->form()->getErrorByElementName($name);
	}

	/**
	* Returns whether the field has an error from running validate()
	*
	* @return boolean
	*/
	public function hasError() {
		$name = $this->name();
		return $this->form()->hasErrorByElementName($name);
	}

	/**
	* Set an error for this field, usually used by validate()
	*
	* @param string $error
	* @return FORM_FIELD
	*/
	public function setError($error) {
		$name = $this->name();
		$this->form()->addError($name, $error);
		return $this;
	}

	/**
	* Unset the error, if any
	*
	* @return FORM_FIELD
	*/
	public function clearError() {
		$name = $this->name();
		$this->form()->clearErrorByElementName($name);
		return $this;
	}

	/**
	* Set a validator to make the field required
	*
	* @param array|string $message
	* @return FORM_FIELD
	*/
	public function validateRequired($message=null) {
		if (!isset($message)) {
			$message = array(
				'en' => "Field {$this->name()} is required.",
				'fr' => "Le champ {$this->name()} est requis."
			);
		}

		$this->addValidator(FORM_VALIDATOR::$required, null, $message);
		return $this;
	}

	/**
	* Set a validator for email matching
	*
	* @param array|string $message
	* @return FORM_FIELD
	*/
	public function validateEmail($message=null) {
		if (!isset($message)) {
			$message = array(
				'en' => "Field {$this->name()} is not a valid email.",
				'fr' => "Le champ {$this->name()} n'est pas un courriel valide."
			);
		}

		$this->addValidator(FORM_VALIDATOR::$email, null, $message);
		return $this;
	}

	/************************
	 **  FIELD ATTRIBUTES  **
	 ************************/

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
	* Return a string of this field's HTML attributes
	*
	* Optinally provide an array of attributes to override or add to
	* the attributes already set
	*
	* @param array $override
	* @return string
	*/
	public function getFieldAttributesString(array $override=array()) {
		return self::attr2str(array_merge($this->getFieldAttributesArray(), $override));
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

	/**
	* Render this element's field.
	*
	* Uses basic input tag.
	*
	* For other type of tags this function should be
	* overwritten in the super class.
	*
	* @param array $languages useful mostly for select elements
	* @return string
	*/
	public function render_field(array $languages) {
		$attributes = $this->getFieldAttributesString(array(
			'type' => $this->type(),
			'name' => $this->name(),
		));

		return "<input {$attributes} />";
	}


	/**
	* A default renderer for fields
	*
	* @param FORM_FIELD $element
	* @param array $languages
	* @returns string
	*/
	public static function _div_renderer($element, array $languages) {
		$type = $element->type();

		$name = $element->name();

		$errors = $element->getError();


		$attributes = $element->getAttributesArray();

		$attributes['class'] .= " form-element-container form-field-container form-field-container-{$type} form-element-name-{$name}";

		if ($element->hasError()) {
			$attributes['class'] .= " form-element-has-error";
		}

		$attributes = self::attr2str($attributes);


		$original_field_id = $element->getFieldID();

		if (empty($original_field_id)) {
			$field_id = uniqid("form-{$type}-");
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

		$field = $element->render_field($languages);
		$str .= "\t<div class='form-field form-field-{$type}'>{$field}</div>\n";

		if ($element->hasError()) {
			$str .= "\t<div class='form-error'>\n";

			foreach ($languages as $lang) {
				$str .= "\t\t<span class='form-error-{$lang}'>{$errors[$lang]}</span>";
			}

			$str .= "\t</div>\n";
		}

		$str .= "</div>\n";

		$element->setFieldID($original_field_id);

		return $str;
	}

	/**
	* A renderer for fields as table rows
	*
	* @param FORM_FIELD $element
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

		$str .= "\t<th class='form-element-label form-field-label form-field-label-{$type}'>\n";

		foreach ($languages as $lang) {
			$str .= "\t\t<span class='form-field-label-{$lang} form-field-label-{$type}-{$lang}'>{$labels[$lang]}</span>\n";
		}

		$str .= "\t</th>\n";

		$str .= "\t<td class='form-field form-field-{$type}'>{$field}</td>\n";

		$str .= "</tr>";

		return $str;
	}

}
