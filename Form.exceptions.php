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
