<?php

require_once "Form.Element.class.php";

require_once "Form.utils.php";

class FORM_FIELDSET extends FORM_ELEMENT implements ArrayAccess {

	static private $type = 'fieldset';

	protected $elements=array();

	protected static $static_renderer = array('self', "_default_renderer");

	protected $field_renderers = array();

	public function __construct($parent, $name, $label='') {
		$this->parent = $parent;
		$this->name = $name;
		$this->label = $label;
	}

	/**
	* Creates a new fieldset, inserts it into the current fieldset, and return the new fieldset
	*
	* @param string $name
	* @param string $label
	* @return FORM_FIELDSET
	*/
	public function fieldset($name, $label='') {
		return $this->addElement(new FORM_FIELDSET($this, $name, $label));
	}

	/**
	* Creates a new text field, inserts it into the current fieldset, and returns the new text field
	*
	* @param string $name
	* @param string $label
	* @param string $default
	* @return FORM_TEXT
	*/
	public function text($name, $label='', $default=null) {
		return $this->addElement(new FORM_TEXT($this, $name, $label, $default));
	}

	/**
	* Creates a new textarea field, inserts it into the current fieldset, and returns the new textarea field
	*
	* @param string $name
	* @param string $label
	* @param string $default
	* @return FORM_TEXTAREA
	*/
	public function textarea($name, $label='', $default=null) {
		return $this->addElement(new FORM_TEXTAREA($this, $name, $label, $default));
	}

	/**
	* Creates a new hidden field, inserts it into the current fieldset, and returns the new hidden field
	*
	* @param string $name
	* @param string $default
	* @return FORM_HIDDEN
	*/
	public function hidden($name, $default=null) {
		return $this->addElement(new FORM_HIDDEN($this, $name, $default));
	}

	/**
	* Creates a new password field, inserts it into the current fieldset, and returns the new password field
	*
	* @param string $name
	* @param string $label
	* @param string $default
	* @return FORM_PASSWORD
	*/
	public function password($name, $label='', $default=null) {
		return $this->addElement(new FORM_PASSWORD($this, $name, $label, $default));
	}

	/**
	* Creates a new file field, inserts it into the current fieldset, and returns the new file field
	*
	* @param string $name
	* @param string $label
	* @return FORM_FILE
	*/
	public function file($name, $label='') {
		$this->form()->attr('enctype', FORM::ENCTYPE_FILE);
		return $this->addElement(new FORM_FILE($this, $name, $label)); }

	/**
	* Creates a new select field, inserts it into the current fieldset, and returns the new select field
	*
	* @param string $name
	* @param string $label
	* @param array $options an array of options in the $value=>$text format
	* @param string $default
	* @return FORM_SELECT
	*/
	public function select($name, $label='', $options=array(), $default=null) {
		return $this->addElement(new FORM_SELECT($this, $name, $label, $options, $default));
	}

	/**
	* Creates a new checkbox field, inserts it into the current fieldset, and returns the new checkbox field
	*
	* @param string $name
	* @param string $label
	* @param string $default
	* @return FORM_CHECKBOX
	*/
	public function checkbox($name, $label='', $default=null) {
		return $this->addElement(new FORM_CHECKBOX($this, $name, $label, $default));
	}

	/**
	* Creates a new radio list, inserts it into the current fieldset, and returns the new radio list
	*
	* @param string $name
	* @param string $label
	* @param string $default
	* @return FORM_RADIO_LIST
	*/
	public function radio_list($name, $label='', $default=null) {
		return $this->addElement(new FORM_RADIO_LIST($this, $name, $label, $default));
	}

	/**
	* Creates a new button, inserts it into the current fieldset, and returns the new button
	*
	* @param string $name
	* @param string $text
	* @return FORM_BUTTOM
	*/
	public function button($name, $text='') {
		return $this->addElement(new FORM_BUTTOM($this, $name, $text));
	}

