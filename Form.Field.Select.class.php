<?php

require_once "Form.Field.class.php";

require_once "Form.utils.php";

class FORM_SELECT extends FORM_FIELD {

	static private $type = 'select';
	protected $validate_option=true, $validate_option_errortext='';
	protected static $static_renderer, $static_field_renderer;

	private $options;

	public function __construct($parent, $name, $label='', array $options=array(), $default=null) {
		$this->options($options, $default);
		parent::__construct($parent, $name, $label, $this->default_value);
		//$this->validator(array('FORM_SELECT', '_valid_option'), "The value of {$label} must be a valid option!");
	}

	public function options(array $options=null, $default=null) {
		if (!isset($options)) return $this->options;
		if (!isset($default)) $default = current(array_keys($options));

		$this->default_value = $default;
		$this->options = $options;

		return $this;
	}

	public function html() {
		$options = "";
		foreach ($this->options as $value=>$text) {
			if (is_array($text)) {
				$options .= "<optgroup label='{$value}'>";
				foreach ($text as $v=>$t) $options .= $this->_html_option($v, $t);
				$options .= "</optgroup>";
			} else {
				$options .= $this->_html_option($value, $text);
			}
		}

		$this->field_attr('name', $this->name);
		$attributes = $this->field_attr2str();

		return "<select {$attributes}>{$options}</select>";
	}

	protected function _html_option($value, $text) {
		$selected = ($value == $this->value()) ? "selected='selected'" : "";
		return "<option value='{$value}' {$selected}>{$text}</option>";
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

	public function type() { return self::$type; }

	public static function renderer($renderer) {
		if (isset($this)) $this->renderer = $renderer;
		else self::$static_renderer = $renderer;
	}

	public static function static_field_renderer($field_renderer) {
		self::$static_field_renderer = $field_renderer;
	}

	public function validate() {
		if (!empty($this->options) && $this->validate_option) $this->validator(array('FORM_SELECT', '_valid_option'), array(), $this->validate_option_errortext);
		return parent::validate();
	}

	public function validate_option($set=null, $errortext='') {
		if (isset($set)) {
			$this->validate_option = (bool) $set;
			$this->validate_option_errortext = $errortext;
			return $this;
		}
		return $this->validate_option;
	}

	static function _valid_option(FORM_SELECT &$element) {
		$valid = array_key_exists($element->value(), $element->options());
		if (!$valid) $element->value(current(array_keys($element->options())));

		return $valid;
	}

}
