@mvp @guardian
Feature: Guardian account and billing
  In order to keep her details current and understand what she will be charged,
  a guardian manages her profile, sign-in, and billing from one Account area in
  the Parent Portal. Billing is display-only at the free launch — the plan and a
  first bill date are shown and an invoice history is kept, but no charges are
  taken until a payment provider is added.

  @scenario:GA-01
  Scenario: A guardian opens her Account area
    Given a verified guardian in the Parent Portal
    When she opens the Account page
    Then she sees her profile, a billing summary, and her billing history
    And the Account link is available in the portal navigation

  @scenario:GA-02
  Scenario: A guardian edits her profile
    Given a verified guardian on the Account page
    When she changes her name or phone number and saves
    Then the new details are stored on her account
    And she is shown a confirmation that the profile was saved

  @scenario:GA-03
  Scenario: Changing the email address requires re-verification
    Given a verified guardian on the Account page
    When she changes her email address and saves
    Then her email is marked unverified
    And a fresh verification email is sent to the new address

  @scenario:GA-04
  Scenario: A guardian changes her password
    Given a verified guardian on the Account page
    When she enters her current password and a valid new password
    Then her password is updated
    And an incorrect current password is rejected

  @scenario:GA-05
  Scenario: The billing summary shows the plan, first bill date, and invoices
    Given a verified guardian on the Account page
    When she reads the billing section
    Then she sees her current plan and the first bill date (or a dash when none)
    And she sees an honest empty state when she has no invoices
    And her paid and due invoices are listed with amount, period, and status when present

  @scenario:GA-06
  Scenario: A guardian deletes her account
    Given a verified guardian on the Account page
    When she confirms her password and deletes her account
    Then her account and every linked child are permanently removed
    And she is signed out and returned to the public site
