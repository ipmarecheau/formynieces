# Mobile API Contract Draft

**Status:** Draft contract for the two-week Flutter MVP. These responses are intentionally screen-shaped so the app can move quickly without duplicating Laravel dashboard logic.

All endpoints are under `/api/mobile`. All authenticated endpoints return `401` when the token is missing or expired and `403` when the role is wrong for the requested resource.

---

## Auth

### POST `/api/mobile/login`

Request:

```json
{
  "email": "parent@example.com",
  "password": "secret",
  "device_name": "Isaac's iPhone"
}
```

Response:

```json
{
  "token": "plain-text-token-once",
  "user": {
    "id": 1,
    "name": "Maya Baptiste",
    "role": "parent"
  },
  "default_experience": "parent"
}
```

### GET `/api/mobile/me`

Response:

```json
{
  "user": { "id": 1, "name": "Maya Baptiste", "role": "parent" },
  "children": [
    { "id": 22, "name": "Aaliyah", "standard": "Standard 5" }
  ]
}
```

---

## Parent endpoints

### GET `/api/mobile/children`

Response:

```json
{
  "children": [
    {
      "id": 22,
      "name": "Aaliyah",
      "standard": "Standard 5",
      "latest_activity": "Practised Fractions today",
      "status": "needs_attention",
      "status_label": "Practise fractions next",
      "streak_days": 4
    }
  ]
}
```

### GET `/api/mobile/children/{child}/overview`

Response:

```json
{
  "child": { "id": 22, "name": "Aaliyah", "standard": "Standard 5" },
  "next_action": {
    "title": "Practise equivalent fractions next",
    "why": "She missed equivalent fraction questions twice this week.",
    "cta": "Assign 10-minute practice"
  },
  "weekly_summary": {
    "practice_minutes": 48,
    "sessions_completed": 5,
    "streak_days": 4,
    "last_practised_at": "2026-09-11T17:30:00-04:00"
  },
  "cards": {
    "weak_topics": 3,
    "writing_status": "Feedback ready",
    "readiness_status": "Building evidence"
  }
}
```

### GET `/api/mobile/children/{child}/weak-topics`

Response:

```json
{
  "topics": [
    {
      "id": 108,
      "subject": "Mathematics",
      "title": "Equivalent fractions",
      "reason": "2 misses in the last 3 attempts",
      "suggested_action": "Do one 10-minute practice set today",
      "severity": "high"
    }
  ]
}
```

### GET `/api/mobile/children/{child}/writing`

Response:

```json
{
  "status": "feedback_ready",
  "latest": {
    "prompt": "A surprise at the beach",
    "submitted_at": "2026-09-10T18:10:00-04:00",
    "summary": "Clear beginning and middle. Ending needs more development.",
    "skills": ["structure", "development", "vocabulary"]
  }
}
```

### GET `/api/mobile/children/{child}/readiness`

Response:

```json
{
  "status": "building_evidence",
  "headline": "Aaliyah needs more timed practice before we estimate readiness.",
  "evidence": [
    "5 practice sessions completed this week",
    "No full timed mock completed yet"
  ],
  "next_action": "Complete one timed mixed practice this weekend"
}
```

---

## Child endpoints

### GET `/api/mobile/child/today`

Response:

```json
{
  "child": { "id": 22, "name": "Aaliyah" },
  "smooth_message": "Today we fix fractions, then you earn progress on your voyage.",
  "mission": {
    "id": "daily-2026-09-11-22",
    "subject": "Mathematics",
    "topic": "Equivalent fractions",
    "estimated_minutes": 10,
    "cta": "Start mission"
  },
  "streak": { "days": 4, "label": "4-day streak" },
  "voyage": { "current_island": "Number Cove", "progress_label": "3 stars this week" }
}
```

### POST `/api/mobile/child/practice/start`

Request:

```json
{
  "mission_id": "daily-2026-09-11-22"
}
```

Response:

```json
{
  "session_id": 991,
  "total_questions": 5,
  "current_question": {
    "id": 501,
    "prompt": "Which fraction is equivalent to 1/2?",
    "choices": [
      { "id": "A", "text": "1/4" },
      { "id": "B", "text": "2/4" },
      { "id": "C", "text": "3/4" },
      { "id": "D", "text": "4/4" }
    ]
  }
}
```

### POST `/api/mobile/child/practice/{session}/answer`

Request:

```json
{
  "question_id": 501,
  "choice_id": "B"
}
```

Response:

```json
{
  "correct": true,
  "feedback": "Yes. 2/4 simplifies to 1/2.",
  "next_question": null,
  "progress": { "answered": 5, "total": 5 }
}
```

### POST `/api/mobile/child/practice/{session}/finish`

Response:

```json
{
  "accuracy": 80,
  "celebration": "Great work — you strengthened Fractions today.",
  "streak": { "days": 5, "changed": true },
  "next_action": "Return tomorrow for your next mission"
}
```
