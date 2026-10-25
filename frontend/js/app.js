import { initialData, demoFoods } from "./mockData.js";

/** M1 SPA prototype. All records are local demo state; no requests to the project's backend. */
const routes = {
  dashboard: "Overview",
  plans: "Workout plans",
  exercises: "Exercise library",
  progress: "Progress tracker",
  appointments: "Appointments",
  nutrition: "Nutrition explorer",
  users: "User management",
  profile: "My profile",
  login: "Sign in",
  register: "Create account",
};
const editorDialog = document.getElementById("editorDialog");
const editorForm = document.getElementById("editorForm");
const demoRole = document.getElementById("demoRole");
const keys = Object.keys(initialData);
const clone = (obj) => JSON.parse(JSON.stringify(obj));
let data = clone(initialData);
let currentRole = sessionStorage.getItem("fittrack-demo-role") || "client";
let selectedFoodId = "banana";
let navSequence = 0;
let toastTimer;

try {
  const saved = JSON.parse(
    sessionStorage.getItem("fittrack-demo-data") || "null",
  );
  if (saved && keys.every((key) => Array.isArray(saved[key]))) data = saved;
} catch (_) {
  /* Invalid or blocked session storage: use initial demo data. */
}
if (!["client", "trainer", "admin"].includes(currentRole))
  currentRole = "client";
demoRole.value = currentRole;

const $ = (selector, within = document) => within.querySelector(selector);
const safe = (value) =>
  String(value ?? "").replace(
    /[&<>"']/g,
    (char) =>
      ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" })[
        char
      ],
  );
const title = (value) =>
  String(value || "").replace(/\b\w/g, (x) => x.toUpperCase());
const member = (id) => data.users.find((user) => user.id === Number(id));
const memberName = (id) => member(id)?.name || "Unassigned";
const currentUser = () =>
  data.users.find((user) => user.role === currentRole) || data.users[0];
const initials = (name) =>
  String(name || "?")
    .split(/\s+/)
    .slice(0, 2)
    .map((part) => part[0] || "")
    .join("")
    .toUpperCase();
const formatDate = (date) => {
  const parsed = new Date(String(date).length === 10 ? `${date}T12:00` : date);
  return Number.isNaN(parsed.getTime())
    ? String(date)
    : parsed.toLocaleDateString("en-GB", {
        day: "numeric",
        month: "short",
        year: "numeric",
      });
};
const formatDateTime = (date) =>
  `${formatDate(date)} · ${String(date).split("T")[1] || "—"}`;
const statsCard = (label, value, icon, footer = "Overview", unit = "") =>
  `<article class="card-ui stat-card"><div class="stat-top"><div class="stat-meta">${safe(label)}</div><div class="stat-icon"><i class="bi ${safe(icon)}"></i></div></div><div><div class="stat-value">${safe(value)}${unit ? ` <small>${safe(unit)}</small>` : ""}</div><span class="stat-growth">${safe(footer)}</span></div></article>`;
const chip = (status) =>
  `<span class="tiny-chip ${status === "pending" ? "gold" : status === "completed" || status === "admin" ? "blue" : status === "cancelled" ? "ash" : ""}">${safe(title(status))}</span>`;
const buttons = (entity, id) =>
  `<div class="btn-row"><button class="btn-icon-mini" type="button" data-action="edit" data-entity="${entity}" data-id="${id}" aria-label="Edit"><i class="bi bi-pencil-square"></i></button><button class="btn-icon-mini" type="button" data-action="delete" data-entity="${entity}" data-id="${id}" aria-label="Delete"><i class="bi bi-trash3"></i></button></div>`;
const persist = () => {
  try {
    sessionStorage.setItem("fittrack-demo-data", JSON.stringify(data));
  } catch (_) {
    /* Storage unavailable; in-memory demo still works. */
  }
};
function toast(message) {
  const node = $("#toast");
  node.textContent = message;
  node.classList.add("visible");
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => node.classList.remove("visible"), 3800);
}
function closeMenu() {
  $("#sidebar").classList.remove("open");
  $("#sidebarBackdrop").classList.remove("open");
}

