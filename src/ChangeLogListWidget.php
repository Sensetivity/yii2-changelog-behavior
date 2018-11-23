<?php

namespace Sensetivity\ChangeLog;


use yii\base\Widget;
use Sensetivity\ChangeLog\LogItem;
use Qazd\TextDiff;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Inflector;
use yii\helpers\Json;

class ChangeLogListWidget extends Widget
{
    /**
     * @param $dataProvider
     * @throws \Exception
     */
    protected function renderProvider($dataProvider)
    {
        echo GridView::widget([
            'dataProvider' => $dataProvider,
            'options' => [
                'class' => 'table-responsive',
            ],
            'columns' => [
                [
                    'attribute' => 'createdAt',
                    'format' => 'datetime',
                    'headerOptions' => ['style' => 'width:100px']
                ],
                'type',
                [
                    'format' => 'html',
                    'value' => function (LogItem $model) {
                        return $this->drawData($model);
                    },
                    'headerOptions' => ['style' => 'width:50%']
                ],
//                [
//                    'attribute' => 'new_data',
//                    'format' => 'html',
//                    'value' => function (LogItem $model) {
//                        return $this->drawData($model, 'new_data');
//                    },
//                    'headerOptions' => ['style' => 'width:25%']
//                ],
//                [
//                    'format' => 'html',
//                    'value' => function (LogItem $model) {
//                        if ($data = json_decode($model->data, true)) {
//                            $out = '';
//                            if ($this->model && $this->model->tableName() != $model->relatedObjectType) {
//                                $out = Inflector::humanize($model->relatedObjectType) . ' #' . $model->relatedObjectId
//                                    . ": " . '<br>';
//                            }
//                            foreach ($data as $fieldName => $val) {
//                                if (is_string($val)) {
//                                    $out .= $val . '<br>';
//                                } else {
//                                    if (substr($fieldName, -2) === "At") {
//                                        $val[0] = \Yii::$app->formatter->asDatetime($val[0]);
//                                        $val[1] = \Yii::$app->formatter->asDatetime($val[1]);
//
//                                        $out .= ($fieldName . ': <span style="color:#ccc">'
//                                                . print_r($val[0], true) .
//                                                ' => </span>' . print_r($val[1], true)) . '<br>';
//                                    } else {
//                                        $out .= ($fieldName . ': <span style="color:#ccc">'
//                                                . Html::encode(print_r($val[0], true)) .
//                                                ' => </span>' . Html::encode(print_r($val[1], true))) . '<br>';
//                                    }
//
//                                }
//                            }
//
//                            return $out;
//                        } else {
//                            return Html::encode($model->data);
//                        }
//                    },
//                    'headerOptions' => ['style' => 'width:55%']
//                ],
                'userId',
                'hostname',
            ],
        ]);
    }

    protected function drawData(LogItem $model)
    {
        $oldData = Json::decode($model->old_data);
        $newData = Json::decode($model->new_data);

        if (!$oldData || !$newData) {
            return null;
        }

        $out = '';
        if ($model && $model->tableName() != $model->relatedObjectType) {
            $out .= Inflector::humanize($model->relatedObjectType) . ' #' . $model->relatedObjectId . ': </br>';
        }

        foreach ($oldData as $columnName => $columnValue) {
            $label = $model->getAttributeLabel($columnName);
            $leftText = $label . ': ' . $oldData[$columnName];
            $rightText = $label . ': ' . $newData[$columnName];
            $out .= TextDiff::render($leftText, $rightText);
        }

        return $out;
    }
}