	/**
	* Creates a new submit button, inserts it into the current fieldset, and returns the new submit button
	*
	* @param string $name
	* @param string $text
	* @return FORM_SUBMIT_BUTTOM
	*/
	public function submit_button($name, $text='Submit') {
		return $this->addElement(new FORM_SUBMIT_BUTTOM($this, $name, $text));
	}

	/**
	* Creates a new reset button, inserts it into the current fieldset, and returns the new reset button
	*
	* @param string $name
	* @param string $text
	* @return FORM_RESET_BUTTON
	*/
	public function reset_button($name, $text='Reset') {
		return $this->addElement(new FORM_RESET_BUTTOM($this, $name, $text));
	}

	/**
	* Creates a new info field, inserts it into the current fieldset, and returns the new info field
	*
	* @param string $name
	* @param string $label
	* @param string $text
	* @return FORM_INFO
	*/
	public function info($name, $label, $text='') {
		return $this->addElement(new FORM_INFO($this, $name, $label, $text));
	}

	function elements() { return $this->elements; }

	private function addElement(FORM_ELEMENT $elem) {
		return $this->elements[$elem->name()] =& $elem;
	}

	function removeElement($name) {
		if (isset($this->elements[$name])) {
			unset($this->elements[$name]);
			return true;
		}

		return false;
	}

	/**
	* Returns the element with the provided name
	*
	* @param string $name
	* @return FORM_ELEMENT
	*/
	public function getElement($name) { return $this->elements[$name]; }

	/**
	* Recursively returns an array of element $name=>$value of element found inside the fieldset and child fieldsets
	*
	* If parameters are passed to this function, only values with those names are returned.
	*
	* @param $element_name
	* @param $...
	* @return array
	*/
	public function values(){
		$values = array();

		foreach ($this->elements as $elem) {
			if ($elem instanceof FORM_FIELDSET) {
				$values = array_merge_recursive($values, $elem->values());
			} else {
				$arr = array();

				if ( $elem instanceof FORM_CHECKBOX && $elem->value()=='' ) continue;

				parse_str("{$elem->name()}={$elem->value()}", $arr);
				$values = array_merge_recursive($values, $arr);

			}
		}

/*		$names = func_get_args();

		foreach ($this->elements as $elem) {
			if ($elem instanceof FORM_FIELDSET) {
				$values = array_merge_recursive($values, $elem->values());
			} else {
				if (preg_match_all('/\[([^\]]+)\]/', $elem->name(), $matches)) {
					$first = substr($elem->name(), 0, strpos($elem->name(), '['));
					if (!isset($values[$first])) $values[$first] = array();
					$tmp_values =& $values[$first];
					foreach($matches[1] as $m) {
						if (!isset($tmp_values[$m])) $tmp_values[$m] = array();
						$tmp_values =& $tmp_values[$m];
					}
					$tmp_values = $elem->value();
				} else {
					$values[$elem->name()] = $elem->value();
				}
			}
		}

		if (!empty($names)) {
			foreach ($names as $n) $tmp[$n] = $values[$n];
			$values = $tmp;
		}*/

		return $values;
	}

	public function session_save_values() {
		$values = array();

		foreach ($this->elements as $elem) {
			if ($elem instanceof FORM_FIELDSET) {
				$values = array_merge_recursive($values, $elem->session_save_values());
			} elseif ($elem->saveToSession()) {
				$arr = array();
				parse_str("{$elem->name()}={$elem->value()}", $arr);
				$values = array_merge_recursive($values, $arr);
			}
		}

		return $values;
	}

	/**
	* First concatenates all element renderings, and then passes the rendering to the first available renderer, which returns the fieldset's rendering
	*
	* @param callback $renderer
	* @return string
	*/
	public function render($renderer=null) {
		$elements = "";
		foreach ($this->elements as $elem) $elements .= $elem->render();

		if (is_callable($renderer)) return call_user_func($renderer, $this, $elements);
		elseif (is_callable($this->renderer)) return call_user_func($this->renderer, $this, $elements);
		elseif (is_callable(self::$static_renderer)) return call_user_func(self::$static_renderer, $this, $elements);
		else return "";
	}

