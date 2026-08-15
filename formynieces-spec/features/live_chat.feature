@mvp @guardian
Feature: Live chat — Smooth opens the conversation, a human finishes it
  A parent browsing at 9pm has a question that a form cannot catch. Smooth
  pops up in his own voice, runs a short qualification (name, standard,
  worry, how to reach them), and hands the warm lead to the founder via
  Slack and email. The widget is honest that a human replies within hours,
  offers the WhatsApp and book-a-call paths as instant alternatives, never
  nags a visitor who has dismissed it, and never appears for signed-in
  users on the marketing pages.

  @scenario:LC-01
  Scenario: Smooth proactively opens the chat — politely
    Given a signed-out visitor has been on a public page for a while
    When the chat has not been dismissed by this visitor before
    Then Smooth's chat bubble pops up with a short invitation
    And a visitor who dismisses it is not auto-prompted again
    And a signed-in user never sees the proactive chat

  @scenario:LC-02
  Scenario: The bot qualifies the parent in Smooth's voice
    Given a visitor starts the chat
    Then Smooth asks for their name
    And the child's standard
    And their biggest worry
    And where to reach them — email or WhatsApp
    And the whole conversation is stored as it happens

  @scenario:LC-03
  Scenario: The team sees the conversation in the admin panel
    Given a visitor has chatted
    When an admin opens the admin panel
    Then the conversation appears with the captured name, standard, worry and contact
    And the full message transcript is viewable
    And the conversation can be marked closed

  @scenario:LC-04
  Scenario: The founder is notified instantly
    Given a visitor sends a chat message
    Then a notification is pushed to the team's Slack channel
    And an email notification is sent to the team
    And a chat failure never breaks the visitor's page

  @scenario:LC-05
  Scenario: Instant handoffs — WhatsApp and the booking page
    Given a visitor finishes (or skips) the chat
    Then the widget offers to continue on WhatsApp with the founder
    And to book the free 15-minute call
    And the WhatsApp link opens the founder's number with a prefilled message

  @scenario:LC-06
  Scenario: The chat is honest about response time
    Given a visitor reads the chat's closing message
    Then it states a human replies within a few hours
    And it never claims 24/7 instant support
