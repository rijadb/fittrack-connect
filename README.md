# FitTrack Connect

**Personal Training & Nutrition Portal** — a responsive single-page web app where trainers design and assign workout plans, clients track metrics, and both schedule virtual training appointments.

## Features and pages

- **Login and register**: Sign-in and registration forms with temporary browser-based functionality.
- **Dashboard**: training statistics, activity example chart, goals, assigned plans, upcoming sessions.
- **Workout plans**: list, search, sample create/edit/delete; trainer and client assignment, nutrition guidance and exercise associations.
- **Exercise library**: view, search, create/edit/delete sample exercises.
- **Progress tracker**: weight, water, training and food records, demo create/edit/delete.
- **Appointments**: virtual session schedule and demo create/edit/delete.
- **Nutrition explorer**: sample food lookup and calorie/macronutrient preview with a demo food-log action; frontend currently uses sample data; backend Spoonacular integration is implemented
- **User management**: admin-style overview and demo create/edit/delete for all roles.
- **Profile**: account preview, with Client / Trainer / Admin demo-role selector.

Navigation is hash-routed (`#/dashboard`, `#/plans`, etc.) and swaps HTML view templates using browser `fetch()` **without a full page reload**. A local static web server is required. The frontend is designed with **Tabler** (MIT-licensed Bootstrap 5 dashboard kit), Bootstrap Icons and custom CSS. The layout is responsive for desktop and mobile.

## Database entities and relationships

1. `users`: account information and role (`admin`, `trainer`, `client`).
2. `workout_plans`: created by a trainer and assigned to a client; contains basic nutrition guidance.
3. `exercises`: a reusable library of exercises.
4. `progress_logs`: daily weight, hydration, training and food events, optionally tied to a plan or exercise.
5. `appointments`: bookings between trainer and client.

**Auxiliary junction table:** `plan_exercises` links many exercises to many workout plans and stores sets/reps/rest/order. Therefore the design has **five core entities and six database tables**. The planned schema is shown in [docs/ERD.md](docs/ERD.md) and the picture [docs/ERD.png](docs/ERD.png).

## Database setup

The MySQL database schema and sample data are provided in
`database/fittrack_connect.sql`.

To set up the database:

1. Import the SQL file into MySQL using DBeaver or the MySQL command line.
2. Copy `backend/config/config.example.php` to `backend/config/config.php`.
3. Enter your local database host, port, username and password in `config.php`.

The application uses PDO for database access. Common CRUD operations
are implemented in `BaseDao.php`, while separate DAO classes manage
users, workout plans, exercises, progress logs and appointments.

The `PlanExerciseDao` class manages exercise assignments to workout plans.

To test database operations, run:

    php backend/tests/dao_smoke_test.php

The test verifies CRUD operations and rolls back its changes.

The frontend currently uses sample data. Backend API integration
will allow the interface to retrieve and modify database records.

### Planned access rules

| Role        | Planned permissions                                                                                    |
| ----------- | ------------------------------------------------------------------------------------------------------ |
| **Admin**   | Manage users and all core entities (CRUD); view operational records.                                   |
| **Trainer** | Manage own plans and plan exercises, assigned clients' relevant logs and coaching appointments.        |
| **Client**  | View assigned plans/exercises; manage own progress and appointments; search foods; update own profile. |

## External API — Spoonacular

FitTrack Connect integrates the Spoonacular Food API through the FlightPHP backend.

Implemented endpoints:

- `GET /api/nutrition/search?q=banana` — searches for food ingredients.
- `GET /api/nutrition/ingredients/{id}?amount=100&unit=grams` — retrieves nutritional information for a selected ingredient and portion.

The Spoonacular API key is stored in the local `backend/config/config.php` file and is never exposed to frontend JavaScript or committed to Git.

The ingredient search endpoint was successfully tested through Swagger using live Spoonacular data.

The frontend nutrition explorer currently displays sample data and has not yet been connected to the backend API.

## Architecture / project tree

