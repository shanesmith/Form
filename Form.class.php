<?
/**
* TO-DO
*
* - better SESSION handling
* - SELECT OPTGROUPs
*
*/

include_once 'Alerts.class.php';

abstract class FORM_ELEMENT {

	static private $type;

	protected $name, $label, $parent, $form, $renderer, $attributes=array();

	protected $saveToSession, $loadFromSession;

	protected $validators = array();

	function __construct($parent, $name, $label=null) {
		$this->parent = $parent;
		$this->name = $name;
		$this->label = $label;
	}

	/**
	* Return the element's name
	*
	* @return string the element's name
	*/
	public function name() { return $this->name; }

	/**
	* If $label is set, then sets the label and returns the element,
	* else the element's label is returned
	*
	* @param string $label
	* @return string|FORM_ELEMENT
	*/
	public function label($label=null) { return isset($label) ? $this->setLabel($label) : $this->getLabel(); }

	/**
	* Set the element's label
	*
	* @param string|array $label the element's label
	* @return FORM_ELEMENT
	*/
	public function setLabel($label) {
		$this->label = $label;
		return $this;
	}

	/**
	* Return the element's label
	*
	* @return string
	*/
	public function getLabel($useLang=true) {
		$label = $this->label;

		/*if ($useLang) {
			if (is_array($label)) {
				$lang = $this->form()->lang();

				$en = $label[0];
				$fr = $label[1];

				if ($lang=='both') $label = "<span class='label-lang label-lang-en'>{$en}"

				$label = ($lang=='en') ? $en : $fr;
			}
		}*/

		return $label;

	}

	/**
	* Return the element's parent
	*
	* @return FORM_FIELDSET
	*/
	public function parent() { return $this->parent; }

	/**
	* Climbs up the parent hierarchy all the way to the root (ie: the form itself) and returns it
	*
	* @return FORM
	*/
	public function form() {
		// Can't use static to remember form since there may be multiple forms...

		if (!isset($this->form)) {
			$form =& $this;
			while (!($form instanceof FORM)) $form = $form->parent();
			$this->form = $form;
		}
		return $this->form;
	}

	/**
	* If $attributes is set, then sets the attributes and returns the element,
	* else the element's attributes is returned
	*
	* @param mixed $attributes
	*/
	public function attributes(array $attributes=null) { return isset($attributes) ? $this->setAttributes($attributes) : $this->getAttributes(); }

	/**
	* Erases and sets the element's attributes
	*
	* @param array $attributes
	* @return FORM_ELEMENT
	*/
	public function setAttributes(array $attributes) {
		$this->attributes = $attributes;
		return $this;
	}

	/**
	* Returns the element's attributes
	*
	* @return array
	*/
	public function getAttributes() { return $this->attributes; }

	/**
	* Merges $attributes with the current set of the element's attributes
	*
	* @param array $attributes
	* @return FORM_ELEMENT
	*/
	public function addAttributes (array $attributes) {
		$this->attributes = array_merge($this->attributes, $attributes);
		return $this;
	}

	/**
	* If $value is set, sets the attribute with key $key and returns the element,
	* else return the attribute value at key $key
	*
	* @param string $key
	* @param mixed $value
	* @return mixed|FORM_ELEMENT
	*/
	public function attr($key, $value=null) {
		if (isset($value)) {
			$this->attributes[$key] = $value;
			return $this;
		} else {
			return isset($this->attributes[$key]) ? $this->attributes[$key] : null;
		}
	}

	/**
	* Appends a class to the element's class attribute
	*
	* @param string $class
	* @return FORM_ELEMENT
	*/
	public function addClass($class) {
		if (!isset($this->attributes['class'])) {
			$this->attributes['class'] = array();
		} elseif (is_string($this->attributes['class'])) {
			$this->attributes['class'] = explode(" ", $this->attributes['class']);
		}

		$this->attributes['class'][] = $class;

		return $this;
	}

	/**
	* Iteritavely adds each class in $classes to the element's class attribute
	*
	* @param mixed $classes
	* @return FORM_ELEMENT
	*/
	public function addClasses(array $classes) {
		foreach ($classes as $class) $this->addClass($class);
		return $this;
	}

	/**
	* Sets the element's id attribute
	*
	* @param mixed $id
	* @return FORM_ELEMENT
	*/
	public function id($id) { $this->attr('id', $id); return $this; }

