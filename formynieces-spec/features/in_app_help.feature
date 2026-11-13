@mvp @mobile
Feature: In-app help — a cry for help from anywhere, caught early
  Anyone using SmoothSeas — a parent on her dashboard, a child mid-voyage, a visitor on
  the public site — will sometimes hit friction: a bank that says "coming soon", a page
  that won't load, a question they can't answer. The whole point of a one-stop shop is
  that help is never more than a tap away and a complaint reaches the team the moment it
  happens, not days later through a lost WhatsApp message. From any screen a user can open
  a small help panel and either type a message or leave a voice note; it is captured with
  who they are and where they were, and lands in the team's inbox to be fixed almost
  instantly.

  Because the whole site is ported to native apps with Capacitor, the help control is
  written mobile-first: a thumb-reachable floating button, native text and microphone
  input, and nothing that depends on hover or a mouse. A child who cannot read well can
  speak their problem instead of typing it.

  @scenario:HC-01
  Scenario: Help is one tap from every screen
    Given a user is anywhere in the app — a student on the voyage, a parent on the dashboard, or a visitor on a public page
    Then a help button is within thumb's reach without leaving the page
    And opening it never interrupts or loses the work they were doing

  @scenario:HC-02
  Scenario: A parent types a problem and it reaches the team
    Given a parent hits friction and opens the help panel
    When she chooses what kind of thing it is, types what happened, and sends it
    Then a support ticket is recorded with her message
    And it captures who she is, her role, and the page she was on
    And she is thanked and told the team will look into it

  @scenario:HC-03
  Scenario: A child who struggles to type can speak the problem instead
    Given a child on the voyage cannot easily type what is wrong
    When he records a voice note in the help panel and sends it
    Then the voice note is stored privately with the ticket
    And it is never exposed to the public — only the team can hear it

  @scenario:HC-04
  Scenario: An empty cry for help is gently refused, not lost
    Given a user opens the help panel but neither types nor records anything
    When they try to send
    Then they are asked to add a message or a voice note first
    And no empty ticket is created

  @scenario:HC-05
  Scenario: Every ticket carries enough context to act on it fast
    Given any help ticket is received
    Then it records the page URL, the user's role, and the child in context when there is one
    And a signed-out visitor can still send one, tagged as a guest
    And the team can triage it from open through to resolved
