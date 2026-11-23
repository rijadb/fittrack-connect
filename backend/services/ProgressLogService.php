<?php
require_once __DIR__ . '/CrudService.php';
require_once __DIR__ . '/../dao/ProgressLogDao.php';
require_once __DIR__ . '/../dao/UserDao.php';
require_once __DIR__ . '/../dao/WorkoutPlanDao.php';
require_once __DIR__ . '/../dao/ExerciseDao.php';

class ProgressLogService extends CrudService
{
    public function __construct(ProgressLogDao $dao, private UserDao $users,
        private WorkoutPlanDao $plans, private ExerciseDao $exercises)
    {
        parent::__construct($dao);
    }

    protected function validate(array $input, bool $partial): array
    {
        $allowed = ['client_id','workout_plan_id','exercise_id','log_date','log_type','value',
            'unit','notes','external_food_id','calories_kcal','protein_g','carbs_g','fat_g'];
        $this->fields($input, $allowed, ['client_id','log_date','log_type','value','unit'], $partial);
        foreach (['client_id','workout_plan_id','exercise_id'] as $key) {
            $this->integer($input, $key, 1, PHP_INT_MAX, $key !== 'client_id');
        }
        $this->date($input, 'log_date');
        $this->enum($input, 'log_type', ['weight','water','training','food']);
        $this->numeric($input, 'value', 0, 1000000);
        foreach (['calories_kcal','protein_g','carbs_g','fat_g'] as $key) {
            $this->numeric($input, $key, 0, 1000000, true);
        }
        foreach (['unit'=>25,'notes'=>10000,'external_food_id'=>150] as $key => $max) {
            $this->string($input, $key, $max, $key !== 'unit');
        }
        if (isset($input['unit']) && $input['unit'] === '') throw new ApiException('Unit cannot be empty.');
        if (isset($input['client_id']) && ($this->users->findById($input['client_id'])['role'] ?? '') !== 'client') {
            throw new ApiException('client_id must reference a client.');
        }
        if (!empty($input['workout_plan_id']) && !$this->plans->findById($input['workout_plan_id'])) {
            throw new ApiException('Workout plan not found.');
        }
        if (!empty($input['exercise_id']) && !$this->exercises->findById($input['exercise_id'])) {
            throw new ApiException('Exercise not found.');
        }
        return $input;
    }
}
