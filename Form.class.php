<?
set_include_path(dirname(__FILE__)."/");
require_once "Form.Element.class.php";
require_once "Form.Info.class.php";
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
require_once "Form.exceptions.php";
require_once "Form.Validator.class.php";
require_once "Form.Formatter.class.php";
require_once "Form.Renderer.class.php";
require_once "Form.Renderer.Div.class.php";
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
	 * All radio lists, keyed first by radio name, then keys of unique names whith values of radio text
	 *
	 * radio_name => ( unique_name => text, ... ), ...
	 *
	 * @var array
	 */
	protected $radio_lists = array();

	/**
	 * The text of the default checked radio, keyed by radio name
	 *
	 * @var array
	 */
	protected $radio_default_checked = array();

	/**
	 * The text of the posted checked radio, keyed by radio name
	 *
	 * @var array
	 */
	protected $radio_posted_checked = array();

	/**
	* A list of all errors on this form,
	* keyed by element names
	*
	* @var array
	*/
	protected $errors = array();

	/**
	* The default FORM_RENDERER class
	*
	* @var FORM_RENDERER
	*/
	protected $default_renderer;


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
	 */
	function __construct($id, $action=null, $method='post', $languages=array(), FORM_RENDERER $default_renderer=null) {
		$this->id = $id;

		$this->form = $this;

		$this->attributes = array(
			'method' => $method,
			'action' => isset($action) ? $action : $_SERVER['PHP_SELF']
		);

		if (!empty($languages) && is_array($languages)) {
			$this->languages = $languages;
		}

		if (!$default_renderer) $default_renderer = new FORM_DIV_RENDERER();

		$this->default_renderer = $default_renderer;
		$this->default_renderer->init($this);

		//$this->options = array_merge($options, array(
		//	'trim' => true,
		//));

		//$this->hidden(self::FORM_ATTR_FIELD_PREFIX."id", $id);

		//$this->hidden(self::FORM_ATTR_FIELD_PREFIX."lang", $this->options['lang']);

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
			}
			elseif ($this->isRadioList($name)) {
				$this->setRadioPostedChecked($name, $value);
			}
			else {
				$field = $this->getField($name);
				if ($field) {
					$field->setPostedValue($value);
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
				$file  = $this->getFile($name);
				if ($file) {
					$file->setUploadedFileInfo($info);
				}
			}
		}

		return $this;
	}


	/**************
	 **  RADIOS  **
	 **************/

	/**
	 * Add a unique radio name and value to a list of radios
	 *
	 * @param string $radio_name
	 * @param string $unique_name
	 * @param string $value
	 * @return FORM
	 */
	public function addToRadioList($radio_name, $unique_name, $value) {
		$this->radio_lists[$radio_name][$unique_name] = $value;
		return $this;
	}

	/**
	 * Get the list of radios under the given radio name,
	 * where keys are unique names and values are the text
	 *
	 * @param string $radio_name
	 * @return array
	 */
	public function getRadioList($radio_name) {
		return $this->radio_lists[$radio_name];
	}

	/**
	 * Return all unique names from the given radio name
	 *
	 * @param string $radio_name
	 * @return array
	 */
	public function getRadioListUniqueNames($radio_name) {
		return array_keys($this->radio_lists[$radio_name]);
	}

	/**
	 * Return all radio names (ie: the names of each radio lists)
	 *
	 * @return array
	 */
	public function getRadioListNames() {
		return array_keys($this->radio_lists);
	}

	/**
	 * Whether the name is a radio list name
	 *
	 * @param string $name
	 * @return boolean
	 */
	public function isRadioList($name) {
		return in_array($name, $this->getRadioListNames());
	}

	/**
	 * Set the default checked radio value for the radio list
	 *
	 * @param string $radio_name
	 * @param string $value
	 * @return FORM
	 */
	public function setRadioDefaultChecked($radio_name, $value) {
		$this->radio_default_checked[$radio_name] = $value;
		return $this;
	}

	/**
	 * Set the posted checked radio value for the radio list
	 *
	 * @param string $radio_name
	 * @param string $value
	 * @return FORM
	 */
	public function setRadioPostedChecked($radio_name, $value) {
		$this->radio_posted_checked[$radio_name] = $value;
		return $this;
	}

	/**
	 * Get the text of the default checked radio from the given radio list
	 *
	 * @param string $radio_name
	 * @return string
	 */
	public function getRadioDefaultCheckedText($radio_name) {
		return $this->radio_default_checked[$radio_name];
	}

	/**
	 * Get the text of the posted checked radio from the given radio list
	 *
	 * @param string $radio_name
	 * @return string
	 */
	public function getRadioPostedCheckedText($radio_name) {
		return $this->radio_posted_checked[$radio_name];
	}

	/**
	 * Get the posted checked radio text, or the default if none found
	 *
	 * @param string $radio_name
	 * @return string
	 */
	public function getRadioCheckedText($radio_name) {
		if (isset($this->radio_posted_checked[$radio_name])) {
			return $this->radio_posted_checked[$radio_name];
		} else {
			return $this->radio_default_checked[$radio_name];
		}
	}

	/**
	 * Get the radio element from the given radio list by looking up the text
	 *
	 * @param string $radio_name
	 * @param string $text
	 * @return FORM_RADIO
	 */
	public function getRadioByText($radio_name, $text) {
		$unique_name = array_search($text, $this->radio_lists[$radio_name]);

		return $this->getRadio($unique_name);
	}


	/*****************
	 **  RENDERING  **
	 *****************/

	/**
	* Set default sections and renderers for each type at the same time through an array, first keyed by type, then section
	*
	*	A special type of 'form' can be used to set sections and renderers for the form.
	*
	* A special type of 'field' can be used to set sections and renderers for each field child type (won't overwrite other field
	* types that are specified).
	*
	* @param array $type_section_renderer
	* @return FORM
	*/
	public function setDefaultSectionsAndRenderers(array $type_section_renderer) {

		if (array_key_exists('form', $type_section_renderer)) {

			$section_renderer = $type_section_renderer['form'];

			$sections = array_diff(array_keys($section_renderer), array('container'));

			$this->setSections($sections);
			$this->setRenderersArray($section_renderer);

			unset($type_section_renderer['form']);
		}

		$this->setChildTypeSectionsAndRenderers($type_section_renderer);

		return $this;
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

