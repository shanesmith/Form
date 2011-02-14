<?

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

	protected $id;
	protected $options = array();

	protected static $form_renderer = array('self', "_default_form_renderer");

	const ENCTYPE_FILE = 'multipart/form-data';

	const FORM_ATTR_FIELD_PREFIX = '__form-';

	function __construct($id, $action=null, $method='post', array $attributes=array(), array $options=array()) {
		$this->id = $id;

		$this->attributes = $attributes;
		$this->attributes['method'] = $method;
		$this->attributes['action'] = isset($action) ? $action : $_SERVER['PHP_SELF'];

		$this->options = $options + array(
			'lang' => 'en',
			'trim' => true,
		);

		$this->hidden(self::FORM_ATTR_FIELD_PREFIX."id", $id);

		$lang_field = $this->hidden(self::FORM_ATTR_FIELD_PREFIX."lang", $this->options['lang']);

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

	private function addElement(FORM_ITEM $elem) {
		return $this->elements[$elem->name()] =& $elem;
	}

	public function get($name) { return $this->elements[$name]; }

	public function htmlOpenForm() {
		$attributes = $this->attr2str();
		$s = "<form {$attributes}>";
		return $s;
	}

	public function htmlCloseForm() {
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
		$s .= $form->htmlOpenForm();
		$s .= $elements;
		$s .= $form->htmlCloseForm();
		return $s;
	}

	public function type() { return self::$type; }
}

