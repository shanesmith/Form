<?php
require_once dirname(__FILE__) . "/Form.class.php";

/**
 *
 * FORM_FIELDSET
 *
 *
 * Represents a collection of Form Elements (minus Form itself).
 *
 */
class FORM_FIELDSET extends FORM_ELEMENT {

	/**
	 * List of element _names_ that this fieldset contains
	 *
	 * @var array
	 */
	protected $children = array();


	/**
	 * Callable of renderers keyed by field type, then section
	 *
	 * @var array
	 */
	protected $child_type_renderers = array();

	/**
	 * Sections keyed by field type
	 *
	 * @var array
	 */
	protected $child_type_sections = array();


	/********************
	 **  FIELD ADDERS  **
	 ********************/

	/**
	 * Creates a new fieldset, inserts it into the current fieldset, and return the new fieldset
	 *
	 * @param string $name
	 * @param string $labels
	 * @return FORM_FIELDSET
	 */
	public function fieldset($name, $labels=null) {
		return $this->addChild(new FORM_FIELDSET($this, $name, $labels));
	}

	/**
	 * Creates a new text field, inserts it into the current fieldset, and returns the new text field
	 *
	 * @param string $name
	 * @param string $labels
	 * @param string $default
	 * @return FORM_TEXT
	 */
	public function text($name, $labels=null, $default=null) {
		return $this->addChild(new FORM_TEXT($this, $name, $labels, $default));
	}

	/**
	 * Creates a new textarea field, inserts it into the current fieldset, and returns the new textarea field
	 *
	 * @param string $name
	 * @param string $labels
	 * @param string $default
	 * @return FORM_TEXTAREA
	 */
	public function textarea($name, $labels=null, $default=null) {
		return $this->addChild(new FORM_TEXTAREA($this, $name, $labels, $default));
	}

	/**
	 * Creates a new hidden field, inserts it into the current fieldset, and returns the new hidden field
	 *
	 * @param string $name
	 * @param string $default
	 * @return FORM_HIDDEN
	 */
	public function hidden($name, $default=null) {
		return $this->addChild(new FORM_HIDDEN($this, $name, $default));
	}

	/**
	 * Creates a new password field, inserts it into the current fieldset, and returns the new password field
	 *
	 * @param string $name
	 * @param string $labels
	 * @param string $default
	 * @return FORM_PASSWORD
	 */
	public function password($name, $labels=null, $default=null) {
		return $this->addChild(new FORM_PASSWORD($this, $name, $labels, $default));
	}

	/**
	 * Creates a new file field, inserts it into the current fieldset, and returns the new file field
	 *
	 * @param string $name
	 * @param string $labels
	 * @param string $target_dir
	 * @param array $options
	 * @return FORM_FILE
	 */
	public function file($name, $labels=null, $target_dir=null, array $options=array()) {
		$this->form()->setAttribute('enctype', FORM::ENCTYPE_FILE);
		return $this->addChild(new FORM_FILE($this, $name, $labels, $target_dir, $options));
	}

	/**
	 * Creates a new select field, inserts it into the current fieldset, and returns the new select field
	 *
	 * @param string $name
	 * @param string $labels
	 * @param array $options an array of options in the $value=>$text format
	 * @param string $default
	 * @return FORM_SELECT
	 */
	public function select($name, $labels=null, $options=array(), $default=null) {
		return $this->addChild(new FORM_SELECT($this, $name, $labels, $options, $default));
	}

	/**
	 * Creates a new checkbox field, inserts it into the current fieldset, and returns the new checkbox field
	 *
	 * @param string $name
	 * @param string $labels
	 * @param string $default
	 * @return FORM_CHECKBOX
	 */
	public function checkbox($name, $labels=null, $default=null) {
		return $this->addChild(new FORM_CHECKBOX($this, $name, $labels, $default));
	}

