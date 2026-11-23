<?php
// Lightweight service validation checks that do not connect to MySQL.
foreach (['BaseDao','UserDao','WorkoutPlanDao','ExerciseDao','AppointmentDao','ProgressLogDao'] as $name) {
    require_once __DIR__ . '/../dao/' . $name . '.php';
}
foreach (['ApiException','CrudService','UserService','ExerciseService','WorkoutPlanService','AppointmentService','ProgressLogService'] as $name) {
    require_once __DIR__ . '/../services/' . $name . '.php';
}

class FakeUserDao extends UserDao
{
    public function __construct() {}
    public function findById(int $id): ?array
    {
        return [1=>['id'=>1,'role'=>'client'],2=>['id'=>2,'role'=>'trainer']][$id] ?? null;
    }
    public function create(array $values): int
    {
        if (empty($values['password_hash']) || isset($values['password'])) throw new RuntimeException('Password handling failed.');
        return 1;
    }
}
class FakeExerciseDao extends ExerciseDao
{
    public function __construct() {}
    public function create(array $values): int { return 7; }
    public function findById(int $id): ?array { return ['id'=>$id,'name'=>'Squat']; }
}
class FakePlanDao extends WorkoutPlanDao
{
    public function __construct() {}
    public function create(array $values): int { return 4; }
    public function findById(int $id): ?array { return ['id'=>$id,'title'=>'Program']; }
}

function expectFailure(callable $call, string $label): void
{
    try { $call(); } catch (ApiException $e) { return; }
    throw new RuntimeException("Expected validation failure: {$label}");
}

$users = new FakeUserDao();
$plan = new WorkoutPlanService(new FakePlanDao(), $users);
$exercise = new ExerciseService(new FakeExerciseDao());
$userService = new UserService($users);
expectFailure(fn() => $plan->create(['title'=>'Plan','focus'=>'Strength','trainer_id'=>1,'client_id'=>1]), 'trainer role');
expectFailure(fn() => $plan->create(['title'=>'Plan','focus'=>'Strength','trainer_id'=>2,'client_id'=>2]), 'client role');
expectFailure(fn() => $exercise->create(['name'=>'','muscle_group'=>'Legs']), 'empty exercise name');
expectFailure(fn() => $exercise->create(['name'=>'Squat','muscle_group'=>'Legs','difficulty'=>'Very hard']), 'invalid difficulty');
expectFailure(fn() => $userService->create(['name'=>'A','email'=>'invalid','role'=>'client','password'=>'12345678']), 'invalid email');
expectFailure(fn() => $userService->create(['name'=>'A','email'=>'a@example.com','role'=>'client','password'=>'abc']), 'short password');
$created = $plan->create(['title'=>'Strength','focus'=>'Legs','trainer_id'=>2,'client_id'=>1,'sessions_per_week'=>3]);
if ($created['id'] !== 4) throw new RuntimeException('Expected plan to be created.');
$created = $userService->create(['name'=>'Test','email'=>'test@example.com','role'=>'client','password'=>'Pass1234!']);
if ($created['id'] !== 1) throw new RuntimeException('Expected user to be created.');
echo "PASS: basic service validation and password hashing.\n";
