<?php

function attr2str(array $attributes) {
	$s = "";
	foreach($attributes as $key=>$value) {
		if (is_array($value)) $value = implode(' ', $value);
		$s.= " {$key}='{$value}' ";
	}
	return $s;
}

function climb($lookup, array $var) {
	if (is_string($lookup)) {
		$str = $lookup;
		parse_str($str, $lookup);
	}

	$curlookup = current($lookup);
	$curkey = key($lookup);
	$curvalue = is_array($var) && isset($var[$curkey]) ? $var[$curkey] : null;

	if ($curlookup==null) return $curvalue;
	elseif ($curvalue==null) return null;
	else return climb($curlookup, $curvalue);
}