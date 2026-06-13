@mvp @guardian
Feature: Guardian account and child setup
  In order to give a niece a safe, personalised SEA preparation journey
  a guardian creates a verified account and sets up her student profile.
  All students must be linked to a verified guardian aged 18 or over.

  Scenario: A guardian registers with an 18+ attestation
    Given a visitor is on the registration screen
    When she registers with her name, email, password, and confirms she is 18 or older
    Then a guardian account is created with role "guardian"
    And she is redirected to the email verification notice

  Scenario: An unverified guardian cannot reach child setup
    Given a guardian who has registered but not verified her email
    When she attempts to open the child setup screen
    Then she is redirected to the email verification notice

  Scenario: A verified guardian without a child is routed to child setup
    Given a verified guardian with no linked student
    When she logs in
    Then she is taken to the child setup screen

  Scenario: A guardian creates a child profile
    Given a verified guardian is on the child setup screen
    When she submits the child's name, target SEA year, and optional known weak areas
    Then a student account linked to her is created
    And the student's onboarding is not yet completed
    And the child's login details are shown to her once

  Scenario: A new student is routed to the diagnostic at first login
    Given a student whose onboarding is not completed
    When the student logs in
    Then she is taken to the diagnostic intro instead of the dashboard

  @v1.1
  Scenario: A guardian verifies a phone number
    Given a verified guardian on the phone verification screen
    When she submits her phone number and the confirmation code sent to it
    Then her account is marked phone-verified

  @roadmap @scenario-S8
  Rule: A second guardian has read-only visibility
    Scenario: The primary guardian invites a second guardian
      Given a primary guardian of a student
      When she sends a second-guardian invitation by email
      Then a read-only guardian invitation is created for that student

    Scenario: A second guardian views but cannot change anything
      Given a second guardian who accepted an invitation and verified her account
      When she opens the student's guardian dashboard
      Then she sees the same dashboard as the primary guardian
      And no settings, pause, or profile controls are available to her
