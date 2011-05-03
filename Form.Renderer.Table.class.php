<?php
require_once dirname(__FILE__) . "/Form.class.php";

class FORM_TABLE_RENDERER extends FORM_DIV_RENDERER {

	public function init(FORM $form) {
		$form->setDefaultSectionsAndRenderers(array(
			'form' => array(
				'container'  => array("FORM_DIV_RENDERER", "renderFormContainer"),
				'errors'     => array("FORM_DIV_RENDERER", "renderFormErrors"),
				'children'   => array("FORM_DIV_RENDERER", "renderFormChildren")
			),

			'fieldset' => array(
				'container' => array("FORM_TABLE_RENDERER", "renderFieldsetContainer"),
				'label' 		=> array("FORM_TABLE_RENDERER", "renderFieldsetLabel"),
				'children' 	=> array("FORM_TABLE_RENDERER", "renderFieldsetChildren")
			),

			'info' => array(
				'container' => array("FORM_TABLE_RENDERER", "renderInfoContainer"),
				'label' 		=> array("FORM_TABLE_RENDERER", "renderInfoLabel"),
				'text'			=> array("FORM_TABLE_RENDERER", "renderInfoText")
			),

			'field' => array(
				'container' => array("FORM_TABLE_RENDERER", "renderFieldContainer"),
				'label' 		=> array("FORM_TABLE_RENDERER", "renderFieldLabel"),
				'field' 		=> array("FORM_TABLE_RENDERER", "renderField"),
				'error' 		=> array("FORM_TABLE_RENDERER", "renderFieldError"),
			),
		));

		// field types below only need a field section (no need for label and error sections)
		$form->setChildTypeSections(array('hidden', 'button', 'submit', 'reset'), array('field'));

	}

	
	/****************
	 **  FIELDSET  **
	 ****************/

	public static function renderFieldsetContainer(FORM_FIELDSET $fieldset, $languages, $rendered_sections) {
		$attributes = $fieldset->getAttributesArray();
		$attributes['class'] .= " form-element-container form-fieldset-container form-fieldset-name-{$fieldset->name()}";
		$attributes_str = self::attr2str($attributes);

		if ($fieldset->parentIsForm()) {
			return "<div {$attributes_str}>{$rendered_sections}</div>";
		} else {
			return "<tr><td {$attributes_str} colspan='100%'>{$rendered_sections}</td></tr>";
		}
	}

	public static function renderFieldsetLabel(FORM_FIELDSET $fieldset, $languages) {
		$labels = "";
		foreach ($languages as $lang) {
			$labels .= "<span class='form-element-label-{$lang} form-fieldset-label-{$lang}'>{$fieldset->getLabelByLang($lang)}</span>";
		}

		return "<label class='form-element-label form-fieldset-label'>{$labels}</label>";
	}

	public static function renderFieldsetChildren(FORM_FIELDSET $fieldset, $languages) {
		$children = $fieldset->renderAllChildren($languages);
		return "<table class='form-fieldset-children'>{$children}</table>";
	}


	/************
	 **  INFO  **
	 ************/

	public static function renderInfoContainer(FORM_INFO $info, $languages, $rendered_sections) {
		$attributes = $info->getAttributesArray();
		$attributes['class'] .= " form-element-container form-info-container form-element-name-{$info->name()}";
		$attributes_str = self::attr2str($attributes);

		if ($info->parentIsForm()) {
			return "<div {$attributes_str}>{$rendered_sections}</div>";
		} else {
			return "<tr {$attributes_str}>{$rendered_sections}</tr>";
		}
	}

	public static function renderInfoLabel(FORM_INFO $info, $languages) {
		$labels = "";
		foreach ($languages as $lang) {
			$labels .= "<span class='form-element-label-{$lang} form-info-label-{$lang}'>{$info->getLabelByLang($lang)}</span>";
		}

		$html = "<label class='form-element-label form-info-label'>{$labels}</label>";

		if (!$info->parentIsForm()) {
			$html = "<th>{$html}</th>";
		}

		return $html;
	}

	public static function renderInfoText(FORM_INFO $info, $languages) {
		$texts = "";
		foreach ($languages as $lang) {
			$texts .= "<span class='form-info-{$lang}'>{$info->getTextByLang($lang)}</span>";
		}

		$html = "<div class='form-info'>{$texts}</div>";

		if (!$info->parentIsForm()) {
			$html = "<td>{$html}</td>";
		}

		return $html;
	}


	/*************
	 **  FIELD  **
	 *************/

	public static function renderFieldContainer(FORM_FIELD $field, $languages, $rendered_sections) {
		$attributes = $field->getAttributesArray();
		$attributes['class'] .= " form-element-container form-field-container form-field-container-{$field->type()} form-element-name-{$field->name()}";
		if ($field->hasError()) $attributes['class'] .= " form-element-has-error";
		$attributes_str = self::attr2str($attributes);

		if ($field->parentIsForm()) {
			return "<div {$attributes_str}>{$rendered_sections}</div>";
		} else {
			return "<tr {$attributes_str}>{$rendered_sections}</tr>";
		}
	}

	public static function renderFieldLabel(FORM_FIELD $field, $languages) {
		$attributes = array();
		$attributes['for'] = $field->getFieldID(true);
		$attributes['class'] = "form-element-label form-field-label form-field-label-{$field->type()}";
		$attributes_str = self::attr2str($attributes);

		$labels = "";
		foreach ($languages as $lang) {
			$labels .= "<span class='form-element-label-{$lang} form-field-label-{$lang} form-field-label-{$field->type()}-{$lang}'>{$field->getLabelByLang($lang)}</span>";
		}

		$html = "<label {$attributes_str}>{$labels}</label>";

		if (!$field->parentIsForm()) {
			$html = "<th>{$html}</th>";
		}

		return $html;
	}

	public static function renderFieldError(FORM_FIELD $field, $languages) {
		if (!$field->hasError()) return "";

		$content = "";
		foreach ($languages as $lang) {
			$content .= "<span class='form-field-error-{$lang}'>{$field->getErrorByLang($lang)}</span>";
		}

		$html = "<div class='form-field-error'>{$content}</div>";

		if (!$field->parentIsForm()) {
			$html = "<td>{$html}</td>";
		}

		return $html;
	}

	public static function renderField(FORM_FIELD $field, $languages) {
		$attributes = array();
		$attributes['class'] = "form-field form-field-{$field->type()}";
		$attributes_str = self::attr2str($attributes);

		$content = $field->fieldHTML($languages);

		$html = "<div {$attributes_str}>{$content}</div>";

		if (!$field->parentIsForm()) {
			$html = "<td>{$html}</td>";
		}

		return $html;
	}

}