	/**
	 * Creates a new radio, inserts it into the current fieldset, and returns the new radio
	 *
	 * The unique name parameter can be set to null, in which case a name will be automatically generated
	 *
	 * @param string $radio_name
	 * @param string $unique_name
	 * @param string $text
	 * @param string $labels
	 * @param bool $default_checked
	 * @return FORM_RADIO
	 */
	public function radio($radio_name, $unique_name, $text, $labels=null, $default_checked=false) {
		if (is_null($unique_name)) {
			$unique_name = "radio_" . substr(md5(uniqid()), 0, 5);
		}
		$radio = $this->addChild(new FORM_RADIO($this, $radio_name, $unique_name, $text, $labels, $default_checked));
		$this->form()->addToRadioList($radio_name, $unique_name, $text);
		return $radio;
	}

	/**
	 * Creates a new button, inserts it into the current fieldset, and returns the new button
	 *
	 * @param string $name
	 * @param string $texts
	 * @return FORM_BUTTON
	 */
	public function button($name, $texts=null) {
		return $this->addChild(new FORM_BUTTON($this, $name, $texts));
	}

	/**
	 * Creates a new submit button, inserts it into the current fieldset, and returns the new submit button
	 *
	 * @param string $name
	 * @param string $texts
	 * @return FORM_SUBMIT_BUTTON
	 */
	public function submit_button($name, $texts=null) {
		return $this->addChild(new FORM_SUBMIT_BUTTON($this, $name, $texts));
	}

	/**
	 * Creates a new reset button, inserts it into the current fieldset, and returns the new reset button
	 *
	 * @param string $name
	 * @param string $texts
	 * @return FORM_RESET_BUTTON
	 */
	public function reset_button($name, $texts=null) {
		return $this->addChild(new FORM_RESET_BUTTON($this, $name, $texts));
	}

	/**
	 * Creates a new info field, inserts it into the current fieldset, and returns the new info field
	 *
	 * @param string $name
	 * @param string $labels
	 * @param string $texts
	 * @return FORM_INFO
	 */
	public function info($name, $labels=null, $texts=null) {
		return $this->addChild(new FORM_INFO($this, $name, $labels, $texts));
	}

	/****************
	 **  CHILDREN  **
	 ****************/

	/**
	 * Add the given element as a child of this fieldset
	 *
	 * @param FORM_ELEMENT $elem
	 * @return FORM_ELEMENT
	 */
	protected function addChild(FORM_ELEMENT $elem) {
		$this->children[] = $elem->name();
		return $this->form()->addElement($elem);
	}

	/**
	 * Returns whether this fieldset holds the named child
	 *
	 * @param string $name
	 * @return boolean
	 */
	public function hasChild($name) {
		return in_array($name, $this->children);
	}

	/**
	 * Returns whether this fieldset, or any descendant fieldsets,
	 * holds the named child
	 *
	 * @param string $name
	 * @return boolean
	 */
	public function hasChildRecursive($name) {
		return (bool)$this->getChildRecursive($name);
	}

	/**
	 * Remove and return the named element from this fieldset (and the form)
	 *
	 * If there are no child of the given name, then null is returned
	 *
	 * @param string $name
	 * @return FORM_ELEMENT
	 */
	public function removeChild($name) {
		if (!$this->hasChild($name)) return null;

		unset($this->children[$name]);

		return $this->form()->removeElement($name);
	}

	/**
	 * Remove and return the named element from this fieldset,
	 * or any descendant fieldsets, and the form
	 *
	 * If there are no child of the given name, then null is returned
	 *
	 * @param string $name
	 * @return FORM_ELEMENT
	 */
	public function removeChildRecursive($name) {
		$elem = $this->getChildRecursive($name);

		if ($elem) {
			$elem->parent()->removeChild($name);
		}

		return $elem;
	}

	/**
	 * Return the named child element if it exists, null otherwise
	 *
	 * @param string $name
	 * @return FORM_ELEMENT
	 */
	public function getChild($name) {
		if (!$this->hasChild($name)) return null;

		return $this->form()->getElement($name);
	}

	/**
	 * Return the named child element if it exists in this fieldset or
	 * any descendant fieldsets, null otherwise
	 *
	 * @param string $name
	 * @return FORM_ELEMENT
	 */
	public function getChildRecursive($name) {
		// checking ancestory should be faster than drilling down each fieldset
		$elem = $this->form()->getElement($name);
		return $elem->hasAncestor($this->name()) ? $elem : null;
	}

