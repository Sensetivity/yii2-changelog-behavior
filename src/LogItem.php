<?php

namespace Sensetivity\ChangeLog;

use Sensetivity\ChangeLog\helpers\CompositeRelationHelper;
use yii\behaviors\TimestampBehavior;
use yii\console\Application;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "log_event".
 *
 * @property integer $id
 * @property string $relatedObjectType
 * @property integer $relatedObjectId
 * @property string $old_data
 * @property string $new_data
 * @property string $createdAt
 * @property string $type
 * @property integer $userId
 * @property \yii\db\ActiveQuery $user
 * @property string $hostname
 *
 * example of log event creation:
 *          $model =    $this->findModel($id);
 *          $event = new Event;
 *          $event->type  = 'user_view';
 *          $event->relatedObject = $model;
 *          $event->save(false);
 */
class LogItem extends ActiveRecord
{
    /**
     * @var ActiveRecord
     */
    public $relatedObject;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%changelogs}}';
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'createdAt',
                'updatedAtAttribute' => false,
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['relatedObjectId', 'userId'], 'integer'],
            //[['data'], 'string'],
            [['createdAt', 'relatedObject', 'old_data', 'new_data'], 'safe'],
            [['relatedObjectType', 'type', 'hostname'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'relatedObjectType' => 'Related Object Type',
            'relatedObjectId' => 'Related Object ID',
            'old_data' => 'Old Data',
            'new_data' => 'New Data',
            'createdAt' => 'Created At',
            'type' => 'Type',
            'userId' => 'User ID',
            'hostname' => 'Hostname',
        ];
    }

    /**
     * @param bool $insert
     *
     * @return bool
     * @throws \ReflectionException
     */
    public function beforeSave($insert)
    {
        if (empty($this->userId) && !(\Yii::$app instanceof Application) && !\Yii::$app->user->isGuest) {
            $this->userId = \Yii::$app->user->id;
        }

        if (empty($this->hostname) && \Yii::$app->request->hasMethod('getUserIP')) {
            $this->hostname = \Yii::$app->request->getUserIP();
        }

        if (!empty($this->old_data) && is_array($this->old_data)) {
            $this->old_data = json_encode($this->old_data);
        }

        if (!empty($this->new_data) && is_array($this->new_data)) {
            $this->new_data = json_encode($this->new_data);
        }

        if ($this->relatedObject) {
            $this->relatedObjectType = CompositeRelationHelper::resolveObjectType($this->relatedObject);
            $this->relatedObjectId = $this->relatedObject->primaryKey;
        }

        return parent::beforeSave($insert);
    }
}
