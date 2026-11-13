@mvp
Feature: Persona-simulation agent — the app walked nightly by stand-in users
  To catch friction before a real family does, SmoothSeas runs a small agent every night that
  plays two stand-in users through the live app: an anxious parent and a child completing a
  full day's work (Captain's Orders and island mastery). Each run records what the persona
  noticed — a confusing step, a dead end, a slow page — as findings the team reviews and
  critiques in the admin panel. The agent has its own modest monthly budget, separate from
  the per-child AI budget, and simply skips a run once that budget is reached, so it can never
  run up a surprise bill.

  The engine that drives the browser and reasons about the experience is the live piece; the
  schedule, the budget, the recording of runs and findings, and the admin review are all
  exercised here against a stand-in simulator.

  @scenario:PA-01
  Scenario: A nightly run records its findings for the team
    Given the nightly agent runs
    When a persona finishes its walk through the app
    Then a run is recorded with what it found, how long it took, and what it cost

  @scenario:PA-02
  Scenario: The monthly budget caps the agent
    Given this month's persona spend has reached the budget
    When the nightly agent runs
    Then each persona is recorded as skipped for budget and nothing is spent

  @scenario:PA-03
  Scenario: Both personas are walked
    Given the nightly agent runs within budget
    Then both the anxious parent and the child's full day are walked and recorded

  @scenario:PA-04
  Scenario: The team critiques a finding
    Given a run has raised a finding
    When an admin reviews it and records a verdict and notes
    Then the finding carries that verdict and notes for the team

  @scenario:PA-05
  Scenario: Findings are actionable
    Given a persona raises a finding
    Then it carries a severity, an area, an observation, and (where it can) a suggestion

  @scenario:PA-06
  Scenario: A failed walk never stops the others
    Given one persona's walk errors out
    When the nightly agent runs
    Then that run is recorded as failed and the other persona still runs
