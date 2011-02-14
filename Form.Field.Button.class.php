<?php

require_once "Form.Field.class.php";

require_once "Form.utils.php";

class FORM_BUTTOM extends FORM_FIELD {

	static private $type = 'button';
	protected static $static_renderer = array('self', '_button_renderer'), $static_field_renderer;

	public function __construct($parent, $name, $text='button') {
		parent::__construct($parent, $name, '', $text);
		$this->value($text);
	}

	public function html() {
		$value = $this->value();

		if (is_array($value)) {
			$lang = $this->form()->language();

			$en = $value[0];
			$fr = $value[1];

			if ($lang=='both') $value = "{$en} / {$fr}";
			elseif ($lang=='fr') $value = $fr;
			else $value = $en;
		}

		$this->field_addAttributes(array(
			'type' => 'button',
			'name' => $this->name,
			'value' => $value
		));

		$attributes = $this->field_attr2str();

		return "<input {$attributes} />";
	}

	public function render($renderer=null) {
		if (is_callable($renderer)) return call_user_func($renderer, $this);
		elseif (is_callable($this->renderer)) return call_user_func($this->renderer, $this);
		elseif (is_callable(self::$static_renderer)) return call_user_func(self::$static_renderer, $this);
		elseif (is_callable(parent::$base_renderer)) return call_user_func(parent::$base_renderer, $this);
		else return "";
	}

	public function render_field($field_renderer=null) {
		if (is_callable($field_renderer)) return call_user_func($field_renderer, $this);
		elseif (is_callable($this->field_renderer)) return call_user_func($this->field_renderer, $this);
		elseif (is_callable(self::$static_field_renderer)) return call_user_func(self::$static_field_renderer, $this);
		elseif (is_callable(parent::$base_field_renderer)) return call_user_func(parent::$base_field_renderer, $this);
		else return "";
	}

	static function _button_renderer($element) {
		$element->addClasses(array('form-element-container', 'form-field-container', "form-field-container-button"));

		$html = $element->html();

		$attributes = $element->attr2str();

		$s  = "<div {$attributes}>";
		$s .= "		<div class='form-field form-field-button'>{$html}</div>";
		$s .= "</div>";
		return $s;
	}

	public function type() { return self::$type; }

	public static function renderer($renderer) {
		if (isset($this)) $this->renderer = $renderer;
		else self::$static_renderer = $renderer;
	}

	public static function static_field_renderer($field_renderer) {
		self::$static_field_renderer = $field_renderer;
	}

}

class FORM_SUBMIT_BUTTOM extends FORM_FIELD {

	static private $type = 'submit_button';
	protected static $static_renderer = array('self', '_submit_renderer'), $static_field_renderer;

	public function __construct($parent, $name, $text='Submit') {
		parent::__construct($parent, $name, '', $text);
		$this->value($text);
	}

	public function html() {
		$value = $this->value();

		if (is_array($value)) {
			$lang = $this->form()->language();

			$en = $value[0];
			$fr = $value[1];

			if ($lang=='both') $value = "{$en} / {$fr}";
			elseif ($lang=='fr') $value = $fr;
			else $value = $en;
		}

		$this->field_addAttributes(array(
			'type' => 'submit',
			'name' => $this->name,
			'value' => $value
		));

		$attributes = $this->field_attr2str();

		return "<input {$attributes} />";
	}

	public function render($renderer=null) {
		if (is_callable($renderer)) return call_user_func($renderer, $this);
		elseif (is_callable($this->renderer)) return call_user_func($this->renderer, $this);
		elseif (is_callable(self::$static_renderer)) return call_user_func(self::$static_renderer, $this);
		elseif (is_callable(parent::$base_renderer)) return call_user_func(parent::$base_renderer, $this);
		else return "";
	}

	public function render_field($field_renderer=null) {
		if (is_callable($field_renderer)) return call_user_func($field_renderer, $this);
		elseif (is_callable($this->field_renderer)) return call_user_func($this->field_renderer, $this);
		elseif (is_callable(self::$static_field_renderer)) return call_user_func(self::$static_field_renderer, $this);
		elseif (is_callable(parent::$base_field_renderer)) return call_user_func(parent::$base_field_renderer, $this);
		else return "";
	}

	static function _submit_renderer($element) {
		$element->addClasses(array('form-element-container', 'form-field-container', "form-field-container-submit_button"));

		$html = $element->html();

		$attributes = $element->attr2str();

		$s  = "<div {$attributes}>";
		$s .= "		<div class='form-field form-field-submit_button'>{$html}</div>";
		$s .= "</div>";
		return $s;
	}

	public function type() { return self::$type; }

	public static function renderer($renderer) {
		if (isset($this)) $this->renderer = $renderer;
		else self::$static_renderer = $renderer;
	}

	public static function static_field_renderer($field_renderer) {
		self::$static_field_renderer = $field_renderer;
	}

	public function submitted() {
		$form = $this->form();
		$postvar = $form->postvar();

		return $form->isPosted() && array_key_exists($this->name, $postvar);
	}

}

class FORM_RESET_BUTTOM extends FORM_FIELD {

	static private $type = 'reset_button';
	protected static $static_renderer = array('self', '_reset_renderer'), $static_field_renderer;

	public function __construct($parent, $name, $text='Reset') {
		parent::__construct($parent, $name, '', $text);
		$this->value($text);
	}

	public function html() {
		$value = $this->value();

		if (is_array($value)) {
			$lang = $this->form()->language();

			$en = $value[0];
			$fr = $value[1];

			if ($lang=='both') $value = "{$en} / {$fr}";
			elseif ($lang=='fr') $value = $fr;
			else $value = $en;
		}

		$this->field_addAttributes(array(
			'type' => 'reset',
			'name' => $this->name,
			'value' => $value
		));

		$attributes = $this->field_attr2str();

		return "<input {$attributes} />";
	}

	public function render($renderer=null) {
		if (is_callable($renderer)) return call_user_func($renderer, $this);
		elseif (is_callable($this->renderer)) return call_user_func($this->renderer, $this);
		elseif (is_callable(self::$static_renderer)) return call_user_func(self::$static_renderer, $this);
		elseif (is_callable(parent::$base_renderer)) return call_user_func(parent::$base_renderer, $this);
		else return "";
	}

	public function render_field($field_renderer=null) {
		if (is_callable($field_renderer)) return call_user_func($field_renderer, $this);
		elseif (is_callable($this->field_renderer)) return call_user_func($this->field_renderer, $this);
		elseif (is_callable(self::$static_field_renderer)) return call_user_func(self::$static_field_renderer, $this);
		elseif (is_callable(parent::$base_field_renderer)) return call_user_func(parent::$base_field_renderer, $this);
		else return "";
	}

	static function _reset_renderer($element) { return $element->html(); }

	public function type() { return self::$type; }

	public static function renderer($renderer) {
		if (isset($this)) $this->renderer = $renderer;
		else self::$static_renderer = $renderer;
	}

	public static function static_field_renderer($field_renderer) {
		self::$static_field_renderer = $field_renderer;
	}

}