	/**
	 * Get all children, either only names or resolved to actual elements
	 *
	 * @param boolean $resolve
	 * @return array(string|FORM_ELEMENT)
	 */
	public function getAllChildren($resolve=true) {
		if (!$resolve) return $this->children;

		$children = array();

		foreach ($this->children as $name) {
			$children[$name] = $this->getChild($name);
		}

		return $children;
	}

	/**
	 * Returns the named child element if it exists (possibly under descendant fieldsets if recurse)
	 * and if it matches the given type (class name), null otherwise
	 *
	 * @param string $name
	 * @param string $type
	 * @param boolean $recurse
	 * @return FORM_ELEMENT
	 */
	public function getChildWithTypeCheck($name, $type, $recurse=false) {
		$elem = $recurse ? $this->getChildRecursive($name) : $this->getChild($name);

		if ($elem && $elem->type() != $type) return null;

		return $elem;
	}

	/**
	 * Returns the named child element if it exists in this fieldset or under descendant fieldsets
	 * and if it matches the given type (class name), null otherwise
	 *
	 * @param string $name
	 * @param string $type
	 * @return FORM_ELEMENT
	 */
	public function getChildWithTypeCheckRecursive($name, $type) {
		return $this->getChildWithTypeCheck($name, $type, true);
	}


	/**
	 * Returns the named child element if it exists (possibly under descendant fieldsets if recurse)
	 * and if it's of the type FORM_FIELD, null otherwise
	 *
	 * @param string $name
	 * @param boolean $recurse
	 * @return FORM_FIELD
	 */
	public function getField($name, $recurse=true) {
		$elem = $recurse ? $this->getChildRecursive($name) : $this->getChild($name);

		if (!($elem instanceof FORM_FIELD)) return null;

		return $elem;
	}

	/**
	 * Returns the named child element if it exists (possibly under descendant fieldsets if recurse)
	 * and if it's of the type FORM_FIELDSET, null otherwise
	 *
	 * @param string $name
	 * @param boolean $recurse
	 * @return FORM_FIELDSET
	 */
	public function getFieldset($name, $recurse=true) {
		return $this->getChildWithTypeCheck($name, 'fieldset', $recurse);
	}

	/**
	 * Returns the named child element if it exists (possibly under descendant fieldsets if recurse)
	 * and if it's of the type FORM_TEXT, null otherwise
	 *
	 * @param string $name
	 * @param boolean $recurse
	 * @return FORM_TEXT
	 */
	public function getText($name, $recurse=true) {
		return $this->getChildWithTypeCheck($name, 'text', $recurse);
	}

	/**
	 * Returns the named child element if it exists (possibly under descendant fieldsets if recurse)
	 * and if it's of the type FORM_BUTTON, null otherwise
	 *
	 * @param string $name
	 * @param boolean $recurse
	 * @return FORM_BUTTON
	 */
	public function getButton($name, $recurse=true) {
		return $this->getChildWithTypeCheck($name, 'button', $recurse);
	}

	/**
	 * Returns the named child element if it exists (possibly under descendant fieldsets if recurse)
	 * and if it's of the type FORM_SUBMIT_BUTTON, null otherwise
	 *
	 * @param string $name
	 * @param boolean $recurse
	 * @return FORM_SUBMIT_BUTTON
	 */
	public function getSubmitButton($name, $recurse=true) {
		return $this->getChildWithTypeCheck($name, 'submit_button', $recurse);
	}

	/**
	 * Returns the named child element if it exists (possibly under descendant fieldsets if recurse)
	 * and if it's of the type FORM_RESET_BUTTON, null otherwise
	 *
	 * @param string $name
	 * @param boolean $recurse
	 * @return FORM_RESET_BUTTON
	 */
	public function getResetButton($name, $recurse=true) {
		return $this->getChildWithTypeCheck($name, 'reset_button', $recurse);
	}

	/**
	 * Returns the named child element if it exists (possibly under descendant fieldsets if recurse)
	 * and if it's of the type FORM_CHECKBOX, null otherwise
	 *
	 * @param string $name
	 * @param boolean $recurse
	 * @return FORM_CHECKBOX
	 */
	public function getCheckbox($name, $recurse=true) {
		return $this->getChildWithTypeCheck($name, 'checkbox', $recurse);
	}