	/**
	* Returns the element's attributes converted into and html attributes string
	*
	* @return string
	*/
	public function attr2str() { return attr2str($this->attributes); }

	public function setError($message) {
		$this->form()->setError($message, $this);
	}

	public function getError() {
		return $this->form()->getError($this->name());
	}

	/**
	* Set a validator for the element.
	*
	* @param callback $validator A function which returns whether or not the element is valid.
	* @param array $args Arguments to pass to the validator
	* @param string $errortext Text to display in case the element is invalid
	*/
	public function validator($validator, $args=array(), $errortext='') { $this->validators[] = array($validator, $args, $errortext); return $this; }

	public function validate() {
		$valid = true;
		foreach ($this->validators as $validator) {
			$function = $validator[0];
			if (is_callable($function)) {
				$error_text = $validator[2];
				$arguments = array_merge(array($this), (array)$validator[1]);
				$ok = call_user_func_array($function, $arguments);
				if (!$ok) {
					$valid = false;
					if (is_array($error_text)) {
						$error_text = ($this->form()->lang()=='fr' ? $error_text[1] : $error_text[0]);
					}
					$this->setError($error_text);
				}
			} else {
				user_error("{$function} is not callable!", E_USER_WARNING );
			}
		}
		return $valid;
	}

	public function loadFromSession($set=null) {
		if (isset($set)) {
			$this->loadFromSession = (bool) $set;
			return $this;
		}
		return isset($this->loadFromSession) ? $this->loadFromSession : $this->parent()->loadFromSession();
	}

	public function saveToSession($set=null) {
		if (isset($set)) {
			$this->saveToSession = (bool) $set;
			return $this;
		}
		return isset($this->saveToSession) ? $this->saveToSession : $this->parent()->saveToSession();
	}

}

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

class FORM extends FORM_FIELDSET {

	static private $type = 'form';

	protected $id, $errors;
	protected $options = array();
	protected $useValue;

	protected static $form_renderer = array('self', "_default_form_renderer");

	protected $session_values;

	const ENCTYPE_FILE = 'multipart/form-data';
	const FORM_ATTR_FIELD_PREFIX = '__form-';

	const SESSION_KEY_PREFIX = "_form_";

	function __construct($id, array $options=array()) {
		$this->id = $id;

		$this->saveToSession = $this->loadFromSession = false;

		$this->attributes = array(
			'method' => 'post',
			'action' => $_SERVER['PHP_SELF']
		);

		$this->options = $options + array(
			'session' => false,
			'lang' => 'en',
			'trim' => true,
			'error_title' => array("There are errors in the form!", "Il y a des erreurs dans ce formulaire!")
		);

		$this->errors = ALERT_LIST::load("form_{$id}_alerts");

		$this->hidden(self::FORM_ATTR_FIELD_PREFIX."id", $id);

		$lang_field = $this->hidden(self::FORM_ATTR_FIELD_PREFIX."lang", $this->options['lang']);

		if ($this->isPosted()) {
			$this->options['lang'] = $lang_field->value();
		} else {
			$lang_field->value($this->options['lang']);
		}

		register_shutdown_function(array($this, "session_save"));
	}

	function option($key, $value=null) {
		if (isset($value)) {
			$this->options[$key] = $value;
			return $this;
		}

		return isset($this->options[$key]) ? $this->options[$key] : null;
	}

	function lang($lang=null) { return $this->language($lang); }

	function language($lang=null) {
		if (isset($lang)) {
			$this->options['lang'] = $lang;
			return $this;
		}

		return $this->options['lang'];
	}

	/**
	* @return FORM
	*/
	function forget() {
		$this->errors()->erase();
		$this->session_erase();
		return $this;
	}

	function session_key() { return self::SESSION_KEY_PREFIX.$this->id; }

	function sessionvar() {
		if (!isset($this->session_values)) {
			$this->session_values = isset($_SESSION[$this->session_key()])
				? unserialize($_SESSION[$this->session_key()])
				: array();
		}
		return $this->session_values;
	}

	function session_save() {
		$_SESSION[$this->session_key()] = serialize($this->session_save_values());
	}

	function session_erase() { unset($_SESSION[$this->session_key()]); return $this; }

	private function addElement(FORM_ITEM $elem) {
		return $this->elements[$elem->name()] =& $elem;
	}

	public function isSessioned() {
		return (bool)$this->sessionvar();
	}

	public function sessionValue($name) {
		return ($this->isSessioned()) ? climb($name, $this->sessionvar()) : null;
	}

