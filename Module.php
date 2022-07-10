<?php

namespace greenodeen\language;

use yii\i18n\I18N;
use yii\di\Instance;
use yii\base\BootstrapInterface;
use yii\base\Module as BaseModule;

class Module extends BaseModule implements BootstrapInterface {

	/** @var I18N */
	public $i18n = 'i18n';

	/** @inheritdoc */
	public $controllerNamespace = 'greenodeen\language\controllers';

	/** @inheritdoc */
	public function bootstrap($app) {
		$app->getUrlManager()->addRules([
			[
				'class' => 'yii\web\UrlRule',
				'route' => $this->id . '/default/index',
				'pattern' => $this->id . '/<category:[\w\-?]+>',
				'suffix' => false
			]
		], false);
	}

	/** @inheritdoc */
	public function init() {
		parent::init();
		$this->i18n = Instance::ensure($this->i18n, I18N::class);
	}
}