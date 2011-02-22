<?php

function attr2str(array $attributes) {
	$s = "";
	foreach($attributes as $key=>$value) {
		if (is_array($value)) $value = implode(' ', $value);
		$s.= " {$key}='{$value}' ";
	}
	return $s;
}

function form_valid_lang($lang) {
	return in_array($lang, array('en', 'fr'));
}