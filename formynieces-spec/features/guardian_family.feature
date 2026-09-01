@mvp @guardian
Feature: Guardian family management
  In order to keep the whole family's picture in one place, a guardian manages
  each child's details — only the essentials are required, with optional metadata
  like birth year and current school — and invites the other parent as a co-parent.

  @scenario:GF-01
  Scenario: A guardian opens the Family area
    Given a verified guardian with a child in the Parent Portal
    When she opens the Family page
    Then she sees each child's editable details and a way to invite the other parent
    And the Family link is available in the portal navigation

  @scenario:GF-02
  Scenario: A guardian records optional child metadata
    Given a verified guardian on the Family page
    When she adds a child's birth year and current school and saves
    Then the metadata is stored on that child
    And she is shown a confirmation

  @scenario:GF-03
  Scenario: Only the child's name is mandatory
    Given a verified guardian on the Family page
    When she clears a child's name and saves
    Then the save is rejected with a validation error
    But saving with only the name and no optional metadata succeeds

  @scenario:GF-04
  Scenario: A guardian invites the other parent
    Given a verified guardian on the Family page
    When she submits the other parent's name and email
    Then a co-parent invitation is recorded against her account
    And an invitation email is sent to that address

  @scenario:GF-05
  Scenario: The same co-parent cannot be invited twice
    Given a guardian who has already invited a co-parent by email
    When she invites the same email again
    Then the second invitation is rejected

  @scenario:GF-06
  Scenario: A guardian removes a co-parent
    Given a guardian with an invited co-parent
    When she removes that co-parent
    Then the co-parent invitation is deleted from her account
