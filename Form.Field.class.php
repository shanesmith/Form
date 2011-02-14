<?php

require_once "Form.Element.class.php";

require_once "Form.utils.php";

abstract class FORM_FIELD extends FORM_ELEMENT {

	protected $default_value;

	protected $value, $post_value, $session_value;

	protected $field_attributes = array();

	protected static $base_renderer 	  	= array('self', '_default_renderer'),
					 $base_field_renderer 	= array('self', '_default_field_renderer');

	public function __construct($parent, $name, $label='', $default=null) {
		parent::__construct($parent, $name, $label);
		$this->default_value = $default;
	}

	public function loadPostedValue() {
		$trim = (bool)$this->form()->option('trim');

		$this->post_value = $this->form()->loadPostedValue($this->name());

		if ($trim && $this->post_value !== null) $this->post_value = trim($this->post_value);

		return $this;
	}

	public function loadSessionedValue() {
		$trim = (bool)$this->form()->option('trim');

		$this->session_value = $this->form()->loadSessionedValue($this->name());

		if ($trim && $this->session_value !== null) $this->session_value = trim($this->session_value);

		return $this;
	}

	public function postValue() { return $this->post_value; }

	public function sessionValue() { return $this->session_value; }

	public function defaultValue() { return $this->default_value; }

	public function value($value=null) {
		if (isset($value)) {
			$this->value = $value;
			return $this;
		}

		$this->loadPostedValue();
		if ($this->loadFromSession()) $this->loadSessionedValue();

		switch ($this->form()->useValue()) {
			case 'value': return $this->value;
			case 'post': return $this->post_value;
			case 'session': return $this->session_value;
			case 'default': return $this->default_value;
			default: {
				if (isset($this->value)) return $this->value;
				elseif (isset($this->post_value)) return $this->post_value;
				elseif (isset($this->session_value)) return $this->session_value;
				else return $this->default_value;
			}
		}
	}

	public function field_attributes(array $attributes=null) {
		if (isset($attributes)) {
			$this->field_attributes = $attributes;
			return $this;
		}

		return $this->field_attributes;
	}

	public function field_addAttributes (array $attributes) {
		$this->field_attributes = array_merge($this->field_attributes, $attributes);
		return $this;
	}

	public function field_attr($key, $value=null) {
		if (isset($value)) {
			$this->field_attributes[$key] = $value;
			return $this;
		}

		return $this->field_attributes[$key];
	}

	public function field_addClass($class) {
		if (!isset($this->field_attributes['class'])) {
			$this->field_attributes['class'] = array();
		} elseif (is_string($this->field_attributes['class'])) {
			$this->field_attributes['class'] = explode(' ', $this->field_attributes['class']);
		}

		$this->field_attributes['class'][] = $class;

		return $this;
	}

	public function field_addClasses(array $classes) {
		foreach ($classes as $class) $this->field_addClass($class);

		return $this;
	}

	public function field_id($id) {
		$this->field_attr('id', $id);

		return $this;
	}

	public function field_attr2str() { return attr2str($this->field_attributes); }

	public function html($type) {
		$this->field_addAttributes(array(
			'type' => $type,
			'name' => $this->name,
			'value' => htmlspecialchars($this->value(), ENT_QUOTES)
		));
		$attributes = $this->field_attr2str();
		return "<input {$attributes} />";
	}

	abstract public function render($renderer='');

	abstract public function render_field($renderer='');

	public static function renderer($renderer) {
		self::$base_renderer = $renderer;
	}

	public function field_renderer($field_renderer) {
		$this->field_renderer = $field_renderer;
		return $this;
	}

	public static function static_field_renderer($field_renderer) {
		self::$base_field_renderer = $field_renderer;
	}

	public function __toString() { return $this->render(); }

	public static function _default_renderer($element) {

		$type = $element->type();

		$element->addClasses(array('form-element-container', 'form-field-container', "form-field-container-{$type}"));
		if ($element->getError()) $element->addClasses(array('form-element-error', 'form-field-error'));

		$attributes = $element->attr2str();
		$s  = "<div {$attributes}>";

			$label = $element->label();
			if (!empty($label)) {

				if ($element->field_attr('id') == null) $element->field_attr('id', uniqid('form-id-'));

				$field_id = $element->field_attr('id');

				$s .= "<label class='form-element-label form-field-label form-field-label-{$type}' for='{$field_id}'>";

				if (is_array($label)) {
					$lang = $element->form()->language();
					$en = "<span class='form-field-label-en'>{$label[0]}</span>";
					$fr = "<span class='form-field-label-fr'>{$label[1]}</span>";

					if ($lang=='both') $s .= $en.$fr;
					elseif ($lang=='fr') $s .= $fr;
					else $s .= $en;
				} else {
					$s .= "<span class='form-field-label-nolang'>{$label}</span>";
				}

				$s .= "</label>";
			}

			$type = $element->type();
			$field = $element->render_field();
			$s .= "<div class='form-field form-field-{$type}'>{$field}</div>";

		$s .= "</div>";
		return $s;
	}

	public static function _default_field_renderer($element) { return $element->html(); }

	public function required($text) { $this->validator(array('FORM_FIELD', '_required'), array(), $text); return $this; }
	public static function _required($element) { return (bool)strlen($element->value()); }

	public function required_not($value, $text) { $this->validator(array('FORM_FIELD', '_required_not'), array($value), $text); return $this; }
	public static function _required_not($element, $value) { return $element->value() != $value; }

}
