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
	protected $default_value, $posted_value, $posted_raw_value;

	/**
	 * An array of validators to be run on validate()
	 *
	 * Each element is an array with the keys 'func', 'args' and 'msg'
	 *
	 * @var array
	 */
	protected $validators = array();

	/**
	 * An array of formatters to be run on loadPostedValue()
	 *
	 * Each element is an array with the keys 'func' and 'args'
	 *
	 * @var array
	 */
	protected $formatters = array();


	/*******************
	 **  CONSTRUCTOR  **
	 *******************/

	/**
	 * Field constructor
	 *
	 * @param FORM_FIELDSET $parent
	 * @param string $name
	 * @param string $labels
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
	 * Return the posted raw (before formatting) value
	 *
	 * @return string
	 */
	public function getPostedRawValue() {
		return $this->posted_raw_value;
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
		$this->posted_raw_value = $value;

		$formatted_value = $value;

		foreach ($this->formatters as $formatter) {
			if (!is_callable($formatter['func'])) {
				$formatter_str = !is_string($formatter['func']) ? var_export($formatter['func'], true) : $formatter['func'];
				throw new FormInvalidFormatter("Formatter is not callable: {$formatter_str}", $this, $formatter);
			}

			$arguments = array_merge(array($formatted_value, $this), (array)$formatter['args']);

			$formatted_value = call_user_func_array($formatter['func'], $arguments);
		}

		$this->posted_value = $formatted_value;


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
	 * @return FORM_FIELD
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
	 * Return the error in the specified language, if any were set
	 *
	 * @param string $lang
	 * @return string
	 */
	public function getErrorByLang($lang) {
		if (!$this->form()->isValidLanguage($lang)) {
			throw new FormInvalidLanguageException("Language {$lang} is invalid.", $lang, $this);
		}

		$error = $this->getError();
		return $error[$lang];
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
	 * Returns whether the field is valid (ie: has no errors)
	 *
	 * @return boolean
	 */
	public function isValid() {
		return !$this->hasError();
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


	/******************
	 **  FORMATTERS  **
	 ******************/

	/**
	 * Add a formatter to this field, including option arguments array
	 *
	 * @param callable $formatter
	 * @param array $args
	 * @return FORM_FIELD
	 */
	public function addFormatter($formatter, $args=array()) {
		$this->formatters[] = array(
			'func' => $formatter,
			'args' => $args
		);
		return $this;
	}

	/**
	 * Remove all formatters associated to this field
	 *
	 * @return FORM_FIELD
	 */
	public function clearFormatters() {
		$this->formatters = array();
		return $this;
	}

	/**
	 * Return all formatters
	 *
	 * @return array
	 */
	public function getAllFormatters() {
		return $this->formatters;
	}

	/**
	 * Trim formatter
	 *
	 * @param mixed $left
	 * @param mixed $right
	 * @param mixed $charlist
	 * @return FORM_FIELD
	 */
	public function formatTrim($left=true, $right=true, $charlist=null) {
		$arguments = array($left, $right, $charlist);
		$this->addFormatter(FORM_FORMATTER::$trim, $arguments);
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
	 * @return string
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
		return FORM_RENDERER::attr2str(array_merge($this->getFieldAttributesArray(), $override));
	}

	/**
	 * Set the specified field attribute to a given value
	 *
	 * @param string $key
	 * @param string $value
	 * @return FORM_ELEMENT
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
	 * @return string
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
	 * @param boolean $create
	 * @return string
	 */
	public function getFieldID($create=false) {
		$id = $this->getFieldAttribute('id');
		if (empty($id) && $create) {
			$id = uniqid("form-{$this->type()}-");
			$this->setFieldID($id);
		}
		return $id;
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
	public function fieldHTML(array $languages) {
		$attributes = $this->getFieldAttributesString(array(
			'type' => $this->type(),
			'name' => $this->name(),
		));

		return "<input {$attributes} />";
	}

}
