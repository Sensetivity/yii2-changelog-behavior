<?php

namespace Sensetivity\ChangeLog\actions;

use Sensetivity\ChangeLog\ChangeLogBehavior;
use Sensetivity\ChangeLog\LogItem;
use Yii;
use yii\base\Action;
use yii\base\InvalidArgumentException;
use yii\db\ActiveRecord;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;

class RevertAction extends Action
{
    /**
     * @var yii\db\ActiveRecord|string
     */
    public $modelClass;

    /**
     * @var string
     */
    public $primaryKey = 'id';

    /**
     * @var string|array
     */
    public $successRedirect;

    /**
     * @var string|array
     */
    public $failRedirect;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        if (empty($this->modelClass)) {
            throw new InvalidArgumentException('Argument modelClass is missing');
        }

        if (empty($this->primaryKey)) {
            throw new InvalidArgumentException('Argument primaryKey is missing');
        }
    }

    /**
     * @return \yii\web\Response
     */
    public function run()
    {
        $pkLogItem = Yii::$app->request->getQueryParam($this->primaryKey);
        $itemModel = $this->findItemModel($pkLogItem);
        $itemLastModel = $this->findLastItemModel($itemModel->relatedObjectType, $itemModel->relatedObjectId);
        $model = $this->findModel($itemModel->relatedObjectId);

        $attributesToRevert = $this->processAttributes($itemModel);
        $model->setAttributes($attributesToRevert);

        $behavior = $this->findBehavior($model);
        $behavior->revert($itemLastModel->new_data, $attributesToRevert);
        $behavior->detach();

        $result = $model->update();

        return $this->controller->redirect($this->resolveRedirectUrl($result));
    }

    /**
     * @param yii\db\ActiveRecord $model
     * @return ChangeLogBehavior
     */
    protected function findBehavior($model)
    {
        $behaviors = $model->getBehaviors();
        if (key_exists('changelog', $behaviors)) {
            return $behaviors['changelog'];
        }

        foreach ($behaviors as $behavior) {
            if ($behavior instanceof ChangeLogBehavior) {
                return $behavior;
            }
        }

        throw new InvalidArgumentException('Changelog behavior not attached to model: ' . $this->modelClass);
    }

    /**
     * @param LogItem $itemModel
     * @return mixed
     */
    protected function processAttributes(LogItem $itemModel)
    {
        $data = $itemModel->old_data;
        $attributes = Json::decode($data);

        return $attributes;
    }

    /**
     * @param $pk
     * @return null|LogItem
     * @throws NotFoundHttpException
     */
    protected function findItemModel($pk)
    {
        if (($model = LogItem::findOne([$this->primaryKey => $pk])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * @param string $relatedObjectType
     * @param string|integer $relatedObjectId
     * @return null|LogItem|ActiveRecord
     * @throws NotFoundHttpException
     */
    protected function findLastItemModel($relatedObjectType, $relatedObjectId)
    {
        $model = LogItem::find()->andWhere([
            'relatedObjectType' => $relatedObjectType,
            'relatedObjectId' => $relatedObjectId,
        ])
            ->orderBy([$this->primaryKey => SORT_DESC])
            ->one();

        if ($model) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * @param boolean $result
     * @return string
     */
    protected function resolveRedirectUrl($result)
    {
        $referrer = Yii::$app->request->getReferrer();

        if ($result) {
            return $this->successRedirect ? Url::to($this->successRedirect) : Url::to($referrer);
        } else {
            return $this->failRedirect ? Url::to($this->failRedirect) : Url::to($referrer);
        }
    }

    /**
     * @param integer $pk
     * @return ActiveRecord|null
     * @throws NotFoundHttpException
     */
    protected function findModel($pk)
    {
        $modelClass = $this->modelClass;
        if (($model = $modelClass::findOne([$this->primaryKey => $pk])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}