	public function loadSessionedValue($name) {
		return ($this->isSessioned()) ? climb($name, $this->sessionvar()) : null;
	}

	/*private function climb_var($string, $var) {
		if (preg_match_all('/\[([^\]]+)\]/', $string, $matches)) {
			$first = substr($string, 0, strpos($string, '['));
			$matches = array_merge(array($first), $matches[1]);
			foreach($matches as $m) {
				if (isset($var[$m])) $var = $var[$m];
				else return null;
			}
			return $var;
		} else {
			return isset($var[$string]) ? $var[$string] : null;
		}
	}*/

	public function useValue($what=null) {
		if (isset($what)) {
			$this->useValue = $what;
			return $this;
		}

		return $this->useValue;
	}

	public function isPosted() {
		$post = $this->postvar();
		return $post && $post[self::FORM_ATTR_FIELD_PREFIX."id"] == $this->id;
	}

	public function loadPostedValue($name) {
		return $this->isPosted() ? climb($name, $this->postvar()) : null;
	}

	public function postValue($name) {
		return $this->isPosted() ? climb($name, $this->postvar()) : null;
	}

	public function postvar() {
		return ($this->attributes['method'] == 'post') ? $_POST : $_GET;
	}

	public function get($name) { return $this->elements[$name]; }

	public function start() {
		$attributes = $this->attr2str();
		$s = "<form {$attributes}>";
		return $s;
	}

	public function end() {
		return "</form>";
	}

	public function render($renderer='') {
		$elements = "";
		foreach ($this->elements as $elem) $elements .= $elem->render();
		if (is_callable($renderer)) return call_user_func($renderer, $this, $elements);
		elseif (is_callable($this->renderer)) return call_user_func($this->renderer, $this, $elements);
		elseif (is_callable(self::$form_renderer)) return call_user_func(self::$form_renderer, $this, $elements);
		else return "";
	}

	public static function _default_form_renderer($form, $elements) {
		$s = "";
		if ($form->errors()->peek()) {
			$error_title = $form->option('error_title');
			if (is_array($error_title)) $error_title = ($form->lang()=='fr' ? $error_title[1] : $error_title[0]);
			$form->errors()->title($error_title);
			$s .= $form->errors()->render();
		}
		$s .= $form->start();
		$s .= $elements;
		$s .= $form->end();
		return $s;
	}

	public function setError($message, $element=null) {
		if ($message !== null) {
			$this->errors->add(new ALERT($message, 0, array('element'=>$element)));
		}
	}

	public function getError($name) {
		foreach($this->errors as $error) {
			$element = $error->attr('element');
			if ($element instanceof FORM_ELEMENT && $element->name() == $name) return $error;
		}
		return null;
	}

	public function errors() { return $this->errors; }

	public function validate_if_posted() { return ($this->isPosted()) ? $this->validate() : null; }

	public function buttonUsedForPosting() {
		$elements = array_values($this->elements);
		for($i=0; $i < count($elements); $i++) {
			$elem = $elements[$i];
			if ($elem instanceof FORM_FIELDSET) {
				array_splice($elements, $i, 1, array_values($elem->elements()));
				$i -= 1;
			}
			elseif ($elem instanceof FORM_SUBMIT_BUTTOM) {
				if ($elem->submitted()) return $elem;
			}
		}
		return null;
	}

	public function type() { return self::$type; }
}

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

class FORM_TEXT extends FORM_FIELD {

	static private $type = 'text';
	protected static $static_renderer, $static_field_renderer;

	public function html() { return parent::html('text'); }

	public function render($renderer=null) {
		if (is_callable($renderer)) return call_user_func($renderer, $this);
		elseif (is_callable($this->renderer)) return call_user_func($this->renderer, $this);
		elseif (is_callable($this->parent()->field_renderer(self::$type))) return call_user_func($this->parent()->field_renderer(self::$type), $this);
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
}

class FORM_TEXTAREA extends FORM_FIELD {

	static private $type = 'textarea';
	protected static $static_renderer, $static_field_renderer;

	public function html() {
		$this->field_addAttributes(array(
			'name' => $this->name
		));

		$value = $this->value();
		$attributes = $this->field_attr2str();

		return "<textarea {$attributes}>{$value}</textarea>";
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

}

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

class FORM_PASSWORD extends FORM_FIELD {

