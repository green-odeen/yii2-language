<?php

namespace greenodeen\language\models;

use Yii;
use yii\base\Model;
use yii\base\InvalidArgumentException;

class Language extends Model {
	
	/**
	 * @var array Массив языков, например `['en', 'ru']`
	 */
	public $languages;

	/**
	 * @var array Массив, содержащий в себе все варианты языковых переменных
	 */
	private $_attributes = [];

	/**
	 * @var array Названия
	 */
	private $_labels = [];

	/**
	 * Language model constructor.
	 *
	 * @param array $messages
	 * @param array $config
	 */
	public function __construct($messages, $config = []) {
		$this->languages = array_keys($messages);
		$labels = array_keys(call_user_func_array('array_merge', $messages));

		//В качестве label будет ключ из языкового файла (значение в sourceLanguage)
		//Ключи атрибутов - md5 лейбла + код языка
		foreach($labels as $label) {
			$key = $this->generateKey($label);
			$this->_labels[$key] = $label;
			foreach($this->languages as $language) {
				$this->_attributes[$key . '_' . $language] = isset($messages[$language][$label]) ? $messages[$language][$label] : '';
			}
		}
		parent::__construct($config);
	}

	/** @inheritDoc */
	public function rules() {
		return [
			[array_keys($this->_attributes), 'string']
		];
	}

	/** @inheritDoc */
	public function attributeLabels() {
		return $this->_labels;
	}

	/** @inheritDoc */
	public function __get($name) {
		if (isset($this->_attributes[$name]) || array_key_exists($name, $this->_attributes)) {
			return $this->_attributes[$name];
		} else {
			return parent::__get($name);
		}
	}

	/** @inheritDoc */
	public function __set($name, $value) {
		if ($this->hasAttribute($name)) {
			$this->_attributes[$name] = $value;
		} else {
			parent::__set($name, $value);
		}
	}

	/** @inheritDoc */
	public function __isset($name) {
		try {
			return $this->__get($name) !== null;
		} catch (\Exception $e) {
			return false;
		}
	}

	/** @inheritDoc */
	public function __unset($name) {
		if ($this->hasAttribute($name)) {
			unset($this->_attributes[$name]);
		} else {
			parent::__unset($name);
		}
	}

	/** @inheritDoc */
	public function attributes() {
		return array_keys($this->_attributes);
	}

	/** @return bool */
	public function hasAttribute($name) {
		return isset($this->_attributes[$name]);
	}

	/**
	 * @param string $name
	 *
	 * @return mixed|null
	 */
	public function getAttribute($name) {
		return isset($this->_attributes[$name]) ? $this->_attributes[$name] : null;
	}

	/**
	 * @param string $name
	 * @param mixed $value
	 */
	public function setAttribute($name, $value) {
		if ($this->hasAttribute($name)) {
			$this->_attributes[$name] = $value;
		} else {
			throw new InvalidArgumentException(get_class($this) . ' has no attribute named "' . $name . '".');
		}
	}

	/** @inheritDoc */
	public function getAttributes($names = null, $except = []) {
		if($this->_attributes && is_array($this->_attributes)) {
			return $this->_attributes;
		} else {
			return parent::getAttributes($names, $except);
		}
	}

	/** @inheritDoc */
	public function setAttributes($values, $safeOnly = true) {
		if (is_array($values)) {
			$attributes = array_flip($this->attributes());
			foreach ($values as $name => $value) {
				if (isset($attributes[$name])) {
					$this->$name = $value;
				} elseif ($safeOnly) {
					$this->onUnsafeAttribute($name, $value);
				}
			}
		}
	}

	/**
	 * @return array
	 */
	public function getMessages() {
		$messages = [];
		$labels = $this->attributeLabels();
		natsort($labels);
	
		foreach($this->languages as $language) {
			$values = [];
			foreach ($labels as $key => $label) {
				$values[$label] = $this->getAttribute($key . '_' . $language);
			}
			$messages[$language] = $values;
		}
		return $messages;
	}

	/**
	 * @param string $k
	 *
	 * @return string
	 */
	protected function generateKey($k) {
		return md5($k);
	}
}
