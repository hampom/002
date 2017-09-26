<?php

namespace App\Models;

use \Cake\Database\StatementInterface;

class Calendar extends Model
{
    /**
     * @param string $id
     * @return StatementInterface
     */
    public function getById(string $id): StatementInterface
    {
        return $this->db->newQuery()
            ->select('id, calendar_id, description')
            ->from('calendar')
            ->where(['calendar_id' => $id])
            ->execute();
    }

    /**
     * @param string $id
     * @return bool
     */
    public function has(string $id): bool
    {
        return $this->getById($id)->rowCount() > 0;
    }
}
