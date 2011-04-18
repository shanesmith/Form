<?php

/**
* Basic exception for Form
*/
class FormException extends Exception {

	/**
	* @var FORM_ELEMENT
	*/
	private $element;

	/**
	* @param string $message
	* @param FORM_ELEMENT $element
	* @return FormException
	*/
	public function __construct($message, $element=null) {
		parent::__construct($message);
		$this->element = $element;
	}

	/**
	* @return FORM_ELEMENT
	*/
	public function getElement() { return $this->element; }

}

/**
* Invlid language used for getting or setting
*/
class FormInvalidLanguageException extends FormException {

	/**
	* @var string
	*/
	private $lang;

	/**
	* @param string $message
	* @param string $lang
	* @param FORM_ELEMENT $element
	* @return FormInvalidLanguageException
	*/
	function __construct($message=null, $lang=null, $element=null) {
		parent::__construct($message, $element);
		$this->lang = $lang;
	}

	/**
	* @return string
	*/
	function getLang() { return $this->lang; }

}

/**
* No valid renderer found while attempting to run render()
*/
class FormNoRendererFound extends FormException { }

/**
* An invalid validator was invoked
*/
class FormInvalidValidator extends FormException {

	private $validator;

	public function __construct($message, $element=null, $validator=null) {
		$this->validator = $validator;
		parent::__construct($message, $element);
	}

	public function getValidator() {
		return $this->validator;
	}

}

/**
* An invalid formatter was invoked
*/
class FormInvalidFormatter extends FormException {

	private $formatter;

	public function __construct($message, $element=null, $formatter=null) {
		$this->formatter = $formatter;
		parent::__construct($message, $element);
	}

	public function getFormatter() {
		return $this->formatter;
	}

}