<?php

namespace Sensetivity\ChangeLog\actions;

use Yii;
use yii\base\Action;
use yii\base\InvalidArgumentException;
use yii\db\ActiveRecord;
use yii\web\NotFoundHttpException;

class ChangeLogAction extends Action
{
    /**
     * @var string the view file to be rendered. If not set, it will take the value of [[id]].
     * That means, if you name the action as "error" in "SiteController", then the view name
     * would be "error", and the corresponding view file would be "views/site/error.php".
     */
    public $view = '@vendor/sensetivity/yii2-changelog-behavior/src/views/changelog';

    /**
     * @var string|false|null the name of the layout to be applied to this error action view.
     * If not set, the layout configured in the controller will be used.
     * @see \yii\base\Controller::$layout
     * @since 2.0.14
     */
    public $layout;

    /**
     * @var yii\db\ActiveRecord
     */
    public $modelClass;

    /**
     * @var string
     */
    public $primaryKey = 'id';

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

    public function run()
    {
        if ($this->layout !== null) {
            $this->controller->layout = $this->layout;
        }

        if (Yii::$app->getRequest()->getIsAjax()) {
            return $this->renderAjaxResponse();
        }

        return $this->renderHtmlResponse();
    }

    /**
     * Renders a view that represents the exception.
     * @return string
     * @since 2.0.11
     */
    protected function renderHtmlResponse()
    {
        return $this->controller->render($this->view ?: $this->id, $this->getViewRenderParams());
    }

    protected function renderAjaxResponse()
    {
        return $this->controller->renderAjax($this->view ?: $this->id, $this->getViewRenderParams());
    }

    /**
     * Builds array of parameters that will be passed to the view.
     * @return array
     * @since 2.0.11
     */
    protected function getViewRenderParams()
    {
        $pk = Yii::$app->request->getQueryParam($this->primaryKey);
        $model = $this->findModel($pk);
        $referrer = Yii::$app->request->getReferrer();

        return [
            'model' => $model,
            'referrer' => $referrer,
        ];
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