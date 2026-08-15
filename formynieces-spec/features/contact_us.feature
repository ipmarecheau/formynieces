@mvp @guardian
Feature: Contact us — a message reaches a human
  Some parents will not book a call before asking a question. The contact
  page takes their message, stores it for the team, and confirms receipt.
  Messages land in the admin panel where they are triaged and marked
  handled — nothing is lost to an inbox no one checks.

  @scenario:CU-01
  Scenario: A visitor sends a message
    Given a visitor on the contact page
    When they submit their name, email and message
    Then the message is stored for the team
    And the page confirms it was received

  @scenario:CU-02
  Scenario: An incomplete message is refused politely
    Given a visitor on the contact page
    When they submit without a name, email or message
    Then the form is returned with validation errors
    And nothing is stored

  @scenario:CU-03
  Scenario: The team sees messages in the admin panel
    Given a contact message has been sent
    When an admin opens the admin panel
    Then the message appears with sender, topic and date
    And can be marked handled
