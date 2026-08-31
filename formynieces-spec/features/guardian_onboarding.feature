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

  @retired @scenario:GO-06
  Scenario: A guardian verifies a phone number
    # RETIRED (2026-08-31): phone verification was removed from the product.
    # The number is still captured at registration but never verified, and it
    # never gates onboarding. Kept for history; superseded by GO-15.
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

  @retired @scenario:GO-13
  Scenario: Phone is verified by WhatsApp with an SMS fallback
    # RETIRED (2026-08-31): phone verification removed. The WhatsApp/SMS OTP
    # code path (PhoneVerifier / Twilio) remains behind a hardcoded-off switch
    # but is never invoked in production. Superseded by GO-15.
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
    And once her email is verified she is taken straight to child setup
    And a "Need help" path to a real person is offered throughout
    # Email verification alone opens onboarding — there is no phone step (GO-15).

  @scenario:GO-15
  Scenario: The phone is captured but never verified
    Given phone verification is permanently disabled
    When a guardian registers with a phone number
    Then her phone number is captured on her account
    And no phone verification code is sent
    And verifying her email alone opens child setup
    And no environment setting can re-gate onboarding on a phone code
    # services.phone_verification.enabled is hardcoded false; the
    # PHONE_VERIFICATION_ENABLED env var is intentionally ignored.

  @scenario:GO-17
  Scenario: A returning guardian who types an existing email is sent to sign in
    Given a visitor on the registration form
    When she enters an email address that already has an account
    Then she is stopped before completing the form
    And she is shown a notice to sign in to her dashboard, with a link to log in
    And no second account is created for that email
    # Enforced two ways: an on-blur check (register/check-email) locks the form,
    # and the server-side unique rule rejects a submitted duplicate with the same
    # sign-in guidance for the JS-off case.

  @scenario:GO-18
  Scenario: Login routes a guardian by how far she has got
    Given a guardian whose email is verified
    When she logs in
    Then a guardian with no linked student is taken to child setup
    And a guardian who already has a student is taken to her dashboard
    # There is no phone-verification step in the login path (GO-15).

  @scenario:GO-16
  Scenario: The guardian must read and accept the Terms & Conditions
    Given a guardian on the registration form
    When she submits without accepting the Terms & Conditions
    Then registration is rejected and no account is created
    And when she accepts, the acceptance time and terms version are recorded on her account
    And the full terms are shown on the form (scroll-gated) and on a public /terms page
