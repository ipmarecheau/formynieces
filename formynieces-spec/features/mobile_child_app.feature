@mvp @mobile @student
Feature: Mobile child app — the daily SEA habit
  The child app is the sticky student surface for SmoothSeas. It opens on a short,
  friendly Today screen where Smooth gives the next useful SEA mission. The child
  should not navigate a browser-shaped dashboard or choose from a long syllabus
  before practising. The mobile habit is one tap into a short session, immediate
  feedback, and a reason to return tomorrow.

  Rule: Today is the child app front door

    @scenario:MC-01
    Scenario: An onboarded child lands on today's mission
      Given an onboarded child with an active daily plan
      When she opens the mobile app
      Then she sees Smooth and today's next SEA mission
      And the mission names the subject and topic
      And one primary button starts the mission
      And no parent pace figures or placement estimates are shown

    @scenario:MC-02
    Scenario: A child with no available mission gets a kind next step
      Given an onboarded child without an available daily mission
      When she opens the mobile app
      Then she sees a calm empty state
      And she can refresh the plan or ask her parent to check the account
      And she is not shown an error trace or admin wording

  Rule: Practice is short and touch-first

    @scenario:MC-03
    Scenario: A child completes a short practice session
      Given a child starts today's mission
      When she answers each question in the mobile practice session
      Then each answer can be submitted with touch-friendly controls
      And the session shows progress through the short set
      And finishing the session records attempts against the existing learning record

    @scenario:MC-04
    Scenario: A missed answer becomes a re-teach moment
      Given a child misses a practice question
      When the app shows feedback
      Then Smooth explains the missed rule in child-friendly language
      And the explanation is based on the verified SEA explanation for that question
      And the child can continue without leaving the session

    @scenario:MC-05
    Scenario: Completing practice updates the habit loop
      Given a child completes a mobile practice session
      When the result screen appears
      Then she sees her accuracy and a simple celebration
      And her streak or voyage progress is updated when earned
      And she sees the next available action

  Rule: Child access is low-friction but separated from parent controls

    @scenario:MC-06
    Scenario: A child enters through parent-created credentials or handoff
      Given a parent has created access for a child
      When the child signs in on a device
      Then the app routes her to the child experience
      And she cannot reach billing, parent reports, rewards controls or account management

    @scenario:MC-07
    Scenario: A child can return to Today from any child screen
      Given a child is in practice, feedback, result, or voyage preview
      When she taps the home control
      Then she returns to the Today screen
      And she does not rely on browser navigation
