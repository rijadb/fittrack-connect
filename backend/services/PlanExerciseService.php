<?php
require_once __DIR__ . '/ApiException.php';
require_once __DIR__ . '/../dao/PlanExerciseDao.php';
require_once __DIR__ . '/../dao/WorkoutPlanDao.php';
require_once __DIR__ . '/../dao/ExerciseDao.php';

class PlanExerciseService
{
    public function __construct(private PlanExerciseDao $links,
        private WorkoutPlanDao $plans, private ExerciseDao $exercises) {}

    public function list(int $planId): array
    {
        $this->plan($planId);
        return $this->links->findByPlan($planId);
    }

    public function attach(int $planId, int $exerciseId, array $input): array
    {
        $this->plan($planId);
        if (!$this->exercises->findById($exerciseId)) throw new ApiException('Exercise not found.', 404);
        $values = $this->values($input, true);
        $this->links->attach($planId, $exerciseId, ...array_values($values));
        return ['message' => 'Exercise added to workout plan.'];
    }

    public function update(int $planId, int $exerciseId, array $input): array
    {
        $this->plan($planId);
        $existing = $this->findLink($planId, $exerciseId);
        $values = $this->values(array_merge($existing, $input), false);
        $this->links->update($planId, $exerciseId, ...array_values($values));
        return ['message' => 'Plan exercise updated.'];
    }

    public function detach(int $planId, int $exerciseId): array
    {
        $this->plan($planId);
        if (!$this->links->detach($planId, $exerciseId)) throw new ApiException('Plan exercise not found.', 404);
        return ['message' => 'Exercise removed from workout plan.'];
    }

    private function plan(int $id): void
    {
        if (!$this->plans->findById($id)) throw new ApiException('Workout plan not found.', 404);
    }

    private function findLink(int $planId, int $exerciseId): array
    {
        foreach ($this->links->findByPlan($planId) as $link) {
            if ((int) $link['exercise_id'] === $exerciseId) return $link;
        }
        throw new ApiException('Plan exercise not found.', 404);
    }

    private function values(array $input, bool $creating): array
    {
        foreach ($input as $key => $value) {
            if (!in_array($key, ['sets','reps','rest_seconds','sort_order','plan_id','exercise_id'], true)) {
                throw new ApiException("Unknown field: {$key}.");
            }
        }
        $limits = ['sets'=>[1,30,3], 'reps'=>[1,1000,10], 'rest_seconds'=>[0,3600,60], 'sort_order'=>[0,10000,0]];
        $values = [];
        foreach ($limits as $key => [$min,$max,$default]) {
            $raw = $input[$key] ?? $default;
            $num = filter_var($raw, FILTER_VALIDATE_INT);
            if ($num === false || $num < $min || $num > $max) {
                throw new ApiException("Invalid {$key}.");
            }
            $values[$key] = $num;
        }
        return $values;
    }
}
