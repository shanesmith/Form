<?php

require_once "Form.utils.php";

abstract class FORM_ELEMENT {

	static private $type;

	protected $name, $label, $parent, $form, $renderer, $attributes=array();

	protected $saveToSession, $loadFromSession;

	protected $validators = array();

	function __construct($parent, $name, $label=null) {
		$this->parent = $parent;
		$this->name = $name;
		$this->label = $label;
	}

	/**
	* Return the element's name
	*
	* @return string the element's name
	*/
	public function name() { return $this->name; }

	/**
	* If $label is set, then sets the label and returns the element,
	* else the element's label is returned
	*
	* @param string $label
	* @return string|FORM_ELEMENT
	*/
	public function label($label=null) { return isset($label) ? $this->setLabel($label) : $this->getLabel(); }

	/**
	* Set the element's label
	*
	* @param string|array $label the element's label
	* @return FORM_ELEMENT
	*/
	public function setLabel($label) {
		$this->label = $label;
		return $this;
	}

	/**
	* Return the element's label
	*
	* @return string
	*/
	public function getLabel($useLang=true) {
		$label = $this->label;

		/*if ($useLang) {
			if (is_array($label)) {
				$lang = $this->form()->lang();

				$en = $label[0];
				$fr = $label[1];

				if ($lang=='both') $label = "<span class='label-lang label-lang-en'>{$en}"

				$label = ($lang=='en') ? $en : $fr;
			}
		}*/

		return $label;

	}

	/**
	* Return the element's parent
	*
	* @return FORM_FIELDSET
	*/
	public function parent() { return $this->parent; }

	/**
	* Climbs up the parent hierarchy all the way to the root (ie: the form itself) and returns it
	*
	* @return FORM
	*/
	public function form() {
		// Can't use static to remember form since there may be multiple forms...

		if (!isset($this->form)) {
			$form =& $this;
			while (!($form instanceof FORM)) $form = $form->parent();
			$this->form = $form;
		}
		return $this->form;
	}

	/**
	* If $attributes is set, then sets the attributes and returns the element,
	* else the element's attributes is returned
	*
	* @param mixed $attributes
	*/
	public function attributes(array $attributes=null) { return isset($attributes) ? $this->setAttributes($attributes) : $this->getAttributes(); }

	/**
	* Erases and sets the element's attributes
	*
	* @param array $attributes
	* @return FORM_ELEMENT
	*/
	public function setAttributes(array $attributes) {
		$this->attributes = $attributes;
		return $this;
	}

	/**
	* Returns the element's attributes
	*
	* @return array
	*/
	public function getAttributes() { return $this->attributes; }

	/**
	* Merges $attributes with the current set of the element's attributes
	*
	* @param array $attributes
	* @return FORM_ELEMENT
	*/
	public function addAttributes (array $attributes) {
		$this->attributes = array_merge($this->attributes, $attributes);
		return $this;
	}

	/**
	* If $value is set, sets the attribute with key $key and returns the element,
	* else return the attribute value at key $key
	*
	* @param string $key
	* @param mixed $value
	* @return mixed|FORM_ELEMENT
	*/
	public function attr($key, $value=null) {
		if (isset($value)) {
			$this->attributes[$key] = $value;
			return $this;
		} else {
			return isset($this->attributes[$key]) ? $this->attributes[$key] : null;
		}
	}

	/**
	* Appends a class to the element's class attribute
	*
	* @param string $class
	* @return FORM_ELEMENT
	*/
	public function addClass($class) {
		if (!isset($this->attributes['class'])) {
			$this->attributes['class'] = array();
		} elseif (is_string($this->attributes['class'])) {
			$this->attributes['class'] = explode(" ", $this->attributes['class']);
		}

		$this->attributes['class'][] = $class;

		return $this;
	}

	/**
	* Iteritavely adds each class in $classes to the element's class attribute
	*
	* @param mixed $classes
	* @return FORM_ELEMENT
	*/
	public function addClasses(array $classes) {
		foreach ($classes as $class) $this->addClass($class);
		return $this;
	}

	/**
	* Sets the element's id attribute
	*
	* @param mixed $id
	* @return FORM_ELEMENT
	*/
	public function id($id) { $this->attr('id', $id); return $this; }

	/**
	* Returns the element's attributes converted into and html attributes string
	*
	* @return string
	*/
	public function attr2str() { return attr2str($this->attributes); }

	public function setError($message) {
		$this->form()->setError($message, $this);
	}

	public function getError() {
		return $this->form()->getError($this->name());
	}

	/**
	* Set a validator for the element.
	*
	* @param callback $validator A function which returns whether or not the element is valid.
	* @param array $args Arguments to pass to the validator
	* @param string $errortext Text to display in case the element is invalid
	*/
	public function validator($validator, $args=array(), $errortext='') { $this->validators[] = array($validator, $args, $errortext); return $this; }

	public function validate() {
		$valid = true;
		foreach ($this->validators as $validator) {
			$function = $validator[0];
			if (is_callable($function)) {
				$error_text = $validator[2];
				$arguments = array_merge(array($this), (array)$validator[1]);
				$ok = call_user_func_array($function, $arguments);
				if (!$ok) {
					$valid = false;
					if (is_array($error_text)) {
						$error_text = ($this->form()->lang()=='fr' ? $error_text[1] : $error_text[0]);
					}
					$this->setError($error_text);
				}
			} else {
				user_error("{$function} is not callable!", E_USER_WARNING );
			}
		}
		return $valid;
	}

	public function loadFromSession($set=null) {
		if (isset($set)) {
			$this->loadFromSession = (bool) $set;
			return $this;
		}
		return isset($this->loadFromSession) ? $this->loadFromSession : $this->parent()->loadFromSession();
	}

	public function saveToSession($set=null) {
		if (isset($set)) {
			$this->saveToSession = (bool) $set;
			return $this;
		}
		return isset($this->saveToSession) ? $this->saveToSession : $this->parent()->saveToSession();
	}

}