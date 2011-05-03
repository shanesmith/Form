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
	 * An optional list of renderers for this element, keyed by section
	 *
	 * @var callable
	 */
	protected $renderers = array();

	/**
	 * The (optional) set of container sections for this element
	 *
	 * @var array
	 */
	protected $sections = array();

	/**
	 * A list of form classes and their string type
	 *
	 * @var array
	 */
	protected static $types = array(
		'FORM'								=> 'form',
		'FORM_FIELDSET' 			=> 'fieldset',
		'FORM_BUTTON' 				=> 'button',
		'FORM_CHECKBOX' 			=> 'checkbox',
		'FORM_FILE' 					=> 'file',
		'FORM_HIDDEN' 				=> 'hidden',
		'FORM_INFO' 					=> 'info',
		'FORM_PASSWORD' 			=> 'password',
		'FORM_RADIO' 					=> 'radio',
		'FORM_SELECT' 				=> 'select',
		'FORM_TEXT' 					=> 'text',
		'FORM_TEXTAREA' 			=> 'textarea',
		'FORM_SUBMIT_BUTTON'  => 'submit',
		'FORM_RESET_BUTTON' 	=> 'reset',
	);

	/**
	 * A list of all Field types (ie: subclasses of FORM_FIELD)
	 *
	 * @var array
	 */
	protected static $field_types = array(
		'info', 'button', 'checkbox', 'file', 'hidden', 'password', 'radio', 'select', 'text', 'textarea', 'submit', 'reset'
	);


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
	public function type() {
		$class = get_class($this);
		return self::$types[$class];
	}

	/**
	 * Return an array of all type strings,
	 * keyed by class names
	 *
	 * @return array
	 */
	public static function getAllTypes() {
		return self::$types;
	}

	/**
	 * Return a list of all Field types
	 *
	 * @return array
	 */
	public static function getFieldTypes() {
		return self::$field_types;
	}

	/**
	 * Return the element's parent
	 *
	 * @return FORM_FIELDSET
	 */
	public function parent() { return $this->parent; }

	/**
	 * Whether this element's parent is the FORM itself
	 *
	 * @return bool
	 */
	public function parentIsForm() { return ($this->parent() instanceof FORM); }

	/**
	 * Returns whether this element has a fieldset
	 * of the given name as an ancestor
	 *
	 * @param string $name
	 * @return boolean
	 */
	public function hasAncestor($name) {
		$parent = $this->parent();
		while($parent && $parent->name() != $name) {
			$parent = $parent->parent();
		}
		return isset($parent);
	}

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
	 * @param string|array $labels
	 * @return FORM_ELEMENT
	 */
	public function setLabels($labels) {
		$this->process_languaged_argument($this->labels, $labels);
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
	 * @return FORM_ELEMENT
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
	 * @return string
	 */
	public function getAttribute($key) {
		return $this->attributes[$key];
	}

	/**
	 * Set the specified attribute to a given value
	 *
	 * @param string $key
	 * @param string $value
	 * @return FORM_ELEMENT
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
	 * Return a string of this element's HTML attributes
	 *
	 * Optinally provide an array of attributes to override or add to
	 * the attributes already set
	 *
	 * @param array $override
	 * @return string
	 */
	public function getAttributesString(array $override=array()) {
		return FORM_RENDERER::attr2str(array_merge($this->getAttributesArray(), $override));
	}

	/**
	 * Get the element's class attribute
	 *
	 * @return string
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
		return $this->setAttribute('class', preg_replace("/\\b{$class}\\b/i", '', $this->getClass()));
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
		return $this->setAttribute('id', $id);
	}


	/*****************
	 **  RENDERING  **
	 *****************/

	/**
	 * Render this element (in fact calls rendering on the container)
	 *
	 * @param string|array $lang
	 * @return string
	 */
	public function render($lang) {
		return $this->renderSection('container', $lang);
	}

	/**
	 * Render this element's specified section by calling the renderer
	 *
	 * @param string $section
	 * @param string|array $lang
	 * @return string
	 */
	public function renderSection($section, $lang) {
		$lang = $this->resolve_lang($lang);

		$renderer = $this->getRendererResolved($section);

		if ($section == 'container') {
			$rendered_sections = $this->renderAllSections($lang);
		}

		$output = call_user_func($renderer, $this, $lang, $rendered_sections);

		return $output;
	}

	/**
	 * Renders all sections and returns its concatenation
	 *
	 * @param string|array $lang
	 * @return FORM_ELEMENT
	 */
	public function renderAllSections($lang) {
		$str = "";

		foreach ($this->getSectionsResolved() as $section) {
			$str .= $this->renderSection($section, $lang);
		}

		return $str;
	}

	/**
	 * Set a renderer for the specified section
	 *
	 * @param string $section
	 * @param callable $renderer
	 * @return FORM_ELEMENT
	 */
	public function setRenderer($section, $renderer) {
		$this->renderers[$section] = $renderer;
		return $this;
	}

	/**
	 * Set multiple renderers by an array, keyed by sections
	 *
	 * @param array $section_renderers
	 * @return FORM_ELEMENT
	 */
	public function setRenderersArray(array $section_renderers) {
		foreach ($section_renderers as $section => $renderer) {
			$this->setRenderer($section, $renderer);
		}
		return $this;
	}

	/**
	 * Return the renderer for the specified section
	 *
	 * @param string $section
	 * @return callable
	 */
	public function getRenderer($section) {
		return $this->renderers[$section];
	}

	/**
	 * Return the array for all section renderers, keyed by section
	 *
	 * @return array
	 */
	public function getAllRenderers() {
		return $this->renderers;
	}

	/**
	 * Get the renderer for this element, resolved through parents if none specifically set
	 *
	 * @param string $section
	 * @return callable
	 */
	public function getRendererResolved($section) {
		$renderer = $this->getRenderer($section);

		if (!$renderer && $this->parent()) {
			$renderer = $this->parent()->getChildTypeRendererRecurse($this->type(), $section);
		}

		return $renderer;
	}

	/**
	 * Get all section renderers for this element, resolved through parents if
	 * none specifically set, as an array keyed by sections
	 *
	 * @return array
	 */
	public function getAllRenderersResolved() {
		$all_renderers = array();

		$container_renderer = $this->getRendererResolved('container');
		if ($container_renderer) {
			$all_renderers['container'] = $container_renderer;
		}

		foreach ($this->getSectionsResolved() as $section) {
			$all_renderers[$section] = $this->getRendererResolved($section);
		}

		return $all_renderers;
	}


	/****************
	 **  SECTIONS  **
	 ****************/

	/**
	 * Add a section to the end of the current section list
	 *
	 * @param string|array $sections
	 * @return FORM_ELEMENT
	 */
	public function addSections($sections) {
		$this->addSectionsLast($sections);
		return $this;
	}

	/**
	 * Add a section to the end of the current section list
	 *
	 * @param string|array $sections
	 * @return FORM_ELEMENT
	 */
	public function addSectionsLast($sections) {
		$offset = count($this->sections);
		$this->addSectionsAt($offset, $sections);
		return $this;
	}

	/**
	 * Add a section to the start of the current section list
	 *
	 * @param string|array $sections
	 * @return FORM_ELEMENT
	 */
	public function addSectionsFirst($sections) {
		$this->addSectionsAt(0, $sections);
		return $this;
	}

	/**
	 * Add a section after the specified section in the current section list
	 *
	 * @param string $after
	 * @param string|array $sections
	 * @return FORM_ELEMENT
	 */
	public function addSectionsAfter($after, $sections) {
		$offset = array_search($after, $this->sections) + 1;
		$this->addSectionsAt($offset, $sections);
		return $this;
	}

	/**
	 * Add a section before the specified section in the current section list
	 *
	 * @param string $before
	 * @param string|array $sections
	 * @return FORM_ELEMENT
	 */
	public function addSectionsBefore($before, $sections) {
		$offset = array_search($before, $this->sections);
		$this->addSectionsAt($offset, $sections);
		return $this;
	}

	/**
	 * Add a section at the specified offset in the current section list,
	 * or at the end of the list if the offset is not valid
	 *
	 * @param int $offset
	 * @param string|array $sections
	 * @return FORM_ELEMENT
	 */
	public function addSectionsAt($offset, $sections) {
		if (!is_numeric($offset)) $offset = count($this->sections);
		array_splice($this->sections, $offset, 0, $sections);
		return $this;
	}

	/**
	 * Remove the specified section(s) from the current sections list
	 *
	 * @param string|array $sections
	 * @return FORM_ELEMENT
	 */
	public function removeSections($sections) {
		$this->sections = array_diff($this->sections, (array)$sections);
		return $this;
	}

	/**
	 * Returns whether the current sections list contains the specified section(s)
	 *
	 * @param string|array $sections
	 * @return boolean
	 */
	public function hasSections($sections) {
		$interset = array_intersect((array)$sections, $this->sections);
		return (count($interset) == count($sections));
	}

	/**
	 * Remove all sections from the current list
	 *
	 * @return FORM_ELEMENT
	 */
	public function clearSections() {
		$this->sections = array();
		return $this;
	}

	/**
	 * Set the current sections list to the specified array
	 *
	 * @param array $sections
	 * @return FORM_ELEMENT
	 */
	public function setSections(array $sections) {
		$this->sections = $sections;
		return $this;
	}

	/**
	 * Return the current sections list
	 *
	 * @return array
	 */
	public function getSections() {
		return $this->sections;
	}

	/**
	 * Get the sections list for this element, resolved through parents if none specifically set
	 *
	 * @return array
	 */
	public function getSectionsResolved() {
		$sections = $this->getSections();

		if (empty($sections) && $this->parent()) {
			$sections = $this->parent()->getChildTypeSectionsRecurse($this->type());
		}

		return $sections;
	}


	/***************
	 **  HELPERS  **
	 ***************/

	/**
	 * Set the given $current variable to the argument with special language processing.
	 *
	 * If a string, it is set to the form's first defined language
	 *
	 * If a numbered array, they are set in same sequence as the form's defined languages
	 *
	 * If an associative array, they are set to the keys
	 *
	 * @param array $current
	 * @param string|array $arg
	 * @return array
	 */
	public function process_languaged_argument(array &$current, $arg) {
		$languages = $this->form()->getLanguages();

		if (is_array($arg)) {
			$keys = array_keys($arg);

			if (is_integer(current($keys))) {
				// sequential
				$languages = array_slice($languages, 0, count($arg));
				$current = array_combine($languages, $arg);

			} else {
				// associative
				if (!$this->form()->areValidLanguages($keys, $invalid)) {
					throw new FormInvalidLanguageException(null, $invalid, $this);
				}

				$current = array_merge($current, $arg);

			}

		} else {
			$lang = $languages[0];
			$current[$lang] = $arg;
		}

		return $current;

	}

	/**
	 * Makes sure it returns an array of valid languages.
	 *
	 * If null is passed, the array of valid languages is returned.
	 *
	 * If a string is passed, it checked for validity and returned as a one-item array.
	 *
	 * If an array is passed, each item is validated and the whole array is returned.
	 *
	 * @param mixed $lang
	 * @return array
	 */
	public function resolve_lang($lang=null) {
		if (!isset($lang)) {
			return $this->form()->getLanguages();
		} else{
			if (is_string($lang)) $lang = array($lang);

			if (!$this->form()->areValidLanguages($lang, $invalid)) {
				throw new FormInvalidLanguageException(null, $invalid, $this);
			}

			return $lang;
		}
	}

	/**
	 * Return the element's rendering
	 *
	 * @return string
	 */
	public function __toString() {
		$languages = $this->form()->getLanguages();
		return $this->render($languages);
	}

}