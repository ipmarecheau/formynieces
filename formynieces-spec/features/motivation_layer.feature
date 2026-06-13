@mvp @student
Feature: Motivational layer
  Streaks and celebrations keep a 10-year-old returning across 30 weeks. They
  are honest about what they measure, restart without shame, and never appear
  in the guardian's honest layer as a judgement metric.

  Scenario: Completing a learning activity extends the streak
    Given a student whose streak was 4 days as of yesterday
    When she masters a module today
    Then her streak shows 5 days on her dashboard

  Scenario: A broken streak restarts without shame
    Given a student whose streak was 9 days and who missed yesterday
    When she masters a module today
    Then her streak shows 1 day
    And the message welcomes her back without referencing the broken streak

  @scenario-S6
  Scenario: A pause freezes the streak
    Given a student who was paused by her guardian 5 days ago with a 6-day streak
    When she completes a learning activity on the day she is resumed
    Then her streak shows 7 days