	/**
	* Sets the appropiate renderer, depending on if the function was called in a dynamic or static context
	*
	* @param callback $renderer
	*/
	public function renderer($renderer) {
		if (isset($this)) $this->renderer = $renderer;
		else self::$static_renderer = $renderer;
	}

	public function field_renderer($field, $renderer=null) {
		if (isset($renderer)) {
			$this->field_renderers[$field] = $renderer;
			return $this;
		} else {
			if (isset($this->field_renderers[$field])) return $this->field_renderers[$field];
			elseif ($this->parent()) return $this->parent()->field_renderer($field);
			else return null;
		}
	}

	/**
	* Returns the ANDed validation of all sub element
	*
	* @return boolean
	*/
	public function validate() {
		$valid = true;

		$valid &= parent::validate();

		foreach ($this->elements as $elem) {
			$valid &= $elem->validate();
		}

		return $valid;
	}

	/**
	* @param mixed $name
	* @return FORM_FIELD
	*/
	public function findField($name) {
		foreach ($this->elements as $elem) {
			if ($elem instanceof FORM_FIELDSET) {
				$found = $elem->findField($name);
				if ($found !== null) return $found;
			} else {
				if ($elem->name() == $name) return $elem;
			}
		}

		return null;
	}

	/**
	* @param mixed $name
	* @return FORM_ELEMENT
	*/
	public function findElement($name) {
		foreach ($this->elements as $elem) {
			if ($elem->name() == $name) {
				return $elem;
			} elseif ($elem instanceof FORM_FIELDSET) {
				$found = $elem->findElement($name);
				if ($found != null) return $found;
			}
		}

		return null;
	}

	public function processFileFields($folder=null) {

		$success = true;

		foreach ($this->elements as $elem) {
			switch ($elem->type()) {
				case 'fieldset': {
					$success &= $elem->processFileFields($folder);
					break;
				}
				case 'file': {
					$success &= $elem->process($folder);
				}
			}

		}

		return $success;

	}

	/**
	* A default renderer for fieldsets
	*
	* @param FORM_FIELDSET $fieldset
	* @param string $elements
	*/
	public static function _default_renderer($fieldset, $elements) {
		$label = $fieldset->label();

		$fieldset->addClasses(array('form-element-container', 'form-fieldset-container'));
		if ($fieldset->getError()) $fieldset->addClasses(array('form-element-error', 'form-fieldset-error'));

		$attributes = $fieldset->attr2str();

		$s  = "<div {$attributes}>";

		if (!empty($label)) {
			$s .= "<label class='form-element-label form-fieldset-label'>";

			if (is_array($label)) {
				$lang = $fieldset->form()->language();
				$en = "<span class='form-fieldset-label-en'>{$label[0]}</span>";
				$fr = "<span class='form-fieldset-label-fr'>{$label[1]}</span>";

				if ($lang=='both') $s .= $en.$fr;
				elseif ($lang=='fr') $s .= $fr;
				else $s .= $en;
			} else {
				$s .= "<span class='form-fieldset-label-nolang'>{$label}</span>";
			}

			$s .= "</label>";
		}

		$s .= "		<div class='form-fieldset'>{$elements}</div>";

		$s .= "</div>";

		return $s;
	}

	public function type() { return self::$type; }

	public function __toString() { return $this->render(); }

	public function offsetExists($offset) { return !is_null($this->getElement($offset)); }
	public function offsetGet($offset) { return $this->getElement($offset); }
	public function offsetSet($offset, $value) { die("Cannot set elements through array access."); }
	public function offsetUnset($offset) { $this->removeElement($offset); }
}
