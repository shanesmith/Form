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
	 * Callable of renderers keyed by field type
	 *
	 * @var array
	 */
	protected $child_type_renderers = array();


	/********************
	 **  FIELD ADDERS  **
	 ********************/

	/**
	* Creates a new fieldset, inserts it into the current fieldset, and return the new fieldset
	*
	* @param string $name
	* @param string $label
	* @return FORM_FIELDSET
	*/
	public function fieldset($name, $labels=null) {
		return $this->addChild(new FORM_FIELDSET($this, $name, $labels));
	}

	/**
	* Creates a new text field, inserts it into the current fieldset, and returns the new text field
	*
	* @param string $name
	* @param string $label
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
	* @param string $label
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
	* @param string $label
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
	* @param string $label
	* @return FORM_FILE
	*/
	public function file($name, $labels=null) {
		$this->form()->setAttribute('enctype', FORM::ENCTYPE_FILE);
		return $this->addChild(new FORM_FILE($this, $name, $labels));
	}

	/**
	* Creates a new select field, inserts it into the current fieldset, and returns the new select field
	*
	* @param string $name
	* @param string $label
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
	* @param string $label
	* @param string $default
	* @return FORM_CHECKBOX
	*/
	public function checkbox($name, $labels=null, $default=null) {
		return $this->addChild(new FORM_CHECKBOX($this, $name, $labels, $default));
	}

	/**
	* Creates a new radio list, inserts it into the current fieldset, and returns the new radio list
	*
	* @param string $name
	* @param string $label
	* @param string $default
	* @return FORM_RADIO_LIST
	*/
	public function radio_list($name, $labels=null, $default=null) {
		return $this->addChild(new FORM_RADIO_LIST($this, $name, $labels, $default));
	}

	/**
	* Creates a new button, inserts it into the current fieldset, and returns the new button
	*
	* @param string $name
	* @param string $text
	* @return FORM_BUTTON
	*/
	public function button($name, $texts=null) {
		return $this->addChild(new FORM_BUTTON($this, $name, $texts));
	}

	/**
	* Creates a new submit button, inserts it into the current fieldset, and returns the new submit button
	*
	* @param string $name
	* @param string $text
	* @return FORM_SUBMIT_BUTTON
	*/
	public function submit_button($name, $texts=null) {
		return $this->addChild(new FORM_SUBMIT_BUTTON($this, $name, $texts));
	}

	/**
	* Creates a new reset button, inserts it into the current fieldset, and returns the new reset button
	*
	* @param string $name
	* @param string $text
	* @return FORM_RESET_BUTTON
	*/
	public function reset_button($name, $texts=null) {
		return $this->addChild(new FORM_RESET_BUTTON($this, $name, $texts));
	}

	/**
	* Creates a new info field, inserts it into the current fieldset, and returns the new info field
	*
	* @param string $name
	* @param string $label
	* @param string $text
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
	* @returns FORM_ELEMENT
	*/
	protected function addChild(FORM_ELEMENT $elem) {
		$this->children[] = $elem->name();
		return $this->form()->addElement($elem);
	}

	/**
	 * Returns whether this fieldset holds the named child
	 *
	 * @param string $name
	 * @returns boolean
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
	 * @returns FORM_ELEMENT
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
	 * @returns FORM_ELEMENT
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
	 * @returns FORM_ELEMENT
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
	* @return FORM_FIELDSET
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
	 * @returns FORM_ELEMENT
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
	 * @returns FORM_ELEMENT
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
	 * @returns FORM_FIELD
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
	 * @returns FORM_FIELDSET
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
	 * @returns FORM_TEXT
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
	 * @returns FORM_BUTTON
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
	 * @returns FORM_SUBMIT_BUTTON
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
	 * @returns FORM_RESET_BUTTON
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
	 * @returns FORM_CHECKBOX
	 */
	public function getCheckbox($name, $recurse=true) {
		return $this->getChildWithTypeCheck($name, 'checkbox', $recurse);
	}

	/**
	 * Returns the named child element if it exists (possibly under descendant fieldsets if recurse)
	 * and if it's of the type FORM_RADIO_LIST, null otherwise
	 *
	 * @param string $name
	 * @param boolean $recurse
	 * @returns FORM_RADIO_LIST
	 */
	public function getRadioList($name, $recurse=true) {
		return $this->getChildWithTypeCheck($name, 'radio_list', $recurse);
	}

	/**
	 * Returns the named child element if it exists (possibly under descendant fieldsets if recurse)
	 * and if it's of the type FORM_FILE, null otherwise
	 *
	 * @param string $name
	 * @param boolean $recurse
	 * @returns FORM_FILE
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
	 * @returns FORM_HIDDEN
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
	 * @returns FORM_INFO
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
	 * @returns FORM_PASSWORD
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
	 * @returns FORM_SELECT
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
	 * @returns FORM_TEXTAREA
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
				if ($child->hasError()) return true;
			}
			elseif ($child instanceof FORM_FIELD)  {
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
				$errors = array_merge($errors, $child->getAllErrors(true));
			}
			elseif ($child instanceof FORM_FIELD) {
				$errors[$child->name()] = $child->getError();
			}
		}

		return $errors;
	}

	/**
	* Returns an element name keyed array of all errors found in the fields
	* of this fieldset, where each item is the error message in the specified language
	*
	* @param array|string $lang
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


	/*****************
	 **  RENDERING  **
	 *****************/

	/**
	* Set a renderer for all child elements of the specified type,
	* which can optionally be an array of types
	*
	* @param string|array $type
	* @param callable $renderer
	*/
	public function setChildTypeRenderer($types, $renderer) {
		foreach ((array)$types as $t) {
				$this->child_type_renderers[$t] = $renderer;
		}

		return $this;
	}

	/**
	* Set multiple child renderers by an array, keyed by type
	*
	* @param array $renderers
	* @return FORM_FIELDSET
	*/
	public function setChildTypeRenderersArray(array $renderers) {
		$this->child_type_renderers = array_merge(
			$this->child_type_renderers, $renderers
		);
		return $this;
	}

	/**
	* Get the renderer set for all children of the specified type.
	*
	* @param string $type
	*/
	public function getChildTypeRenderer($type) {
		return $this->child_type_renderers[$type];
	}

	/**
	* Get the renderer set for all children of the specified type,
	* determined by self and recursive calls to parents until one is found
	*
	* @param string $type
	*/
	public function getChildTypeRendererRecurse($type) {
		$renderer = $this->getChildTypeRenderer($type);

		if (!$renderer && $this->parent()) {
			$renderer = $this->parent()->getChildTypeRendererRecurse($type);
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
			$parent_child_type_renderers = $this->parent()->getAllChildTypeRenderers();
			$renderers = array_merge($parent_child_type_renderers, $renderers);
		}

		return $renderers;
	}

	/**
	* Renders all children and return the concat
	*
	* @return string
	*/
	public function renderAllChildren($lang=null) {
		$render = "";

		/** @var FORM_ELEMENT $child */
		foreach ($this->getAllChildren() as  $child) {
			$render .= $child->render($lang);
		}

		return $render;
	}

	/**
	* Return the list of errors found in this fieldset as an html list
	*
	* @param string|array $languages
	* @param boolean $recurse
	* @return boolean
	*/
	public function renderErrorList($languages=null, $recurse=true) {
		$languages = $this->resolve_lang($languages);

		$list = "<ul>";

		foreach ($this->getAllErrors($recurse) as $error) {
			$list .= "<li>";

			foreach ($languages as $lang) {
				$list .= "<span class='{$lang}'>{$error[$lang]}</span>";
			}

			$list .= "</li>";
		}

		$list .= "</ul>";

		return $list;
	}

	/**
	* A default renderer for fieldsets
	*
	* @param FORM_FIELDSET $fieldset
	* @param array $languages
	* @returns string
	*/
	public static function _div_renderer($fieldset, array $languages) {
		$labels = $fieldset->getLabels();

		$name = $fieldset->name();

		$elements = $fieldset->renderAllChildren($languages);

		$attributes = $fieldset->getAttributesArray();

		$attributes['class'] .= " form-element-container form-fieldset-container form-fieldset-name-{$name}";

		$attributes = self::attr2str($attributes);


		$str  = "<div {$attributes}>\n";

		$str .= "\t<label class='form-element-label form-fieldset-label'>\n";

		foreach ($languages as $lang) {
			$str .= "\t\t<span class='form-fieldset-label-{$lang}'>{$labels[$lang]}</span>\n";
		}

		$str .= "\t</label>\n";

		$str .= "\t<div class='form-fieldset'>{$elements}</div>\n";

		$str .= "</div>";

		return $str;
	}


	/**
	* A fieldset renderer based on tables
	*
	* @param FORM_FIELDSET $fieldset
	* @param array $languages
	*/
	public static function _table_renderer($fieldset, array $languages) {
		$labels = $fieldset->getLabels();

		$name = $fieldset->name();

		$elements = $fieldset->renderAllChildren($languages);

		$attributes = $fieldset->getAttributesArray();

		$attributes['class'] .= " form-element-container form-fieldset-container form-fieldset-name-{$name}";

		$attributes = self::attr2str($attributes);

		$subfieldset = ($fieldset->parent()->type() == 'fieldset');


		$str = "";

		if ($subfieldset) {
			$str .= "<tr {$attributes}>";
			$str .= "<th class='form-element-label form-fieldset-label'>";
		} else {
			$str .= "<table {$attributes}>\n";
			$str .= "\t<thead class='form-element-label form-fieldset-label'>\n\t\t<tr>\n\t\t\t<th colspan='2'>\n";
		}

		foreach ($languages as $lang) {
			$str .= "\t\t<span class='form-fieldset-label-{$lang}'>{$labels[$lang]}</span>\n";
		}

		if ($subfieldset) {
			$str .= "</th><td><table>\n";
		} else {
			$str .= "\t\t\t</th>\n\t\t</tr>\n\t</thead>\n";
		}

		$str .= "\t<tbody class='form-fieldset'>\n\t\t{$elements}\n\t</tbody>\n";

		$str .= "</table>\n";

		if ($subfieldset) {
			$str .= "</td></tr>";
		}

		return $str;
	}

}
