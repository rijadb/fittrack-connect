# FitTrack Connect 🏋️

**Personal Training & Nutrition Portal** — a responsive single-page web app where trainers design and assign workout plans, clients track metrics, and both schedule virtual training appointments.

## Features and pages

- **Login and register**: Sign-in and registration forms with temporary browser-based functionality.
- **Dashboard**: training statistics, activity example chart, goals, assigned plans, upcoming sessions.
- **Workout plans**: list, search, sample create/edit/delete; trainer and client assignment, nutrition guidance and exercise associations.
- **Exercise library**: view, search, create/edit/delete sample exercises.
- **Progress tracker**: weight, water, training and food records, demo create/edit/delete.
- **Appointments**: virtual session schedule and demo create/edit/delete.
- **Nutrition explorer**: sample food lookup and calorie/macronutrient preview with a demo food-log action; **no external API call yet**.
- **User management**: admin-style overview and demo create/edit/delete for all roles.
- **Profile**: account preview, with Client / Trainer / Admin demo-role selector.

Navigation is hash-routed (`#/dashboard`, `#/plans`, etc.) and swaps HTML view templates using browser `fetch()` **without a full page reload**. A local static web server is required. The frontend is designed with **Tabler** (MIT-licensed Bootstrap 5 dashboard kit), Bootstrap Icons and custom CSS. The layout is responsive for desktop and mobile.

## Database entities and relationships (draft)

1. `users`: account information and role (`admin`, `trainer`, `client`).
2. `workout_plans`: created by a trainer and assigned to a client; contains basic nutrition guidance.
3. `exercises`: a reusable library of exercises.
4. `progress_logs`: daily weight, hydration, training and food events, optionally tied to a plan or exercise.
5. `appointments`: bookings between trainer and client.

**Auxiliary junction table:** `plan_exercises` links many exercises to many workout plans and stores sets/reps/rest/order. Therefore the design has **five core entities and six database tables**. The planned schema is shown in [docs/ERD.md](docs/ERD.md) and the picture [docs/ERD.png](docs/ERD.png).

### Planned access rules

| Role        | Planned permissions                                                                                    |
| ----------- | ------------------------------------------------------------------------------------------------------ |
| **Admin**   | Manage users and all core entities (CRUD); view operational records.                                   |
| **Trainer** | Manage own plans and plan exercises, assigned clients' relevant logs and coaching appointments.        |
| **Client**  | View assigned plans/exercises; manage own progress and appointments; search foods; update own profile. |

Role checking **must be performed on the backend**, not just by hiding buttons. The current demo role switch has no security function.

## Planned external API

**Spoonacular API**, accessed by a **FlightPHP backend service** (never directly with the private API key from JavaScript). Planned UI feature: clients search foods and preview nutrition values; selected food/nutrient data are stored as food-type `progress_logs` with relevant portion data. Different foods and portions have different estimated nutrients — we will **not** claim the values are exact.

Planned integration: `GET /api/nutrition/search?q=...` and a follow-up nutrient-information request for a chosen item, routed through a PHP service to Spoonacular. Server-side timeout, error handling, sanitized error messages and logging will be included. The nutrition page currently uses labeled example data only.

## Architecture / project tree

```text
fittrack-connect/
├── frontend/
│   ├── index.html          # SPA shell, navigation and dialog
│   ├── css/styles.css      # Custom responsive styling
│   ├── js/app.js           # M1 hash router and demo UI logic
│   ├── js/mockData.js      # M1 static sample data ONLY
│   ├── views/*.html        # 10 HTML page templates
│   └── assets/             # Static assets
├── backend/
│   ├── routes/             # FlightPHP route definitions
│   ├── services/           # Business logic
│   ├── dao/                # PDO data access
│   ├── middleware/         # JWT, roles, request/error handlers
│   ├── config/config.example.php
│   └── logs/               # Gitignored runtime info/error logs
├── docs/
│   ├── ERD.md / ERD.mmd / ERD.png
│   └── AI_LOG.md
│
├── .gitignore
└── AGENTS.md
```

The REST backend will be pure PHP with **FlightPHP**, **PDO prepared statements**, **MySQL** and **JWT**. Frontend pages remain HTML/CSS/JS and will use AJAX (e.g. `fetch`) through dedicated frontend service modules.

## References and third-party credits

- [Tabler UI](https://github.com/tabler/tabler) — MIT license, Bootstrap 5-based dashboard component library.
- [Bootstrap Icons](https://icons.getbootstrap.com/) — MIT license.
- [Spoonacular documentation](https://spoonacular.com/food-api/docs) — planned third-party food API.
- [FlightPHP](https://flightphp.com/) — required PHP REST framework; planned.

## AI use

Assistance is documented in [docs/AI_LOG.md](docs/AI_LOG.md). Project architecture and instructions for AI assistants are documented in [AGENTS.md](AGENTS.md). All submitted code should be personally reviewed and understood before defense.
