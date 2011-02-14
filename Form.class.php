<?
/**
* TO-DO
*
* - better SESSION handling
* - SELECT OPTGROUPs
*
*/

include_once 'Alerts.class.php';

require_once "Form.Element.class.php";
require_once "Form.Fieldset.class.php";
require_once "Form.Field.class.php";
require_once "Form.Field.Text.class.php";
require_once "Form.Field.Textarea.class.php";
require_once "Form.Field.Hidden.class.php";
require_once "Form.Field.Select.class.php";
require_once "Form.Field.File.class.php";
require_once "Form.Field.Password.class.php";
require_once "Form.Field.Checkbox.class.php";
require_once "Form.Field.Radio.class.php";
require_once "Form.Field.Button.class.php";
require_once "Form.Field.Info.class.php";

require_once "Form.utils.php";

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

