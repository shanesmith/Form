<?php
require_once dirname(__FILE__) . "/Form.class.php";

/**
*
* FORM_ELEMENT
*
*
* A Form Element represents an html element that is related to html forms.
*
* Form Elements can be Fieldsets (or a Form, which is a subclass of Fieldset)
* or Fields (of which there are Text, Select, File, etc...)
*
*/
abstract class FORM_ELEMENT {

	/**
	* The element's type (fieldset, text, file, submit, etc...)
	*
	* @var string
	*/
	static private $type;

	/**
	* A reference to the element's parent Fieldset
	*
	* Set to null if this element is Form
	*
	* @var FORM_FIELDSET
	*/
	protected $parent;

	/**
	* A reference all the way back to the containing Form
	*
	* @var FORM
	*/
	protected $form;

	/**
	* The element's name
	*
	* @var string
	*/
	protected $name;

	/**
	* A descriptive label for rendering
	*
	* @var array
	*/
	protected $labels = array();

	/**
	* An array of attributes for rendering
	* with name and value pairs matching
	* attribute names and values
	*
	* @var array
	*/
	protected $attributes = array();

	/**
	* An optional renderer for this element
	*
	* @var callable
	*/
	protected $renderer;


	/*******************
	 **  CONSTRUCTOR  **
	 *******************/

	/**
	* Basic Constructor
	*
	*** Should not be called from FORM
	*
	* @param FORM_FIELDSET $parent
	* @param string $name
	* @param string $label
	* @return FORM_ELEMENT
	*/
	public function __construct(&$parent, $name, $labels=null) {
		$this->parent =& $parent;
		$this->form =& $parent->form();
		$this->name = $name;
		if (isset($labels)) {
			$this->setLabels($labels);
		}
	}

	/*************************
	 **  GETTERS / SETTERS  **
	 *************************/

	/**
	* Return the element's name
	*
	* @return string the element's name
	*/
	public function name() { return $this->name; }

	/**
	 * Return the element's type
	 *
	 * @return string
	 */
	public function type() { return $this->late_static_get('type'); }

	/**
	* Return the element's parent
	*
	* @return FORM_FIELDSET
	*/
	public function parent() { return $this->parent; }

	/**
	* Return the associated FORM
	*
	* @return FORM
	*/
	public function form() { return $this->form; }

	/**
	* Return the element's labels in an array
	*
	* @return array
	*/
	public function getLabels() {
		return $this->labels;
	}

	/**
	* Set this element's labels
	*
	* If a string, it is set to the form's first defined language
	*
	* If a numbered array, they are set in same sequence as the form's defined languages
	*
	* If an associative array, they are set to the keys
	*
	* @param string|array $labels
	* @return FORM_ELEMENT
	*/
	public function setLabels($labels) {

		if (!empty($labels)) {

			$languages = $this->form()->getLanguages();

			if (is_array($labels)) {

				if (is_integer(array_shift(array_keys($labels)))) {
					// sequencial

					$languages = array_slice($languages, 0, count($labels));
					$this->labels = array_combine($languages, $labels);

				} else {
					// associative

					foreach (array_keys($labels) as $lang) {
						if (!$this->form()->isValidLanguage()) {
							throw new FormInvalidLanguageException(null, $lang, $this);
						}
					}

					$this->labels = array_merge($this->labels, $labels);

				}

			} else {

				$lang = $languages[0];
				$this->labels[$lang] = $labels;

			}

		}

		return $this;
	}

	/**
	* Returns the label for the specified language
	*
	* @param string $lang
	*/
	public function getLabelByLang($lang) {
		if (!$this->form()->isValidLanguage($lang)) {
			throw new FormInvalidLanguageException(null, $lang, $this);
		}

		return $this->labels[$lang];
	}

	/**
	* Sets the label for the specified language
	*
	* @param string $lang
	* @param string $label
	* @returns FORM_ELEMENT
	*/
	public function setLabelByLang($lang, $label) {
		return $this->setLabels(array($lang => $label));
	}


