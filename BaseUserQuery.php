<?php
namespace vtvz\basics;

use yii\db\ActiveQuery;

class BaseUserQuery extends ActiveQuery
{
    public function active($state = true)
    {
        $this->andWhere(['status' => (int) $state]);
    }
}
