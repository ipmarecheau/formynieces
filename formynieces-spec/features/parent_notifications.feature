@mvp @guardian
Feature: Parent daily task notifications
  A guardian is kept in the loop on the day's paced work by email: once the child
  finishes everything, or once she has gone inactive for a while with tasks still
  open. The note is honest but kind — it names what was done and what is still open,
  ties it to staying ready for the exam, and never shames the child. At most one
  email per child per day.

  Background:
    Given a student linked to a guardian, with paced lessons for today

  @scenario:PN-01
  Scenario: The guardian is emailed when the day's tasks are all done
    Given the student has completed her daily minimum and every paced lesson today
    When the daily summary runs
    Then her guardian is emailed that today's plan is complete
    And no second email is sent for the same day

  @scenario:PN-02
  Scenario: The guardian is emailed when the student goes inactive with work open
    Given the student was active today but has been idle for a while with lessons unfinished
    When the daily summary runs
    Then her guardian is emailed a summary naming what is still to do to stay on pace
    And a student who is still active, or has done nothing yet, triggers no email
