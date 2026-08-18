@roadmap @admin @system
Feature: Content audit — recommend what to bank before AI has to improvise
  An interactive admin page that reads the content banks, the features that draw on
  them, and the reactions people leave, then recommends the minimum content to author
  in advance so the app runs seamlessly — with AI kept for learning and assessment,
  not for generating lessons, passages or prompts in real time. It recommends only;
  it never generates content itself. It reasons over three axes: coverage (how much
  exists), realtime-generation exposure (where a thin bank forces AI to improvise),
  and quality (how the content is received).

  @scenario:CA-01
  Scenario: The audit shows current coverage per content type
    Given authored content across lessons, practice, reading, vocabulary and writing
    When an admin opens the content audit
    Then it shows how much of each type exists against its seamless-operation target
    And these figures come from the deterministic coverage service, always live
    # The figures are ContentCoverageService::report() — the same source as
    # `php artisan content:coverage`.

  @scenario:CA-02
  Scenario: The audit flags where a thin bank forces realtime generation
    Given a content type whose bank is below its target
    And a feature that falls back to generating that content at runtime when it is thin
    When the audit is read
    Then it flags that realtime-generation exposure, drawn from the content-dependency catalog
    And it separates sanctioned learning and assessment AI, which is never a target
    # Catalog examples: worked-example generation, reading-pool gap-fill, vocabulary
    # examples, writing prompts. Learning/assessment (tutoring, scoring, moderation) stays.

  @scenario:CA-03
  Scenario: The audit folds in the quality of existing content
    Given content items carry reactions from students and guardians
    When the audit is read
    Then each item's average reaction and how many it has are shown
    And well-stocked but poorly-received content is flagged as "needs rework", not just "add more"
    # The quality axis is fed by content_feedback (CF-*).

  @scenario:CA-04
  Scenario: An AI pass produces a ranked set of recommendations
    Given the coverage figures, the dependency catalog, and the reaction signals
    When the admin runs the audit
    Then an AI reasoning pass returns a ranked list — what to bank, how much, which first, and why
    And it runs on demand and is cached, under the AI budget, with a template fallback when AI is unavailable
    # This AI use is analysis over app state, not content generation — consistent
    # with the philosophy the audit exists to enforce.

  @scenario:CA-05
  Scenario: The page is interactive
    Given a set of recommendations
    When the admin explores them
    Then she can filter by content type, priority or feature
    And she can drill into a recommendation to the exact modules, levels or genres it names

  @scenario:CA-06
  Scenario: The audit recommends, and never generates
    Given the content audit has produced recommendations
    When the admin reviews them
    Then the page offers no control that generates lessons, passages, or prompts
    And its whole output is advice — the authoring happens through the banks (lesson_bank, writing_bank)