	/**
	 * Returns the named child element if it exists (possibly under descendant fieldsets if recurse)
	 * and if it's of the type FORM_RADIO, null otherwise
	 *
	 * @param string $unique_name
	 * @param boolean $recurse
	 * @return FORM_RADIO
	 */
	public function getRadio($unique_name, $recurse=true) {
		return $this->getChildWithTypeCheck($unique_name, 'radio', $recurse);
	}

	/**
	 * Returns the named child element if it exists (possibly under descendant fieldsets if recurse)
	 * and if it's of the type FORM_FILE, null otherwise
	 *
	 * @param string $name
	 * @param boolean $recurse
	 * @return FORM_FILE
	 */
	public function getFile($name, $recurse=true) {
		return $this->getChildWithTypeCheck($name, 'file', $recurse);
	}

	/**
	 * Returns the named child element if it exists (possibly under descendant fieldsets if recurse)
	 * and if it's of the type FORM_HIDDEN, null otherwise
	 *
	 * @param string $name
	 * @param boolean $recurse
	 * @return FORM_HIDDEN
	 */
	public function getHidden($name, $recurse=true) {
		return $this->getChildWithTypeCheck($name, 'hidden', $recurse);
	}

	/**
	 * Returns the named child element if it exists (possibly under descendant fieldsets if recurse)
	 * and if it's of the type FORM_INFO, null otherwise
	 *
	 * @param string $name
	 * @param boolean $recurse
	 * @return FORM_INFO
	 */
	public function getInfo($name, $recurse=true) {
		return $this->getChildWithTypeCheck($name, 'info', $recurse);
	}

	/**
	 * Returns the named child element if it exists (possibly under descendant fieldsets if recurse)
	 * and if it's of the type FORM_PASSWORD, null otherwise
	 *
	 * @param string $name
	 * @param boolean $recurse
	 * @return FORM_PASSWORD
	 */
	public function getPassword($name, $recurse=true) {
		return $this->getChildWithTypeCheck($name, 'password', $recurse);
	}

	/**
	 * Returns the named child element if it exists (possibly under descendant fieldsets if recurse)
	 * and if it's of the type FORM_SELECT, null otherwise
	 *
	 * @param string $name
	 * @param boolean $recurse
	 * @return FORM_SELECT
	 */
	public function getSelect($name, $recurse=true) {
		return $this->getChildWithTypeCheck($name, 'select', $recurse);
	}

	/**
	 * Returns the named child element if it exists (possibly under descendant fieldsets if recurse)
	 * and if it's of the type FORM_TEXTAREA, null otherwise
	 *
	 * @param string $name
	 * @param boolean $recurse
	 * @return FORM_TEXTAREA
	 */
	public function getTextarea($name, $recurse=true) {
		return $this->getChildWithTypeCheck($name, 'textarea', $recurse);
	}


	/******************
	 **  VALIDATING  **
	 ******************/

	/**
	 * Iterate over each child and run validation
	 *
	 * Returns true if all are valid, false otherwise
	 *
	 * @return boolean
	 */
	public function validate() {
		$valid = true;
		foreach ($this->getAllChildren() as $child) {
			if (!($child instanceof FORM_INFO)) {
				/** @var FORM_FIELD|FORM_FIELDSET $child */
				$valid |= $child->validate();
			}
		}
		return $valid;
	}

	/**
	 * Returns true if at least one of its children, including sub-fieldset's chilren if $recurse,
	 * has an error, false otherwise
	 *
	 * @param boolean $recurse
	 * @return boolean
	 */
	public function hasErrors($recurse=true) {
		foreach ($this->getAllChildren() as $child) {
			if ($child instanceof FORM_FIELDSET && $recurse) {
				/** @var FORM_FIELDSET $child */
				if ($child->hasError()) return true;
			}
			elseif ($child instanceof FORM_FIELD)  {
				/** @var FORM_FIELD $child */
				if ($child->hasError()) return true;
			}
		}
		return false;
	}

	/**
	 * Synonym for hasErrors()
	 *
	 * @see hasErrors()
	 * @param boolean $recurse
	 * @return boolean
	 */
	public function hasError($recurse=true) {
		return $this->hasErrors($recurse);
	}

