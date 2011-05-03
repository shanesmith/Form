<?php
require_once dirname(__FILE__) . "/Form.class.php";

/**
*
* FORM_SELECT
*
*
* An HTML select field
*
*/
class FORM_SELECT extends FORM_FIELD {

	/**
	* Collection of both options and groups (which contain the sub-options)
	*
	* @see FORM_SELECT_OPTION
	* @see FORM_SELECT_GROUP
	* @var array[FORM_SELECT_OPTION|FORM_SELECT_GROUP]
	*/
	protected $options = array();

	/**
	* Constructor
	*
	* @param FORM_FIELDSET $parent
	* @param string $name
	* @param array|string $labels
	* @param array $options
	* @return FORM_SELECT
	*/
	public function __construct($parent, $name, $labels=null, $options=null, $default=null) {
		parent::__construct($parent, $name, $labels, $default);

		if (isset($options)) {
			$this->addOptionsArray($options);
		}
	}

	/**
	* Return the collection of options
	*
	* @return array[FORM_SELECT_OPTION|FORM_SELECT_GROUP]
	*/
	public function getOptions() {
		return $this->options;
	}

	/**
	* Append an option to the collection
	*
	* @param string $value
	* @param array|string $labels
	* @param boolean $enabled
	* @return FORM_SELECT
	*/
	public function addOption($value, $labels, $enabled=true) {
		$this->options[] = new FORM_SELECT_OPTION($this, $value, $labels, $enabled);
		return $this;
	}

	/**
	* Iteratively add options from the given array, which
	* should be of the form (value => label)
	*
	* ex: array(
	*		'on' => "Ontario",
	* 	'nb' => array("New Brunswick", "Nouveau Brunswick")
	* )
	*
	* @param array $options
	* @param boolean $enabled
	* @return FORM_SELECT
	*/
	public function addOptionsArray(array $options, $enabled=true) {
		foreach ($options as $value => $labels) {
			$this->addOption($value, $labels, $enabled);
		}
		return $this;
	}

	/**
	* Add and return a new group.
	*
	* @param string $name OPTIONAL using null will automatically create a name
	* @param mixed $labels
	* @param array $options
	* @return FORM_SELECT_GROUP
	*/
	public function addGroup($name, $labels, $options=array()) {
		if (!isset($name)) {
			$name = uniqid('group-');
		}

		$group = new FORM_SELECT_GROUP($this, $name, $labels, $options);

		$this->options[$name] = $group;

		return $group;
	}

	/**
	* Get the named group, or null if none found
	*
	* @param string $name
	* @return FORM_SELECT_GROUP
	*/
	public function getGroup($name) {
		$group = $this->options[$name];
		return ($group instanceof FORM_SELECT_GROUP) ? $group : null;
	}

	/**
	* Render the select tag and its groups and options
	*
	* @param array $languages
	* @return string
	*/
	public function fieldHTML(array $languages) {
		$attributes = $this->getFieldAttributesString(array(
			'name' => $this->name(),
		));

		$options = "";
		foreach ($this->options as $opt) {
			$options .= $opt->render($languages);
		}

		return "<select {$attributes}>{$options}</select>";
	}

}


/**
*
* FORM_SELECT_GROUP
*
*
* Groups options for FORM_SELECT
*
*/
class FORM_SELECT_GROUP extends FORM_SELECT_OPT_BASE {

	/**
	* Group's name for reference
	*
	* @var string
	*/
	protected $name;

	/**
	* Collection of options
	*
	* @var array
	*/
	protected $options = array();

	/**
	* Constructor
	*
	* @param FORM_SELECT $select
	* @param string $name
	* @param array|string $labels
	* @param array $options
	* @param boolean $enabled
	* @return FORM_SELECT_GROUP
	*/
	public function __construct(&$select, $name, $labels, array $options=array(), $enabled=true) {
		parent::__construct($select, $labels, $enabled);

		$this->name = $name;

		if (isset($options)) {
			$this->addOptionsArray($options);
		}
	}

	/**
	* Return the full collection of options
	*
	* @return array
	*/
	public function getOptions() {
		return $this->options;
	}

	/**
	* Get the group's name
	*
	* @return string
	*/
	public function getName() {
		return $this->name;
	}

	/**
	* Return the select element that this group belongs to
	*
	* @return FORM_SELECT
	*/
	public function backtoSelect() {
		return $this->getOptParent();
	}

	/**
	* Add an option to the collection
	*
	* @param string $value
	* @param array|string $labels
	* @param boolean $enabled
	*/
	public function addOption($value, $labels, $enabled=true) {
		$this->options[] = new FORM_SELECT_OPTION($this, $value, $labels, $enabled);
		return $this;
	}

