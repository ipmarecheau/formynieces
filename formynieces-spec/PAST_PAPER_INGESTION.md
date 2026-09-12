# Past-paper content pipeline

Past papers are source material. They are never published directly into the student question pool. The maintained path is:

1. An admin uploads or stages a source PDF and records its year, subject, paper type, and provenance.
2. The importer checks whether the PDF contains a text layer. Text is extracted directly where possible; scanned pages are rendered at a consistent resolution and OCR'd.
3. Page images are deskewed and orientation-corrected before OCR. OCR output keeps page, block, word, bounding box, and confidence metadata so an editor can compare every field with the source.
4. A structured extraction pass proposes question boundaries, options, marks, answer keys, and syllabus-module mappings. The model may propose; it does not approve.
5. An editor confirms the original question, answer, topic, difficulty, and source page. Unmapped or uncertain records remain in the audit queue.
6. Controlled variants are generated only from approved originals. Each variant keeps a `seed_question_id`, source reference, objective, marks, module, and QC status. Generated content remains unpublished until checked.
7. The content audit reports original, generated, approved, pending, rejected, and unmapped counts by subject and topic. It is the operational queue for growing the bank over time.

The first repeatable command is `python3 scripts/past_paper_inventory.py storage/app/private/past-paper-source/'Past Papers' --output storage/app/private/past-paper-source/manifest.json`. It creates a manifest keyed by SHA-256, extracts available text into a sibling `text/` directory, and marks scanned pages as `needs_ocr`. Re-running it is safe and makes new source files visible without duplicating questions.

After the key and a PDF model are configured, an admin can extract one paper with `php artisan past-papers:extract past-paper-source/Past Papers/SEA-MATHEMATICS-2025.pdf --write`. This sends one PDF to the configured OpenRouter model's native file input and saves an unapproved JSON draft under `past-paper-source/extractions/`. It is intentionally one paper per action so cost, output, and review remain visible.

V1 has been smoke-tested against `CW-2025.pdf` (3 Creative Writing prompts) and `sea_2001_mathematics.pdf` (44 Mathematics questions). The Mathematics source did not contain an answer key, so all 44 answers remain null and require answer-key or editor confirmation. This is expected and is a release gate: extraction success never means answer-key success.

## Cost-conscious implementation

The first pass should use local tools for deterministic work: PDF inspection, rasterisation, deskewing, and text OCR. OCRmyPDF can add a searchable layer and uses Tesseract; its documented processing includes page rotation, deskewing, cleanup, and oversampling. Tesseract can emit TSV or hOCR with word confidence and bounding boxes. PaddleOCR PP-Structure is the next local option when layout, tables, or multi-column reading order need better handling.

The language model should receive one page or a small page group only after deterministic preprocessing. It should return strict JSON with source page references and uncertainty. Use it for question segmentation, SEA topic mapping, and controlled variant drafting, never as the sole answer-key authority. If the answer key is missing, the record must remain pending human verification.

## Long-term maintenance

Keep source PDFs immutable and retain the extraction version, OCR engine/model, prompt version, and reviewer timestamps. Re-running an improved extractor should create a new draft revision rather than overwrite an approved question. A question's stable lineage is:

`source paper → original question → approved variant → student exposure → physical assessment result`.

Topic coverage drives eligibility: a student enters maintenance variants only after the relevant topic cluster is covered. Parent-issued physical papers remain a separate assessment lane; their results inform guardian-facing focus and trends without silently changing mastery.