	/**
	 * Returns true if there are no errors
	 *
	 * @see hasErrors()
	 * @param boolean $recurse
	 * @return boolean
	 */
	public function isValid($recurse=true) {
		return !$this->hasErrors($recurse);
	}

	/**
	 * Returns an element name keyed array of all errors found in the fields
	 * of this fieldset.
	 *
	 * Each element is a language keyed array of error messages.
	 *
	 * @param boolean $recurse
	 * @return array
	 */
	public function getAllErrors($recurse=true) {
		$errors = array();

		foreach ($this->getAllChildren() as $child) {
			if ($child instanceof FORM_FIELDSET && $recurse) {
				/** @var FORM_FIELDSET $child */
				$errors = array_merge($errors, $child->getAllErrors(true));
			}
			elseif ($child instanceof FORM_FIELD) {
				/** @var FORM_FIELD $child */
				$errors[$child->name()] = $child->getError();
			}
		}

		return $errors;
	}

	/**
	 * Returns an element name keyed array of all errors found in the fields
	 * of this fieldset, where each item is the error message in the specified language
	 *
	 * @param string $lang
	 * @param boolean $recurse
	 * @return array
	 */
	public function getAllErrorsByLang($lang, $recurse=true) {
		if (!$this->form()->isValidLanguage($lang)) {
			throw new FormInvalidLanguageException("The language {$lang} is invalid", $lang, $this);
		}

		$errors = $this->getAllErrors($recurse);

		foreach ($errors as $elem => $err) {
			$errors[$elem] = $err[$lang];
		}

		return $errors;
	}


	/***********************
	 **  FILE PROCESSING  **
	 ***********************/

	/**
	 * Processes all child file fields, optionally recursive
	 * down other fieldsets
	 *
	 * @param boolean $recurse
	 * @return FORM_FIELDSET
	 */
	public function processUploadedFiles($recurse=true) {
		foreach ($this->getAllChildren() as $child) {
			if ($child instanceof FORM_FILE) {
				/** @var FORM_FILE $child */
				$child->process();
			}
			elseif ($child instanceof FORM_FIELDSET && $recurse) {
				/** @var FORM_FIELDSET $child */
				$child->processUploadedFiles(true);
			}
		}

		return $this;
	}


	/****************
	 **  SECTIONS  **
	 ****************/

	/**
	 * Set a sections list by type(s)
	 *
	 * @param string|array $types
	 * @param string|array $sections
	 * @return FORM_FIELDSET
	 */
	public function setChildTypeSections($types, $sections) {
		foreach ((array)$types as $t) {
			$this->child_type_sections[$t] = (array)$sections;
		}
		return $this;
	}

	/**
	 * Set sections list by an array keyed by types
	 *
	 * @param array $type_sections
	 * @return FORM_FIELDSET
	 */
	public function setChildTypeSectionsArray(array $type_sections) {
		foreach ($type_sections as $type => $sections) {
			$this->setChildTypeSections($type, $sections);
		}
		return $this;
	}

	/**
	 * Set the sections list for the specified types to the parent's resolved sections list
	 *
	 * @param string|array $types
	 * @return FORM_FIELDSET
	 */
	public function inheritChildTypeSections($types) {
		if ($this->parent()) {
			$parent = $this->parent();
			foreach ((array)$types as $t) {
				$this->setChildTypeSections($t, $parent->getChildTypeSectionsRecurse($t));
			}
		}
		return $this;
	}

	/**
	 * Add the specified section(s) to the end of the current sections
	 * list of the given type(s)
	 *
	 * @param string|array $types
	 * @param string|array $sections
	 * @return FORM_FIELDSET
	 */
	public function addChildTypeSections($types, $sections) {
		foreach ((array)$types as $t) {
			$this->addChildTypeSectionsLast($t, $sections);
		}
		return $this;
	}

	/**
	 * Add the specified section(s) to the end of the current sections
	 * list of the given type(s)
	 *
	 * @param string|array $types
	 * @param string|array $sections
	 * @return FORM_FIELDSET
	 */
	public function addChildTypeSectionsLast($types, $sections) {
		foreach ((array)$types as $t) {
			$offset = count($this->getChildTypeSections($t));
			$this->addChildTypeSectionsAt($t, $offset, $sections);
		}
		return $this;
	}

