<?php
/** The extra junction table uses two primary-key columns instead of an id. */
class PlanExerciseDao
{
    public function __construct(private PDO $pdo)
    {
    }

    public function findByPlan(int $planId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT plan_id, exercise_id, sets, reps, rest_seconds, sort_order
             FROM plan_exercises WHERE plan_id = :plan_id ORDER BY sort_order, exercise_id'
        );
        $stmt->execute(['plan_id' => $planId]);
        return $stmt->fetchAll();
    }

    public function attach(int $planId, int $exerciseId, int $sets = 3,
                           int $reps = 10, int $restSeconds = 60, int $sortOrder = 0): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO plan_exercises
                (plan_id, exercise_id, sets, reps, rest_seconds, sort_order)
             VALUES (:plan_id, :exercise_id, :sets, :reps, :rest_seconds, :sort_order)'
        );
        $stmt->execute([
            'plan_id' => $planId, 'exercise_id' => $exerciseId,
            'sets' => $sets, 'reps' => $reps,
            'rest_seconds' => $restSeconds, 'sort_order' => $sortOrder,
        ]);
    }

    public function update(int $planId, int $exerciseId, int $sets,
                           int $reps, int $restSeconds, int $sortOrder): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE plan_exercises SET sets = :sets, reps = :reps,
             rest_seconds = :rest_seconds, sort_order = :sort_order
             WHERE plan_id = :plan_id AND exercise_id = :exercise_id'
        );
        $stmt->execute([
            'plan_id' => $planId, 'exercise_id' => $exerciseId,
            'sets' => $sets, 'reps' => $reps,
            'rest_seconds' => $restSeconds, 'sort_order' => $sortOrder,
        ]);
        return $stmt->rowCount() > 0;
    }

    public function detach(int $planId, int $exerciseId): bool
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM plan_exercises WHERE plan_id = :plan_id AND exercise_id = :exercise_id'
        );
        $stmt->execute(['plan_id' => $planId, 'exercise_id' => $exerciseId]);
        return $stmt->rowCount() > 0;
    }
}
