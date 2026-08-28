@mvp @guardian
Feature: Guardian account and child setup
  In order to give a niece a safe, personalised SEA preparation journey
  a guardian creates a verified account and sets up her student profile — guided
  step by step, and always clear about whose name is being asked for.
  All students must be linked to a verified guardian aged 18 or over.

  @scenario:GO-01
  Scenario: A guardian registers with an 18+ attestation
    Given a visitor is on the registration screen
    When she registers with her name, email, password, and confirms she is 18 or older
    Then a guardian account is created with role "guardian"
    And she is redirected to the email verification notice

  @scenario:GO-02
  Scenario: An unverified guardian cannot reach child setup
    Given a guardian who has registered but not verified her email
    When she attempts to open the child setup screen
    Then she is redirected to the email verification notice

  @scenario:GO-03
  Scenario: A verified guardian without a child is routed to child setup
    Given a verified guardian with no linked student
    When she logs in
    Then she is taken to the child setup screen

  @scenario:GO-04
  Scenario: A guardian creates a child profile
    Given a verified guardian is on the child setup screen
    When she submits the child's name, target SEA year, and optional known weak areas
    Then a student account linked to her is created
    And the student's onboarding is not yet completed
    And the child's login details are shown to her once

  @scenario:GO-05
  Scenario: A new student is routed to the diagnostic at first login
    Given a student whose onboarding is not completed
    When the student logs in
    Then she is taken to the diagnostic intro instead of the dashboard

  @scenario:GO-09
  Scenario: The registration form asks for the guardian's own name, not the child's
    Given a visitor is on the registration screen
    When she reads the name field
    Then it is labelled as her own name, as the parent or guardian
    And its helper text tells her she will add her child in the next step
    And its example is an adult's name, so she does not enter the child's name here
    # Observed: the name field's example ("e.g. Aaliyah Thomas") reads as a child's name.

  @scenario:GO-10
  Scenario: The setup journey shows the guardian where she is
    Given a guardian moving from registration through child setup to the diagnostic hand-off
    When she is on any step of that setup journey
    Then the step she is on and how many remain are shown
    And each step names, in plain language, what it is for

  @scenario:GO-11
  Scenario: The verification notice tells her exactly what to do next
    Given a guardian who has registered but not verified her email
    When she lands on the email verification notice
    Then it names the address the link was sent to and what happens once she confirms
    And she can resend the link and reach a human for help without leaving the flow
    # Verification still gates child setup (GO-02); this removes the dead-end feeling.
    # Fully deferring verification until after child setup is a separate product call.

  @v1.1 @scenario:GO-06
  Scenario: A guardian verifies a phone number
    Given a verified guardian on the phone verification screen
    When she submits her phone number and the confirmation code sent to it
    Then her account is marked phone-verified

  @roadmap
  Rule: A second guardian has read-only visibility

    @roadmap @scenario:GO-07
    Scenario: The primary guardian invites a second guardian
      Given a primary guardian of a student
      When she sends a second-guardian invitation by email
      Then a read-only guardian invitation is created for that student

    @roadmap @scenario:GO-08
    Scenario: A second guardian views but cannot change anything
      Given a second guardian who accepted an invitation and verified her account
      When she opens the student's guardian dashboard
      Then she sees the same dashboard as the primary guardian
      And no settings, pause, or profile controls are available to her

  @scenario:GO-12
  Scenario: Registration is protected and captures a reachable phone
    Given a guardian on the registration form
    When she submits the form
    Then she must pass a Cloudflare Turnstile CAPTCHA (when configured)
    And she must provide a phone number in full international format
    And a missing or malformed phone number is rejected

  @scenario:GO-13
  Scenario: Phone is verified by WhatsApp with an SMS fallback
    Given a newly-registered guardian with a phone on file
    When registration completes
    Then a verification code is sent to her phone on WhatsApp first
    And she can request the code by SMS instead if WhatsApp does not arrive
    And entering the correct code marks her phone verified

  @scenario:GO-14
  Scenario: Email is verified by link or by code, then onboarding begins
    Given a newly-registered guardian on the verification screen
    When she either taps the emailed link or types the 6-digit code
    Then her email is marked verified
    And once both email and phone are verified she is taken straight to child setup
    And a "Need help" path to a real person is offered throughout

  @scenario:GO-15
  Scenario: At the free launch the phone is captured but not verified
    Given phone verification is switched off (the free-launch default)
    When a guardian registers with a phone number
    Then her phone number is captured on her account
    And no phone verification code is sent
    And verifying her email alone opens child setup
    # Flipping services.phone_verification.enabled on (with Twilio keys) restores
    # the required WhatsApp/SMS OTP step described in GO-13/GO-14.
