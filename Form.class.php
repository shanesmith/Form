<?
set_include_path(dirname(__FILE__)."/");
require_once "Form.Element.class.php";
require_once "Form.Info.class.php";
require_once "Form.Fieldset.class.php";
require_once "Form.RadioList.class.php";
require_once "Form.Field.class.php";
require_once "Form.Field.Text.class.php";
require_once "Form.Field.Textarea.class.php";
require_once "Form.Field.Hidden.class.php";
require_once "Form.Field.Select.class.php";
require_once "Form.Field.File.class.php";
require_once "Form.Field.Password.class.php";
//require_once "Form.Field.Checkbox.class.php";
require_once "Form.Field.Radio.class.php";
require_once "Form.Field.Button.class.php";
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
	* Default child renderers
	*
	* @var array
	*/
	protected static $default_child_type_renderers = array(
		'fieldset'  => array('FORM_FIELDSET', 	'_default_renderer'),
		'button' 		=> array('FORM_BUTTON', 		'_default_renderer'),
		'checkbox'  => array('FORM_CHECKBOX', 	'_default_renderer'),
		'file' 			=> array('FORM_FILE',				'_default_renderer'),
		'hidden' 		=> array('FORM_HIDDEN', 		'_default_renderer'),
		'info' 			=> array('FORM_INFO', 			'_default_renderer'),
		'password' 	=> array('FORM_PASSWORD', 	'_default_renderer'),
		'radio' 		=> array('FORM_RADIO', 			'_default_renderer'),
		'radio_list'=> array('FORM_RADIO_LIST', '_default_renderer'),
		'select'		=> array('FORM_SELECT', 		'_default_renderer'),
		'text' 			=> array('FORM_TEXT', 			'_default_renderer'),
		'textarea' 	=> array('FORM_TEXTAREA', 	'_default_renderer'),
		'submit'		=> array('FORM_SUBMIT_BUTTON', 	'_default_renderer'),
		'reset' 		=> array('FORM_RESET_BUTTON', 	'_default_renderer'),
	);


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

		$this->setDefaultRenderers();

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

	/**
	* Sets the default renderers for the form and its children
	*/
	protected function setDefaultRenderers() {
		$this->setChildTypeRenderersArray(self::$default_child_type_renderers);
		$this->setRenderer(array("FORM", "_default_renderer"));
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
	* Default renderer for Forms
	*
	* @param FORM $form
	* @param array $languages
	* @returs string
	*/
	public static function _default_renderer($form, array $languages) {
		$attributes = $form->getAttributesString();
		return "<form {$attributes}>" . $form->renderAllChildren($languages) . "</form>";
	}

}

