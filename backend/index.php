<?php
require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/services/ApiResponse.php';
require_once __DIR__ . '/routes/CrudRoutes.php';

foreach (['User', 'WorkoutPlan', 'Exercise', 'ProgressLog', 'Appointment', 'PlanExercise'] as $name) {
    require_once __DIR__ . '/dao/' . $name . 'Dao.php';
}
foreach (['User','WorkoutPlan','Exercise','ProgressLog','Appointment','PlanExercise','Spoonacular'] as $name) {
    require_once __DIR__ . '/services/' . $name . 'Service.php';
}

Flight::set('flight.debug', false);

// The database is initialized once and shared by the DAO objects.
$pdo = Database::connect();
$userDao = new UserDao($pdo);
$planDao = new WorkoutPlanDao($pdo);
$exerciseDao = new ExerciseDao($pdo);
$logDao = new ProgressLogDao($pdo);
$appointmentDao = new AppointmentDao($pdo);
$linkDao = new PlanExerciseDao($pdo);

$userService = new UserService($userDao);
$workoutPlanService = new WorkoutPlanService($planDao, $userDao);
$exerciseService = new ExerciseService($exerciseDao);
$progressLogService = new ProgressLogService($logDao, $userDao, $planDao, $exerciseDao);
$appointmentService = new AppointmentService($appointmentDao, $userDao);
$planExerciseService = new PlanExerciseService($linkDao, $planDao, $exerciseDao);
$config = require __DIR__ . '/config/config.php';
$spoonacularService = new SpoonacularService((string)($config['spoonacular_api_key'] ?? ''));

Flight::route('GET /api/health', function () {
    Flight::json(['status' => 'ok', 'application' => 'FitTrack Connect']);
});

foreach (['users','workout_plans','exercises','progress_logs','appointments','plan_exercises','nutrition'] as $route) {
    require __DIR__ . '/routes/' . $route . '.php';
}

Flight::route('GET /openapi.json', function () {
    header('Content-Type: application/json; charset=utf-8');
    readfile(__DIR__ . '/docs/openapi.json');
});
Flight::route('GET /docs', function () {
    header('Content-Type: text/html; charset=utf-8');
    readfile(__DIR__ . '/docs/swagger.html');
});

Flight::start();