async function navigate() {
  const route = location.hash.slice(2).split("?")[0] || "dashboard";
  if (!Object.hasOwn(routes, route)) {
    location.hash = "#/dashboard";
    return;
  }
  const request = ++navSequence;
  const isAuth = route === "login" || route === "register";
  $("#appShell").classList.toggle("d-none", isAuth);
  $("#authShell").classList.toggle("d-none", !isAuth);
  $(".topbar-current").textContent = routes[route];
  document.title = `${routes[route]} | FitTrack Connect`;
  document.querySelectorAll("[data-route-link]").forEach((link) => {
    const active = link.dataset.routeLink === route;
    link.classList.toggle("active", active);
    if (active) link.setAttribute("aria-current", "page");
    else link.removeAttribute("aria-current");
  });
  closeMenu();
  const target = isAuth ? $("#authView") : $("#appView");
  target.innerHTML =
    '<div class="muted" style="padding:35px">Loading view…</div>';
  try {
    const response = await fetch(`./views/${route}.html`, {
      cache: "no-store",
    });
    if (!response.ok)
      throw Error(`Could not load the ${route} view (${response.status})`);
    const html = await response.text();
    if (request !== navSequence) return; // Ignore slower, outdated navigation requests.
    target.innerHTML = html;
    render(route);
    window.scrollTo({ top: 0, behavior: "instant" });
  } catch (error) {
    if (request !== navSequence) return;
    target.innerHTML = `<section class="card-ui pad-card"><h2>View unavailable</h2><p>Please run FitTrack from a local web server (not file://).</p><p class="muted">${safe(error.message)}</p></section>`;
  }
}
function render(route) {
  if (route === "dashboard") renderDashboard();
  if (route === "plans") renderPlans();
  if (route === "exercises") renderExercises();
  if (route === "progress") renderProgress();
  if (route === "appointments") renderAppointments();
  if (route === "users") renderUsers();
  if (route === "nutrition") renderNutrition();
  if (route === "profile") renderProfile();
  const avatar = $(".avatar-circle");
  avatar.textContent = initials(currentUser()?.name);
}
function rerender() {
  render(location.hash.slice(2) || "dashboard");
}

