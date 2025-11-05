<?php
require_once __DIR__ . '/../config.php';

class BaseModel {
    protected $pdo;
    public function __construct(){
        $this->pdo = getPDO();
    }
}
