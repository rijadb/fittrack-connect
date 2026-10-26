# AI Coding Assistant Context — FitTrack Connect

This file describes the project's architecture and development conventions.

## Product and purpose

FitTrack Connect is a client–trainer personal training portal with five core entities: users, workout_plans, exercises, progress_logs, appointments. A plan_exercises junction table links workout plans and exercises. An external Spoonacular integration will power food lookup in nutrition tracking.

## Technology stack

- Frontend: pure HTML5, CSS3, vanilla JavaScript, Tabler / Bootstrap 5 components; responsive SPA.
- Backend: pure PHP 8+ using FlightPHP for REST routes.
- Data: MySQL with PHP PDO prepared statements for all SQL calls.
- API: REST JSON, JWT (role-based authorization), OpenAPI / Swagger documentation.
- No PHP markup mixed into frontend HTML; no database calls in views.

## Architecture boundaries

1. `frontend/views/*.html` contain structure only. In M1, `frontend/js/app.js` handles view navigation and sample interactions. In M5, separate it into frontend MVC and API service modules; only service files make backend AJAX requests.
2. `backend/routes/` defines endpoints, reads requests, calls services, and returns JSON.
3. `backend/services/` handles validation, permissions/business logic and only layer permitted to call DAO classes. External API logic belongs in a dedicated service.
4. `backend/dao/` uses a shared PDO connection, prepared statements and reusable DAO base class. No direct SQL in route handlers.
5. `backend/middleware/` houses reusable JWT authentication, role authorization, request logging and error handling. Never copy checks into every route.
6. `backend/config/config.php`, `.env` and `backend/logs/*.log` are **not committed**. Only `config.example.php` is committed.

## Naming and design conventions

- Database tables/columns: snake_case, plural table names (`workout_plans`, `progress_logs`).
- PHP class names: PascalCase (`WorkoutPlanService`, `WorkoutPlanDao`).
- PHP methods/JS functions: camelCase (`getById`, `renderPlans`).
- REST endpoints: plural nouns (`/api/workout-plans`, `/api/progress-logs`).
- JS view templates: short lowercase names (`plans.html`, `progress.html`).
- Consistent UX: dark navy sidebar, lime accent, accessible labels, responsive content, clear empty/error states.
- Return appropriate HTTP codes (200, 201, 400, 401, 403, 404, 500), no credentials or internal error traces in responses.

## Development guidelines

- Keep frontend and backend code separate.
- Use reusable functions and components where possible.
- Follow the planned REST API architecture.
- Store sensitive configuration outside the repository.
- Update documentation when important changes are made.
- Keep the code organized and easy to maintain.
