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
  Scenario: Only one other parent may be added
    Given a guardian who has already added one other parent
    When she tries to add a second one
    Then the second is rejected
    And the invite form is hidden while an other parent is present

  @scenario:GF-06
  Scenario: A guardian removes the other parent
    Given a guardian with an added other parent
    When she removes them
    Then the other parent is deleted from her account
    And she can add a different one in their place

  @scenario:GF-07
  Scenario: The family is shown as a tree
    Given a verified guardian on the Family page
    When she views the family tree illustration
    Then she sees herself and the other parent as the two parent nodes
    And each child appears as a node beneath them
    And a missing other parent shows as a placeholder to add
