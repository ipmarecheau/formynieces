@mvp @mobile
Feature: Local reminders — the day's voyage, nudged from the child's own device
  A child keeps the habit when the next thing to do finds them, not the other way around.
  SmoothSeas schedules a small set of gentle reminders directly on the child's phone — no
  server, no internet, no account with Apple or Google — so they fire even when the app is
  closed and the phone is offline. Four reminders carry the day: a morning muster when the
  Captain's Orders are ready, a writing mission on writing days, an evening nudge when the
  day's work is still unfinished, and a late streak-saver when the daily minimum has not been
  met. A guardian stays in control: they can switch reminders off, and quiet hours are never
  broken.

  Because a local notification is scheduled ahead of time with fixed words, the app computes
  the next week of reminders from the real daily plan and writes them to the device on launch,
  replacing the previous set so nothing duplicates or goes stale. The conditional reminders
  (outstanding tasks, streak-saver) are skipped for a day already finished and are cancelled
  the moment the child completes the day — so a child who has done their work is never nagged.

  This is the on-device alternative to push: it covers "do today's work" entirely on its own,
  and needs only the local-notifications plugin compiled into the Android app.

  @scenario:LR-01
  Scenario: The next week of reminders is written to the device on launch
    Given a child opens the native app
    When the app asks the server for the reminder plan for the next seven days
    Then it receives a self-contained plan it can schedule on the device
    And scheduling it replaces the previous set so no reminder duplicates or goes stale

  @scenario:LR-02
  Scenario: Morning Muster greets every day
    Given the reminder plan is built for a child
    Then every day carries a Morning Muster reminder in the afternoon when orders are ready

  @scenario:LR-03
  Scenario: Writing Mission lands only on writing days
    Given the reminder plan is built across a full week
    Then a Writing Mission reminder appears only on writing days (Mon, Wed, Fri)
    And no Writing Mission is scheduled on a non-writing day

  @scenario:LR-04
  Scenario: The evening Outstanding-Tasks nudge is only for unfinished work
    Given a child still has work left for today
    Then an Outstanding-Tasks reminder is scheduled for this evening
    But when the child has already finished the whole day, today's Outstanding-Tasks reminder is not scheduled

  @scenario:LR-05
  Scenario: The late Streak-Saver fires only when the minimum is unmet
    Given a child has not yet met today's daily minimum
    Then a late Streak-Saver reminder is scheduled to protect the streak
    But a child who has met the minimum gets no Streak-Saver for today

  @scenario:LR-06
  Scenario: The guardian stays in control
    Given a guardian has switched reminders off, or it is inside the child's quiet hours
    Then no reminder is scheduled that the guardian silenced
    And no reminder is ever placed inside quiet hours

  @scenario:LR-07
  Scenario: Reminders fire offline, with the app closed, no server call at fire time
    Given the reminder plan has been written to the device
    Then each reminder carries its own time and words so it fires without the app running
    And without any further call to the server