function renderDashboard() {
  const user = currentUser();
  $("#dashboardGreeting").textContent =
    `${user?.name?.split(" ")[0] || "Athlete"} 👋`;
  const assigned = data.workout_plans.filter(
    (plan) => currentRole !== "client" || plan.client_id === user.id,
  );
  const upcoming = data.appointments.filter(
    (item) => item.status !== "completed" && item.status !== "cancelled",
  );
  $("#dashboardStats").innerHTML = [
    statsCard(
      "ACTIVE PLANS",
      assigned.length,
      "bi-journal-check",
      "Your programs",
    ),
    statsCard(
      "EXERCISE LIBRARY",
      data.exercises.length,
      "bi-lightning-charge",
      "Movements ready",
    ),
    statsCard(
      "PROGRESS ENTRIES",
      data.progress_logs.length,
      "bi-graph-up-arrow",
      "Logs recorded",
    ),
    statsCard(
      "UPCOMING SESSIONS",
      upcoming.length,
      "bi-calendar-event",
      "Virtual coaching",
    ),
  ].join("");
  const values = [38, 65, 28, 85, 48, 94, 58];
  $("#weeklySessions").textContent = "4 sessions";
  $("#activityChart").innerHTML = values
    .map(
      (height, i) =>
        `<div class="bar-wrap ${i === 5 ? "highlight" : ""}"><div class="bar-track"><div class="bar-fill" style="height:${height}%" title="Demo activity"></div></div><span class="bar-label">${["M", "T", "W", "T", "F", "S", "S"][i]}</span></div>`,
    )
    .join("");
  $("#goalProgress").innerHTML = [
    ["Workout sessions", 75, "3 of 4"],
    ["Hydration goal", 80, "2.4 / 3 L"],
    ["Nutrition consistency", 60, "3 of 5 days"],
  ]
    .map(
      ([name, percentage, value]) =>
        `<div class="progress-row"><div class="progress-row-label"><span>${safe(name)}</span><span class="muted">${safe(value)}</span></div><div class="progress"><div class="progress-bar" role="progressbar" style="width:${percentage}%" aria-valuenow="${percentage}" aria-valuemin="0" aria-valuemax="100"></div></div></div>`,
    )
    .join("");
  $("#dashboardPlans").innerHTML = assigned.length
    ? assigned
        .slice(0, 3)
        .map(
          (plan) =>
            `<li class="list-entry"><span class="list-icon"><i class="bi bi-activity"></i></span><div class="flex-fill"><strong>${safe(plan.title)}</strong><small>${safe(plan.focus)} · ${plan.sessions} days/week</small></div><span class="tiny-chip">Active</span></li>`,
        )
        .join("")
    : '<li class="empty-message">No assigned workout plans yet.</li>';
  $("#dashboardAppointments").innerHTML =
    upcoming
      .slice(0, 3)
      .map(
        (item) =>
          `<li class="list-entry"><span class="list-icon"><i class="bi bi-camera-video"></i></span><div class="flex-fill"><strong>${safe(memberName(item.trainer_id))}</strong><small>${safe(formatDateTime(item.scheduled_at))}</small></div>${chip(item.status)}</li>`,
      )
      .join("") || '<li class="empty-message">No upcoming sessions.</li>';
}
function renderPlans(filter = "") {
  const matches = data.workout_plans.filter((plan) =>
    `${plan.title} ${plan.focus} ${plan.level} ${memberName(plan.client_id)}`
      .toLowerCase()
      .includes(filter.toLowerCase()),
  );
  $("#plansCount").textContent = `${matches.length} programs available`;
  $("#planCards").innerHTML =
    matches
      .map((plan) => {
        const assigned = data.plan_exercises.filter(
          (link) => link.plan_id === plan.id,
        );
        return `<article class="card-ui plan-tile"><div class="d-flex align-items-center justify-content-between"><span class="plan-tag">${safe(plan.level).toUpperCase()} PROGRAM</span><span class="tiny-chip">${plan.sessions}× / week</span></div><div><h3>${safe(plan.title)}</h3><p>${safe(plan.notes)}</p></div><div class="d-flex gap-3 flex-wrap" style="font-size:11px;color:#7e8d9c"><span><i class="bi bi-bullseye me-1"></i>${safe(plan.focus)}</span><span><i class="bi bi-lightning-charge me-1"></i>${assigned.length} exercises</span></div><div style="font-size:11px;color:#7c8a9a"><strong style="color:#344358">Coach:</strong> ${safe(memberName(plan.trainer_id))}<br><strong style="color:#344358">Client:</strong> ${safe(memberName(plan.client_id))}</div><p style="font-size:11px;color:#72962d;margin:0"><i class="bi bi-egg-fried me-1"></i>${safe(plan.nutrition_guidance || "No nutrition guidance yet")}</p><div class="plan-footer"><button class="btn btn-soft btn-sm" data-action="assign" data-id="${plan.id}"><i class="bi bi-list-check me-1"></i> Plan exercises</button>${buttons("workout_plans", plan.id)}</div></article>`;
      })
      .join("") ||
    '<div class="card-ui empty-message">No plans match the search.</div>';
}
function renderExercises(filter = "") {
  const matches = data.exercises.filter((ex) =>
    `${ex.name} ${ex.muscle_group} ${ex.equipment}`
      .toLowerCase()
      .includes(filter.toLowerCase()),
  );
  $("#exercisesCount").textContent = matches.length;
  $("#exerciseCards").innerHTML =
    matches
      .map(
        (ex) =>
          `<article class="card-ui resource-card"><div class="d-flex justify-content-between align-items-start"><span class="resource-icon"><i class="bi ${safe(ex.icon || "bi-lightning-charge")}"></i></span><span class="tiny-chip blue">${safe(ex.difficulty)}</span></div><div><h3>${safe(ex.name)}</h3><p>${safe(ex.description)}</p></div><div class="resource-meta"><span>${safe(ex.muscle_group)} · ${safe(ex.equipment)}</span>${buttons("exercises", ex.id)}</div></article>`,
      )
      .join("") ||
    '<div class="card-ui empty-message">No matching exercises.</div>';
}
function renderProgress() {
  const weightLogs = data.progress_logs.filter(
    (log) => log.log_type === "weight",
  );
  const waterLogs = data.progress_logs.filter(
    (log) => log.log_type === "water",
  );
  const trainingLogs = data.progress_logs.filter(
    (log) => log.log_type === "training",
  );
  const latestWeight = weightLogs
    .filter((log) => log.client_id === 1)
    .sort((a, b) => b.log_date.localeCompare(a.log_date))[0];
  $("#progressStats").innerHTML = [
    statsCard(
      "LATEST WEIGHT",
      latestWeight?.value ?? "—",
      "bi-speedometer2",
      "Latest measurement",
      latestWeight?.unit || "",
    ),
    statsCard(
      "HYDRATION ENTRIES",
      waterLogs.length,
      "bi-droplet",
      "Daily water logs",
    ),
    statsCard(
      "TRAINING LOGS",
      trainingLogs.length,
      "bi-activity",
      "Workout events",
    ),
    statsCard(
      "TOTAL ENTRIES",
      data.progress_logs.length,
      "bi-clipboard-data",
      "All metric types",
    ),
  ].join("");
  $("#progressRows").innerHTML =
    [...data.progress_logs]
      .sort((a, b) => b.log_date.localeCompare(a.log_date))
      .map(
        (log) =>
          `<tr><td>${safe(formatDate(log.log_date))}</td><td class="table-primary">${safe(memberName(log.client_id))}</td><td>${chip(log.log_type)}</td><td class="table-primary">${safe(log.value)} ${safe(log.unit)}</td><td class="muted">${safe(log.notes || "—")}</td><td>${buttons("progress_logs", log.id)}</td></tr>`,
      )
      .join("") ||
    '<tr><td colspan="6" class="empty-message">No progress logs yet.</td></tr>';
}
function renderAppointments() {
  $("#appointmentStats").innerHTML = [
    statsCard(
      "ALL APPOINTMENTS",
      data.appointments.length,
      "bi-calendar2-week",
      "Total scheduled",
    ),
    statsCard(
      "CONFIRMED",
      data.appointments.filter((a) => a.status === "confirmed").length,
      "bi-check2-circle",
      "Ready to go",
    ),
    statsCard(
      "PENDING",
      data.appointments.filter((a) => a.status === "pending").length,
      "bi-hourglass-split",
      "Awaiting confirmation",
    ),
    statsCard(
      "COMPLETED",
      data.appointments.filter((a) => a.status === "completed").length,
      "bi-trophy",
      "Previous sessions",
    ),
  ].join("");
  $("#appointmentRows").innerHTML =
    [...data.appointments]
      .sort((a, b) => a.scheduled_at.localeCompare(b.scheduled_at))
      .map(
        (item) =>
          `<tr><td class="table-primary">${safe(formatDateTime(item.scheduled_at))}</td><td>${safe(memberName(item.client_id))}</td><td>${safe(memberName(item.trainer_id))}</td><td>${safe(item.duration_minutes)} min</td><td>${chip(item.status)}</td><td>${buttons("appointments", item.id)}</td></tr>`,
      )
      .join("") ||
    '<tr><td colspan="6" class="empty-message">No appointments yet.</td></tr>';
}
function renderUsers() {
  $("#userStats").innerHTML = [
    statsCard(
      "TOTAL USERS",
      data.users.length,
      "bi-people",
      "Registered users",
    ),
    statsCard(
      "CLIENTS",
      data.users.filter((u) => u.role === "client").length,
      "bi-person",
      "Active trainees",
    ),
    statsCard(
      "TRAINERS",
      data.users.filter((u) => u.role === "trainer").length,
      "bi-person-badge",
      "Coaching team",
    ),
    statsCard(
      "ADMINS",
      data.users.filter((u) => u.role === "admin").length,
      "bi-shield-check",
      "Management team",
    ),
  ].join("");
  $("#userRows").innerHTML =
    data.users
      .map(
        (user) =>
          `<tr><td><div class="d-flex gap-2 align-items-center"><span class="avatar-mini">${safe(initials(user.name))}</span><div><div class="table-primary">${safe(user.name)}</div><div class="table-sub">${safe(user.email)}</div></div></div></td><td>${chip(user.role)}</td><td>${safe(formatDate(user.joined))}</td><td>${buttons("users", user.id)}</td></tr>`,
      )
      .join("") ||
    '<tr><td colspan="4" class="empty-message">No users yet.</td></tr>';
}
function renderProfile() {
  const user = currentUser();
  $("#profileIdentity").innerHTML =
    `<span class="profile-avatar">${safe(initials(user.name))}</span><div><div class="page-eyebrow">YOUR ACCOUNT</div><h2>${safe(user.name)}</h2>${chip(user.role)}</div>`;
  $("#profileData").innerHTML = [
    ["Full name", user.name],
    ["Email", user.email],
    ["Account role", title(user.role)],
    ["Member since", formatDate(user.joined)],
  ]
    .map(
      ([field, value]) =>
        `<div class="settings-line"><span>${safe(field)}</span><strong>${safe(value)}</strong></div>`,
    )
    .join("");
}
function renderNutrition(filter = "") {
  const chosen =
    demoFoods.find((food) => food.id === selectedFoodId) || demoFoods[0];
  $("#foodChoices").innerHTML =
    demoFoods
      .filter((food) => food.name.toLowerCase().includes(filter.toLowerCase()))
      .map(
        (food) =>
          `<button class="food-choice ${food.id === chosen.id ? "active" : ""}" data-action="pick-food" data-id="${safe(food.id)}" type="button"><span class="food-glyph">${safe(food.emoji)}</span><span><strong>${safe(food.name)}</strong><small>${food.calories} kcal / 100 g</small></span></button>`,
      )
      .join("") || '<div class="empty-message">No matching foods found.</div>';
  $("#foodBigIcon").textContent = chosen.emoji;
  $("#selectedFoodTitle").textContent = chosen.name;
  $("#macroDetails").innerHTML = [
    ["Calories", chosen.calories, "kcal"],
    ["Protein", chosen.protein, "g"],
    ["Carbs", chosen.carbs, "g"],
    ["Fat", chosen.fat, "g"],
  ]
    .map(
      ([name, value, unit]) =>
        `<div class="macro-box"><strong>${value}</strong><span>${name} (${unit})</span></div>`,
    )
    .join("");
}

