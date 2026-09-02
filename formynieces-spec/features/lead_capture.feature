@roadmap @guardian @marketing @monetization
Feature: Lead magnet — the free SEA Mock and First-Choice Placement Report
  The top-of-funnel offer that turns anonymous traffic into named, segmented leads and
  routes them toward a trial. It is built on the words this market already uses — a
  "mock", a "first-choice" placement, "extra lessons", "built for the T&T SEA syllabus" —
  and on the one asset no free competitor gives away: a personalised projection of whether
  a child will make their first-choice school.

  A visitor is offered a FREE SEA MOCK for their child plus a personalised PLACEMENT
  REPORT, in exchange for their email (and, optionally, a WhatsApp number for higher-open
  delivery). The child sits a short, AI-graded mock assembled from the practice/diagnostic
  bank. On completion a report is generated: a projected first-choice readiness band, the
  three weakest strands, and the single next step — the honest-layer value, delivered
  before they ever pay. The report ends with a shareable "SEA-Ready" score card built for
  WhatsApp, so the funnel spreads parent-to-parent, and with a call to action offering a
  FULL MONTH FREE (double the category-norm seven-day trial) plus a downloadable AI
  Practice Pack — 30 fresh, past-paper-style questions with worked solutions.

  Every captured lead lands in an admin-visible list with its placement snapshot, is
  segmented by the child's weak strands for targeted follow-up, and may opt in to a weekly
  "SEA Question of the Week" nurture message that keeps the list warm until conversion.
  This feature specifies capture, the mock, the report, delivery, the offer and nurture;
  the free plan a non-converting lead falls back to is specified in free_tier.feature.

  Background:
    Given a marketing visitor arrives on the placement-report landing page

  # --------------------------------------------------- capture

  @scenario:LG-01
  Scenario: The landing page offers the free mock and placement report for an email
    When the visitor reads the offer
    Then it promises a free SEA mock and a personalised first-choice placement report
    And it asks for the parent's email to begin
    And it offers an optional WhatsApp number for delivery
    And its copy uses "first-choice school" and "built for the T&T SEA syllabus"

  @scenario:LG-02
  Scenario: Submitting the form captures a lead
    When the visitor submits their email and their child's standard/level
    Then a Lead record is created with the email, any WhatsApp number and the child's level
    And the lead's source and timestamp are recorded
    And the visitor proceeds to the mock without creating a full account first

  @scenario:LG-13
  Scenario: A returning lead skips capture
    Given a lead who already gave their email
    When they return to the placement-report flow
    Then they are not asked to re-enter their email
    And they go straight to the mock or their existing report

  # --------------------------------------------------- the mock

  @scenario:LG-03
  Scenario: The child sits a short, AI-graded SEA mock
    Given a captured lead for a Standard 4 child
    When the child starts the mock
    Then it is a short timed set drawn from the SEA-aligned practice bank
    And open responses are graded by AI, selected responses auto-graded
    And the mock never requires payment to complete

  # --------------------------------------------------- the report

  @scenario:LG-04
  Scenario: A personalised first-choice placement report is generated
    Given the child has completed the mock
    When the report is produced
    Then it shows a projected first-choice readiness band
    And it names the child's three weakest strands
    And it gives a single, specific next step
    And it is honest — it never inflates the projection

  @scenario:LG-05
  Scenario: The report is shown on-screen and emailed to the parent
    When the report is ready
    Then it is displayed to the parent immediately
    And a copy is emailed to the captured address
    And, when a WhatsApp number was given, it is also sent via WhatsApp

  @scenario:LG-06
  Scenario: The report ends with a shareable SEA-Ready score card
    When the parent reaches the end of the report
    Then they are offered a shareable "SEA-Ready" score card sized for WhatsApp and status
    And sharing it links back to the placement-report landing page

  # --------------------------------------------------- the offer

  @scenario:LG-07
  Scenario: The report's call to action offers a full month free and the practice pack
    When the parent finishes the report
    Then the primary call to action offers a FULL MONTH free of the platform
    And it highlights that this is double the usual seven-day trial
    And it offers a downloadable AI Practice Pack as an immediate bonus

  @scenario:LG-08
  Scenario: Claiming the offer provisions a one-month trial that falls back to free
    When the parent claims the offer
    Then a one-month paid trial account is provisioned for them and the child
    And when the trial ends without payment the account falls back to the free plan
    And the child keeps any mastery earned during the trial

  @scenario:LG-09
  Scenario: The AI Practice Pack is generated and delivered as a PDF
    When the parent claims the practice pack
    Then 30 fresh past-paper-style questions with worked solutions are assembled
    And they are rendered as a branded PDF booklet
    And the booklet is delivered by email (and WhatsApp when available)

  # --------------------------------------------------- nurture and admin

  @scenario:LG-10
  Scenario: A lead can opt in to the weekly SEA Question of the Week
    When the parent opts in to the weekly question
    Then the lead is added to the weekly nurture list
    And each week they receive one AI past-paper-style question with a worked solution
      and the SEA countdown
    And every message offers a one-tap path to start the free month

  @scenario:LG-11
  Scenario: Leads are segmented by the child's weak strands
    Given several leads whose reports name different weakest strands
    When an admin views the leads
    Then each lead carries its child's weakest strands as segmentation tags
    And follow-up can be targeted to a specific weak strand

  @scenario:LG-14
  Scenario: An admin can review captured leads with their placement snapshot
    Given captured leads with completed reports
    When an admin opens the leads panel
    Then they see each lead's email, WhatsApp, child level and placement snapshot
    And they can see which leads converted to a trial
