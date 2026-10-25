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
