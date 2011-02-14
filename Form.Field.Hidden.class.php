<?php

require_once "Form.Field.class.php";

require_once "Form.utils.php";

class FORM_HIDDEN extends FORM_FIELD {

	static private $type = 'hidden';
	protected static $static_renderer = array('self', '_default_hidden_renderer'), $static_field_renderer;

	public function __construct($parent, $name, $default=null) {
		parent::__construct($parent, $name, '', $default);
	}

	public function html() { return parent::html('hidden'); }

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

	static function _default_hidden_renderer($element) { return $element->html(); }

}
