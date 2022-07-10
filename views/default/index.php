<?php

use yii\bootstrap\Nav;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use greenodeen\language\models\Language;
use greenodeen\language\widgets\MultiLangInput;

/**
 * @var $model Language
 * @var $categories array
 * @var $category string
 */

$this->title = 'Language editor';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="tabs-container">
	<?= Nav::widget([
		'options' => ['class' => 'nav-tabs'],
		'items' => array_map(function($value) use ($category) {
			return [
				'label' => $value,
				'url' => ['index', 'category' => $value],
				'active' => $value == $category
			];
		}, $categories)
	]); ?>
	<div class="tab-content">
		<div class="tab-pane active">
			<div class="panel-body">
				<?php
				$form = ActiveForm::begin([
				    'enableAjaxValidation' => false,
				    'enableClientValidation' => false,
				    'layout' => 'horizontal',
				    'fieldConfig' => [
				        'horizontalCssClasses' => [
					        'offset' => 'offset-sm-3',
					        'label' => 'col-sm-3 col-form-label',
					        'wrapper' => 'col-sm-9',
					        'error' => ''
				        ]
				    ]
				]);
				foreach ($model->attributeLabels() as $attribute => $label) {
	                echo $form->field($model, $attribute)->widget(MultiLangInput::class, [
                        'languages' => $model->languages
	                ]);
	            }
				
				echo Html::submitButton('Save', ['class' => 'btn btn-primary']);
				ActiveForm::end();
				?>
			</div>
		</div>
	</div>
</div>
