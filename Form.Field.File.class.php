<?php
require_once dirname(__FILE__) . "/Form.class.php";

/**
 *
 * FORM_FILE
 *
 *
 * An HTML file input
 *
 */
class FORM_FILE extends FORM_FIELD {

	/**
	 * Keys found in a $_FILES entry, for reference
	 *
	 * @var array
	 */
	public static $infokeys = array('name', 'type', 'size', 'tmp_name', 'error');

	/**
	 * The $_FILES entry associated to this file field, if any
	 *
	 * @var array
	 */
	protected $file_info;

	/**
	 * The class.upload.php object set when a file is uploaded
	 *
	 * @var upload
	 */
	protected $handle;

	/**
	 * The list of class.upload.php options to set
	 *
	 * Needed since options are most likely set before the handle is created,
	 * notice that initHandle() will copy these options when creating the handle
	 *
	 * @var array
	 */
	protected $options = array();

	/**
	 * The target directory to be passed to class.upload.php's process()
	 *
	 * @var string
	 */
	protected $target_dir;

	/**
	 * Optional user defined processor, has keys 'func' and 'args'
	 *
	 * @var array
	 */
	protected $processor;


	/*******************
	 **  CONSTRUCTOR  **
	 *******************/

	/**
	 * Constructor
	 *
	 * @param FORM_FIELDSET $parent
	 * @param string $name
	 * @param array|string $labels
	 * @param string $target_dir
	 * @param array $options
	 * @return FORM_FILE
	 */
	public function __construct(&$parent, $name, $labels=null, $target_dir=null, array $options=array()) {
		parent::__construct($parent, $name, $labels);
		$this->setTargetDir($target_dir);
		$this->setProcessingOptionsArray($options);
	}

	/***************
	 **  OPTIONS  **
	 ***************/

	/**
	 * Sets uploaded file information (an entry from $_FILES) and initializes a handle with it
	 *
	 * @param array $file_info An entry from $_FILES
	 * @return FORM_FILE
	 */
	public function setUploadedFileInfo(array $file_info) {
		$this->file_info = $file_info;
		$this->initHandle();
		return $this;
	}

	/**
	 * Return the file information array,
	 * or a specific item is $arg is provided
	 *
	 * @param string $arg
	 * @return array|string
	 */
	public function getFileInfo($arg=null) {
		$info = $this->file_info;
		return $arg ? $info[$arg] : $info;
	}

	/**
	 * Creates a new handle and copies
	 * over previously set options
	 *
	 * @return FORM_FILE
	 */
	public function initHandle() {
		$this->handle = new upload($this->file_info);
		foreach ($this->options as $opt => $val) {
			$this->handle->{$opt} = $val;
		}
		return $this;
	}

	/**
	 * Get the handle, which is possibly null
	 *
	 * @return UPLOAD
	 */
	public function getHandle() {
		return $this->handle;
	}

	/**
	 * Whether a file was sucessfully uploaded
	 * (note: not necessarily processed)
	 *
	 * @return boolean
	 */
	public function hasUploadedFile() {
		$handle = $this->getHandle();
		return ($handle && $handle->uploaded);
	}

	/**
	 * Set the target directory, which will be passed
	 * to class.upload.php's process()
	 *
	 * @param string $dir
	 * @return FORM_FILE
	 */
	public function setTargetDir($dir) {
		$this->target_dir = $dir;
		return $this;
	}

	/**
	 * Return the currently set target directory
	 *
	 * @return string
	 */
	public function getTargetDir() {
		return $this->target_dir;
	}

	/**
	 * Set an array's worth or processing options,
	 * each key should be a valid class.upload.php option
	 *
	 * @param array $options
	 * @return FORM_FILE
	 */
	public function setProcessingOptionsArray(array $options) {
		foreach ($options as $opt => $val) {
			$this->setProcessingOption($opt, $val);
		}
		return $this;
	}

	/**
	 * Set an individual processing option,
	 * the option name should be a valid class.upload.php option
	 *
	 * @param string $opt
	 * @param mixed $val
	 * @return FORM_FILE
	 */
	public function setProcessingOption($opt, $val) {
		$this->options[$opt] = $val;
		if ($this->getHandle()) {
			$this->getHandle()->{$opt} = $val;
		}
		return $this;
	}

	/**
	 * Clear all previously set processing options and loading defaults
	 *
	 * @return FORM_FILE
	 */
	public function clearProcessingOptions() {
		$this->options = array();
		if ($this->getHandle()) {
			$this->getHandle()->init();
		}
		return $this;
	}

	/**
	 * Get a specific processing option,
	 * the option name should be a valid class.upload.php option
	 *
	 * @param string $opt
	 * @return mixed
	 */
	public function getProcessingOption($opt) {
		$handle = $this->getHandle();
		return $handle ? $handle->{$opt} : $this->options[$opt];
	}


	/******************
	 **  PROCESSING  **
	 ******************/

	/**
	 * Set a processor, with optiona argument array that will be passed to it
	 *
	 * @param callable $processor
	 * @param array $args
	 * @return FORM_FILE
	 */
	public function setProcesssor($processor, array $args=array()) {
		$this->processor = array(
			'func' => $processor,
			'args' => $args
		);
		return $this;
	}

	/**
	 * Get the currently set processor, which will be an array
	 * with the keys 'func' and 'args', or null is none set
	 *
	 * @return array
	 */
	public function getProcessor() {
		return $this->processor;
	}

	/**
	 * Remove the currently set processor
	 *
	 * @return FORM_FILE
	 */
	public function clearProcessor() {
		unset($this->processor);
		return $this;
	}

	/**
	 * Process the uploaded file by calling the
	 * processor, or, if none are set, the default processor
	 *
	 * @return FORM_FILE
	 */
	public function process() {
		if ($this->hasUploadedFile()) {

			$processor = $this->getProcessor();

			if (empty($processor)) {
				$processor = array(
					'func' => array("FORM_FILE", "default_processor"),
					'args' => array()
				);
			}

			if (!is_callable($processor['func'])) {
				throw new FormInvalidProcessor("The processor is not a callable", $this, $processor);
			}

			$arguments = array_merge(array($this, $this->getTargetDir(), $this->getHandle()), (array)$processor['args']);

			$error = call_user_func_array($processor['func'], $arguments);

			if (!empty($error)) {
				$error = $this->name() . ": " . $error;
				throw new FormFileUploadError($error, $this);
			}

		}

		return $this;
	}

	/**
	 * The default processor to run when there is no other processor set,
	 * returns the error text, if any
	 *
	 * @param FORM_FILE $elem
	 * @param string $target_dir
	 * @param UPLOAD $handle
	 * @return string
	 */
	public static function default_processor($elem, $target_dir, $handle) {
		$handle->process($target_dir);

		return $handle->processed ? "" : $handle->error;
	}

}