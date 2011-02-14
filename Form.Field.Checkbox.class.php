<?php

require_once "Form.Field.class.php";

require_once "Form.utils.php";

class FORM_CHECKBOX extends FORM_FIELD {

	static private $type = 'checkbox';
	protected static $static_renderer, $static_field_renderer;

	public function __construct($parent, $name, $label='', $default=null) {
		parent::__construct($parent, $name, $label, $default);
		//if ($this->form()->isPosted() && !$this->form()->postValue($name)) $this->post_value = false;
	}

	public function html() {
		$this->field_addAttributes(array(
			'type' => 'checkbox',
			'name' => $this->name,
			'value' => 'on',
		));

		if ($this->value()) $this->field_attr('checked', 'checked');

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

	public function loadPostedValue() {
		$form = $this->form();

		$trim = (bool)$form->option('trim');

		$this->post_value = $form->loadPostedValue($this->name());

		if (!isset($this->post_value) && $form->isPosted()) $this->post_value = false;

		return $this;
	}

	/*static function labeled_renderer($element) {
		$id = $element->attr("id");
		if (!isset($id)) {
			$element->attr("id", $element->name());
			$id = $element->name();
		}
		$html = $element->html();
		$label = $element->label();
		return "<label for='{$id}'>{$html} {$label}</label>";
	}*/

	public function type() { return self::$type; }

	public static function renderer($renderer) {
		if (isset($this)) $this->renderer = $renderer;
		else self::$static_renderer = $renderer;
	}

	public static function static_field_renderer($field_renderer) {
		self::$static_field_renderer = $field_renderer;
	}
}