<?php

namespace Sensetivity\ChangeLog;


use Sensetivity\ChangeLog\LogItem;
use yii\base\Behavior;
use yii\base\Event;
use yii\db\ActiveRecord;
use yii\helpers\StringHelper;

class ChangeLogBehavior extends Behavior
{
    /**
     * @var array
     */
    public $excludedAttributes = [];

    /**
     * @return array
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_UPDATE => 'addLog',
            ActiveRecord::EVENT_AFTER_INSERT => 'addLog',
            ActiveRecord::EVENT_BEFORE_DELETE => 'addDeleteLog',
        ];
    }

    /**
     * @param \yii\base\Event $event
     */
    public function addLog(Event $event)
    {
        /**
         * @var ActiveRecord $owner
         */
        $owner = $this->owner;
        $changedAttributes = $event->changedAttributes;

        $oldValues = [];
        $newValues = [];

        foreach ($changedAttributes as $attrName => $attrVal) {
            $newAttrVal = $owner->getAttribute($attrName);

            //avoid float compare
            $newAttrVal = is_float($newAttrVal) ? StringHelper::floatToString($newAttrVal) : $newAttrVal;
            $attrVal = is_float($attrVal) ? StringHelper::floatToString($attrVal) : $attrVal;

            if ($newAttrVal != $attrVal) {
                $oldValues[$attrName] = $attrVal;
                $newValues[$attrName] = $newAttrVal;
            }
        }

        list($oldValues, $newValues) = $this->applyExclude($oldValues, $newValues);

        if ($oldValues && $newValues) {
            $logEvent = new LogItem();
            $logEvent->relatedObject = $owner;
            $logEvent->old_data = $oldValues;
            $logEvent->new_data = $newValues;
            $logEvent->type = $this->selectType($event->name);
            $logEvent->save();
        }
    }


    public function addDeleteLog(Event $event)
    {
        $logEvent = new LogItem();
        $logEvent->relatedObject = $this->owner;
        $logEvent->old_data = $event->sender ? $event->sender->getAttributes() : null;
        $logEvent->new_data = null;
        $logEvent->type = $this->selectType($event->name);
        $logEvent->save();
    }

    /**
     * @param $data
     * @param $type
     */
    public function addCustomLog($data, $type = null)
    {
        if (!is_array($data)) {
            $data = [$data];
        }

        $logEvent = new LogItem();
        $logEvent->relatedObject = $this->owner;
        $logEvent->old_data = null;
        $logEvent->new_data = $data;
        $logEvent->type = $type;
        $logEvent->save();
    }

    /**
     * @param array $oldValues
     * @param array $newValues
     * @return array
     */
    protected function applyExclude(array $oldValues, array $newValues)
    {
        foreach ($this->excludedAttributes as $attr) {
            unset($oldValues[$attr]);
            unset($newValues[$attr]);
        }

        return [$oldValues, $newValues];
    }

    /**
     * @param $eventName
     * @return string
     */
    protected function selectType($eventName)
    {
        switch ($eventName) {
            case ActiveRecord::EVENT_AFTER_INSERT:
                $type = 'created';
                break;
            case ActiveRecord::EVENT_AFTER_UPDATE:
                $type = 'updated';
                break;
            case ActiveRecord::EVENT_AFTER_DELETE:
                $type = 'deleted';
                break;
            default:
                $type = 'updated';
                break;
        }

        return $type;
    }
}