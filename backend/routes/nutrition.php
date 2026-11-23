<?php
Flight::route('GET /api/nutrition/search', function () use ($spoonacularService) {
    ApiResponse::run(fn() => $spoonacularService->search((string)($_GET['q'] ?? '')));
});
Flight::route('GET /api/nutrition/ingredients/@id', function ($id) use ($spoonacularService) {
    ApiResponse::run(function () use ($id, $spoonacularService) {
        $amount = $_GET['amount'] ?? 100;
        if (!is_numeric($amount)) throw new ApiException('Invalid amount.');
        return $spoonacularService->nutrition(
            ApiResponse::positiveId((string)$id), (float)$amount, (string)($_GET['unit'] ?? 'grams'));
    });
});
