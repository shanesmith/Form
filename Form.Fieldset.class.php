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
	* Type of Form Element
	*
	* @var string
	*/
	static protected $type = 'fieldset';

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


	/**************
	 **  HELPERS **
	 **************/

	/**
	 * Return the element's type
	 *
	 * @return string
	 */
	public function type() { return self::$type; }



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
	* @return FORM_BUTTOM
	*/
	public function button($name, $texts=null) {
		return $this->addChild(new FORM_BUTTOM($this, $name, $texts));
	}

	/**
	* Creates a new submit button, inserts it into the current fieldset, and returns the new submit button
	*
	* @param string $name
	* @param string $text
	* @return FORM_SUBMIT_BUTTOM
	*/
	public function submit_button($name, $texts=null) {
		return $this->addChild(new FORM_SUBMIT_BUTTOM($this, $name, $texts));
	}

	/**
	* Creates a new reset button, inserts it into the current fieldset, and returns the new reset button
	*
	* @param string $name
	* @param string $text
	* @return FORM_RESET_BUTTON
	*/
	public function reset_button($name, $texts=null) {
		return $this->addChild(new FORM_RESET_BUTTOM($this, $name, $texts));
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
	private function addChild(FORM_ELEMENT $elem) {
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
	* Get all children, either only names or resolved to actual elements
	*
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
	 * Returns the named child element if it exists
	 * and if it matches the given type (class name), null otherwise
	 *
	 * @param string $name
	 * @param string $type
	 * @returns FORM_ELEMENT
	 */
	public function getChildWithTypeCheck($name, $type) {
		$elem = $this->getChild($name);

		if ($elem->type() != $type) return null;

		return $elem;
	}

	/**
	 * Returns the named child element if it exists
	 * and if it's of the type FORM_FIELD, null otherwise
	 *
	 * @param string $name
	 * @returns FORM_FIELD
	 */
	public function getField($name) {
		return $this->getChildWithTypeCheck($name, 'field');
	}

	/**
	 * Returns the named child element if it exists
	 * and if it's of the type FORM_FIELDSET, null otherwise
	 *
	 * @param string $name
	 * @returns FORM_FIELDSET
	 */
	public function getFieldset($name) {
		return $this->getChildWithTypeCheck($name, 'fieldset');
	}

	/**
	 * Returns the named child element if it exists
	 * and if it's of the type FORM_TEXT, null otherwise
	 *
	 * @param string $name
	 * @returns FORM_TEXT
	 */
	public function getText($name) {
		return $this->getChildWithTypeCheck($name, 'text');
	}

	/**
	 * Returns the named child element if it exists
	 * and if it's of the type FORM_BUTTON, null otherwise
	 *
	 * @param string $name
	 * @returns FORM_BUTTON
	 */
	public function getButton($name) {
		return $this->getChildWithTypeCheck($name, 'button');
	}

	/**
	 * Returns the named child element if it exists
	 * and if it's of the type FORM_SUBMIT_BUTTON, null otherwise
	 *
	 * @param string $name
	 * @returns FORM_SUBMIT_BUTTON
	 */
	public function getSubmitButton($name) {
		return $this->getChildWithTypeCheck($name, 'submit_button');
	}

	/**
	 * Returns the named child element if it exists
	 * and if it's of the type FORM_RESET_BUTTON, null otherwise
	 *
	 * @param string $name
	 * @returns FORM_RESET_BUTTON
	 */
	public function getResetButton($name) {
		return $this->getChildWithTypeCheck($name, 'reset_button');
	}

	/**
	 * Returns the named child element if it exists
	 * and if it's of the type FORM_CHECKBOX, null otherwise
	 *
	 * @param string $name
	 * @returns FORM_CHECKbOX
	 */
	public function getCheckbox($name) {
		return $this->getChildWithTypeCheck($name, 'checkbox');
	}

	/**
	 * Returns the named child element if it exists
	 * and if it's of the type FORM_FILE, null otherwise
	 *
	 * @param string $name
	 * @returns FORM_FILE
	 */
	public function getFile($name) {
		return $this->getChildWithTypeCheck($name, 'file');
	}

	/**
	 * Returns the named child element if it exists
	 * and if it's of the type FORM_HIDDEN, null otherwise
	 *
	 * @param string $name
	 * @returns FORM_HIDDEN
	 */
	public function getHidden($name) {
		return $this->getChildWithTypeCheck($name, 'hidden');
	}

	/**
	 * Returns the named child element if it exists
	 * and if it's of the type FORM_INFO, null otherwise
	 *
	 * @param string $name
	 * @returns FORM_INFO
	 */
	public function getInfo($name) {
		return $this->getChildWithTypeCheck($name, 'info');
	}

	/**
	 * Returns the named child element if it exists
	 * and if it's of the type FORM_PASSWORD, null otherwise
	 *
	 * @param string $name
	 * @returns FORM_PASSWORD
	 */
	public function getPassword($name) {
		return $this->getChildWithTypeCheck($name, 'password');
	}

	/**
	 * Returns the named child element if it exists
	 * and if it's of the type FORM_RADIO, null otherwise
	 *
	 * @param string $name
	 * @returns FORM_RADIO
	 */
	public function getRadio($name) {
		return $this->getChildWithTypeCheck($name, 'radio');
	}

	/**
	 * Returns the named child element if it exists
	 * and if it's of the type FORM_SELECT, null otherwise
	 *
	 * @param string $name
	 * @returns FORM_SELECT
	 */
	public function getSelect($name) {
		return $this->getChildWithTypeCheck($name, 'select');
	}

	/**
	 * Returns the named child element if it exists
	 * and if it's of the type FORM_TEXTAREA, null otherwise
	 *
	 * @param string $name
	 * @returns FORM_TEXTAREA
	 */
	public function getTextarea($name) {
		return $this->getChildWithTypeCheck($name, 'textarea');
	}


	/*****************
	 **  RENDERING  **
	 *****************/

	/**
	* Set a renderer for all child elements of the specified type
	*
	* @param string $type
	* @param callable $renderer
	*/
	public function setChildTypeRenderer($type, $renderer) {
		$this->child_type_renderers[$type] = $renderer;
		return $this;
	}

	/**
	* Set multiple child renderers by an array, keyed by type
	*
	* @param array $renderers
	*/
	public function setChildTypeRenderersArray(array $renderers) {
		$this->child_type_renderers = array_merge(
			$this->child_type_renderers, $renderers
		);
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
		foreach ($this->getAllChildren() as   $child) {
			$render .= $child->render($lang);
		}

		return $render;
	}

	/**
	* A default renderer for fieldsets
	*
	* @param FORM_FIELDSET $fieldset
	* @param array $languages
	* @returns string
	*/
	public static function _default_renderer($fieldset, array $languages) {
		$labels = $fieldset->getLabels();

		$elements = $fieldset->renderAllChildren($languages);

		$attributes = $fieldset->getAttributesArray();

		$attributes['class'] .= " form-element-container form-fieldset-container";

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

}
