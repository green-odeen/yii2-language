<?php

namespace greenodeen\language\controllers;

use Yii;
use yii\i18n\I18N;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use greenodeen\language\models\Language;
use greenodeen\language\services\LanguageService;

/**
 * Class DefaultController
 *
 * @package greenodeen\language\controllers
 */
class DefaultController extends Controller {
	
	/** @var LanguageService */
	private $service;
	
	/** @inheritdoc */
	public function init() {
		parent::init();
		$this->service = new LanguageService($this->module->i18n);
	}
	
	/**
	 * @param string|null $category
	 * @return string|\yii\web\Response
	 */
	public function actionIndex($category = null) {
		$categories = $this->service->getCategories();
		if($category === null) {
			$category = reset($categories);
		}
		if(!in_array($category, $categories) || empty($messages = $this->service->getMessages($category))) {
			throw new NotFoundHttpException();
		}
		$model = new Language($messages);

		if ($model->load(Yii::$app->request->post())) {
			if($this->service->saveFromModel($category, $model)) {
				Yii::$app->session->setFlash('success', 'Saved');
				return $this->refresh();
			} else {
				Yii::$app->session->setFlash('error', 'Error');
			}
		}

		return $this->render('index', [
			'categories' => $this->service->getCategories(),
			'category' => $category,
			'model' => $model
		]);
	}
}
