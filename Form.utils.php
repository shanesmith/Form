<?php

function attr2str(array $attributes) {
	$s = "";
	foreach($attributes as $key=>$value) {
		if (is_array($value)) $value = implode(' ', $value);
		$s.= " {$key}='{$value}' ";
	}
	return $s;
}