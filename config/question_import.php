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
    ],

    // Source difficulty level (D1…D5) => practice rung (1 easy, 2 medium, 3 hard).
    'difficulty_map' => [
        1 => 1,
        2 => 1,
        3 => 2,
        4 => 3,
        5 => 3,
    ],

    // Where extracted question images are stored (on the 'public' disk).
    'media_directory' => 'question-media',
];