const peopleOptions = (role) =>
  data.users
    .filter((user) => user.role === role)
    .map((user) => ({ value: user.id, label: user.name }));
const fieldsByEntity = {
  users: [
    { key: "name", label: "Full name", required: true },
    { key: "email", label: "Email", type: "email", required: true },
    {
      key: "role",
      label: "Role",
      options: ["client", "trainer", "admin"],
      required: true,
    },
  ],
  workout_plans: [
    { key: "title", label: "Plan name", required: true },
    { key: "focus", label: "Focus", required: true },
    {
      key: "level",
      label: "Difficulty",
      options: ["Beginner", "Intermediate", "Advanced", "All levels"],
    },
    {
      key: "sessions",
      label: "Sessions per week",
      type: "number",
      min: 1,
      max: 7,
      required: true,
    },
    {
      key: "trainer_id",
      label: "Assigned trainer",
      options: () => peopleOptions("trainer"),
      required: true,
    },
    {
      key: "client_id",
      label: "Assigned client",
      options: () => peopleOptions("client"),
      required: true,
    },
    { key: "notes", label: "Training notes", type: "textarea" },
    {
      key: "nutrition_guidance",
      label: "Nutrition / meal guidance",
      type: "textarea",
    },
  ],
  exercises: [
    { key: "name", label: "Exercise name", required: true },
    {
      key: "muscle_group",
      label: "Muscle group",
      options: ["Chest", "Back", "Legs", "Shoulders", "Arms", "Core"],
    },
    { key: "equipment", label: "Equipment" },
    {
      key: "difficulty",
      label: "Difficulty",
      options: ["Beginner", "Intermediate", "Advanced"],
    },
    { key: "description", label: "Coaching notes", type: "textarea" },
  ],
  progress_logs: [
    {
      key: "client_id",
      label: "Client",
      options: () => peopleOptions("client"),
      required: true,
    },
    { key: "log_date", label: "Log date", type: "date", required: true },
    {
      key: "log_type",
      label: "Metric",
      options: ["weight", "water", "training", "food"],
      required: true,
    },
    {
      key: "value",
      label: "Value",
      type: "number",
      min: 0,
      step: "any",
      required: true,
    },
    { key: "unit", label: "Unit (e.g. kg, L, sets, kcal)", required: true },
    { key: "notes", label: "Notes", type: "textarea" },
  ],
  appointments: [
    {
      key: "client_id",
      label: "Client",
      options: () => peopleOptions("client"),
      required: true,
    },
    {
      key: "trainer_id",
      label: "Trainer",
      options: () => peopleOptions("trainer"),
      required: true,
    },
    {
      key: "scheduled_at",
      label: "Start time",
      type: "datetime-local",
      required: true,
    },
    {
      key: "duration_minutes",
      label: "Duration (minutes)",
      type: "number",
      min: 15,
      max: 240,
      required: true,
    },
    {
      key: "status",
      label: "Status",
      options: ["pending", "confirmed", "completed", "cancelled"],
    },
    { key: "meeting_url", label: "Virtual meeting link", type: "url" },
  ],
};
function fieldHtml(field, value) {
  const fieldId = `edit-${field.key}`;
  const required = field.required ? "required" : "";
  if (field.options) {
    const options =
      typeof field.options === "function"
        ? field.options()
        : field.options.map((item) => ({ value: item, label: item }));
    return `<label for="${fieldId}">${safe(field.label)}<select id="${fieldId}" class="form-select" name="${field.key}" ${required}>${options.map((item) => `<option value="${safe(item.value)}" ${String(value ?? "") === String(item.value) ? "selected" : ""}>${safe(item.label)}</option>`).join("")}</select></label>`;
  }
  if (field.type === "textarea")
    return `<label for="${fieldId}">${safe(field.label)}<textarea id="${fieldId}" class="form-control" name="${field.key}" rows="2">${safe(value)}</textarea></label>`;
  return `<label for="${fieldId}">${safe(field.label)}<input id="${fieldId}" class="form-control" name="${field.key}" type="${field.type || "text"}" value="${safe(value)}" ${field.min === undefined ? "" : `min="${field.min}"`} ${field.max === undefined ? "" : `max="${field.max}"`} ${field.step ? `step="${safe(field.step)}"` : ""} ${required}></label>`;
}
function openEditor(entity, id = null) {
  if (!Object.hasOwn(fieldsByEntity, entity)) return;
  const item = data[entity].find((record) => record.id === id) || {};
  editorForm.dataset.entity = entity;
  editorForm.dataset.id = id ?? "";
  editorForm.dataset.mode = "edit";
  $("#editorTitle").textContent =
    `${id ? "Edit" : "Add"} ${entity.replaceAll("_", " ").replace(/s$/, "")}`;
  $("#editorFields").innerHTML = fieldsByEntity[entity]
    .map((field) => fieldHtml(field, item[field.key]))
    .join("");
  editorDialog.showModal();
}
function openAssignments(planId) {
  const plan = data.workout_plans.find((record) => record.id === planId);
  if (!plan) return;
  editorForm.dataset.entity = "plan_exercises";
  editorForm.dataset.id = planId;
  editorForm.dataset.mode = "assignment";
  $("#editorTitle").textContent = `Exercises · ${plan.title}`;
  const assigned = data.plan_exercises
    .filter((item) => item.plan_id === planId)
    .map((item) => item.exercise_id);
  $("#editorFields").innerHTML =
    `<p class="muted" style="font-size:12px">Select the movements included in this plan .</p>` +
    data.exercises
      .map(
        (item) =>
          `<label class="d-flex align-items-center gap-2" style="font-weight:600"><input class="form-check-input m-0" type="checkbox" name="exercise" value="${item.id}" ${assigned.includes(item.id) ? "checked" : ""}><span>${safe(item.name)}</span></label>`,
      )
      .join("");
  editorDialog.showModal();
}
function submitEditor() {
  const entity = editorForm.dataset.entity,
    id = Number(editorForm.dataset.id);
  if (entity === "plan_exercises") {
    const selected = [
      ...editorForm.querySelectorAll('[name="exercise"]:checked'),
    ].map((node) => Number(node.value));
    data.plan_exercises = data.plan_exercises.filter(
      (link) => link.plan_id !== id,
    );
    data.plan_exercises.push(
      ...selected.map((exercise_id) => ({
        plan_id: id,
        exercise_id,
        sets: 3,
        reps: 10,
      })),
    );
    persist();
    editorDialog.close();
    rerender();
    toast("Plan exercises updated.");
    return;
  }
  const obj = {};
  const formData = new FormData(editorForm);
  for (const field of fieldsByEntity[entity]) {
    const raw = String(formData.get(field.key) ?? "").trim();
    obj[field.key] =
      (field.type === "number" || field.key.endsWith("_id")) && raw !== ""
        ? Number(raw)
        : raw;
  }
  if (entity === "users") {
    const duplicate = data.users.some(
      (user) =>
        user.email.toLowerCase() === obj.email.toLowerCase() && user.id !== id,
    );
    if (duplicate) {
      toast("This email is already used.");
      return;
    }
    obj.joined =
      data.users.find((user) => user.id === id)?.joined ||
      new Date().toISOString().slice(0, 10);
  }
  if (entity === "exercises")
    obj.icon =
      data.exercises.find((ex) => ex.id === id)?.icon || "bi-lightning-charge";
  if (id) {
    const index = data[entity].findIndex((record) => record.id === id);
    if (index < 0) return;
    data[entity][index] = { ...data[entity][index], ...obj };
  } else {
    obj.id = Math.max(0, ...data[entity].map((record) => record.id)) + 1;
    data[entity].push(obj);
  }
  persist();
  editorDialog.close();
  rerender();
  toast("Saved to browser.");
}
function removeRecord(entity, id) {
  const record = data[entity]?.find((item) => item.id === id);
  if (!record) return;
  if (entity === "users") {
    const referenced =
      data.workout_plans.some(
        (p) => p.trainer_id === id || p.client_id === id,
      ) ||
      data.progress_logs.some((log) => log.client_id === id) ||
      data.appointments.some((a) => a.trainer_id === id || a.client_id === id);
    if (referenced) {
      toast("Cannot delete a user assigned to a plan, log or appointment.");
      return;
    }
  }
  if (!window.confirm("Delete this record?")) return;
  data[entity] = data[entity].filter((item) => item.id !== id);
  if (entity === "workout_plans")
    data.plan_exercises = data.plan_exercises.filter(
      (item) => item.plan_id !== id,
    );
  if (entity === "exercises")
    data.plan_exercises = data.plan_exercises.filter(
      (item) => item.exercise_id !== id,
    );
  persist();
  rerender();
  toast("Record deleted.");
}

