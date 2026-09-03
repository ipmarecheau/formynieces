@ops @quality
Feature: Quality control & observability
  SmoothSeas is specified behaviour-first: every capability is a Gherkin scenario with an id
  (LL-14, WT-03, …). This feature closes the loop the other way — it makes the RUNNING app
  report against those same scenarios, so a deviation from the spec in production is caught,
  attributed, and alerted on, not discovered weeks later by a parent.

  The mechanism is one scenario-tagged event bus. At each spec-critical step the code records a
  structured event keyed to the scenario it realises — reteach.started tagged LL-14, target.met
  tagged WT-03. That single stream is fanned out to three sinks: an always-on JSON log (greppable
  by scenario id), invariant guards that assert the Gherkin Then still holds at runtime, and a
  behavioural-analytics provider that answers the OTHER question operators ask — how children
  actually move through the app, where they linger, and where they drop off.

  Because the users are primary-school children, observability here carries a duty of care:
  events describe behaviour with IDs and enums, never a child's personal data, and any
  behavioural analytics or session capture of a minor is gated on guardian consent. Correctness
  and curiosity must never cost a child their privacy.

  Background:
    Given the app records scenario-tagged learning events
    And an operator can query them by scenario id

  # --------------------------------------------------------- the event bus (realised)

  @scenario:QC-01
  Scenario: A spec-critical step records a scenario-tagged event
    Given a student has missed two questions in a row on both attempts
    When she is pulled into an AI-assisted re-teach
    Then a "reteach.started" event is recorded
    And it is tagged with the scenario it realises, "LL-14"
    And it carries the module and trigger, but no personal data

  # --------------------------------------------------------- deviation detection

  @scenario:QC-02
  Scenario: An expected outcome that never fires is flagged as a deviation
    Given a scenario's Then is expected to fire within a bounded window
    When the precondition occurs but the outcome event is never recorded
    Then a spec-deviation is raised naming the scenario id
    And the operator is alerted with the offending student and module

  @scenario:QC-03
  Scenario: An outcome firing without its precondition is flagged as a deviation
    Given an outcome event may only occur after its precondition event
    When the outcome is recorded with no matching precondition
    Then a spec-deviation is raised naming the scenario id
    And the event pair is preserved for inspection

  # --------------------------------------------------------- behavioural analytics

  @scenario:QC-04
  Scenario: Student navigation is captured so paths and hotspots can be seen
    Given a student moves through lessons, practice and the map
    When she visits and leaves each page
    Then her navigation is captured as events an operator can assemble into paths
    And drop-off points and hotspots are visible in the analytics view

  @scenario:QC-05
  Scenario: The operator can see where students abandon a flow
    Given many students enter the same multi-step flow
    When some leave before completing it
    Then the analytics view shows the step-by-step drop-off for that flow
    And it is keyed to the flow's scenario id

  # --------------------------------------------------------- duty of care (children)

  @scenario:QC-06
  Scenario: A child's events never carry personal data
    Given any learning or navigation event is recorded for a student
    Then the event identifies her by an internal id, never by name or email
    And no free-text she typed is included in the event properties

  @scenario:QC-07
  Scenario: Behavioural analytics of a minor requires guardian consent
    Given a guardian has not consented to behavioural analytics
    When their child uses the app
    Then her behaviour is not sent to the analytics provider
    And session capture (replay) is off until consent is given
    And granting or withdrawing consent takes effect immediately

  # --------------------------------------------------------- probes & errors

  @scenario:QC-08
  Scenario: Each key page is probed on a schedule and alerts when it breaks the spec
    Given a scheduled probe reuses the app's own BDD scenarios against a page
    When a page stops behaving as its scenario specifies
    Then the probe fails and the operator is alerted with the page and scenario id

  @scenario:QC-09
  Scenario: A runtime exception is captured with its scenario context
    Given a student is part-way through a spec-critical flow
    When an unhandled exception occurs
    Then the error is captured with a stack trace and the active scenario id
    And it is grouped so a recurring deviation is one issue, not many
