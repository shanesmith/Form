<?php

require_once "Form.Field.class.php";

require_once "Form.utils.php";

class FORM_FILE extends FORM_FIELD {

	static private $type = 'file';
	protected $file, $upload_error_check=true, $required=false;
	protected static $static_renderer, $static_field_renderer;

	public function __construct($parent, $name, $label='') {
		require_once 'Upload.class.php';

		parent::__construct($parent, $name, $label);

		$this->file = new Upload($_FILES[$this->name()]);
	}

	public function html() { return parent::html('file'); }

	public function render($renderer=null) {
		if (is_callable($renderer)) return call_user_func($renderer, $this);
		elseif (is_callable($this->renderer)) return call_user_func($this->renderer, $this);
		elseif (is_callable(self::$static_renderer)) return call_user_func(self::$static_renderer, $this);
		elseif (is_callable(parent::$base_renderer)) return call_user_func(parent::$base_renderer, $this);
		else return "";
	}

	public function render_field($field_renderer=null) {
		if (is_callable($field_renderer)) return call_user_func($field_renderer, $this);
		elseif (is_callable($this->field_renderer)) return call_user_func($this->field_renderer, $this);
		elseif (is_callable($this->fieldset_field_renderer())) return call_user_func($this->fieldset_field_renderer(), $this);
		elseif (is_callable(self::$static_field_renderer)) return call_user_func(self::$static_field_renderer, $this);
		elseif (is_callable(parent::$base_field_renderer)) return call_user_func(parent::$base_field_renderer, $this);
		else return "";
	}

	public function fieldset_field_renderer() {
		return $this->parent()->field_renderer(self::$type);
	}

	public function type() { return self::$type; }

	public function file() { return $this->file; }

	public function process_options(array $options=null) {
		if (isset($options)) {
			foreach ($options as $key=>$value) $this->file->{$key} = $value;
			return $this;
		}

		return $this->file_options;
	}

	public function error_messages(array $messages=null) {
		if (isset($messages)) {
			$this->file->translation = array_merge($this->file->translation, $messages);
			return $this;
		}

		return $this->file()->translation;
	}

	public function uploaded() { return $this->file->uploaded; }

	public function processed() { return $this->file->processed; }

	public function process($folder=null) {
		if (!$this->uploaded()) return true;

		$this->file->process($folder);

		if (!$this->processed()) $this->setError($this->file->error);

		return $this->processed();
	}

	public static function renderer($renderer) {
		if (isset($this)) $this->renderer = $renderer;
		else self::$static_renderer = $renderer;
	}

	public static function static_field_renderer($field_renderer) {
		self::$static_field_renderer = $field_renderer;
	}

	private function printf_error($matches) {
		$match = strtolower($matches[1]);

		if (property_exists($this->file, $match)) $match = $this->file->$match;
		elseif (method_exists($this->file, $match)) return $this->file->$match();

		return $match;
	}

	public function validate() {
		$error_key = $this->file->error_key;
		$error = $this->file->error = $this->file->translation[$error_key];

		if (!empty($error_key)) {
			if (($error_key=='uploaded_missing' && !$this->required)) {
				return true;
			} else {
				$this->setError($error);
				return false;
			}
		}

		return true;
	}

	public function required($text) {
		$this->error_messages(array('uploaded_missing'=>$text));
		$this->required = true;
		return $this;
	}

	public function upload_error_check($check=null) {
		if (isset($check)) {
			$this->upload_error_check = (bool) $check;
			return $this;
		}

		return $this->upload_error_check;
	}

	public function setError($error) {
		$error = preg_replace_callback('/%([^%]+)%/', array($this, 'printf_error'), $error);
		return parent::setError($error);
	}

}