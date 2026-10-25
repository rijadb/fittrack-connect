/** Milestone 1 only: replace this module with AJAX service calls by Milestone 4. */
export const initialData = {
  users: [
    { id: 1, name: 'Jordan Davis', email: 'client@example.com', role: 'client', joined: '2026-09-11' },
    { id: 2, name: 'Alex Morgan', email: 'alex.trainer@example.com', role: 'trainer', joined: '2026-08-23' },
    { id: 3, name: 'Sam Taylor', email: 'sam.trainer@example.com', role: 'trainer', joined: '2026-08-29' },
    { id: 4, name: 'Casey Brooks', email: 'casey@example.com', role: 'client', joined: '2026-09-19' },
    { id: 5, name: 'Jamie Reed', email: 'jamie@example.com', role: 'client', joined: '2026-10-02' },
    { id: 6, name: 'Taylor Smith', email: 'admin@example.com', role: 'admin', joined: '2026-08-10' },
  ],
  workout_plans: [
    { id: 1, title: 'Strength Foundations', focus: 'Full body strength', level: 'Beginner', sessions: 3, trainer_id: 2, client_id: 1, notes: 'Compound movements and progressive overload.', nutrition_guidance: 'Aim for balanced meals with protein after training.' },
    { id: 2, title: 'Build & Sculpt', focus: 'Hypertrophy', level: 'Intermediate', sessions: 4, trainer_id: 3, client_id: 4, notes: 'Upper/lower split with controlled tempo.', nutrition_guidance: 'Discuss a consistent protein goal and adequate hydration.' },
    { id: 3, title: 'Athletic Conditioning', focus: 'Cardio & mobility', level: 'All levels', sessions: 3, trainer_id: 2, client_id: 5, notes: 'Mix cardio intervals with mobility.', nutrition_guidance: 'Fuel longer sessions and hydrate throughout the day.' },
    { id: 4, title: 'Next-Level Push/Pull', focus: 'Strength & muscle', level: 'Intermediate', sessions: 5, trainer_id: 3, client_id: 1, notes: 'Alternating push and pull days.', nutrition_guidance: 'Prioritize recovery, sleep, and regular meals.' },
  ],
  exercises: [
    { id: 1, name: 'Barbell Squat', muscle_group: 'Legs', equipment: 'Barbell', difficulty: 'Intermediate', description: 'Keep the torso braced and drive through the feet.', icon: 'bi-person-standing' },
    { id: 2, name: 'Bench Press', muscle_group: 'Chest', equipment: 'Barbell', difficulty: 'Intermediate', description: 'Control the bar down and press evenly.', icon: 'bi-lightning-charge' },
    { id: 3, name: 'Deadlift', muscle_group: 'Back', equipment: 'Barbell', difficulty: 'Advanced', description: 'Engage the lats and extend through the hips.', icon: 'bi-activity' },
    { id: 4, name: 'Lat Pulldown', muscle_group: 'Back', equipment: 'Cable machine', difficulty: 'Beginner', description: 'Pull elbows down while keeping the core stable.', icon: 'bi-arrow-down-circle' },
    { id: 5, name: 'Dumbbell Shoulder Press', muscle_group: 'Shoulders', equipment: 'Dumbbells', difficulty: 'Beginner', description: 'Press overhead without arching your lower back.', icon: 'bi-arrow-up-circle' },
    { id: 6, name: 'Plank', muscle_group: 'Core', equipment: 'Bodyweight', difficulty: 'Beginner', description: 'Hold a straight line and breathe steadily.', icon: 'bi-person-arms-up' },
    { id: 7, name: 'Walking Lunges', muscle_group: 'Legs', equipment: 'Bodyweight', difficulty: 'Beginner', description: 'Take controlled steps and keep knees aligned.', icon: 'bi-person-walking' },
    { id: 8, name: 'Biceps Curl', muscle_group: 'Arms', equipment: 'Dumbbells', difficulty: 'Beginner', description: 'Keep elbows stable and lift with control.', icon: 'bi-lightning' },
  ],
  plan_exercises: [
    { plan_id: 1, exercise_id: 1, sets: 3, reps: 8 },
    { plan_id: 1, exercise_id: 2, sets: 3, reps: 10 },
    { plan_id: 1, exercise_id: 6, sets: 3, reps: 30 },
    { plan_id: 2, exercise_id: 5, sets: 4, reps: 12 },
    { plan_id: 2, exercise_id: 7, sets: 4, reps: 10 },
    { plan_id: 3, exercise_id: 6, sets: 3, reps: 30 },
    { plan_id: 3, exercise_id: 7, sets: 3, reps: 15 },
    { plan_id: 4, exercise_id: 2, sets: 4, reps: 8 },
    { plan_id: 4, exercise_id: 3, sets: 4, reps: 6 },
  ],
  progress_logs: [
    { id: 1, client_id: 1, log_date: '2026-10-25', log_type: 'weight', value: 75.2, unit: 'kg', notes: 'Morning weigh-in' },
    { id: 2, client_id: 1, log_date: '2026-10-25', log_type: 'water', value: 2.4, unit: 'L', notes: 'Daily hydration total' },
    { id: 3, client_id: 1, log_date: '2026-10-24', log_type: 'training', value: 12, unit: 'sets', notes: 'Full-body session' },
    { id: 4, client_id: 4, log_date: '2026-10-24', log_type: 'weight', value: 67.5, unit: 'kg', notes: 'Weekly check-in' },
    { id: 5, client_id: 1, log_date: '2026-10-23', log_type: 'food', value: 580, unit: 'kcal', notes: 'Lunch – sample entry' },
    { id: 6, client_id: 5, log_date: '2026-10-23', log_type: 'training', value: 8, unit: 'sets', notes: 'Conditioning circuit' },
    { id: 7, client_id: 1, log_date: '2026-10-22', log_type: 'water', value: 2.1, unit: 'L', notes: 'Daily hydration total' },
  ],
  appointments: [
    { id: 1, client_id: 1, trainer_id: 2, scheduled_at: '2026-10-28T18:00', duration_minutes: 45, status: 'confirmed', meeting_url: '' },
    { id: 2, client_id: 4, trainer_id: 3, scheduled_at: '2026-10-29T10:30', duration_minutes: 60, status: 'pending', meeting_url: '' },
    { id: 3, client_id: 5, trainer_id: 2, scheduled_at: '2026-10-30T16:00', duration_minutes: 45, status: 'confirmed', meeting_url: '' },
    { id: 4, client_id: 1, trainer_id: 3, scheduled_at: '2026-11-02T18:00', duration_minutes: 30, status: 'pending', meeting_url: '' },
    { id: 5, client_id: 4, trainer_id: 2, scheduled_at: '2026-10-19T10:00', duration_minutes: 45, status: 'completed', meeting_url: '' },
  ],
};

/** Example values only, not sourced from Spoonacular or suitable as clinical nutrition data. */
export const demoFoods = [
  { id: 'banana', name: 'Banana', emoji: '🍌', calories: 89, protein: 1.1, carbs: 22.8, fat: 0.3 },
  { id: 'chicken', name: 'Chicken breast', emoji: '🍗', calories: 165, protein: 31, carbs: 0, fat: 3.6 },
  { id: 'oats', name: 'Oats', emoji: '🥣', calories: 389, protein: 16.9, carbs: 66.3, fat: 6.9 },
  { id: 'egg', name: 'Egg', emoji: '🥚', calories: 143, protein: 12.6, carbs: 0.7, fat: 9.5 },
  { id: 'rice', name: 'Cooked rice', emoji: '🍚', calories: 130, protein: 2.7, carbs: 28.2, fat: 0.3 },
  { id: 'avocado', name: 'Avocado', emoji: '🥑', calories: 160, protein: 2, carbs: 8.5, fat: 14.7 },
];
