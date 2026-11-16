@tooling @qa
Feature: Guided user testing — gather verdicts from non-technical testers
  In order to learn whether a feature really works for the people it is for,
  As the maintainer,
  I want friends and family to walk real scenarios and leave a plain-English verdict,
  without any setup, and to be told when something they checked has since changed.

  This is the one feature that is not itself walked through /user-testing (it cannot
  test itself); it is covered by Pest and a Playwright harness instead. A tester is
  identified only by email, the real screens are embedded beside plain steps, each
  verdict is EVIDENCE the maintainer reviews — never a specs:verify record — and the
  tool keeps a tester honest about change: when a scenario's definition or its code
  moves after their verdict, they are shown a re-test status and nudged once by email.

  Rule: A tester is identified by email alone, durably

    @scenario:UT-01
    Scenario: A tester signs in with just an email and their place is remembered
      Given a non-technical tester with no account
      When she enters her email and name
      Then she can begin walking scenarios without any password or setup
      And her email is the key her verdicts are saved against
      And returning on the same browser restores her straight to her progress

  Rule: Verdicts are evidence, recorded against the version tested

    @scenario:UT-02
    Scenario: Walking a scenario and recording a verdict
      Given a signed-in tester on a feature's scenario
      When she reads the steps beside the embedded real screen and records how it went
      Then her verdict is saved as one row per tester per scenario
      And it is evidence for the maintainer to review, never a specs:verify record

    @scenario:UT-03
    Scenario: Changing an answer updates the same verdict
      Given a tester who already answered a scenario
      When she goes back and answers it differently
      Then the same verdict row is updated rather than duplicated

    @scenario:UT-08
    Scenario: A verdict remembers the version it was given against
      Given a tester recording a verdict on a scenario
      When the verdict is saved
      Then it is stamped with the scenario's Gherkin fingerprint
      And with the fingerprint of the files that implement that scenario
      And with the commit the app was on at the time

  Rule: The real screen is reachable with no gate the tester cannot pass

    @scenario:UT-04
    Scenario: Seeded auto-login is hard-gated and only ever a test account
      Given the guided walkthrough prepares a scenario's state
      When it signs the tester in to view the embedded screen
      Then it only ever logs into an account flagged is_test
      And it is refused entirely in production unless explicitly enabled

    @scenario:UT-05
    Scenario: A device scenario shows an install code and a usable test login
      Given a scenario that can only be tried on a physically installed app
      When the tester opens it
      Then she is shown an install QR for the dev app
      And a real test login she can type into the app, that actually authenticates

  Rule: Delayed feedback is deferred, with reminders around the expected time

    @scenario:UT-06
    Scenario: A notification scenario can be deferred for an email reminder
      Given a scenario whose feedback arrives later, such as a notification
      When the tester chooses to be reminded instead of waiting
      Then a waiting check is recorded
      And the expected time is computed in the tester's own timezone

    @scenario:UT-07
    Scenario: Two reminders bracket the expected notification
      Given a waiting check for a delayed scenario
      When the sweep runs around the expected time
      Then the tester is emailed shortly before to watch for it
      And again after it should have arrived, to record whether it did
      And the "it never arrived" link pre-fills the missed note

  Rule: Testers are told when something they checked has changed

    @scenario:UT-09
    Scenario: A changed scenario is flagged for re-test and the tester nudged once
      Given a tester who answered a scenario
      When that scenario's definition or its implementation changes afterwards
      Then the scenario shows a re-test status, distinct from done and waiting
      And the tester is emailed once, told whether the steps, the code, or both changed
      And re-testing records a fresh verdict against the new version and clears the flag

    @scenario:UT-10
    Scenario: The catalog groups features and shows each one's status at a glance
      Given a tester with verdicts, a waiting check, and a changed scenario
      When she opens the catalog
      Then features are grouped by area, each group collapsible
      And each feature shows done, waiting, or re-test with a clear colour and icon
