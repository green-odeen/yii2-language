<?php

namespace greenodeen\language\services;

use Yii;
use yii\i18n\I18N;
use yii\i18n\MessageSource;
use yii\i18n\PhpMessageSource;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\helpers\FileHelper;
use yii\helpers\ArrayHelper;
use greenodeen\language\models\Language;
use greenodeen\language\exceptions\FileNotExistsException;
use greenodeen\language\exceptions\FileNotWritableException;

class LanguageService extends Component {
	
	/** @var array */
	private $_messages = [];
	
	/** @var I18N */
	private $_i18n;

	/**
	 * LocalizationService constructor.
	 *
	 * @param I18N  $i18N
	 * @param array $config
	 */
	public function __construct(I18N $i18N, $config = []) {
		$this->_i18n = $i18N;
		parent::__construct($config);
	}

	/**
	 * Полный список категорий
	 * @return array
	 */
	public function getCategories() {
		$categories = [];
		foreach($this->_i18n->translations as $translation) {
			$source = $translation instanceof MessageSource ? $translation : Yii::createObject($translation);
			if(!$source instanceof PhpMessageSource) {
				continue;
			}
			if(!is_dir($dir = Yii::getAlias($source->basePath))) {
				continue;
			}
			$files = FileHelper::findFiles($dir, ['only' => ['*.php']]);
			if(!empty($files)) {
				foreach($files as $file) {
					$categories[] = basename(Yii::getAlias($file), '.php');
				}
			}
		}
		return array_unique($categories);
	}

	/**
	 * Список всех языковых переменных категории
	 * @param string $category
	 *
	 * @return array
	 * @throws InvalidConfigException
	 */
	public function getMessages($category) {
		if(!isset($this->_messages[$category])) {
			$this->_messages[$category] = [];
			$source = $this->_i18n->getMessageSource($category);
			if(!$source instanceof PhpMessageSource) {
				throw new InvalidConfigException("Unsupported message source '{$category}'.");
			}

			//Получаем название языкового файла
			if (isset($this->fileMap[$category])) {
				$messageFile = $this->fileMap[$category];
			} else {
				$messageFile = str_replace('\\', '/', $category) . '.php';
			}

			//Пробегаемся по всем файлам источника в поиске файлов с совпадающим именем
			$dir = Yii::getAlias($source->basePath);
			if(!empty($files = FileHelper::findFiles($dir, ['only' => ['*.php']]))) {
				foreach($files as $file) {
					$preg = '/([a-z0-9_-]+)(?:\\\|\/)(' . quotemeta($messageFile) . ')$/i';
					if(preg_match($preg, $file, $matches)) {
						$language = $matches[1];
						$this->_messages[$category][$language] = require($file);
					}
				}
			}
		}
		return $this->_messages[$category];
	}

	/**
	 * @param string $category
	 * @param Language $model
	 * @return bool
	 */
	public function saveFromModel($category, $model) {
		if(!$model->validate()) {
			return false;
		}
		return $this->saveMessages($category, $model->getMessages());
	}

	/**
	 * Сохраняем языковые переменные определённой категории
	 * @param string $category
	 * @param array $messages
	 *
	 * @return bool
	 * @throws FileNotExistsException
	 * @throws FileNotWritableException
	 * @throws InvalidConfigException
	 */
	public function saveMessages($category, $messages) {
		$source = $this->_i18n->getMessageSource($category);
		if(!$source instanceof PhpMessageSource) {
			throw new InvalidConfigException("Unsupported message source '{$category}'.");
		}
		foreach($messages as $language => $langMessages) {
			$messageFile = Yii::getAlias($source->basePath) . DIRECTORY_SEPARATOR . $language . DIRECTORY_SEPARATOR;
			if (isset($source->fileMap[$category])) {
				$messageFile .= $source->fileMap[$category];
			} else {
				$messageFile .= $category . '.php';
			}
			FileHelper::normalizePath($messageFile);

			if(!file_exists($messageFile)) {
				throw new FileNotExistsException($messageFile);
			}
			if(!is_writable($messageFile)) {
				throw new FileNotWritableException($messageFile);
			}
			//Запись в файл
			$fp = fopen($messageFile, "w");

			fwrite($fp, $this->generateText($langMessages));
			fclose($fp);
		}
		return true;
	}

	/**
	 * Генерация содержимого файла
	 *
	 * @param array $messages
	 *
	 * @return string
	 */
	protected function generateText($messages) {
		$output = preg_replace('#,$\n#s', '', $this->generateLangFileRecursive($messages));// Регуляркой убираем лишнее
		return "<?php\nreturn {$output};\n";
	}

	/**
	 * Рекурсивная генерация php-массива с языковыми переменными
	 *
	 * @param array $messages Список языковых переменных
	 * @param int   $depth Глубина, чтоб определить длину отступа
	 *
	 * @return string
	 */
	protected function generateLangFileRecursive(array $messages, $depth = 1) {
		$output = "array(\n";
		foreach ($messages as $key => $value) {
			if (is_array($value)) {
				//В случае с массивом (в языковом файле, конечно, вряд ли), запускаем рекурсию
				$output .= str_repeat("\t", $depth) . "'" . $this->escape($key) . "' => ";
				$output .= $this->generateLangFileRecursive($value, $depth + 1);
			} else {
				$output .= str_repeat("\t", $depth) . "'" . $this->escape($key) . "' => '" . $this->escape($value) . "',\n";
			}
		}
		$output .= str_repeat("\t", $depth - 1) . "),\n";
		return $output;
	}

	/**
	 * @param string $value
	 *
	 * @return string
	 */
	private function escape($value) {
		return str_replace("'", "\'", $value);
	}
}
