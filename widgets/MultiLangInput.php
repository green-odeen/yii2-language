<?php

namespace greenodeen\language\widgets;

use Yii;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\bootstrap\InputWidget;
use yii\base\InvalidConfigException;

/**
 * Class MultiLangInput
 * @package greenodeen\language\widgets
 */
class MultiLangInput extends InputWidget {

	const TYPE_TEXT             = 'text';
	const TYPE_TEXTAREA         = 'textarea';
	const TYPE_RADIO            = 'radio';
	const TYPE_CHECKBOX         = 'checkbox';
	const TYPE_RADIO_LIST       = 'radioList';
	const TYPE_CHECKBOX_LIST    = 'checkboxList';
	const TYPE_DROPDOWN         = 'dropDownList';
	const TYPE_WIDGET           = 'widget';

	/**
	 * @var array Список языков, например `['en', 'ru']`
	 */
	public $languages;

	/**
	 * @var string Тип поля
	 * Либо одна из констант, либо стандартные типы для html (например, number)
	 */
	public $inputType = self::TYPE_TEXT;
	
	/**
	 * @var string Класс виджета. Используется только в случае, если `inputType` задан как [[TYPE_WIDGET]]
	 */
	public $widgetClass;
	
	/**
	 * @var array Опции, которые быдут переданы в класс виджета. Используется только в случае, если `inputType` задан как [[TYPE_WIDGET]]
	 */
	public $widgetOptions = [];

	/**
	 * @var array Список значений. Используется для `inputType` [[TYPE_RADIO_LIST]], [[TYPE_CHECKBOX_LIST]], [[TYPE_DROPDOWN]]
	 */
	public $items = [];
	
	/**
	 * @var string Шаблон отображения каждой строки с input'ом
	 */
	public $inputTemplate = "<div class='input-group'><span title='{lang_name}' class='input-group-addon'>{lang_name}</span>{input}</div>";
	
	/** @inheritdoc */
	public function init() {
		parent::init();
		if($this->languages === null) {
			$this->languages = [Yii::$app->language];
		}

		if($this->inputType === static::TYPE_WIDGET && $this->widgetClass === null) {
			throw new InvalidConfigException('widgetClass is required!');
		}
	}
	
	/** @inheritdoc */
	public function run() {
		$fields = [];
		foreach($this->languages as $lang) {
			$options = $this->options;
			$options['id'] = ArrayHelper::getValue($options, 'id', $this->getId()) . '-' . $lang;
			if($this->hasModel()) {
				$attribute = $this->attribute . "_{$lang}";
				switch($this->inputType) {
					case static::TYPE_TEXTAREA:
						$input = Html::activeTextarea($this->model, $attribute, $options);
						break;
					case static::TYPE_RADIO:
						$input = Html::activeRadio($this->model, $attribute, $options);
						break;
					case static::TYPE_CHECKBOX:
						$input = Html::activeCheckbox($this->model, $attribute, $options);
						break;
					case static::TYPE_RADIO_LIST:
						$input = Html::activeRadioList($this->model, $attribute, $this->items, $options);
						break;
					case static::TYPE_CHECKBOX_LIST:
						$input = Html::activeCheckboxList($this->model, $attribute, $this->items, $options);
						break;
					case static::TYPE_DROPDOWN:
						$input = Html::activeDropDownList($this->model, $attribute, $this->items, $options);
						break;
					case static::TYPE_WIDGET:
						$widgetClass = $this->widgetClass;
						$input = $widgetClass::widget([
							'model' => $this->model,
							'attribute' => $attribute,
							'options' => $options
						]);
						break;
					default:
						$input = Html::activeInput($this->inputType, $this->model, $attribute, $options);
				}
			} else {
				$name = $this->name . "_{$lang}";
				$value = ArrayHelper::getValue((array)$this->value, $lang, $this->value);
				
				switch($this->inputType) {
					case static::TYPE_TEXTAREA:
						$input = Html::textarea($name, $value, $options);
						break;
					case static::TYPE_RADIO:
						$input = Html::radio($name, $value, $options);
						break;
					case static::TYPE_CHECKBOX:
						$input = Html::checkbox($name, $value, $options);
						break;
					case static::TYPE_RADIO_LIST:
						$input = Html::radioList($name, $value, $this->items, $options);
						break;
					case static::TYPE_CHECKBOX_LIST:
						$input = Html::checkboxList($name, $value, $this->items, $options);
						break;
					case static::TYPE_DROPDOWN:
						$input = Html::dropDownList($name, $value, $this->items, $options);
						break;
					case static::TYPE_WIDGET:
						$widgetClass = $this->widgetClass;
						$input = $widgetClass::widget([
							'name' => $name,
							'value' => $value,
							'options' => $options
						]);
						break;
					default:
						$input = Html::input($this->inputType, $name, $value, $options);
				}
			}
			
			$fields[] = strtr($this->inputTemplate, [
				'{input}' => $input,
				'{lang_name}' => $lang
			]);
		}
		return implode("\n", $fields);
	}
}
