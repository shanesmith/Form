<?
set_include_path(dirname(__FILE__)."/");
require_once "Form.Element.class.php";
require_once "Form.Fieldset.class.php";
require_once "Form.Field.class.php";
/*require_once "Form.Field.Text.class.php";
require_once "Form.Field.Textarea.class.php";
require_once "Form.Field.Hidden.class.php";
require_once "Form.Field.Select.class.php";
require_once "Form.Field.File.class.php";
require_once "Form.Field.Password.class.php";
require_once "Form.Field.Checkbox.class.php";
require_once "Form.Field.Radio.class.php";
require_once "Form.Field.Button.class.php";
require_once "Form.Field.Info.class.php";*/
require_once "Form.exceptions.php";
restore_include_path();

/**
 *
 * FORM
 *
 *
 * The form.
 *
 * @TODO Are there Fieldset methods that should not be used in Form?
 */
class FORM extends FORM_FIELDSET {

	/**
	* Type of Form Element
	*
	* @var string
	*/
	static protected $type = 'form';

	/**
	 * The form's id
	 *
	 * @var string
	 */
	protected $id;

	/**
	 * Languages that this form supports
	 *
	 * @var array
	 */
	protected $languages = array('en', 'fr');

	/**
	 * Various options
	 *
	 * @var array
	 */
	protected $options = array();

	/**
	 * All elements found inside this form
	 *
	 * @var array
	 */
	protected $elements = array();

	/**
	* Callable to the static renderer
	*
	* @var callable
	*/
	protected static $static_renderer = array('self', "_default_renderer");


	/*****************
	 **  CONSTANTS  **
	 *****************/

	/**
	 * Value for the enctype attribute of a form tag when files are involved
	 *
	 * @var string
	 */
	const ENCTYPE_FILE = 'multipart/form-data';

	/**
	 * A prefix for special form fields
	 *
	 * @var string
	 */
	const FORM_ATTR_FIELD_PREFIX = '__form-';


	/*******************
	 **  CONSTRUCTOR  **
	 *******************/

	/**
	 * Constructor
	 *
	 * @param string $id
	 * @param string $action
	 * @param string $method
	 * @param array $languages
	 * @param array $options
	 */
	function __construct($id, $action=null, $method='post', $languages=array(), array $options=array()) {
		$this->id = $id;

		$this->form = $this;

		$this->attributes = array(
			'method' => $method,
			'action' => isset($action) ? $action : $_SERVER['PHP_SELF']
		);

		if (!empty($languages) && is_array($languages)) {
			$this->languages = $languages;
		}

		$this->options = array_merge($options, array(
			'trim' => true,
		));

		//$this->hidden(self::FORM_ATTR_FIELD_PREFIX."id", $id);

		//$this->hidden(self::FORM_ATTR_FIELD_PREFIX."lang", $this->options['lang']);

	}


	/*************************
	 **  GETTERS / SETTERS  **
	 *************************/

	/**
	 * Return the element's type
	 *
	 * @return string
	 */
	public function type() { return self::$type; }


	/**
	 * Get all supported languages for this form
	 *
	 * @return array
	 */
	public function getLanguages() {
		return $this->languages;
	}

	/**
	 * Check a language against all supported languages
	 *
	 * @param string $lang
	 * @return boolean
	 */
	public function isValidLanguage($lang) {
		return in_array($lang, $this->getLanguages());
	}

	/**
	 * Checks several languages against all supported languages
	 *
	 * Sets $invalid to first invalid language
	 *
	 * @param array $languages
	 * @param string $invalid
	 * @return boolean
	 */
	public function areValidLanguages(array $languages, &$invalid=null) {
		foreach ($languages as $lang) {
			if (!$this->isValidLanguage($lang)) {
				$invalid = $lang;
				return false;
			}
		}

		return true;
	}

	/**
	 * Get the value for a given option
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function getOption($key) {
		return $this->options[$key];
	}

	/**
	 * Return an array of all set options, keyed by option name
	 *
	 * @return array
	 */
	public function getAllOptions() {
		return $this->options;
	}

	/**
	 * Add the element to the set of all elements, then returns it
	 *
	 * Should only be called from a Fieldset's addChild()
	 *
	 * @param FORM_ELEMENT $elem
	 * @return FORM_ELEMENT
	 */
	public function addElement(FORM_ELEMENT $elem) {
		$this->elements[$elem->name()] = $elem;
		return $elem;
	}

	/**
	 * Remove and return the named element
	 *
	 * Should only be called from a Fieldset's removeChild()
	 *
	 * @param string $name
	 * @return FORM_ELEMENT
	 */
	public function removeElement($name) {
		if (!$this->hasElement($name)) return null;

		$elem = $this->elements[$name];

		unset($this->elements[$name]);

		return $elem;
	}

	/**
	 * Return the named element
	 *
	 * @param string $name
	 * @return FORM_ELEMENT
	 */
	public function getElement($name) {
		if (!$this->hasElement($name)) return null;

		return $this->elements[$name];
	}

	/**
	 * Return whether the named element exists
	 *
	 * @param string $name
	 * @return boolean
	 */
	public function hasElement($name) {
		return isset($this->elements[$name]);
	}


	/*****************
	 **  RENDERING  **
	 *****************/

	/**
	* Step through renderer precedence until one is found, and returns it, null otherwise
	*
	* @return callable
	*/
	public function resolveRenderer() {
		$parent_child_type_renderer = $this->parent() ? $this->parent()->getChildTypeRenderer($this->type(), true) : null;

		if (is_callable($this->renderer)) return $this->renderer;
		elseif (is_callable($parent_child_type_renderer)) return $parent_child_type_renderer;
		elseif (is_callable(self::$static_renderer)) return self::$static_renderer;
		else return null;
	}

	/**
	* Uses the given renderer or, if not provided, a resolved renderer to render the element
	*
	* @param array $lang
	* @param callback $renderer
	* @return string
	*/
	public function render($lang=null, $renderer=null) {
		if (!isset($lang)) {
			$lang = $this->form()->getLanguages();
		} else{
			if (is_string($lang)) $lang = array($lang);

			if (!$this->form()->areValidLanguages($lang, $invalid)) {
				throw new FormInvalidLanguageException(null, $invalid, $this);
			}
		}

		if (is_callable($renderer)) {
			return call_user_func($renderer, $this, $lang);
		}
		elseif (is_callable($this->resolveRenderer())) {
			return call_user_func($this->resolveRenderer(), $this, $lang);
		}
		else {
			throw new FormNoRendererFound(null, $this);
		}
	}

	/**
	* Default renderer for Forms
	*
	* @param FORM $form
	* @param array $languages
	*/
	public static function _default_renderer($form, array $languages) {
		$attributes = $form->getAttributesString();
		return "<form {$attributes}>" . $form->renderAllChildren($languages) . "</form>";
	}

}