	/**
	* Returns all of the element's attributes in an array
	*
	* @return array
	*/
	public function getAttributesArray() {
		return $this->attributes;
	}

	/**
	* Sets all attributes in the given array
	*
	* @param array $attributes
	* @return FORM_ELEMENT
	*/
	public function setAttributesArray(array $attributes) {
		$this->attributes = array_merge($this->attributes, $attributes);
		return $this;
	}

	/**
	* Get the specified attribute
	*
	* @param string $key
	* @returns string
	*/
	public function getAttribute($key) {
		return $this->attributes[$key];
	}

	/**
	* Set the specified attribute to a given value
	*
	* @param string $key
	* @param string $value
	* @returns FORM_ELEMENT
	*/
	public function setAttribute($key, $value) {
		$this->attributes[$key] = $value;
		return $this;
	}

	/**
	* Remove all attributes
	*
	* @return FORM_ELEMENT
	*/
	public function resetAttributes() {
		$this->attributes = array();
		return $this;
	}

	/**
	* Get the element's class attribute
	*
	* @returns string
	*/
	public function getClass() {
		return $this->getAttribute('class');
	}

	/**
	* Appends a class to the element's class attribute
	*
	* @param string $class
	* @return FORM_ELEMENT
	*/
	public function addClass($class) {
		return $this->setAttribute('class', $this->getClass() . " " . $class);
	}

	/**
	* Remove the given class from the element
	*
	* @param string $class
	* @return FORM_ELEMENT
	*/
	public function removeClass($class) {
		$class = preg_quote($class);
		return $this->setClass(preg_replace("/\\b{$class}\\b/i", '', $this->getClass()));
	}

	/**
	* Get the element's ID attribute
	*
	* @return string
	*/
	public function getID() {
		return $this->getAttribute('id');
	}

	/**
	* Sets the element's ID attribute
	*
	* @param string $id
	* @return FORM_ELEMENT
	*/
	public function setID($id) {
		return $this->setAttribute('id', $class);
	}


	/*****************
	 **  RENDERING  **
	 *****************/

	/**
	* Sets the renderer for this specific element
	*
	* @param callback $renderer
	* @return FORM_ELEMENT
	*/
	public function setRenderer($renderer) {
		$this->renderer = $renderer;
		return $this;
	}

	/**
	* Get the renderer for this specific element,
	* or null if none set
	*
	* @return callable
	*/
	public function getRenderer() {
		return $this->renderer;
	}

	/**
	* Set a renderer for all elements of this type
	*
	* @param callable $renderer
	*/
	abstract public static function setStaticRenderer($renderer);

	/**
	* Get the static renderer, or null if none set
	*
	* @return callable
	*/
	abstract public static function getStaticRenderer();

	/**
	* Resolve the rendering chain to return a renderer for this element
	*
	* @return callable
	*/
	abstract public function resolveRenderer();

	/**
	* Render this element, optionally passing a renderer
	* that will override the renderer chain
	*
	* @param callable $renderer
	* @return string
	*/
	abstract public function render($renderer=null);

	/**
	* A default renderer for this element type
	*
	* @param FORM_ELEMENT $element
	* @return string
	*/
	abstract public static function _default_renderer($element, array $languages);


	/***************
	 **  HELPERS  **
	 ***************/

	/**
	* Returns the element's attributes converted into and html attributes string
	*
	* @return string
	*/
	public function attr2str() { return attr2str($this->attributes); }

	/**
	 * Return the element's rendering
	 *
	 * @return string
	 */
	public function __toString() { return $this->render(); }

	/**
	 * A hack to get PHP 5.3's late static binding functionality
	 *
	 * Works only if $var is public or protected
	 *
	 * $var does not need to be static
	 *
	 * @param string $var
	 * @return mixed
	 */
	protected function late_static_get($var) {
		$class_vars = get_class_vars(get_class($this));
		return $class_vars[$var];
	}

}