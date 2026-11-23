<?php
require_once __DIR__ . '/../services/ApiResponse.php';
require_once __DIR__ . '/../services/CrudService.php';

class CrudRoutes
{
    public static function register(string $path, CrudService $service): void
    {
        Flight::route('GET ' . $path, function () use ($service) {
            ApiResponse::run(fn() => $service->list());
        });
        Flight::route('GET ' . $path . '/@id', function ($id) use ($service) {
            ApiResponse::run(fn() => $service->get(ApiResponse::positiveId((string)$id)));
        });
        Flight::route('POST ' . $path, function () use ($service) {
            ApiResponse::run(fn() => $service->create(ApiResponse::body()), 201);
        });
        Flight::route('PUT|PATCH ' . $path . '/@id', function ($id) use ($service) {
            ApiResponse::run(fn() => $service->update(
                ApiResponse::positiveId((string)$id), ApiResponse::body()));
        });
        Flight::route('DELETE ' . $path . '/@id', function ($id) use ($service) {
            ApiResponse::run(fn() => $service->delete(ApiResponse::positiveId((string)$id)));
        });
    }
}
