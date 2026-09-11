@roadmap @guardian @student @admin @system
Feature: Past paper bank — printable weekly papers, sat at home, graded by AI
  # DRAFT SPEC — brainstormed 2026-09-07, pending Isaac's decisions (see the notes at
  # the foot of this file). Scenario IDs are stable; wording may still change.
  #
  # A parent should be able to sit her child down at the weekend with a real, printed
  # past paper — the way SEA is actually sat — and get it marked without lifting a red
  # pen. The platform keeps a bank of genuine past papers (seeded, curated, portable)
  # and grows it by generating skill-preserving VARIANTS of existing questions: the same
  # objective and difficulty, with the numbers, names, and surface story changed. A paper
  # is composed ONLY from topics the child has already covered on her voyage, printed as
  # a clean PDF with working space, and traceable back to its own mark scheme. The child
  # (or her guardian) uploads photos of the finished pages; the school-journal vision
  # pipeline digitises them and — because the platform generated the paper and KNOWS the
  # answers — grades each question authoritatively, flagging only what it could not read.
  # Results live in the honest (guardian) layer, corroborate or gently steer the plan,
  # and never become a mark the child is shamed by.
  #
  # Reuses: question_bank (QB) for source items + portability + QC; school_journal
  # (SJ-01..13) for the upload → OCR → per-question breakdown → topic alignment →
  # confidence-flag → guardian-correct pipeline; learning_loop / weekly_targets for
  # "topics covered"; writing_track for extended writing; ai_governance for disclosure.

  Background:
    Given a student linked to a guardian
    And the student has covered some syllabus modules on her voyage

  # ---------------------------------------------------------------------------
  Rule: A curated bank of real past papers is seeded and kept portable

    @scenario:PP-01
    Scenario: Real past papers are seeded as structured papers, not loose images
      Given a set of genuine SEA past papers with their mark schemes
      When they are seeded into the past paper bank
      Then each paper is stored as an ordered set of questions with its own mark scheme
      And every question carries its item type, its syllabus objective, and a difficulty band
      And the original paper is retained as the gold reference for generating variants

    @scenario:PP-02
    Scenario: A seeded question holds everything grading will later need
      Given a seeded past paper
      When one of its questions is read
      Then it holds the prompt, the item type, the correct answer or worked mark scheme,
        the marks available, the objective it tests, and that it is of real provenance

    @scenario:PP-03
    Scenario: The bank is portable and backed up like the question bank
      Given past papers exist in the bank
      When the admin exports the bank
      Then a portable file is produced that can be re-imported without duplication
      And a dated backup is taken on the same daily schedule as the question bank

  # ---------------------------------------------------------------------------
  Rule: New questions are generated as skill-preserving variants that are verified before use

    @scenario:PP-04
    Scenario: A variant changes the surface but keeps the tested skill and difficulty
      Given a seed question tied to one objective and difficulty band
      When a variant is generated from it
      Then the variant tests the same objective at the same difficulty band
      And its numbers, names, and context differ from the seed
      And it records the seed it descends from

    @scenario:PP-05
    Scenario: A generated answer is verified, not assumed
      Given a newly generated numeric or multiple-choice variant
      When it is checked before entering the bank
      Then its stated answer is independently recomputed and must agree
      And a variant whose answer cannot be verified is rejected, never served

    @scenario:PP-06
    Scenario: A variant that drifts off-skill or is unsolvable is rejected
      Given a generated variant that changes the objective, is ambiguous, or has no valid answer
      When it is validated
      Then it is rejected with the reason recorded
      And it is not offered to any child

    @scenario:PP-07
    Scenario: Generated variants wait in a QC queue before a child can sit them
      Given generated variants that passed automated verification
      When they enter the bank
      Then they are marked unapproved and held in a QC queue
      And only approved questions can be placed on a child's paper
      And an admin can approve, edit, or discard a queued variant

    @scenario:PP-08
    Scenario: A reported question is pulled from circulation
      Given a question on a printed paper that a parent reports as wrong or unclear
      When the report is filed
      Then that question is withdrawn from future papers pending review
      And the report is queued for the admin with the paper and question it came from

  # ---------------------------------------------------------------------------
  Rule: A weekly paper is composed only from topics the child has covered

    @scenario:PP-09
    Scenario: A paper draws only from topics the child has already covered
      Given a student who has covered some modules and not others
      When this weekend's paper is composed for her
      Then every question maps to a module she has covered
      And no question tests a topic she has not yet been taught

    @scenario:PP-10
    Scenario: A paper can revisit earlier covered topics for retention
      Given a student who covered a topic several weeks ago
      When her paper is composed with review enabled
      Then a small share of questions revisit earlier covered topics
      And the remainder come from her most recently covered topics

    @scenario:PP-11
    Scenario: A parent chooses the subject and length, defaulting to a short paper
      Given a guardian preparing this weekend's paper
      When she chooses a subject and a length
      Then a paper of that scope is composed for her child
      And with no choice made a short paper — a couple of questions, sittable in one session — is the default

    @scenario:PP-12
    Scenario: Each generated paper is unique to the child and the week
      Given the same student in two different weeks
      When a paper is composed each week
      Then the two papers do not repeat the same variants
      And each paper is recorded as a sitting for that student with the date it was issued

  # ---------------------------------------------------------------------------
  Rule: The paper prints cleanly at home and is traceable back for grading

    @scenario:PP-13
    Scenario: A paper prints as a clean, exam-styled PDF
      Given a composed paper for a student
      When the guardian downloads it to print
      Then a PDF is produced with a cover — the child's name, the date, the time allowed, and instructions
      And each question is laid out with space to show working
      And it prints legibly in black and white on plain paper

    @scenario:PP-14
    Scenario: The mark scheme is produced separately and never on the child's copy
      Given a composed paper
      When it is generated
      Then a separate mark scheme is stored for grading
      And the child's printable copy shows no answers

    @scenario:PP-15
    Scenario: Every printed paper is uniquely traceable to its mark scheme
      Given a printed paper
      When its pages are later uploaded
      Then a code carried on the paper identifies exactly which sitting and mark scheme they belong to
      And an upload for an unknown or mismatched code is refused rather than mis-graded

  # ---------------------------------------------------------------------------
  Rule: Completed papers are uploaded, digitised, and graded against the known mark scheme

    @scenario:PP-16
    Scenario: A child or guardian uploads the finished pages
      Given a paper the child has sat on paper
      When the student or her guardian uploads photos or a scan of the completed pages
      Then the pages are stored against that sitting with the date filed
      And the originals stay attached and can be viewed again

    @scenario:PP-17
    Scenario: The pages are digitised by the school-journal vision pipeline
      Given uploaded pages for a sitting
      When the digitisation pipeline processes them
      Then each page is read into structured per-question answers with the student's working
      And low-confidence reads are flagged for a quick human confirmation, not trusted blindly

    @scenario:PP-18
    Scenario: Each answer is aligned to its question and graded against the mark scheme
      Given digitised answers for a sitting whose mark scheme is known
      When grading runs
      Then each answer is matched to its question by the paper's code and layout
      And multiple-choice and numeric answers are graded deterministically against the stored answer
      And extended-response answers are graded against the stored rubric with the marks awarded shown

    @scenario:PP-19
    Scenario: A guardian can correct anything the pipeline read or graded wrongly
      Given a graded sitting with a low-confidence question
      When the guardian reviews it
      Then she can correct the read answer or the awarded marks
      And the paper's score updates to reflect her correction

    @scenario:PP-20
    Scenario: A writing task is graded by the writing track, not as a number
      Given a paper that includes a writing prompt
      When the uploaded response is graded
      Then it is routed to the writing track's grading rather than a numeric mark scheme
      And its feedback is presented in the writing track's terms

    @scenario:PP-21
    Scenario: The per-question breakdown captures the work, not just the score
      Given a graded sitting
      When its breakdown is stored
      Then each question keeps a clipped image of the child's working, the read answer,
        the correct answer, and whether it was awarded the marks
      And for a wrong answer the pipeline records the likely misconception in the honest layer
      And a clip that could not be cut cleanly falls back to the full page, never a broken image

  # ---------------------------------------------------------------------------
  Rule: Results inform the honest layer and gently steer the plan, never shame the child

    @scenario:PP-22
    Scenario: The guardian sees the score, the per-topic breakdown, and the trend
      Given a graded sitting
      When the guardian opens the results
      Then she sees the overall score, a breakdown by syllabus topic, and which topics to revisit
      And results across weeks are shown as a trend per topic

    @scenario:PP-23
    Scenario: Paper results corroborate or gently steer, but never gate
      Given a graded sitting on topics the child's voyage covers
      When her learning signals are updated
      Then a strong result is recorded as a corroborating confidence signal for those topics
      And a weak result gives those topics gentle priority in her next daily plan
      And a paper result never on its own marks a module mastered, and never overrides the platform's own mastery

    @scenario:PP-24
    Scenario: The child's world stays mark-free
      Given a graded sitting for a student
      When she uses her voyage, streaks, and celebrations
      Then no paper score is used to gate, penalise, or shame her there
      And sitting a real paper is acknowledged positively, never as a grade to fear

  # ---------------------------------------------------------------------------
  Rule: Generation and grading are safe, auditable, and disclosed

    @scenario:PP-25
    Scenario: AI generation and grading are disclosed and auditable
      Given AI generated a variant and graded a sitting
      When the record is inspected in the honest layer
      Then it is clear which questions were AI-generated and that grading was AI-assisted
      And the seed, the mark scheme, and the grading decision are retained for audit
      And a guardian's correction always takes precedence over the AI's read

  # ===========================================================================
  # OPEN DECISIONS for Isaac (resolve before build; each changes scope):
  #
  # 1. Provenance mix at launch: seed real papers first and generate later, or both
  #    from day one? (Affects whether PP-04..07 are v1 or fast-follow.)
  # 2. Which model generates + verifies variants, and what is the monthly cost ceiling?
  #    (LlmService/OpenRouter today. Numeric self-check can be code, not LLM.)
  # 3. Is this free-tier, paid, or a paid add-on? (Gates cadence + volume.)
  # 4. Delivery: dashboard download only, or also emailed/WhatsApp'd each Friday?
  # 5. Upload identity: paper-id typed in vs a printed QR/DataMatrix the phone reads.
  #    (QR is more robust for PP-15 alignment but needs a scan step.)
  # 6. Extended-response rigour: how close to real SEA marking must the rubric be for
  #    v1 — exact part-marks, or "correct / partial / incorrect" bands?
  # 7. Answer sheet ownership: mark scheme kept for AI only, or also downloadable by the
  #    parent who wants to mark it herself?
  # ===========================================================================
