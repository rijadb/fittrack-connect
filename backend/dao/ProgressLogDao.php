<?php
require_once __DIR__ . '/BaseDao.php';

class ProgressLogDao extends BaseDao
{
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo, 'progress_logs',
            ['client_id', 'workout_plan_id', 'exercise_id', 'log_date', 'log_type', 'value', 'unit', 'notes', 'external_food_id', 'calories_kcal', 'protein_g', 'carbs_g', 'fat_g'],
            ['id', 'client_id', 'workout_plan_id', 'exercise_id', 'log_date', 'log_type', 'value', 'unit', 'notes', 'external_food_id', 'calories_kcal', 'protein_g', 'carbs_g', 'fat_g']
        );
    }
}
