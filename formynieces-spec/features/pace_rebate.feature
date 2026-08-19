@roadmap @guardian
Feature: On-pace subscription rebate (roadmap)
  A parent-facing loyalty reward that keeps guardians invested in the child's pace.
  When a student stays on pace, the platform rebates part of the monthly subscription
  as a CREDIT (never a cash payout, to keep the model a purchase-price adjustment
  rather than money transmission). The dollar value is parent-facing only; the child
  sees a symbolic "treasure" filling, never a cash figure. Amounts are illustrative
  (subscription 300 TTD/month, rebate 60 TTD/month) and must be configurable.
  # Roadmap, not MVP. Cash-vs-credit and jurisdiction (TTD / Trinidad & Tobago) carry
  # legal weight — payments/advertising-to-minors/tax — and want qualified review
  # before build. Shipped as a subscription credit to bound exposure and simplify law.

  Background:
    Given a subscribed guardian whose student is on the platform

  @roadmap @scenario:PR-01
  Scenario: Staying on pace for the month earns a subscription credit
    Given the student was on pace for the whole month
    When the month closes
    Then the guardian earns a subscription credit toward next month
    And the credit is a discount on the subscription, never a cash payout

  @roadmap @scenario:PR-02
  Scenario: The rebate builds visibly on the guardian dashboard
    Given the student is on pace so far this month
    When the guardian opens her dashboard
    Then she sees the rebate building toward this month's credit
    And it is framed to keep her invested in her child's pace, never as a debt

  @roadmap @scenario:PR-03
  Scenario: The parent chooses monthly discount or banks toward a free month
    Given the guardian has earned a monthly on-pace credit
    When she decides how to use it
    Then she may take the credit as a discount on the coming month
    And instead she may bank consecutive on-pace months toward a reward

  @roadmap @scenario:PR-04
  Scenario: Five consecutive on-pace months earns the sixth month free
    Given the student has been on pace for five consecutive months
    And the guardian banked those credits instead of spending them
    When the fifth on-pace month closes
    Then the sixth month is free of charge
    And the consecutive-month count resets after the free month is granted

  @roadmap @scenario:PR-05
  Scenario: A missed month breaks the consecutive run without punishment
    Given the student was not on pace this month
    When the month closes
    Then no credit is earned for this month
    And the consecutive-on-pace count resets to zero
    And nothing is taken away and no penalty language reaches the guardian or child

  @roadmap @scenario:PR-06
  Scenario: The child sees the reward symbolically, never as money
    Given the rebate is building this month
    When the child looks at her Voyage
    Then she sees a treasure filling as a warm sign she is sailing well
    And no dollar figure, price, or pace number is ever shown to her

  @roadmap @scenario:PR-07
  Scenario: The rebate amounts and on/off are configurable
    Given an administrator or the billing settings
    When the subscription price or rebate amount is configured
    Then the monthly rebate follows the configured values
    And the whole rebate can be turned off for a family or globally
