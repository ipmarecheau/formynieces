@mvp @mobile @guardian
Feature: Mobile parent app — the calm SEA control room
  The parent app exists because browser dashboards exhaust parents. It should not
  feel like another system to manage. It answers the parent's recurring question:
  what should my child practise next, why, and are they getting ready for SEA?

  Rule: The parent app leads with the next action

    @scenario:MP-01
    Scenario: A parent opens the app and sees the child who needs attention
      Given a parent has one or more linked children
      When she opens the mobile app
      Then she sees child cards with the latest activity and status
      And any child needing attention is clearly marked
      And she can open a child overview with one tap

    @scenario:MP-02
    Scenario: The child overview answers the parent’s weekly question
      Given a parent opens a child overview
      When the overview loads
      Then it names what the child should practise next
      And it explains why that topic matters
      And it shows whether there is recent practice activity
      And it shows one plain action the parent can take this week

    @scenario:MP-03
    Scenario: Weak topics are actionable rather than a syllabus dump
      Given a child has practice history
      When the parent opens weak topics
      Then the app shows the top weak topics first
      And each topic includes subject, reason, and suggested action
      And mastered and upcoming topics do not crowd the first screen

  Rule: Parents see readiness without panic

    @scenario:MP-04
    Scenario: Readiness is shown with context
      Given a child has enough practice or timed work for a readiness signal
      When the parent opens readiness
      Then the app shows a plain-language readiness signal
      And it labels the evidence behind that signal
      And it avoids bare alarming numbers without a next step

    @scenario:MP-05
    Scenario: Thin readiness evidence is not overstated
      Given a child has too little practice history for a reliable readiness signal
      When the parent opens readiness
      Then the app says more evidence is needed
      And it recommends the next practice or mock action
      And it does not present a firm placement projection

  Rule: Writing feedback is visible without asking the child

    @scenario:MP-06
    Scenario: The parent sees the latest writing status
      Given a child has a writing prompt, submission, or feedback
      When the parent opens Writing
      Then the app shows the latest writing status
      And any feedback is summarized in parent-friendly language
      And the summary names the SEA writing skills being improved

  Rule: Parent controls stay out of the child app

    @scenario:MP-07
    Scenario: Parent account controls remain parent-only
      Given a parent is signed in
      When she opens account controls
      Then she can log out and reach support
      And billing or subscription changes use the approved web or store path
      And these controls are never visible in the child experience

    @scenario:MP-08
    Scenario: Parent app uses notifications to reduce browser checking
      Given a parent has notifications enabled
      When a child completes a mission or becomes stuck
      Then the parent can receive a concise nudge
      And the nudge opens the relevant child overview or weak-topic screen
      And the notification never exposes sensitive child detail on the lock screen by default
