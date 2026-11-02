AI Usage Log — FitTrack Connect

Milestone 1 — Project Setup, Frontend and ERD
AI tool: ChatGPT
How AI was used

- Compared Bootstrap dashboard templates and helped select Tabler for the FitTrack Connect interface.
- Assisted with the initial frontend implementation, including responsive layouts, SPA navigation, page templates, and sample-data interactions.
- Helped organize the project folders and plan the database entities, relationships, and draft ERD.
- Assisted with drafting and reviewing project documentation.
  Main prompts (summarized)

1. Find a suitable Bootstrap template for a personal training and nutrition portal.
2. Plan and implement the initial responsive single-page interface for the required features.
3. Design the database relationships and organize the project according to the university requirements.
   Issues identified and corrections
4. Workout plans and exercises relationship
   The initial five-entity outline did not clearly represent how exercises could be reused across multiple workout plans. This became apparent while reviewing the database relationships. A plan_exercises junction table was added to the ERD and frontend data structure to represent the many-to-many relationship.

5. Unnecessary development messages in the interface
   The initial frontend included notices explaining milestone deadlines, simulated features, and future implementation steps. These distracted from the application interface and were identified during frontend review. The notices were removed or simplified, while relevant technical limitations remained documented in the README.
   Review
   The application was opened locally using the PHP development server. The frontend, database plan, and documentation are being checked against the Milestone 1 requirements. The initial version uses local sample data; backend integration is planned for later milestones.

## Milestone 2 — Database and DAO Layer

**AI Tool:** ChatGPT

### AI Assistance

- Assisted with designing the MySQL schema based on the existing ER diagram.
- Helped implement PDO database connectivity and reusable DAO classes.
- Assisted with writing and reviewing CRUD operations and database tests.
- Provided guidance for configuring MySQL and PHP locally.

### Main Prompts (summarized)

1. Implement the relational database and DAO layer for FitTrack Connect using MySQL and PHP PDO.
2. Preserve the existing project structure while adding the backend components.
3. Help troubleshoot the database connection and verify CRUD functionality.

### Issues Identified and Corrected

**1. Configuration file conflict**

The initial update package contained a configuration template that could overwrite the existing project configuration. This was identified during file comparison, and a code-only update was used to preserve the original configuration.

**2. Documentation compatibility**

An automatically generated documentation patch was based on an earlier version of the project. The compatibility check failed because some files had already been modified or deleted. The documentation was therefore updated manually to avoid overwriting existing changes.

### Testing

The database was successfully imported into MySQL 8.0.46. CRUD operations for all five core entities and the workout-plan exercise relationships were tested using PHP PDO.

The test completed successfully, and temporary test data was rolled back.
