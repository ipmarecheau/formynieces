@mvp @student
Feature: Application tour and welcome
  A new voyager's very first arrival is a warm welcome, not a cold map. Smooth greets
  her aboard, gives her a joining gift of one of every perk, and then walks her through
  what she does each day — a short, chaptered, interactive tour of her ship that she
  drives at her own pace and can replay any time. The welcome and the gift happen once;
  the tour never nags once she has seen it.

  Background:
    Given an onboarded student who has not yet been welcomed

  @scenario:TR-01
  Scenario: The first login opens on a welcome page
    When she logs in for the first time after onboarding
    Then she lands on the welcome page before her Voyage
    And she is greeted aboard by Smooth by name

  @scenario:TR-05
  Scenario: Joining the crew grants one of every perk
    When she is welcomed aboard
    Then one of each streak perk is placed in her Captain's Locker as a joining bonus
    And the bonus is granted only once, no matter how often she returns

  @scenario:TR-02
  Scenario: The tour runs on her first Voyage, in chapters
    Given she has been welcomed aboard
    When she reaches her Voyage for the first time
    Then Smooth's tour opens automatically
    And it is broken into chapters she advances through at her own pace

  @scenario:TR-03
  Scenario: The tour never nags once she has seen it
    Given she has finished or skipped the tour
    When she returns to her Voyage
    Then the tour does not open on its own again

  @scenario:TR-04
  Scenario: She can replay the tour any time
    Given she has already seen the tour
    When she uses the "take the tour" control on her Voyage
    Then the tour opens again from the first chapter

  @scenario:TR-06
  Scenario: The welcome and tour are child-layer only
    When she is welcomed and toured
    Then no pace, percentage, target, or grade is shown to her anywhere in them

  @scenario:TR-07
  Scenario: The tour is interactive and continues across screens
    Given she is on the overworld leg of the tour
    When she reaches the final chapter
    Then she is invited to tap her first island to sail in
    And the tour resumes on the island, asking her to open the first stop
    And it then explains the learning loop inside the lesson before ending
