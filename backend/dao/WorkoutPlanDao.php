<?php
require_once __DIR__ . '/BaseDao.php';

class WorkoutPlanDao extends BaseDao
{
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo, 'workout_plans',
            ['trainer_id', 'client_id', 'title', 'focus', 'level', 'sessions_per_week', 'notes', 'nutrition_guidance'],
            ['id', 'trainer_id', 'client_id', 'title', 'focus', 'level', 'sessions_per_week', 'notes', 'nutrition_guidance', 'created_at']
        );
    }
}
