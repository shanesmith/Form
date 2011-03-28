<?php
require_once dirname(__FILE__) . "/Form.class.php";

/**
 *
 * FORM_RADIO_LIST
 *
 *
 * A collection of HTML radios, sort-of emulating Fieldset
 *
 */
class FORM_RADIO_LIST extends FORM_ELEMENT {

	/**
	* The collection of radios children
	*
	* @var array
	*/
	protected $radios = array();

	/**
	* Optional renderer for all child radio
	*
	* (emulates Fieldset's child_type_renderer)
	*
	* @var string
	*/
	protected $child_radio_renderer;

	/**
	* Values
	*
	* @var string
	*/
	protected $default_value, $posted_value;


	/*******************
	 **  CONSTRUCTOR  **
	 *******************/

	/**
	 * Field constructor
	 *
	 * @param FORM_FIELDSET $parent
	 * @param string $name
	 * @param string $label
	 * @param string $default
	 * @return FORM_FIELD
	 */
	public function __construct(&$parent, $name, $labels=null, $default=null) {
		$this->setDefaultValue($default);
		parent::__construct($parent, $name, $labels);
	}


	/*************
	 **  RADIO  **
	 *************/

	/**
	 * Add a radio field with this list's name
	 *
	 * @param string $value
	 * @param string|array $labels
	 * @return FORM_RADIO
	 */
	public function radio($value, $labels=null) {
		$radio = new FORM_RADIO($this, $this->name(), $value, $labels);
		$this->radios[] = $radio;
		return $radio;
	}


	/**************
	 **  VALUES  **
	 **************/

	 /**
	 * Get the posted value, or if none get default
	 *
	 * @return string
	 */
	 public function getValue() {
		 if ($this->posted_value) {
			 return $this->posted_value;
		 } else {
			 return $this->default_value;
		 }
	 }

	 /**
	 * Return the default value
	 *
	 * @return string
	 */
	public function getDefaultValue() {
		return $this->default_value;
	}

	/**
	* Return the posted value
	*
	* @return string
	*/
	public function getPostedValue() {
		return $this->posted_value;
	}

	/**
	* Set a default value
	*
	* @param string $value
	* @return FORM_FIELD
	*/
	public function setDefaultValue($value) {
		$this->default_value = $value;
		return $this;
	}

	/**
	* Set the posted value
	*
	* @param string $value
	* @return FORM_FIELD
	*/
	public function setPostedValue($value) {
		$this->posted_value = $value;
		return $this;
	}


	/*****************
	 **  RENDERING  **
	 *****************/

	 /**
		* Some functions included to emulate Fieldset
		*
		* see Element::render() needs getChildTypeRendererRecurse() from parent
		*/

	/**
	 * Set a renderer for all child elements of the specified type
	 *
	 * @param string $type
	 * @param callable $renderer
	 */
	public function setChildRadioRenderer($renderer) {
		$this->child_radio_renderer = $renderer;
		return $this;
	}

	/**
	* Get the renderer set for all children of the specified type.
	*
	* @param string $type
	*/
	public function getChildRadioRenderer() {
		return $this->child_radio_renderer;
	}

	/**
	* Get the renderer set for all children of the specified type.
	*
	* @param string $type
	*/
	public function getChildTypeRenderer($type) {
		return ($type == 'radio') ? $this->child_radio_renderer : null;
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
		return isset($this->child_radio_renderer) ? array('radio' => $this->child_radio_renderer) : null;
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
	* Returns the concated rendering of all radios
	*
	* @param string|array $lang
	* @return string
	*/
	public function renderAllRadios($lang=null) {
		$str = "";

		/** @var FORM_RADIO $radio */
		foreach ($this->radios as $radio) {
			$str .= $radio->render($lang);
		}

		return $str;
	}

	/**
	* A default renderer for radio lists
	*
	* @param FORM_RADIO_LIST $radio_list
	* @param array $languages
	* @returns string
	*/
	public static function _div_renderer($radio_list, array $languages) {
		$labels = $radio_list->getLabels();

		$name = $radio_list->name();

		$radios = $radio_list->renderAllRadios($languages);

		$attributes = $radio_list->getAttributesArray();

		$attributes['class'] .= " form-element-container form-radio_list-container form-element-name-{$name}";

		$attributes = self::attr2str($attributes);


		$str  = "<div {$attributes}>\n";

		$str .= "\t<label class='form-element-label form-radio_list-label'>\n";

		foreach ($languages as $lang) {
			$str .= "\t\t<span class='form-radio_list-label-{$lang}'>{$labels[$lang]}</span>\n";
		}

		$str .= "\t</label>\n";

		$str .= "\t<div class='form-radio_list'>{$radios}</div>\n";

		$str .= "</div>";

		return $str;
	}

	/**
	* A default table renderer for radio lists
	*
	* @param FORM_RADIO_LIST $radio_list
	* @param array $languages
	* @returns string
	*/
	public static function _table_renderer($radio_list, array $languages) {
		$labels = $radio_list->getLabels();

		$name = $radio_list->name();

		$elements = $radio_list->renderAllRadios($languages);

		$attributes = $radio_list->getAttributesArray();

		$attributes['class'] .= " form-element-container form-radio_list-container form-element-name-{$name}";

		$attributes = self::attr2str($attributes);

		$subfieldset = ($radio_list->parent()->type() == 'fieldset');


		$str = "";

		if ($subfieldset) {
			$str .= "<tr {$attributes}>";
			$str .= "<th class='form-element-label form-radio_list-label'>";
		} else {
			$str .= "<table {$attributes}>\n";
			$str .= "\t<thead class='form-element-label form-radio_list-label'>\n\t\t<tr>\n\t\t\t<th colspan='2'>\n";
		}

		foreach ($languages as $lang) {
			$str .= "\t\t<span class='form-radio_list-label-{$lang}'>{$labels[$lang]}</span>\n";
		}

		if ($subfieldset) {
			$str .= "</th><td><table>\n";
		} else {
			$str .= "\t\t\t</th>\n\t\t</tr>\n\t</thead>\n";
		}

		$str .= "\t<tbody class='form-radio_list'>\n\t\t{$elements}\n\t</tbody>\n";

		$str .= "</table>\n";

		if ($subfieldset) {
			$str .= "</td></tr>";
		}

		return $str;
	}

}
