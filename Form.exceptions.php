<?php

/**
* Basic exception for Form
*/
class FormException extends Exception {

}

/**
* Invlid language used for getting or setting
*/
class FormInvalidLanguageException extends FormException {

	private $lang;

	function __construct($message=null, $lang=null) {
		$this->lang = $lang;

		parent::__construct($message);
	}

	function getLang() { return $this->lang; }

}
