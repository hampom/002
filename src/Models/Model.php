<?php

namespace App\Models;

use \Cake\Database\Connection;

class Model {

    /**
     * @var Connection
     */
    protected $db;

    /**
     * Model constructor.
     * @param Connection $db
     */
    public function __construct(Connection $db)
    {
        $this->db = $db;
    }
}
