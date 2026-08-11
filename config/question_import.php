<?php

/**
 * Configuration for the Moodle XML question importer.
 *
 * The importer never guesses which syllabus module a question belongs to — it
 * looks the skill code (Q01…QNN, taken from the Moodle category "Qnn <label>")
 * up in `skill_module_map`. A skill that is not listed here is reported and
 * skipped, never imported to the wrong module. To add questions for a new skill,
 * add one line here mapping its code to a syllabus_modules.id.
 *
 * `difficulty_map` collapses the source bank's five levels (D1 easiest … D5
 * hardest, with D3 = SEA standard) onto the practice climb's three rungs
 * (1 easy, 2 medium, 3 hard).
 */
return [

    // Moodle skill code => syllabus_modules.id. Comment shows the target module topic.
    'skill_module_map' => [
        'Q01' => 6,   // Addition                 -> Whole Number Operations: Addition and Subtraction
        'Q02' => 8,   // Division                 -> Whole Number Operations: Division
        'Q03' => 1,   // Place value              -> Number Concepts: Place Value up to One Million
        'Q04' => 12,  // Fraction shaded          -> Fractions: Equivalent Fractions and Simplification
        'Q05' => 12,  // Mixed to improper        -> Fractions: Equivalent Fractions and Simplification
        'Q06' => 15,  // Fraction of a quantity   -> Fractions: Fractions of a Collection
        'Q07' => 9,   // Number pattern           -> Number Patterns: Repeating, Increasing and Decreasing
        'Q08' => 11,  // Function machine         -> Number Relationships: Algebraic Thinking [REVIEW: or 10]
        'Q09' => 27,  // Coins                    -> Number: Multi-step Real-life Problem (Whole Numbers and Money) [REVIEW]
        'Q10' => 37,  // Perimeter                -> Measurement: Perimeter of Squares and Rectangles
        'Q11' => 38,  // Square side from area    -> Measurement: Area of Squares and Rectangles
        'Q12' => 43,  // Minutes to hours         -> Measurement: Time — Reading and Converting
        'Q13' => 25,  // Greatest number buyable  -> Problem Solving: Profit, Loss, Best Buy, Discount and VAT [REVIEW]
        'Q14' => 25,  // Cheaper rate             -> Problem Solving: Profit, Loss, Best Buy, Discount and VAT
        'Q15' => 41,  // Capacity                 -> Measurement: Volume and Capacity
        'Q16' => 28,  // Net to solid             -> Geometry: Properties of Solids and Plane Shapes
        'Q17' => 30,  // Line of symmetry         -> Geometry: Symmetry and Lines of Symmetry
        'Q18' => 31,  // Angles                   -> Geometry: Angles — Right, Acute and Obtuse
        'Q19' => 47,  // Pictograph               -> Statistics: Interpreting Pictographs and Block Graphs
        'Q20' => 49,  // Mean                     -> Statistics: Mean/Average — Calculation and Problems
        'Q21' => 36,  // Cutting lengths          -> Measurement: Linear Measure and Conversion
        'Q22' => 16,  // Two-step fraction        -> Fractions: One-step and Multi-step Word Problems
        'Q23' => 12,  // Compare fractions        -> Fractions: Equivalent Fractions and Simplification [REVIEW]
        'Q24' => 10,  // Sequences                -> Number Patterns: Pattern Rules and Missing Elements
        'Q25' => 7,   // Multiplication error     -> Whole Number Operations: Multiplication
        'Q26' => 8,   // Grouping with remainder  -> Whole Number Operations: Division
        'Q27' => 14,  // Divide mixed numbers     -> Fractions: Multiplication and Division
        'Q28' => 50,  // Points table             -> Statistics: Analysing Data to Draw Conclusions [REVIEW: or 46]
        'Q29' => 25,  // Discount                 -> Problem Solving: Profit, Loss, Best Buy, Discount and VAT
        'Q30' => 43,  // Elapsed time             -> Measurement: Time — Reading and Converting
        'Q31' => 40,  // Semicircle in rectangle  -> Measurement: Area of Compound Shapes
        'Q32' => 40,  // Area of a path           -> Measurement: Area of Compound Shapes
        'Q33' => 27,  // Equal bills              -> Number: Multi-step Real-life Problem (Money) [REVIEW]
        'Q34' => 24,  // Simple interest          -> Percent: One-step and Multi-step Percent Problems [REVIEW]
        'Q35' => 26,  // Matched savings          -> Problem Solving: Direct Proportion and Unequal Sharing [REVIEW]
        'Q36' => 33,  // Composing shapes         -> Geometry: Constructing Polygons and Composite Shapes
        'Q37' => 31,  // Compass turns            -> Geometry: Angles — Right, Acute and Obtuse [REVIEW]
        'Q38' => 28,  // Faces & corners          -> Geometry: Properties of Solids and Plane Shapes
        'Q39' => 29,  // Triangle angles          -> Geometry: Classifying Triangles and Quadrilaterals [REVIEW: or 31]
        'Q40' => 50,  // Pie chart                -> Statistics: Analysing Data to Draw Conclusions [REVIEW: or 47]
        'Q41' => 23,  // Percentage               -> Percent: Calculating Percent of a Quantity
        'Q42' => 27,  // Exact scores             -> Number: Multi-step Real-life Problem [REVIEW]
        'Q43' => 39,  // Composite perimeter      -> Measurement: Perimeter of Compound Shapes
        'Q44' => 25,  // VAT on a bill            -> Problem Solving: Profit, Loss, Best Buy, Discount and VAT
        'Q45' => 30,  // Transformations          -> Geometry: Symmetry and Lines of Symmetry [REVIEW: or 32]
        'Q46' => 46,  // Bar graph                -> Statistics: Tally Charts, Frequency Tables and Bar Graphs

        // --- Math, 3-band set B (B01–B85). Set A (A01–A46) is left unmapped on
        //     purpose so it is skipped — it duplicates the retired Q01–Q46 bank. ---
        'B01' => 6,   'B02' => 6,   // Add / Subtract whole numbers -> Whole Number Operations: Add and Subtract
        'B03' => 7,                 // Multiply whole numbers
        'B04' => 8,                 // Divide whole numbers
        'B05' => 1,                 // Numeral from words -> Place Value
        'B06' => 2,                 // Numeral from expanded form -> Expanded Notation
        'B07' => 1,                 // Value of a digit -> Place Value
        'B08' => 5,                 // Order/compare whole numbers
        'B09' => 3,                 // Round / estimate -> Rounding
        'B10' => 11,                // Missing number / inequality -> Algebraic Thinking [REVIEW]
        'B11' => 11,                // Missing digits in a calculation [REVIEW]
        'B12' => 7,                 // Distributive property -> Multiplication
        'B13' => 4,   'B14' => 4,   // Primes / Squares -> Factors, Multiples, Primes and Square Numbers
        'B15' => 9,   'B16' => 9,   // Number sequences -> Number Patterns: Repeating/Increasing/Decreasing
        'B17' => 10,                // Inverse sequence (find position) -> Pattern Rules and Missing Elements
        'B18' => 9,                 // Figural / dot patterns [REVIEW: or 32]
        'B19' => 11,                // Function machine -> Algebraic Thinking
        'B20' => 7,                 // Multiplication error / correct product -> Multiplication
        'B21' => 12,  'B22' => 12,  // Mixed<->improper -> Equivalent Fractions and Simplification
        'B23' => 15,  'B24' => 15,  // Fraction of a quantity / reverse -> Fractions of a Collection
        'B25' => 16,  'B26' => 16,  // Two-step fraction / of a remainder -> Fractions Word Problems
        'B27' => 13,  'B28' => 13,  // Add / Subtract fractions -> Fractions: Addition and Subtraction
        'B29' => 14,                // Divide mixed numbers -> Fractions: Multiplication and Division
        'B30' => 12,                // Compare / order fractions -> Equivalent Fractions and Simplification [REVIEW]
        'B31' => 12,                // Fraction shaded of a figure [REVIEW: or 15]
        'B32' => 22,                // Order across %, fraction, decimal -> Percent: Converting F/D/%
        'B33' => 19,                // Decimal add / subtract -> Decimals: Addition and Subtraction
        'B34' => 20,                // Decimal multiply / divide -> Decimals: Multiplication and Division
        'B35' => 23,                // Percentage of a quantity -> Percent: Calculating Percent of a Quantity
        'B36' => 23,                // Express a score as a percentage [REVIEW]
        'B37' => 24,                // Percentage increase / decrease -> Percent: Multi-step Percent Problems
        'B38' => 27,  'B39' => 27,  // Count money / Making change -> Number: Multi-step Real-life (Money)
        'B40' => 25,  'B41' => 25,  // Discount / VAT -> Profit, Loss, Best Buy, Discount and VAT
        'B42' => 24,                // Simple interest -> Percent: Multi-step Percent Problems [REVIEW]
        'B43' => 26,  'B44' => 26,  // Cost=rate x qty / Matched savings -> Direct Proportion and Unequal Sharing
        'B45' => 27,                // Complete a shopping bill -> Number: Multi-step Real-life (Money)
        'B46' => 25,  'B47' => 25,  // Greatest number buyable / cheaper unit rate -> Best Buy
        'B48' => 27,                // Number-of-coins puzzle -> Number: Multi-step Real-life (Money)
        'B49' => 37,  'B50' => 37,  // Perimeter of a shape / missing side -> Perimeter of Squares and Rectangles
        'B51' => 38,  'B54' => 38,  // Area of a rectangle / side from area -> Area of Squares and Rectangles
        'B52' => 38,                // Area of a triangle [REVIEW: no dedicated module]
        'B53' => 40,                // Composite / grid area -> Area of Compound Shapes
        'B55' => 38,                // Tiling a floor -> Area of Squares and Rectangles [REVIEW]
        'B56' => 41,                // Volume of a cuboid -> Volume and Capacity
        'B57' => 39,                // Circumference of a circle -> Perimeter of Compound Shapes [REVIEW]
        'B58' => 39,                // Perimeter of composite -> Perimeter of Compound Shapes
        'B59' => 40,  'B60' => 40,  // Semicircle in a rectangle / area of a path -> Area of Compound Shapes
        'B61' => 41,  'B62' => 41,  // Capacity convert / filling -> Volume and Capacity
        'B63' => 42,                // Mass: balancing scales -> Mass and Weight Conversion
        'B64' => 36,  'B65' => 36,  // Metric unit conversion / reading a ruler -> Linear Measure and Conversion
        'B66' => 43,  'B67' => 43,  'B68' => 43,  'B69' => 43, // Time skills -> Time: Reading and Converting
        'B70' => 36,                // Poles-and-gaps spacing -> Linear Measure and Conversion [REVIEW]
        'B71' => 28,  'B72' => 28,  'B73' => 28,  // Solids / nets / properties -> Properties of Solids and Plane Shapes
        'B74' => 30,                // Line of symmetry -> Symmetry and Lines of Symmetry
        'B75' => 31,  'B76' => 31,  // Classify / calculate an angle -> Angles
        'B77' => 29,  'B78' => 29,  // Triangle angle / classify -> Classifying Triangles and Quadrilaterals
        'B79' => 29,                // Identify a shape by properties -> Classifying Triangles and Quadrilaterals [REVIEW]
        'B80' => 33,                // Compose shapes into a rectangle -> Constructing Polygons and Composite Shapes
        'B81' => 30,                // Transformations (slide/flip/turn) -> Symmetry and Lines of Symmetry [REVIEW: or 32]
        'B82' => 31,                // Compass directions & turns -> Angles [REVIEW]
        'B83' => 36,                // Shortest distance / route -> Linear Measure and Conversion [REVIEW]
        'B84' => 47,                // Pictograph -> Statistics: Interpreting Pictographs and Block Graphs
        'B85' => 46,                // Bar graph -> Statistics: Tally, Frequency Tables and Bar Graphs

        // --- ELA (3-band). Codes: S spelling, P punctuation/capitals, G grammar,
        //     V vocabulary, F figurative language, C comprehension. ---
        'S01' => 55,  'S02' => 55,  // Spelling: choose correct / find misspelt -> ie/ei, silent letters, homophones [REVIEW]
        'P01' => 61,  'P02' => 61,  'P03' => 61,  'P04' => 61, // Capitalisation -> Proper Nouns, Titles, Quotations, Headlines
        'P05' => 59,  'P06' => 59,  // Apostrophes (possessive / contraction) -> Apostrophes in Contractions and Possessives
        'P07' => 60,  'P08' => 60,  'P09' => 60,  // Commas / quotation marks -> Quotation Marks, Colons and Commas
        'G01' => 64,                // Subject-verb agreement -> Subject-Verb Agreement and Concord
        'G02' => 63,                // Verb tense consistency -> Verb Tense
        'G03' => 68,                // Comparative & superlative -> Comparative and Superlative Forms
        'G04' => 62,  'G05' => 62,  'G06' => 62,  // Adjective/adverb, nouns, pronouns -> Parts of Speech
        'G07' => 65,                // Conjunctions -> Prepositions and Conjunctions in Context
        'V01' => 57,  'V02' => 57,  'V03' => 57,  // Vocabulary / synonyms / antonyms -> Synonyms, Antonyms, Multiple-meaning [REVIEW: V01 or 75]
        'F01' => 81,                // Identify the technique -> Poetry: Figures of Speech [REVIEW: or 71]
        'C01' => 73,                // Reading comprehension -> Reading Comprehension: Main Idea [REVIEW]
    ],

    // Source difficulty level (D1…D5) => practice climb rung. The climb uses the
    // bank's real levels as its three rungs: D1 -> 1, D3 -> 3, D5 -> 5 (with D2
    // folding down to 1 and D4 up to 5). Mastery is earned at difficulty 5.
    'difficulty_map' => [
        1 => 1,
        2 => 1,
        3 => 3,
        4 => 5,
        5 => 5,
    ],

    // Where extracted question images are stored (on the 'public' disk).
    'media_directory' => 'question-media',
];