	/**
	 * Add the specified section(s) to the start of the current sections
	 * list of the given type(s)
	 *
	 * @param string|array $types
	 * @param string|array $sections
	 * @return FORM_FIELDSET
	 */
	public function addChildTypeSectionsFirst($types, $sections) {
		$this->addChildTypeSectionsAt($types, 0, $sections);
		return $this;
	}

	/**
	 * Add the specified section(s) after the given section in the current sections
	 * list of the given type(s)
	 *
	 * @param string|array $types
	 * @param string $after
	 * @param string|array $sections
	 * @return FORM_FIELDSET
	 */
	public function addChildTypeSectionsAfter($types, $after, $sections) {
		foreach ((array)$types as $t) {
			$offset = array_search($after, $this->getChildTypeSections($t)) + 1;
			$this->addChildTypeSectionsAt($t, $offset, $sections);
		}
		return $this;
	}

	/**
	 * Add the specified section(s) before the given section in the current sections
	 * list of the given type(s)
	 *
	 * @param string|array $types
	 * @param string $before
	 * @param string|array $sections
	 * @return FORM_FIELDSET
	 */
	public function addChildTypeSectionsBefore($types, $before, $sections) {
		foreach ((array)$types as $t) {
			$offset = array_search($before, $this->getChildTypeSections($t));
			$this->addChildTypeSectionsAt($t, $offset, $sections);
		}
		return $this;
	}

	/**
	 * Add the specified section(s) at the given offset in the current sections
	 * list of the given type(s)
	 *
	 * @param string|array $types
	 * @param int $offset
	 * @param string|array $sections
	 * @return FORM_FIELDSET
	 */
	public function addChildTypeSectionsAt($types, $offset, $sections) {
		if (!is_numeric($offset)) $offset = count($this->sections);
		foreach ((array)$types as $t) {
			array_splice($this->child_type_sections[$t], $offset, 0, $sections);
		}
		return $this;
	}

	/**
	 * Remove the specified section(s) from the given type(s)
	 *
	 * @param string|array $types
	 * @param string|array $sections
	 * @return FORM_FIELDSET
	 */
	public function removeChildTypeSections($types, $sections) {
		foreach ((array)$types as $t) {
			$diff = array_diff((array)$this->getChildTypeSections($t), (array)$sections);
			$this->setChildTypeSections($t,	$diff);
		}
		return $this;
	}

	/**
	 * Clear the sections list for each of the given types
	 *
	 * @param string|array $types
	 * @return FORM_FIELDSET
	 */
	public function clearChildTypeSections($types) {
		$this->setChildTypeSections($types, array());
		return $this;
	}

	/**
	 * Returns whether each of the given types has the specified sections
	 *
	 * @param string|array $types
	 * @param string|array $sections
	 * @return FORM_FIELDSET
	 */
	public function hasChildTypeSections($types, $sections) {
		$has = true;
		foreach ((array)$types as $t) {
			$interset = array_intersect((array)$sections, $this->getChildTypeSections($t));
			$has &= (count($interset) == count((array)$sections));
		}
		return $has;
	}

	/**
	 * Return the currently set sections list for the specified child type
	 *
	 * @param string $type
	 * @return array
	 */
	public function getChildTypeSections($type) {
		return $this->child_type_sections[$type];
	}

	/**
	 * Return the sections list for the specified child type, recursing through parents if none
	 *
	 * @param string $type
	 * @return array
	 */
	public function getChildTypeSectionsRecurse($type) {
		$sections = $this->getChildTypeSections($type);

		if (!$sections && $this->parent()) {
			$sections = $this->parent()->getChildTypeSectionsRecurse($type);
		}

		return $sections;
	}

	/**
	 * Return the array of child type sections lists, keyed by child type
	 *
	 * @return array
	 */
	public function getAllChildTypeSections() {
		return $this->child_type_sections;
	}


	/*****************
	 **  RENDERING  **
	 *****************/

	/**
	 * Set a section renderer for all child elements of the specified type,
	 * which can optionally be an array of types
	 *
	 * @param string|array $types
	 * @param string $sections
	 * @param callable $renderer
	 * @return Form_Fieldset
	 */
	public function setChildTypeRenderer($types, $sections, $renderer) {
		foreach ((array)$types as $t) {
			foreach ((array)$sections as $s) {
				$this->child_type_renderers[$t][$s] = $renderer;
			}
		}
		return $this;
	}

