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
	 * Default Value
	 *
	 * @TODO override in Radio, Button, Checkbox, File?
	 *
	 * @var string
	 */
	protected $default_value;

	/**
	 * Posted Value
	 *
	 * @var string
	 */
	protected $posted_value;

	/**
	 * An array of validators to be run on validate()
	 *
	 * Each element is an array with the keys 'func', 'args' and 'msg'
	 *
	 * @var array
	 */
	protected $validators = array();

	/**
	 * Whether this field is required
	 *
	 * @var bool
	 */
	protected $required = false;

	/**
	 * The error texts in case of a 'required' check failure.
	 *
	 * @var array
	 */
	protected $required_error = array();

	/**
	 * Trim the default value during 'get'
	 *
	 * @var array
	 */
	protected $trim_default_value = array(true, true);

	/**
	 * Trim the posted value during 'get'
	 *
	 * @var array
	 */
	protected $trim_posted_value = array(true, true);


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
			return $this->getPostedValue();
		} else {
			return $this->getDefaultValue();
		}
	}

	/**
	 * Return the default value
	 *
	 * @return string
	 */
	public function getDefaultValue() {
		$value = $this->default_value;

		list($ltrim, $rtrim) = $this->getTrimDefault();

		if ($ltrim) $value = ltrim($value);

		if ($rtrim) $value = rtrim($value);

		return $value;
	}

	/**
	 * Return the posted value
	 *
	 * @return string
	 */
	public function getPostedValue() {
		$value = $this->posted_value;

		list($ltrim, $rtrim) = $this->getTrimPosted();

		if ($ltrim) $value = ltrim($value);

		if ($rtrim) $value = rtrim($value);

		return $value;
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

	/****************
	 **  TRIMMING  **
	 ****************/

	/**
	 * Set trimming option on both Default and Posted values
	 *
	 * If $right is null it will take the same value as $left
	 *
	 * @param bool $left
	 * @param bool $right
	 * @return FORM_FIELD
	 */
	public function setTrim($left, $right=null) {
		$this->setTrimDefault($left, $right);
		$this->setTrimPosted($left, $right);
		return $this;
	}

	/**
	 * Set default value trimming option
	 *
	 * If $right is null it will take the same value as $left
	 *
	 * @param bool $left
	 * @param bool $right
	 * @return FORM_FIELD
	 */
	public function setTrimDefault($left, $right=null) {
		$this->trim_default_value = array($left, isset($right) ? $right : $left );
		return $this;
	}

	/**
	 * Set posted value trimming option
	 *
	 * If $right is null it will take the same value as $left
	 *
	 * @param bool $left
	 * @param bool $right
	 * @return FORM_FIELD
	 */
	public function setTrimPosted($left, $right=null) {
		$this->trim_posted_value = array($left, isset($right) ? $right : $left);
		return $this;
	}

	/**
	 * Get default value trimming option
	 *
	 * Returned is an array with two boolean values describing
	 * whether to trim the left and right sides, respectively
	 *
	 * @return array
	 */
	public function getTrimDefault() {
		return $this->trim_default_value;
	}

	/**
	 * Get posted value trimming option
	 *
	 * Returned is an array with two boolean values describing
	 * whether to trim the left and right sides, respectively
	 *
	 * @return array
	 */
	public function getTrimPosted() {
		return $this->trim_posted_value;
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
	 * Negate a given validator
	 *
	 * @param callable $validator
	 * @param array $args
	 * @param array|string $message
	 * @return FORM_FIELD
	 */
	public function addValidatorNot($validator, $args=array(), $message=null) {
		return $this->addValidator(FORM_VALIDATOR::$not, array($validator, $args), $message);
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
	 * Set whether this field is required
	 *
	 * @param bool $required
	 * @param array $message
	 * @return FORM_FIELD
	 */
	public function setRequired($required=true, $message=null) {
		if (!isset($message)) {
			$label = $this->getLabels();
			$message = array(
				"en" => ucfirst($label['en']) . " is required.",
				"fr" => ucfirst($label['fr']) . " est requis."
			);
		}
		$this->required = $required;
		$this->required_error = $message;
		return $this;
	}

	/**
	 * Whether this field is required
	 *
	 * @return bool
	 */
	public function isRequired() {
		return $this->required;
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

		if (empty($value)) {
			if ($this->isRequired()) {
				$this->setError($this->required_error);
				return false;
			} else {
				return true;
			}
		}

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
	 * Value must be a valid email
	 *
	 * @param array|string $message
	 * @return FORM_FIELD
	 */
	public function addValidatorEmail($message=null) {
		if (!isset($message)) {
			$label = $this->getLabels();
			$message = array(
				'en' => ucfirst($label['en']) . " is not a valid email.",
				'fr' => ucfirst($label['fr']) . " n'est pas un courriel valide."
			);
		}

		$this->addValidator(FORM_VALIDATOR::$email, null, $message);
		return $this;
	}

	/**
	 * Value must be between min and max values (inclusive)
	 *
	 * @param float $min
	 * @param float $max
	 * @param array|string $message
	 * @return FORM_FIELD
	 */
	public function addValidatorRange($min, $max, $message=null) {
		if (!isset($message)) {
			$label = $this->getLabels();
			$message = array(
				'en' => ucfirst($label['en']) . " is not between {$min} and {$max}.",
				'fr' => ucfirst($label['fr']) . " n'est pas entre {$min} et {$max}."
			);
		}
		$this->addValidator(FORM_VALIDATOR::$range, array($min, $max), $message);
		return $this;
	}

	/**
	 * Value must not be more than max
	 *
	 * @param float $max
	 * @param array|string $message
	 * @return FORM_FIELD
	 */
	public function addValidatorMax($max, $message=null) {
		if (!isset($message)) {
			$label = $this->getLabels();
			$message = array(
				'en' => ucfirst($label['en']) . " cannot be higher than {$max}.",
				'fr' => ucfirst($label['fr']) . " ne peut être plus haut que {$max}."
			);
		}
		$this->addValidator(FORM_VALIDATOR::$max, array($max), $message);
		return $this;
	}

	/**
	 * Value must not be less than min
	 *
	 * @param float $min
	 * @param array|string $message
	 * @return FORM_FIELD
	 */
	public function addValidatorMin($min, $message=null) {
		if (!isset($message)) {
			$label = $this->getLabels();
			$message = array(
				'en' => ucfirst($label['en']) . " cannot be lower than {$min}.",
				'fr' => ucfirst($label['fr']) . " ne peut être plus bas que {$min}."
			);
		}
		$this->addValidator(FORM_VALIDATOR::$min, array($min), $message);
		return $this;
	}

	/**
	 * Value must be an integer (a whole number)
	 *
	 * @param array|string $message
	 * @return FORM_FIELD
	 */
	public function addValidatorInteger($message = null) {
		if (!isset($message)) {
			$label = $this->getLabels();
			$message = array(
				'en' => ucfirst($label['en']) . " is not a integer number.",
				'fr' => ucfirst($label['fr']) . " n'est pas un nombre entier."
			);
		}
		$this->addValidator(FORM_VALIDATOR::$integer, null, $message);
		return $this;
	}

	/**
	 * Value must be a decimal number with at least min_decimal decimal positions
	 * and no more than max_decimal.
	 *
	 * If max_decimal is null then there is no upper limit.
	 *
	 * @param int $min_decimal
	 * @param int $max_decimal
	 * @param array|string $message
	 * @return FORM_FIELD
	 */
	public function addValidatorDecimal($min_decimal=1, $max_decimal=null, $message=null) {
		if (!isset($message)) {
			$label = $this->getLabels();
			$message = array(
				'en' => ucfirst($label['en']) . " is not a valid decimal-point number.",
				'fr' => ucfirst($label['fr']) . " n'est pas un nombre à point décimal valide."
			);
		}
		$this->addValidator(FORM_VALIDATOR::$decimal, array($min_decimal, $max_decimal), $message);
		return $this;
	}

	/**
	 * Value must be a number (integer or decimal)
	 *
	 * @param array|string $message
	 * @return FORM_FIELD
	 */
	public function addValidatorNumber($message=null) {
		if (!isset($message)) {
			$label = $this->getLabels();
			$message = array(
				'en' => ucfirst($label['en']) . " is not a number.",
				'fr' => ucfirst($label['fr']) . " n'est pas un nombre."
			);
		}
		$this->addValidator(FORM_VALIDATOR::$number, null, $message);
		return $this;
	}

	/**
	 * Value must be made of only alphanumeric characters (and whitespaces if allow_whitespaces)
	 *
	 * @param bool $allow_whitespaces
	 * @param array|string $message
	 * @return FORM_FIELD
	 */
	public function addValidatorAlphaNum($allow_whitespaces = true, $message=null) {
		if (!isset($message)) {
			$label = $this->getLabels();
			$message = array(
				'en' => ucfirst($label['en']) . " is not alphanumeric.",
				'fr' => ucfirst($label['fr']) . " n'est pas alphanumérique."
			);
		}
		$this->addValidator(FORM_VALIDATOR::$alphanum, array($allow_whitespaces), $message);
		return $this;
	}

	/**
	 * Value must be made of only alphabetic characters (and whitespaces if allow_whitespace)
	 *
	 * @param bool $allow_whitespaces
	 * @param array|string $message
	 * @return FORM_FIELD
	 */
	public function addValidatorAlpha($allow_whitespaces = true, $message=null) {
		if (!isset($message)) {
			$label = $this->getLabels();
			$message = array(
				'en' => ucfirst($label['en']) . " is not only letters.",
				'fr' => ucfirst($label['fr']) . " n'est pas juste des lettres."
			);
		}
		$this->addValidator(FORM_VALIDATOR::$alpha, array($allow_whitespaces), $message);
		return $this;
	}

	/**
	 * Value must be between min_length and max_length characters long
	 *
	 * @param int $min_length
	 * @param int $max_length
	 * @param array|string $message
	 * @return FORM_FIELD
	 */
	public function addValidatorLength($min_length, $max_length, $message = null) {
		if (!isset($message)) {
			$label = $this->getLabels();
			$message = array(
				'en' => ucfirst($label['en']) . " is not between {$min_length} and {$max_length} characters long.",
				'fr' => ucfirst($label['fr']) . " n'est pas entre {$min_length} et {$max_length} charactères."
			);
		}
		$this->addValidator(FORM_VALIDATOR::$length, array( $min_length, $max_length ), $message);
		return $this;
	}

	/**
	 * Value must be no more than max_length characters long
	 *
	 * @param int $max_length
	 * @param array|string $message
	 * @return FORM_FIELD
	 */
	public function addValidatorMaxLength($max_length, $message = null) {
		if (!isset($message)) {
			$label = $this->getLabels();
			$message = array(
				'en' => ucfirst($label['en']) . " cannot be more than {$max_length} characters long.",
				'fr' => ucfirst($label['fr']) . " new peut être plus de {$max_length} charactères."
			);
		}
		$this->addValidator(FORM_VALIDATOR::$maxlength, array( $max_length ), $message);
		return $this;
	}

	/**
	 * Value must be no less than min_length characters long
	 *
	 * @param int $min_length
	 * @param array|string $message
	 * @return FORM_FIELD
	 */
	public function addValidatorMinLength($min_length, $message = null) {
		if (!isset($message)) {
			$label = $this->getLabels();
			$message = array(
				'en' => ucfirst($label['en']) . " cannot be less than {$min_length} characters long.",
				'fr' => ucfirst($label['fr']) . " new peut être moin de {$min_length} charactères."
			);
		}
		$this->addValidator(FORM_VALIDATOR::$minlength, array( $min_length ), $message);
		return $this;
	}

	/**
	 * Value must match given regex
	 *
	 * @param string $regex
	 * @param array|string $message
	 * @return FORM_FIELD
	 */
	public function addValidatorRegex($regex, $message=null) {
		if (!isset($message)) {
			$label = $this->getLabels();
			$message = array(
				'en' => ucfirst($label['en']) . " is not valid.",
				'fr' => ucfirst($label['fr']) . " n'est pas valide."
			);
		}
		$this->addValidator(FORM_VALIDATOR::$regex, array($regex), $message);
		return $this;
	}

	/**
	 * Value must be between min_lines and max_lines number of lines (inclusive)
	 *
	 * @param int $min_lines
	 * @param int $max_lines
	 * @param array|string $message
	 * @return FORM_FIELD
	 */
	public function addValidatorLines($min_lines, $max_lines, $message = null) {
		if (!isset($message)) {
			$label = $this->getLabels();
			$message = array(
				'en' => ucfirst($label['en']) . " is not between {$min_lines} and {$max_lines} lines.",
				'fr' => ucfirst($label['fr']) . " n'est pas entre {$min_lines} et {$max_lines} lignes."
			);
		}
		$this->addValidator(FORM_VALIDATOR::$lines, array( $min_lines, $max_lines ), $message);
		return $this;
	}

	/**
	 * Value must be no more than max_lines number of lines
	 *
	 * @param int $max_lines
	 * @param array|string $message
	 * @return FORM_FIELD
	 */
	public function addValidatorMaxLines($max_lines, $message = null) {
		if (!isset($message)) {
			$label = $this->getLabels();
			$message = array(
				'en' => ucfirst($label['en']) . " cannot be more than {$max_lines} lines.",
				'fr' => ucfirst($label['fr']) . " ne peut être plus de {$max_lines} lignes."
			);
		}
		$this->addValidator(FORM_VALIDATOR::$maxlines, array( $max_lines ), $message);
		return $this;
	}

	/**
	 * Value must be no less than min_lines number of lines
	 *
	 * @param int $min_lines
	 * @param array|string $message
	 * @return FORM_FIELD
	 */
	public function addValidatorMinLines($min_lines, $message = null) {
		if (!isset($message)) {
			$label = $this->getLabels();
			$message = array(
				'en' => ucfirst($label['en']) . " cannot be less than {$min_lines} lines.",
				'fr' => ucfirst($label['fr']) . " ne peut être moin de {$min_lines} lignes."
			);
		}
		$this->addValidator(FORM_VALIDATOR::$minlines, array( $min_lines ), $message);
		return $this;
	}

	/**
	 * Value must be a valid postal code (seperating space optional)
	 *
	 * @param array|string $message
	 * @return FORM_FIELD
	 */
	public function addValidatorPostalCode($message=null) {
		if (!isset($message)) {
			$label = $this->getLabels();
			$message = array(
				'en' => ucfirst($label['en']) . " is not a valid postal code.",
				'fr' => ucfirst($label['fr']) . " n'est pas une code postale."
			);
		}
		$this->addValidator(FORM_VALIDATOR::$postalcode, null, $message);
		return $this;
	}

	/**
	 * Value must be on of the entries in the given array
	 *
	 * @param array $array
	 * @param array|string $message
	 * @return FORM_FIELD
	 */
	public function addValidatorIsOneOf(array $array, $message=null) {
		if (!isset($message)) {
			$label = $this->getLabels();
			$message = array(
				'en' => ucfirst($label['en']) . " is not valid.",
				'fr' => ucfirst($label['fr']) . " n'est pas valide."
			);
		}
		$this->addValidator(FORM_VALIDATOR::$isoneof, array($array), $message);
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