window.addEventListener("hashchange", navigate);
demoRole.addEventListener("change", () => {
  currentRole = demoRole.value;
  try {
    sessionStorage.setItem("fittrack-demo-role", currentRole);
  } catch (_) {
    /* optional */
  }
  rerender();
  toast(`Switched to ${title(currentRole)} view.`);
});
document.addEventListener("click", (event) => {
  const node = event.target.closest("[data-action]");
  if (!node) return;
  const action = node.dataset.action;
  if (action === "toggle-menu") {
    $("#sidebar").classList.toggle("open");
    $("#sidebarBackdrop").classList.toggle("open");
  }
  if (action === "close-menu") closeMenu();
  if (action === "close-dialog") editorDialog.close();
  if (action === "add") openEditor(node.dataset.entity);
  if (action === "edit")
    openEditor(node.dataset.entity, Number(node.dataset.id));
  if (action === "delete")
    removeRecord(node.dataset.entity, Number(node.dataset.id));
  if (action === "assign") openAssignments(Number(node.dataset.id));
  if (action === "pick-food") {
    selectedFoodId = node.dataset.id;
    renderNutrition($("#foodSearch").value);
  }
  if (action === "log-food") {
    const chosen = demoFoods.find((item) => item.id === selectedFoodId);
    const client = data.users.find((user) => user.role === "client");
    data.progress_logs.push({
      id: Math.max(0, ...data.progress_logs.map((x) => x.id)) + 1,
      client_id: client.id,
      log_date: new Date().toISOString().slice(0, 10),
      log_type: "food",
      value: chosen.calories,
      unit: "kcal",
      notes: `${chosen.name} · 100 g (sample)`,
    });
    persist();
    toast("Food added to progress logs.");
  }
});
document.addEventListener("input", (event) => {
  if (event.target.dataset.search === "plans") renderPlans(event.target.value);
  if (event.target.dataset.search === "exercises")
    renderExercises(event.target.value);
  if (event.target.id === "foodSearch") renderNutrition(event.target.value);
});
editorForm.addEventListener("submit", (event) => {
  event.preventDefault();
  if (editorForm.reportValidity()) submitEditor();
});
document.addEventListener("submit", (event) => {
  const form = event.target.closest("[data-auth-form]");
  if (!form) return;
  event.preventDefault();
  if (!form.reportValidity()) return;
  if (form.dataset.authForm === "login") {
    currentRole = $("#roleLogin").value;
    demoRole.value = currentRole;
    try {
      sessionStorage.setItem("fittrack-demo-role", currentRole);
    } catch (_) {
      /* optional */
    }
    location.hash = "#/dashboard";
    toast("Welcome to FitTrack Connect!");
  } else {
    const name = $("#registerName").value.trim(),
      email = $("#registerEmail").value.trim();
    if (
      data.users.some(
        (user) => user.email.toLowerCase() === email.toLowerCase(),
      )
    ) {
      toast("Account email already exists.");
      return;
    }
    data.users.push({
      id: Math.max(...data.users.map((u) => u.id)) + 1,
      name,
      email,
      role: "client",
      joined: new Date().toISOString().slice(0, 10),
    });
    persist();
    currentRole = "client";
    demoRole.value = currentRole;
    location.hash = "#/dashboard";
    toast("Account created successfully.");
  }
});
if (!location.hash) location.hash = "#/dashboard";
else navigate();
