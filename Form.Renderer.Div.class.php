<?php
require_once dirname(__FILE__) . "/Form.class.php";

class FORM_DIV_RENDERER extends FORM_RENDERER {

	public function init(FORM $form) {
		$form->setDefaultSectionsAndRenderers(array(
			'form' => array(
				'container' => array("FORM_DIV_RENDERER", "renderFormContainer"),
				'errors' 		=> array("FORM_DIV_RENDERER", "renderFormErrors"),
				'children' 	=> array("FORM_DIV_RENDERER", "renderFormChildren")
			),

			'fieldset' => array(
				'container' => array("FORM_DIV_RENDERER", "renderFieldsetContainer"),
				'label' 		=> array("FORM_DIV_RENDERER", "renderFieldsetLabel"),
				'children' 	=> array("FORM_DIV_RENDERER", "renderFieldsetChildren")
			),

			'info' => array(
				'container' => array("FORM_DIV_RENDERER", "renderInfoContainer"),
				'label' 		=> array("FORM_DIV_RENDERER", "renderInfoLabel"),
				'text'			=> array("FORM_DIV_RENDERER", "renderInfoText")
			),

			'field' => array(
				'container' => array("FORM_DIV_RENDERER", "renderFieldContainer"),
				'label' 		=> array("FORM_DIV_RENDERER", "renderFieldLabel"),
				'error' 		=> array("FORM_DIV_RENDERER", "renderFieldError"),
				'field' 		=> array("FORM_DIV_RENDERER", "renderField")
			),
		));

		// field types below only need a field section (no need for label and error sections)
		$form->setChildTypeSections(array('hidden', 'button', 'submit', 'reset'), array('field'));

	}


	/************
	 **  FORM  **
	 ************/

	public static function renderFormContainer(FORM $form, $languages, $rendered_sections) {
		$attributes = $form->getAttributesString();
		return "<form {$attributes}>{$rendered_sections}</form>";
	}

	public static function renderFormErrors(FORM $form, $languages) {
		if (!$form->hasErrors()) return "";

		$str  = "<div class='form-error-list'><ul>";

		foreach ($form->getAllErrors() as $error) {
			$str .= "<li class='error'>";

			foreach ($languages as $lang) {
				$str .= "<span class='error-{$lang}'>{$error[$lang]}</span>";
			}

			$str .= "</li>";
		}

		$str .= "</ul></div>";

		return $str;
	}

	public static function renderFormChildren(FORM $form, $languages) {
		$children = $form->renderAllChildren($languages);
		return "<div class='form-children'>{$children}</div>";
	}


	/****************
	 **  FIELDSET  **
	 ****************/

	public static function renderFieldsetContainer(FORM_FIELDSET $fieldset, $languages, $rendered_sections) {
		$name = str_replace(array('[', ']'), array('_', ''), $fieldset->name());

		$attributes = $fieldset->getAttributesArray();
		$attributes['class'] .= " form-element-container form-fieldset-container form-fieldset-name-{$name}";
		$attributes_str = self::attr2str($attributes);

		return "<div {$attributes_str}>{$rendered_sections}</div>";
	}

	public static function renderFieldsetLabel(FORM_FIELDSET $fieldset, $languages) {
		$labels = "";
		foreach ($languages as $lang) {
			$lbl = $fieldset->getLabelByLang($lang);
			if (!is_null($lbl)) {
				$labels .= "<span class='form-element-label-{$lang} form-fieldset-label-{$lang}'>{$lbl}</span>";
			}
		}

		return empty($labels) ? "" : "<label class='form-element-label form-fieldset-label'>{$labels}</label>";
	}

	public static function renderFieldsetChildren(FORM_FIELDSET $fieldset, $languages) {
		$children = $fieldset->renderAllChildren($languages);
		return "<div class='form-fieldset-children'>{$children}</div>";
	}


	/************
	 **  INFO  **
	 ************/

	public static function renderInfoContainer(FORM_INFO $info, $languages, $rendered_sections) {
		$name = str_replace(array('[', ']'), array('_', ''), $info->name());

		$attributes = $info->getAttributesArray();
		$attributes['class'] .= " form-element-container form-info-container form-element-name-{$name}";
		$attributes_str = self::attr2str($attributes);

		return "<div {$attributes_str}>{$rendered_sections}</div>";
	}

	public static function renderInfoLabel(FORM_INFO $info, $languages) {
		$labels = "";
		foreach ($languages as $lang) {
			$lbl = $info->getLabelByLang($lang);
			if (!is_null($lbl)) {
				$labels .= "<span class='form-element-label-{$lang} form-info-label-{$lang}'>{$lbl}</span>";
			}
		}

		return empty($labels) ? "" : "<label class='form-element-label form-info-label'>{$labels}</label>";
	}

	public static function renderInfoText(FORM_INFO $info, $languages) {
		$texts = "";
		foreach ($languages as $lang) {
			$texts .= "<span class='form-info-{$lang}'>{$info->getTextByLang($lang)}</span>";
		}

		return "<div class='form-info'>{$texts}</div>";
	}


	/*************
	 **  FIELD  **
	 *************/

	public static function renderFieldContainer(FORM_FIELD $field, $languages, $rendered_sections) {
		$name = str_replace(array('[', ']'), array('_', ''), $field->name());

		$attributes = $field->getAttributesArray();
		$attributes['class'] .= " form-element-container form-field-container form-field-container-{$field->type()} form-element-name-{$name}";
		if ($field->hasError()) $attributes['class'] .= " form-element-has-error";
		$attributes_str = self::attr2str($attributes);

		return  "<div {$attributes_str}>{$rendered_sections}</div>";
	}

	public static function renderFieldLabel(FORM_FIELD $field, $languages) {
		$attributes = array();
		$attributes['for'] = $field->getFieldID(true);
		$attributes['class'] = "form-element-label form-field-label form-field-label-{$field->type()}";
		$attributes_str = self::attr2str($attributes);

		$labels = "";
		foreach ($languages as $lang) {
			$lbl = $field->getLabelByLang($lang);
			if (!is_null($lbl)) {
				$labels .= "<span class='form-element-label-{$lang} form-field-label-{$lang} form-field-label-{$field->type()}-{$lang}'>{$lbl}</span>";
			}
		}

		return empty($labels) ? "" : "<label {$attributes_str}>{$labels}</label>";
	}

	public static function renderFieldError(FORM_FIELD $field, $languages) {
		if (!$field->hasError()) return "";

		$content = "";
		foreach ($languages as $lang) {
			$content .= "<span class='form-field-error-{$lang}'>{$field->getErrorByLang($lang)}</span>";
		}

		return "<div class='form-field-error'>{$content}</div>";
	}

	public static function renderField(FORM_FIELD $field, $languages) {
		$attributes = array();
		$attributes['class'] = "form-field form-field-{$field->type()}";
		$attributes_str = self::attr2str($attributes);

		$content = $field->fieldHTML($languages);

		return "<div {$attributes_str}>{$content}</div>";
	}

}