	static private $type = 'password';
	protected static $static_renderer, $static_field_renderer;

	public function html() { return parent::html('password'); }

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
}

class FORM_FILE extends FORM_FIELD {

	static private $type = 'file';
	protected $file, $upload_error_check=true, $required=false;
	protected static $static_renderer, $static_field_renderer;

	public function __construct($parent, $name, $label='') {
		require_once 'Upload.class.php';

		parent::__construct($parent, $name, $label);

		$this->file = new Upload($_FILES[$this->name()]);
	}

	public function html() { return parent::html('file'); }

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
		elseif (is_callable($this->fieldset_field_renderer())) return call_user_func($this->fieldset_field_renderer(), $this);
		elseif (is_callable(self::$static_field_renderer)) return call_user_func(self::$static_field_renderer, $this);
		elseif (is_callable(parent::$base_field_renderer)) return call_user_func(parent::$base_field_renderer, $this);
		else return "";
	}

	public function fieldset_field_renderer() {
		return $this->parent()->field_renderer(self::$type);
	}

	public function type() { return self::$type; }

	public function file() { return $this->file; }

	public function process_options(array $options=null) {
		if (isset($options)) {
			foreach ($options as $key=>$value) $this->file->{$key} = $value;
			return $this;
		}

		return $this->file_options;
	}

	public function error_messages(array $messages=null) {
		if (isset($messages)) {
			$this->file->translation = array_merge($this->file->translation, $messages);
			return $this;
		}

		return $this->file()->translation;
	}

	public function uploaded() { return $this->file->uploaded; }

	public function processed() { return $this->file->processed; }

	public function process($folder=null) {
		if (!$this->uploaded()) return true;

		$this->file->process($folder);

		if (!$this->processed()) $this->setError($this->file->error);

		return $this->processed();
	}

	public static function renderer($renderer) {
		if (isset($this)) $this->renderer = $renderer;
		else self::$static_renderer = $renderer;
	}

	public static function static_field_renderer($field_renderer) {
		self::$static_field_renderer = $field_renderer;
	}

	private function printf_error($matches) {
		$match = strtolower($matches[1]);

		if (property_exists($this->file, $match)) $match = $this->file->$match;
		elseif (method_exists($this->file, $match)) return $this->file->$match();

		return $match;
	}

	public function validate() {
		$error_key = $this->file->error_key;
		$error = $this->file->error = $this->file->translation[$error_key];

		if (!empty($error_key)) {
			if (($error_key=='uploaded_missing' && !$this->required)) {
				return true;
			} else {
				$this->setError($error);
				return false;
			}
		}

		return true;
	}

	public function required($text) {
		$this->error_messages(array('uploaded_missing'=>$text));
		$this->required = true;
		return $this;
	}

	public function upload_error_check($check=null) {
		if (isset($check)) {
			$this->upload_error_check = (bool) $check;
			return $this;
		}

		return $this->upload_error_check;
	}

	public function setError($error) {
		$error = preg_replace_callback('/%([^%]+)%/', array($this, 'printf_error'), $error);
		return parent::setError($error);
	}

}

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

class FORM_BUTTOM extends FORM_FIELD {

	static private $type = 'button';
	protected static $static_renderer = array('self', '_button_renderer'), $static_field_renderer;

	public function __construct($parent, $name, $text='button') {
		parent::__construct($parent, $name, '', $text);
		$this->post_value = $this->session_value = null;
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
		$this->post_value = $this->session_value = null;
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

class FORM_INFO extends FORM_FIELD {

	static private $type = "info";
	protected static $static_renderer = array('self', '_info_renderer'), $static_field_renderer;

	public function __construct($parent, $name, $label='', $value=null) {
		parent::__construct($parent, $name, $label, '');
		$this->value($value);
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


function attr2str(array $attributes) {
	$s = "";
	foreach($attributes as $key=>$value) {
		if (is_array($value)) $value = implode(' ', $value);
		$s.= " {$key}='{$value}' ";
	}
	return $s;
}

function climb($lookup, array $var) {
	if (is_string($lookup)) {
		$str = $lookup;
		parse_str($str, $lookup);
	}

	$curlookup = current($lookup);
	$curkey = key($lookup);
	$curvalue = is_array($var) && isset($var[$curkey]) ? $var[$curkey] : null;

	if ($curlookup==null) return $curvalue;
	elseif ($curvalue==null) return null;
	else return climb($curlookup, $curvalue);
}

?>