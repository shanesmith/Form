<?php

require_once "Form.Field.class.php";

require_once "Form.utils.php";

class FORM_RADIO_LIST extends FORM_FIELD implements ArrayAccess {

	static private $type = 'radio_list';
	protected static $static_renderer, $static_field_renderer; // = array('self', "_default_radio_list_renderer");

	private $radios = array();

	public function __construct($parent, $name, $label='', $default=null) {
		//foreach ($radios as $value=>$label) $this->radios[$value] = new FORM_RADIO($parent, $name, $label, $value);
		parent::__construct($parent, $name, $label, $default);
	}

	/**
	* @param string $id
	* @param string $value
	* @param string $label
	* @return FORM_RADIO
	*/
	public function addRadio($id, $value, $label='') { return $this->radios[$id] = new FORM_RADIO($this, $this->name(), $label, $value); }

	/**
	* @param array $radios
	* @return FORM_RADIO_LIST
	*/
	public function addMultipleRadios(array $radios) {
		foreach($radios as $r) $this->addRadio($r[0], $r[1], (isset($r[2])?$r[2]:'') );
		return $this;
	}

	public function radio($id) { return $this->radios[$id]; }

	public function radios() { return $this->radios; }

	public function html() {
		foreach($this->radios() as $radio) $s .= $radio->render();
		return $s;
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

	public static function _default_radio_list_renderer($radio_list) {
		foreach($radio_list->radios() as $radio) $s .= $radio->render();
		return $s;
	}

	public function offsetExists($offset) { return !is_null($this->radio($offset)); }
	public function offsetGet($offset) { return $this->radio($offset); }
	public function offsetSet($offset, $value) { die("Cannot set elements through array access."); }
	public function offsetUnset($offset) { die("Cannot unset elements through array access."); }
}

class FORM_RADIO extends FORM_FIELD {

	static private $type = 'radio';
	protected static $static_renderer, $static_field_renderer;

	private $id;

	public function __construct($parent, $name, $label, $value) {
		parent::__construct($parent, $name, $label, null);
		$this->value($value);
	}

	public function html() {
		$this->field_addAttributes(array(
			'type' => 'radio',
			'name' => $this->name(),
			'value' => htmlspecialchars($this->value()),
		));

		if ($this->value() == $this->parent()->value()) $this->field_attr('checked', 'checked');

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

	public function type() { return self::$type; }

	public static function renderer($renderer) {
		if (isset($this)) $this->renderer = $renderer;
		else self::$static_renderer = $renderer;
	}

	public static function static_field_renderer($field_renderer) {
		self::$static_field_renderer = $field_renderer;
	}

	/**
	* @return FORM_RADIO_LIST
	*/
	public function parent() { return parent::parent(); }
}
