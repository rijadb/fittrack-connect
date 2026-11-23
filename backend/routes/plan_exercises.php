<?php
Flight::route('GET /api/workout-plans/@id/exercises', function ($id) use ($planExerciseService) {
    ApiResponse::run(fn() => $planExerciseService->list(ApiResponse::positiveId((string)$id)));
});
Flight::route('POST /api/workout-plans/@id/exercises/@exerciseId', function ($id, $exerciseId) use ($planExerciseService) {
    ApiResponse::run(fn() => $planExerciseService->attach(ApiResponse::positiveId((string)$id),
        ApiResponse::positiveId((string)$exerciseId), ApiResponse::body()), 201);
});
Flight::route('PUT|PATCH /api/workout-plans/@id/exercises/@exerciseId', function ($id, $exerciseId) use ($planExerciseService) {
    ApiResponse::run(fn() => $planExerciseService->update(ApiResponse::positiveId((string)$id),
        ApiResponse::positiveId((string)$exerciseId), ApiResponse::body()));
});
Flight::route('DELETE /api/workout-plans/@id/exercises/@exerciseId', function ($id, $exerciseId) use ($planExerciseService) {
    ApiResponse::run(fn() => $planExerciseService->detach(ApiResponse::positiveId((string)$id),
        ApiResponse::positiveId((string)$exerciseId)));
});
