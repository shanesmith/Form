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
require_once "Form.Field.Checkbox.class.php";
require_once "Form.Field.Radio.class.php";
require_once "Form.Field.Button.class.php";
require_once "Form.exceptions.php";
require_once "Form.Validator.class.php";
require_once "Form.Formatter.class.php";
require_once "lib/upload/class.upload.php";
restore_include_path();

/**
 *
 * FORM
 *
 *
 * The form.
 *
 * @TODO Are there Fieldset methods that should not be used in Form?
 * @TODO Support for name[]
 * @TODO Ability to split a radio list, individual rendering of radios
 * @TODO Hooks and options within renderers for finer customizations without changing the renderer
 */
class FORM extends FORM_FIELDSET {

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
	* A list of all errors on this form,
	* keyed by element names
	*
	* @var array
	*/
	protected $errors = array();


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

	const DIV_DEFAULT_RENDERER = 0;
	const TABLE_DEFAULT_RENDERER = 1;
	protected $default_renderers = array(
		0 => '_div_renderer',
		1 => '_table_renderer'
	);

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
	function __construct($id, $action=null, $method='post', $languages=array(), $default_renderer=FORM::DIV_DEFAULT_RENDERER) {
		$this->id = $id;

		$this->form = $this;

		$this->setDefaultRenderers($default_renderer);

		$this->attributes = array(
			'method' => $method,
			'action' => isset($action) ? $action : $_SERVER['PHP_SELF']
		);

		if (!empty($languages) && is_array($languages)) {
			$this->languages = $languages;
		}

		//$this->options = array_merge($options, array(
		//	'trim' => true,
		//));

		//$this->hidden(self::FORM_ATTR_FIELD_PREFIX."id", $id);

		//$this->hidden(self::FORM_ATTR_FIELD_PREFIX."lang", $this->options['lang']);

	}

	/**
	* Sets the default renderers for the form and its children
	*
	* @param int|string $default_renderer
	*/
	protected function setDefaultRenderers($default_renderer) {
		if (is_numeric($default_renderer)) {
			$class_renderer = true;
			$default_renderer = $this->default_renderers[$default_renderer];
		}

		foreach (self::getAllTypes() as $class => $type) {
			if ($type == 'form') continue;

			$renderer = $class_renderer ? array($class, $default_renderer) : $default_renderer;

			$this->setChildTypeRenderer($type, $renderer);
		}

		$this->setRenderer(array("FORM", $default_renderer));
	}

	/*************************
	 **  GETTERS / SETTERS  **
	 *************************/

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
		$name = $elem->name();

		if ($this->hasElement($name)) {
			throw new FormDuplicateElementName("An element with name '{$name}' already exists.", $elem);
		}

		$this->elements[$name] = $elem;

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

	/**
	* Add an error message for the given element name
	* in the errors list
	*
	* @param string $element_name
	* @param string|array $error
	* @return FORM
	*/
	public function addError($element_name, $error) {
		$processed_error = array();
		$this->process_languaged_argument($processed_error, $error);
		$this->errors[$element_name] = $processed_error;
		return $this;
	}

	/**
	* Return the full list of errors,
	* keyed by element names
	*
	* @return array
	*/
	public function getAllErrors() {
		return $this->errors;
	}

	/**
	* Get the error message for the specified element, if any,
	* keyed by language
	*
	* @param string $element
	* @return array
	*/
	public function getErrorByElementName($element) {
		return $this->errors[$element];
	}

	/**
	* Remove all previously set errors
	*
	* @return FORM
	*/
	public function clearAllErrors() {
		$this->errors = array();
		return $this;
	}

	/**
	* Clear the error set for the specified element, if any
	*
	* @param string $element
	* @return FORM
	*/
	public function clearErrorByElementName($element) {
		unset($this->errors[$element]);
		return $this;
	}

	/**
	* Returns whether the form has any errors set
	*
	* @return boolean
	*/
	public function hasErrors() {
		return !empty($this->errors);
	}

	/**
	* Returns whether the specified element has an error set
	*
	* @param string $element
	* @return boolean
	*/
	public function hasErrorByElementName($element) {
		return !empty($this->errors[$element]);
	}

	/**
	* Load all values from the passed array to the defined fields, optionally
	* including a second parameter for uploaded files
	*
	* @param array $post
	* @param array $files
	* @return FORM
	*/
	public function loadPostedValues(array $post, array $files=array()) {
		$it = new AppendIterator();

		$it->append(new ArrayIterator($post));

		foreach ($it as $name => $value) {
			if (is_array($value)) {
				$value = self::prefix_keys($name, $value);
				$it->append(new ArrayIterator($value));
			} else {
				$elem = $this->getElement($name);
				if ($elem) {
					$elem->setPostedValue($value);
				}
			}
		}

		if (!empty($files)) {
			$this->loadUploadedFiles($files);
		}

		return $this;
	}

	/**
	* Load all file information from an array formatted as $_FILES
	*
	*	Note that the $_FILES array is formatted oddly when field names
	* are of the form arr[a]. arr[b][c], etc...
	*
	* $_FILES = array(
	*
	*		'basic' => array(
	*			'name' => "...",
	* 		'size' => "...",
	* 		...
	* 	),
	*
	* 	'arr' => array(
	*			'name' => array(
	*				'a' => "...",
	* 			'b' => array(
	*					'c' => "..."
	* 			)
	* 		),
	*			'size' => array(
	*				'a' => "...",
	* 			'b' => array(
	*					'c' => "..."
	* 			)
	* 		),
	* 		...
	* 	)
	* )
	*
	* @param array $files
	* @return FORM_FILE
	*/
	public function loadUploadedFiles(array $files) {
		$it = new AppendIterator();

		$it->append(new ArrayIterator($files));

		foreach ($it as $name => $info) {
			if (is_array($info['name'])) {
				// field names are array-based (field[a][b])
				// so we flatten the oddly set information
				// and append it to the iterator
				$more = array();
				foreach (array_keys($info['name']) as $subname) {
					foreach (FORM_FILE::$infokeys as $key) {
						$more["{$name}[{$subname}]"][$key] = $info[$key][$subname];
					}
				}
				$it->append(new ArrayIterator($more));
			} else {
				$elem = $this->getElement($name);
				if ($elem && $elem instanceof FORM_FILE) {
					$elem->setUploadedFileInfo($info);
				}
			}
		}

		return $this;
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
	public static function _div_renderer($form, array $languages) {
		$attributes = $form->getAttributesString();
		$errors = $form->renderErrorList($languages);
		$children = $form->renderAllChildren($languages);
		return "<form {$attributes}>" . $errors . $children . "</form>";
	}

	/**
	* Form rendering doesn't change if table renderer is used
	*
	* @param FORM $form
	* @param array $languages
	* @return string
	*/
	public static function _table_renderer($form, array $languages) {
		return self::_div_renderer($form, $languages);
	}


	/***************
	 **  HELPERS  **
	 ***************/

	/**
	* Returns the given array with each key prefixed with
	* the given string
	*
	* @param string $prefix
	* @param array $array
	* @return array
	*/
	public static function prefix_keys($prefix, $array) {
		$proc = array();

		foreach ($array as $key => $value) {
			$proc["{$prefix}[{$key}]"] = $value;
		}

		return $proc;
	}
}

