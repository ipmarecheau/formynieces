@mvp
Feature: Admin content and pacing settings

  @scenario:AC-01
  Scenario: Setting the global weekly module cap
    Given the admin is on the pacing settings screen in Filament
    When she sets the global weekly module cap to 8
    Then students without a personal cap use a weekly cap of 8

  @mvp @scenario:AC-02
  Scenario: Overriding one student's weekly module cap
    Given a student is using the global weekly module cap
    When the admin sets that student's weekly module cap override to 9
    Then that student's weekly target may include up to 9 modules
    And other students continue to use the global cap

  @mvp @scenario:AC-03
  Scenario: Lowering a struggling student's weekly module cap
    Given a student is using the global weekly module cap
    When the admin sets that student's weekly module cap override to 3
    Then that student's weekly target includes at most 3 modules

  @mvp @scenario:AC-04
  Scenario: A student outpacing her cap is flagged for admin review
    Given a student whose feasible pace would require more modules than her weekly cap
    When the Sunday rollover job runs
    Then she appears in the admin's cap-review list with her required pace