	/**
	 * Set multiple child renderers by an array, keyed by type, then section
	 *
	 * @param array $type_section_renderers
	 * @return FORM_FIELDSET
	 */
	public function setChildTypeRenderersArray(array $type_section_renderers) {
		foreach ($type_section_renderers as $type => $section_renderers) {
			$this->setChildTypeRenderersSectionArray($type, $section_renderers);
		}
		return $this;
	}

	/**
	 * Set renderers for the given child type through an array, keyed by section
	 *
	 * @param string $type
	 * @param array $section_renderers
	 * @return FORM_FIELDSET
	 */
	public function setChildTypeRenderersSectionArray($type, array $section_renderers) {
		foreach ($section_renderers as $section => $renderer) {
			$this->setChildTypeRenderer($type, $section, $renderer);
		}
		return $this;
	}

	/**
	 * Get the renderer set for all children of the specified type and section.
	 *
	 * @param string $type
	 * @param string $section
	 * @return string
	 */
	public function getChildTypeRenderer($type, $section) {
		return $this->child_type_renderers[$type][$section];
	}

	/**
	 * Get all section renderers for the specified type, keyed by section
	 *
	 * @param string $type
	 * @return array
	 */
	public function getChildTypeAllSectionsRenderers($type) {
		return $this->child_type_renderers[$type];
	}

	/**
	 * Get the renderer set for all children of the specified type,
	 * determined by self and recursive calls to parents until one is found
	 *
	 * @param string $type
	 * @param string $section
	 * @return string
	 */
	public function getChildTypeRendererRecurse($type, $section) {
		$renderer = $this->getChildTypeRenderer($type, $section);

		if (!$renderer && $this->parent()) {
			$renderer = $this->parent()->getChildTypeRendererRecurse($type, $section);
		}

		return $renderer;
	}

	/**
	 * Return an array of all renderers set for children, keyed by element type.
	 *
	 * @return array
	 */
	public function getAllChildTypeRenderers() {
		return $this->child_type_renderers;
	}

	/**
	 * Return an array of all renders set for children, keyed by element type,
	 * and recursively determined through parents
	 *
	 * @return array
	 */
	public function getAllChildTypeRenderersRecurse() {
		$renderers = $this->getAllChildTypeRenderers();

		if ($this->parent()) {
			$parent_child_type_renderers = $this->parent()->getAllChildTypeRenderersRecurse();
			$renderers = array_merge_recursive($parent_child_type_renderers, $renderers);
		}

		return $renderers;
	}

	/**
	 * Set sections and renderers at the same time through an array, keyed first by type, then by section
	 *
	 * A special type of 'field' can be used, which is substituted by all field types
	 *
	 * @param array $type_section_renderer
	 * @return FORM_FIELDSET
	 */
	public function setChildTypeSectionsAndRenderers(array $type_section_renderer) {

		foreach ($type_section_renderer as $type => $section_renderer) {
			if ($type == 'field') continue;

			$sections = array_diff(array_keys($section_renderer), array('container'));
			$this->setChildTypeSections($type, $sections);
			$this->setChildTypeRenderersSectionArray($type, $section_renderer);
		}

		// if there exists the special 'field' type, iterate over all field types
		// and set sections and renderes if none previously set
		if (array_key_exists('field', $type_section_renderer)) {

			$section_renderer = $type_section_renderer['field'];
			$sections = array_diff(array_keys($section_renderer), array('container'));

			foreach (self::getFieldTypes() as $type) {
				if (!$this->getChildTypeSections($type)) {
					$this->setChildTypeSections($type, $sections);
					$this->setChildTypeRenderersSectionArray($type, $section_renderer);
				}
			}

		}

		return $this;
	}

	/**
	 * Renders all children and return the concat
	 *
	 * @param string $lang
	 * @return string
	 */
	public function renderAllChildren($lang=null) {
		$render = "";

		foreach ($this->getAllChildren() as $child) {
			/** @var FORM_ELEMENT $child */
			$render .= $child->render($lang);
		}

		return $render;
	}

}