fittrack-connect/
├── frontend/
│ ├── index.html # Main SPA layout and navigation
│ ├── css/
│ │ └── styles.css # Responsive styling
│ ├── js/
│ │ ├── app.js # Navigation and UI functionality
│ │ └── mockData.js # Sample frontend data
│ ├── views/ # HTML page templates
│ └── assets/ # Static assets
│
├── backend/
│ ├── config/
│ │ ├── Database.php # PDO database connection
│ │ └── config.example.php # Configuration template
│ ├── dao/
│ │ ├── BaseDao.php # Reusable CRUD operations
│ │ ├── UserDao.php
│ │ ├── WorkoutPlanDao.php
│ │ ├── ExerciseDao.php
│ │ ├── ProgressLogDao.php
│ │ ├── AppointmentDao.php
│ │ └── PlanExerciseDao.php
│ ├── tests/
│ │ └── dao_smoke_test.php # Database operation tests
│ ├── routes/ # API endpoints
│ ├── services/ # Business logic
│ ├── middleware/ # Authentication and logging
│ └── logs/ # Runtime logs (Gitignored)
│
├── database/
│ └── fittrack_connect.sql # Database schema and sample data
│
├── docs/
│ ├── ERD.md # Database relationships
│ ├── ERD.mmd # Mermaid ER diagram
│ ├── ERD.png # ER diagram image
│ └── AI_LOG.md # AI usage documentation
│
├── .gitignore # Excluded files and directories
├── AGENTS.md # Development conventions
└── README.md # Project documentation

The REST backend uses PHP, FlightPHP, PDO prepared statements and MySQL. JWT authentication is planned for a future milestone.

## References and third-party credits

- [Tabler UI](https://github.com/tabler/tabler) — MIT license, Bootstrap 5-based dashboard component library.
- [Bootstrap Icons](https://icons.getbootstrap.com/) — MIT license.
- [Spoonacular documentation](https://spoonacular.com/food-api/docs) — planned third-party food API.
- [FlightPHP](https://flightphp.com/) — PHP REST framework used by the backend.

## AI use

Assistance is documented in [docs/AI_LOG.md](docs/AI_LOG.md). Project architecture and instructions for AI assistants are documented in [AGENTS.md](AGENTS.md). All submitted code should be personally reviewed and understood before defense.

## REST API (FlightPHP)

FitTrack Connect now includes a PHP 8.2+ REST backend built with FlightPHP, a service layer for input validation, reusable PDO DAOs and MySQL. The backend supports CRUD for users, workout plans, exercises, progress logs and appointments. Workout plans can also be linked to exercises with sets, reps, rest time and ordering.
Run locally

1. Import database/fittrack_connect.sql into a local MySQL database if it is not already present.
2. Copy backend/config/config.example.php to backend/config/config.php and configure database credentials. Keep config.php out of Git.
3. From backend/, run composer install using PHP with pdo_mysql and curl enabled.
4. From the project root, start the API server:
   & "C:\xampp\php\php.exe" -n -d "extension_dir=C:\xampp\php\ext" -d "extension=pdo_mysql" -d "extension=curl" -d "extension=openssl" -d "curl.cainfo=C:\xampp\apache\bin\curl-ca-bundle.crt" -S 127.0.0.1:8001 -t backend backend/router.php
   Open Swagger UI at http://localhost:8001/docs, OpenAPI JSON at http://localhost:8001/openapi.json, and health check at http://localhost:8001/api/health.
   Nutrition integration
   A backend Spoonacular service implements GET /api/nutrition/search?q=banana and GET /api/nutrition/ingredients/{id}?amount=100&unit=grams. Add your own spoonacular_api_key to the local backend/config/config.php. The key is never sent to the frontend or committed to Git. Upstream errors return a controlled API error response.
   Current limits
   Backend CRUD, workout-plan exercise assignment and live Spoonacular ingredient search were manually tested locally through Swagger. The frontend still uses sample data and is not yet connected to these endpoints. JWT authentication and role-based authorization are not implemented yet. This backend is for local development/testing only and should not be publicly exposed until access controls and deployment hardening are complete.
