<?php
/** Run: php backend/tests/dao_smoke_test.php (requires imported database + config.php).
 *  Uses a transaction and rolls back: all test records are temporary.
 */
require_once __DIR__ . '/../config/Database.php';
foreach (['UserDao', 'WorkoutPlanDao', 'ExerciseDao', 'ProgressLogDao', 'AppointmentDao', 'PlanExerciseDao'] as $name) {
    require_once __DIR__ . '/../dao/' . $name . '.php';
}

$pdo = null;
try {
    $pdo = Database::connect();
    $pdo->beginTransaction();
    $nonce = bin2hex(random_bytes(5));
    $users = new UserDao($pdo);
    $plans = new WorkoutPlanDao($pdo);
    $exercises = new ExerciseDao($pdo);
    $logs = new ProgressLogDao($pdo);
    $appointments = new AppointmentDao($pdo);
    $links = new PlanExerciseDao($pdo);

    $trainerId = $users->create([
        'name' => 'Test Coach', 'email' => "coach-{$nonce}@example.test",
        'password_hash' => password_hash('TestOnlyPassword123!', PASSWORD_DEFAULT), 'role' => 'trainer',
    ]);
    $clientId = $users->create([
        'name' => 'Test Client', 'email' => "client-{$nonce}@example.test",
        'password_hash' => password_hash('TestOnlyPassword123!', PASSWORD_DEFAULT), 'role' => 'client',
    ]);
    check(count($users->findAll()) >= 2, 'users: read all');
    check($users->findById($trainerId)['name'] === 'Test Coach', 'users: read');
    check(!array_key_exists('password_hash', $users->findById($clientId)), 'users: safe read');
    check($users->update($trainerId, ['name' => 'Updated Coach']), 'users: update');
    check($users->findById($trainerId)['name'] === 'Updated Coach', 'users: updated name');

    $exerciseId = $exercises->create([
        'name' => "Test Exercise {$nonce}", 'muscle_group' => 'Legs',
        'equipment' => 'Bodyweight', 'difficulty' => 'Beginner', 'description' => 'Test only',
    ]);
    check(count($exercises->findAll()) >= 1, 'exercises: read all');
    check($exercises->findById($exerciseId) !== null, 'exercises: read');
    check($exercises->update($exerciseId, ['description' => 'Updated']), 'exercises: update');

    $planId = $plans->create([
        'trainer_id' => $trainerId, 'client_id' => $clientId,
        'title' => 'Test Plan', 'focus' => 'Mobility', 'level' => 'Beginner',
        'sessions_per_week' => 3, 'notes' => 'Temporary', 'nutrition_guidance' => 'Optional',
    ]);
    check(count($plans->findAll()) >= 1, 'plans: read all');
    check($plans->findById($planId)['title'] === 'Test Plan', 'plans: read');
    check($plans->update($planId, ['title' => 'Updated Plan']), 'plans: update');

    $links->attach($planId, $exerciseId);
    check(count($links->findByPlan($planId)) === 1, 'plan_exercises: attach');
    check($links->update($planId, $exerciseId, 4, 10, 90, 1), 'plan_exercises: update');
    check($links->detach($planId, $exerciseId), 'plan_exercises: detach');

    $logId = $logs->create([
        'client_id' => $clientId, 'workout_plan_id' => $planId, 'exercise_id' => $exerciseId,
        'log_date' => date('Y-m-d'), 'log_type' => 'training',
        'value' => 3, 'unit' => 'sets', 'notes' => 'Temporary test',
    ]);
    check(count($logs->findAll()) >= 1, 'progress_logs: read all');
    check($logs->findById($logId) !== null, 'progress_logs: read');
    check($logs->update($logId, ['value' => 4]), 'progress_logs: update');

    $appointmentId = $appointments->create([
        'trainer_id' => $trainerId, 'client_id' => $clientId,
        'scheduled_at' => '2026-11-03 15:00:00', 'duration_minutes' => 45,
        'status' => 'pending', 'meeting_url' => null,
    ]);
    check(count($appointments->findAll()) >= 1, 'appointments: read all');
    check($appointments->findById($appointmentId) !== null, 'appointments: read');
    check($appointments->update($appointmentId, ['status' => 'confirmed']), 'appointments: update');

    // Delete in child-to-parent order to satisfy foreign keys.
    check($appointments->delete($appointmentId), 'appointments: delete');
    check($logs->delete($logId), 'progress_logs: delete');
    check($plans->delete($planId), 'workout_plans: delete');
    check($exercises->delete($exerciseId), 'exercises: delete');
    check($users->delete($clientId), 'users: delete client');
    check($users->delete($trainerId), 'users: delete trainer');

    $pdo->rollBack();
    echo "PASS: create, read, update, delete for all five core DAOs; junction table tested.\n";
    echo "All test changes were rolled back.\n";
} catch (Throwable $error) {
    if ($pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    fwrite(STDERR, "FAIL: " . $error->getMessage() . PHP_EOL);
    exit(1);
}

function check(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException("Test failed: {$message}");
    }
}
