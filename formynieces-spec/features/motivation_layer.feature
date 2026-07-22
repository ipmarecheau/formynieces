@mvp @student
Feature: Motivational layer
  Streaks and celebrations keep a 10-year-old returning across 30 weeks. They
  are honest about what they measure, restart without shame, and never appear
  in the guardian's honest layer as a judgement metric.

  @scenario:ML-01
  Scenario: Completing a learning activity extends the streak
    Given a student whose streak was 4 days as of yesterday
    When she masters a module today
    Then her streak shows 5 days on her dashboard

  @scenario:ML-02
  Scenario: A broken streak restarts without shame
    Given a student whose streak was 9 days and who missed yesterday
    When she masters a module today
    Then her streak shows 1 day
    And the message welcomes her back without referencing the broken streak

  @scenario:ML-03
  Scenario: A pause freezes the streak
    Given a student who was paused by her guardian 5 days ago with a 6-day streak
    When she completes a learning activity on the day she is resumed
    Then her streak shows 7 days

  @mvp @scenario:ML-04
  Scenario: Logging in on consecutive days extends the login streak
    Given a student whose login streak was 3 days as of yesterday
    When she logs in today
    Then her login streak shows 4 days on her dashboard

  @mvp @scenario:ML-05
  Scenario: Mastering a module on consecutive days extends the mastery streak
    Given a student whose mastery streak was 2 days as of yesterday
    When she masters a module today
    Then her mastery streak shows 3 days on her dashboard

  @mvp @scenario:ML-06
  Scenario: Staying on pace across weeks extends the on-pace streak
    Given a student who has met her weekly target for 3 consecutive weeks
    When the Sunday rollover runs and she is still on pace
    Then her on-pace streak shows 4 weeks on her dashboard
    And the streak is framed kindly, never as a guardian judgement metric

  @mvp @scenario:ML-07
  Scenario: Logging in shows a splash celebrating the student's streaks
    Given a student with one or more active streaks
    When she logs in
    Then she first sees a splash that celebrates her current streaks
    And she can continue from the splash to her learning map
