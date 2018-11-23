<?php

namespace Sensetivity\ChangeLog;


use Sensetivity\ChangeLog\helpers\CompositeRelationHelper;
use yii\base\Widget;
use Qazd\TextDiff;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;
use yii\grid\GridView;
use yii\helpers\Inflector;
use yii\helpers\Json;

class ChangeLogListWidget extends Widget
{
    /**
     * @var ActiveRecord
     */
    public $model;
    /**
     * @var ActiveRecord[]
     */
    public $additionalModels = [];

    /**
     * @return string
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function run()
    {
        $objectType = CompositeRelationHelper::resolveObjectType($this->model);
        $dataProvider = $this->getEventProvider($objectType, $this->model->primaryKey);
        $this->registerCss();

        return $this->renderProvider($dataProvider);
    }

    protected function registerCss()
    {
        $this->view->registerAssetBundle(ChangeLogAsset::class);
    }

    /**
     * @param ActiveDataProvider $dataProvider
     * @return string
     */
    protected function renderProvider($dataProvider)
    {
        return GridView::widget([
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

    /**
     * @param $objectType
     * @param $objectId
     * @return ActiveDataProvider
     * @throws \ReflectionException
     */
    public function getEventProvider($objectType, $objectId)
    {
        $query = LogItem::find()->andWhere([
            'relatedObjectType' => $objectType,
            'relatedObjectId' => $objectId,
        ]);
        if (!empty($this->additionalModels)) {
            foreach ($this->additionalModels as $additionalModel) {
                if ($additionalModel) {
                    if (is_array($additionalModel)) {
                        foreach ($additionalModel as $addModel) {
                            $query->orWhere([
                                'relatedObjectType' => CompositeRelationHelper::resolveObjectType($addModel),
                                'relatedObjectId' => $addModel->primaryKey,
                            ]);
                        }
                    } else {
                        $query->orWhere([
                            'relatedObjectType' => CompositeRelationHelper::resolveObjectType($additionalModel),
                            'relatedObjectId' => $additionalModel->primaryKey,
                        ]);
                    }
                }
            }
        }

        // add conditions that should always apply here
        return new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['id' => SORT_DESC]],
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);
    }
}