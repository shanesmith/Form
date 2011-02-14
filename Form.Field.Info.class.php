<?php

require_once "Form.Field.class.php";

require_once "Form.utils.php";

class FORM_INFO extends FORM_FIELD {

	static private $type = "info";
	protected static $static_renderer = array('self', '_info_renderer'), $static_field_renderer;

	public function __construct($parent, $name, $label='', $value=null) {
		parent::__construct($parent, $name, $label, '');
	}

	public function html() {
		$this->field_addClass("form-info");

		$attributes = $this->field_attr2str();

		$value = $this->value();

		if (is_array($value)) {
			$lang = $this->form()->language();

			$en = "<span class='form-field-info-en'>{$value[0]}</span>";
			$fr = "<span class='form-field-info-fr'>{$value[1]}</span>";

			if ($lang=='both') $value = $en.$fr;
			elseif ($lang=='fr') $value = $fr;
			else $value = $en;
		} else {
			$value = "<span class='form-field-info-nolang'>{$value}</span>";
		}

		return "<div {$attributes}>{$value}</div>";
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

	static function _info_renderer($element) {
		$s = "";

		$element->addClasses(array('form-element-container', 'form-field-container-info'));

		$attributes = $element->attr2str();
		$s .= "<div {$attributes}>";

			$label = $element->label();
			if (!empty($label)) {
				$s .= "<label class='form-field-label form-field-label-info'>";

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

			/*$label = $element->label();
			if (!empty($label)) {
				$s .= "<label class='form-field-label form-field-label-info'><span>{$label}</span></label>";
			}*/

			$html = $element->html();
			$s .= "<div class='form-field form-field-info'>{$html}</div>";

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
