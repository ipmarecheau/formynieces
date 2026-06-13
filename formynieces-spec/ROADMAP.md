# ForMyNieces — Roadmap

**Anchor date:** SEA 2027 sits in late April / early May 2027 (exact date set annually by MoE — confirm when published). Working backwards with the 6-week revision buffer, **new-content learning must start by late September 2026** for a full 24 teaching weeks + 6 revision weeks; later starts use the S4 late-joiner compression.

---

## Phase 0 — Stabilise the foundation (now → end June 2026)
*The platform cannot take a real child's data until this is done.*

- [ ] Resolve the SQLite `job_batches` migration blocker (clean rebuild with `docker rmi`).
- [ ] **Database persistence** — mount SQLite as a volume with proper initialisation OR nightly backup cron. A redeploy must never erase a child's progress. *(Hard gate for Phase 1.)*
- [ ] Apply the 69 pending system updates (5 security).
- [ ] Domain + HTTPS (Caddy or Certbot). Guardians will not trust an IP-and-port URL with a child's name in it — and they shouldn't.
- [ ] GitHub Actions deploy on push (`.github/workflows/deploy.yml`).
- [ ] Apply Slice 1 migrations; verify remap; record per-strand module counts.

## Phase 1 — MVP: the nieces onboard (July 2026)
*Everything tagged `@mvp`. Ship gate: one real student completes onboarding → reveal → two full weekly cycles.*

- [ ] Slice 2: prerequisite graph + anchor bank (~30 items from past papers, misconception distractors).
- [ ] Slice 3: DiagnosticService — adaptive walk, guessing guard, propagation, target seeding. **Unit-tested first; this is the highest-risk logic.**
- [ ] Slice 4: onboarding screens (B1–B7) incl. resumable sessions and the animated reveal.
- [ ] `writing_submissions` table + weekly prompt + Groq rubric feedback + graceful queue on rate-limit.
- [ ] Weekly target rollover job (Sunday) with rollover cap.
- [ ] Guardian dashboard answering the four Sunday questions (D1–D3).
- [ ] Streak (motivational layer) + two-layer separation enforced in routing.
- [ ] Decide + implement `in_review` status semantics.
- [ ] Pilot: onboard the nieces; watch two full weeks; fix what reality breaks.

## Phase 2 — v1.1: a real month of use (August 2026)
*Everything tagged `@v1.1`. Theme: durability of engagement.*

- [ ] **Author Writing modules** (currently zero) so the writing track has curriculum behind it.
- [ ] Weekly guardian email digest + gentle disengagement nudges (S7).
- [ ] Pause/resume with streak freeze and shame-free re-pacing (S6).
- [ ] Diagnostic retake (guardian-initiated).
- [ ] Mastery decay → `in_review` resurfacing via the exam agent.
- [ ] Writing rubric trend view + guardian writing view.
- [ ] Filament: AnchorQuestionResource + diagnostics monitor.
- [ ] Phone verification for guardians.

## Phase 3 — Exam readiness (September → December 2026)
*Tagged `@roadmap`. Theme: converging on the real exam's shape.*

- [ ] Fill-in answer input mode for Math practice (real SEA format).
- [ ] Timed past-paper mocks feeding the agent readiness view (never the map).
- [ ] Deeper ELA Section II practice (fiction/non-fiction/poetry/graphic passages per the 13/13/8 mark split).
- [ ] Adaptive layer v2: agent quietly routes weekly targets at weak strands (S5 refinement).
- [ ] Performance + Groq quota review under real usage (cache insights per student-week).

## Phase 4 — Revision buffer & the exam (January → May 2027)
- [ ] Revision-mode map variant (weakest mastered modules resurface; no new content).
- [ ] Exam-week calm state + guardian "what to do this week" note.
- [ ] Post-exam celebration + journey summary.
- [ ] Second-guardian read-only access (S8) — before this, decide if other families join.
- [ ] If opening to more families: multi-child guardians, onboarding hardening, and a real backup/restore drill.

---

## Standing risks

| Risk | Mitigation |
|---|---|
| DB wiped on redeploy | Phase 0 hard gate; do not onboard a real child before it |
| Groq free-tier limits under growth | Per-student-week insight cache; queue writing scoring; model swap is one env var |
| Solo-developer bus factor | This spec suite + handoffs are the continuity plan; keep them current |
| Anchor bank quality | Misconception distractors reviewed against real student responses in Phase 1 pilot |
| MC-only diagnostic diverges from real exam | Deliberate and documented; Phase 3 fill-in mode closes the gap |
