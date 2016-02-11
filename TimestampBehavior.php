<?php
namespace vtvz\basics;

use yii\db\Expression;

class TimestampBehavior extends \yii\behaviors\TimestampBehavior
{
    public $createdAtAttribute = 'createdAt';

    public $updatedAtAttribute = 'updatedAt';

    public $value;

    /**
     * @inheritdoc
     */
    protected function getValue($event)
    {
        if ($this->value instanceof Expression) {
            return $this->value;
        } else {
            return $this->value !== null ? call_user_func($this->value, $event) : date("Y-m-d H:i:s");
        }
    }
}

