<?php
require_once __DIR__ . '/BaseDao.php';

class ExerciseDao extends BaseDao
{
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo, 'exercises',
            ['name', 'muscle_group', 'equipment', 'difficulty', 'description'],
            ['id', 'name', 'muscle_group', 'equipment', 'difficulty', 'description']
        );
    }
}
