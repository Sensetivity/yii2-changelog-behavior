<?php

use yii\helpers\Url;
use yii\bootstrap\Html;
use Sensetivity\ChangeLog\ChangeLogListWidget;

/* @var $this yii\web\View */
/* @var $model \yii\db\ActiveRecord */
/* @var $referrer string */

?>

<?php
//$title = \yii\helpers\Inflector::pluralize($model->formName());
//$singular = strtolower(\yii\helpers\Inflector::singularize($model->formName()));
//$url = ["{$singular}/list", 'application_id' => $model->application->id];
//
//$this->title = $title;
//$this->params['breadcrumbs'][] = ['label' => 'Applications', 'url' => ['application/index']];
//$this->params['breadcrumbs'][] = ['label' => $model->application->name, 'url' => ['application/view', 'id' => $model->application->id]];
//$this->params['breadcrumbs'][] = ['label' => $title, 'url' => $url];
//$this->params['breadcrumbs'][] = 'Changelog';

?>
<h1>Changelog </h1>

<p>
    <?= Html::a('<- Back', Url::to($referrer), ['class' => 'btn btn-primary']) ?>
</p>

<h2><?= $model->formName() . ': ' . $model->id ?></h2>

<?php echo ChangeLogListWidget::widget([
    'model' => $model,
]); ?>

