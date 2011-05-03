<?php
require_once dirname(__FILE__) . "/Form.class.php";

abstract class FORM_RENDERER {

	public abstract function init(FORM $form);

	/**
	 * Returns the attributes array converted into and html attributes string
	 *
	 * @return string
	 */
	public static function attr2str(array $attributes) {
		$str = "";
		foreach($attributes as $key=>$value) {
			if (is_array($value)) $value = implode(' ', $value);
			$str .= " {$key}='{$value}' ";
		}
		return $str;
	}

}