	/**
	* Iteratively add options from the given array, which
	* should be of the form (value => label)
	*
	* ex: array(
	*		'on' => "Ontario",
	* 	'nb' => array("New Brunswick", "Nouveau Brunswick")
	* )
	*
	* @param array $options
	* @param boolean $enabled
	* @return FORM_SELECT
	*/
	public function addOptionsArray(array $options, $enabled=true) {
		foreach ($options as $value => $label) {
			$this->addOption($value, $label, $enabled);
		}
		return $this;
	}

	/**
	* Render the optgroup tag with all sub-options
	*
	* @param array $languages
	* @return string
	*/
	public function render(array $languages) {
		$labels = array();

		foreach ($languages as $lang) {
			if ($this->labels[$lang])
				$labels[] = $this->labels[$lang];
		}

		$text = implode(" // ", $labels);

		$disabled = $this->enabled ? "" : "disabled='disabled'";

		$options = "";
		foreach ($this->options as $opt) {
			$options .= $opt->render($languages);
		}

		return "<optgroup label='{$text}' {$disabled}>{$options}</optgroup>";
	}
}


/**
*
* FORM_SELECT_OPTION
*
*
* An option in a select field
*
*/
class FORM_SELECT_OPTION extends FORM_SELECT_OPT_BASE {

	/**
	* The option's value
	*
	* @var string
	*/
	protected $value;

	/**
	* Constructor
	*
	* @param FORM_SELECT|FORM_SELECT_GROUP $parent
	* @param string $value
	* @param array|string $labels
	* @param boolean $enabled
	* @return FORM_SELECT_OPTION
	*/
	public function __construct(&$parent, $value, $labels, $enabled=true) {
		parent::__construct($parent, $labels, $enabled);
		$this->value = $value;
	}

	/**
	* Get the option's value
	*
	*	@return string
	*/
	public function getValue() {
		return $this->value;
	}

	public function isSelected() {
		return ($this->backtoSelect()->getValue() == $this->value);
	}

	public function isDefaultSelected() {
		return ($this->backtoSelect()->getDefaultValue() == $this->value);
	}

	public function isPostedSelected() {
		return ($this->backtoSelect()->getPostedValue() == $this->value);
	}

	/**
	* Return the select element that this option belongs to
	*
	* @return FORM_SELECT
	*/
	public function backtoSelect() {
		$select = $this->getOptParent();

		if ($select instanceof FORM_SELECT_GROUP) {
			$select = $select->backtoSelect();
		}

		return $select;
	}


	/**
	* Render the option tag
	*
	* @param array $languages
	* @return string
	*/
	public function render(array $languages) {
		$labels = array();

		foreach ($languages as $lang) {
			if ($this->labels[$lang])
				$labels[] = $this->labels[$lang];
		}

		$value = $this->value;

		$text = implode(" // ", $labels);

		$selected = $this->isSelected() ? "selected='selected'" : "";

		$disabled = $this->enabled ? "" : "disabled='disabled'";

		return "<option value='{$value}' {$selected} {$disabled}>{$text}</option>";
	}
}

/**
*
* FORM_SELECT_OPT_BASE
*
*
* Base class for groups and options, holds shared variables and methods
*
*/
abstract class FORM_SELECT_OPT_BASE {

	/**
	* Either a group or select
	*
	* @var FORM_SELECT|FORM_SELECT_GROUP
	*/
	protected $parent;

	/**
	* Whether or not this option/group is enabled
	*
	* @var boolean
	*/
	protected $enabled = true;

	/**
	* The option/group labels, with the usual keying by lang
	*
	* @var array
	*/
	protected $labels = array();

	/**
	* Constructor
	*
	* @param FORM_SELECT|FORM_SELECT_GROUP $parent
	* @param array|string $labels
	* @param boolean $enabled
	* @return FORM_SELECT_OPT_BASE
	*/
	public function __construct(&$parent, $labels, $enabled) {
		$this->parent = $parent;
		$this->enabled = (bool) $enabled;
		$this->setLabels($labels);
	}

	/**
	* The the direct parent of this option/group
	*
	* @return FORM_SELECT|FORM_SELECT_GROUP
	*/
	public function getOptParent() {
		return $this->parent;
	}

	/**
	* Set this group's labels
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
		$this->backtoSelect()->process_languaged_argument($this->labels, $labels);
		return $this;
	}

	/**
	* Return whethere or not this option/group is enabled
	*
	* @return booleans
	*/
	public function isEnabled() {
		return $this->enabled;
	}

	/**
	* Return the labels array
	*
	* @return array
	*/
	public function getLabels() {
		return $this->labels;
	}

	/**
	* Return the parenting select
	*
	* @return FORM_SELECT
	*/
	abstract public function backtoSelect();

	/**
	* Render the tag
	*
	* @param array $languages
	* @return string
	*/
	abstract public function render(array $languages);
}