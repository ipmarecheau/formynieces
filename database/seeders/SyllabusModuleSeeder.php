<?php

namespace Database\Seeders;

use App\Models\SyllabusModule;
use Illuminate\Database\Seeder;

class SyllabusModuleSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [

            // =============================================
            // MATHEMATICS - Section I (1 mark items)
            // =============================================

            // Number: Concepts, Place Value and Rounding
            ['subject' => 'Math', 'sea_section' => 'Section I', 'sequence_order' => 1,
             'topic' => 'Number Concepts: Place Value up to One Million'],
            ['subject' => 'Math', 'sea_section' => 'Section I', 'sequence_order' => 2,
             'topic' => 'Number Concepts: Expanded Notation'],
            ['subject' => 'Math', 'sea_section' => 'Section I', 'sequence_order' => 3,
             'topic' => 'Number Concepts: Rounding to the Nearest Thousand'],
            ['subject' => 'Math', 'sea_section' => 'Section I', 'sequence_order' => 4,
             'topic' => 'Number Concepts: Factors, Multiples, Primes and Square Numbers'],
            ['subject' => 'Math', 'sea_section' => 'Section I', 'sequence_order' => 5,
             'topic' => 'Number Concepts: Ordering and Comparing Whole Numbers'],

            // Number: Operations
            ['subject' => 'Math', 'sea_section' => 'Section I', 'sequence_order' => 6,
             'topic' => 'Whole Number Operations: Addition and Subtraction'],
            ['subject' => 'Math', 'sea_section' => 'Section I', 'sequence_order' => 7,
             'topic' => 'Whole Number Operations: Multiplication'],
            ['subject' => 'Math', 'sea_section' => 'Section I', 'sequence_order' => 8,
             'topic' => 'Whole Number Operations: Division'],

            // Fractions
            ['subject' => 'Math', 'sea_section' => 'Section I', 'sequence_order' => 9,
             'topic' => 'Fractions: Equivalent Fractions and Simplification'],
            ['subject' => 'Math', 'sea_section' => 'Section I', 'sequence_order' => 10,
             'topic' => 'Fractions: Addition and Subtraction'],
            ['subject' => 'Math', 'sea_section' => 'Section I', 'sequence_order' => 11,
             'topic' => 'Fractions: Multiplication and Division'],
            ['subject' => 'Math', 'sea_section' => 'Section I', 'sequence_order' => 12,
             'topic' => 'Fractions: Fractions of a Collection'],

            // Decimals
            ['subject' => 'Math', 'sea_section' => 'Section I', 'sequence_order' => 13,
             'topic' => 'Decimals: Place Value and Expanded Notation'],
            ['subject' => 'Math', 'sea_section' => 'Section I', 'sequence_order' => 14,
             'topic' => 'Decimals: Ordering and Rounding'],
            ['subject' => 'Math', 'sea_section' => 'Section I', 'sequence_order' => 15,
             'topic' => 'Decimals: Addition and Subtraction'],
            ['subject' => 'Math', 'sea_section' => 'Section I', 'sequence_order' => 16,
             'topic' => 'Decimals: Multiplication and Division'],

            // Percent
            ['subject' => 'Math', 'sea_section' => 'Section I', 'sequence_order' => 17,
             'topic' => 'Percent: Converting Between Fractions, Decimals and Percent'],
            ['subject' => 'Math', 'sea_section' => 'Section I', 'sequence_order' => 18,
             'topic' => 'Percent: Calculating Percent of a Quantity'],

            // Geometry Section I
            ['subject' => 'Math', 'sea_section' => 'Section I', 'sequence_order' => 19,
             'topic' => 'Geometry: Properties of Solids and Plane Shapes'],
            ['subject' => 'Math', 'sea_section' => 'Section I', 'sequence_order' => 20,
             'topic' => 'Geometry: Classifying Triangles and Quadrilaterals'],
            ['subject' => 'Math', 'sea_section' => 'Section I', 'sequence_order' => 21,
             'topic' => 'Geometry: Symmetry and Lines of Symmetry'],
            ['subject' => 'Math', 'sea_section' => 'Section I', 'sequence_order' => 22,
             'topic' => 'Geometry: Angles — Right, Acute and Obtuse'],
            ['subject' => 'Math', 'sea_section' => 'Section I', 'sequence_order' => 23,
             'topic' => 'Geometry: Geometric Patterns'],

            // Measurement Section I
            ['subject' => 'Math', 'sea_section' => 'Section I', 'sequence_order' => 24,
             'topic' => 'Measurement: Linear Measure and Conversion'],
            ['subject' => 'Math', 'sea_section' => 'Section I', 'sequence_order' => 25,
             'topic' => 'Measurement: Perimeter of Squares and Rectangles'],
            ['subject' => 'Math', 'sea_section' => 'Section I', 'sequence_order' => 26,
             'topic' => 'Measurement: Area of Squares and Rectangles'],
            ['subject' => 'Math', 'sea_section' => 'Section I', 'sequence_order' => 27,
             'topic' => 'Measurement: Volume and Capacity'],
            ['subject' => 'Math', 'sea_section' => 'Section I', 'sequence_order' => 28,
             'topic' => 'Measurement: Mass and Weight Conversion'],
            ['subject' => 'Math', 'sea_section' => 'Section I', 'sequence_order' => 29,
             'topic' => 'Measurement: Time — Reading and Converting'],

            // Statistics Section I
            ['subject' => 'Math', 'sea_section' => 'Section I', 'sequence_order' => 30,
             'topic' => 'Statistics: Tally Charts, Frequency Tables and Bar Graphs'],
            ['subject' => 'Math', 'sea_section' => 'Section I', 'sequence_order' => 31,
             'topic' => 'Statistics: Interpreting Pictographs and Block Graphs'],
            ['subject' => 'Math', 'sea_section' => 'Section I', 'sequence_order' => 32,
             'topic' => 'Statistics: Mode of a Data Set'],

            // =============================================
            // MATHEMATICS - Section II (2-3 mark items)
            // =============================================

            ['subject' => 'Math', 'sea_section' => 'Section II', 'sequence_order' => 33,
             'topic' => 'Number Patterns: Repeating, Increasing and Decreasing Patterns'],
            ['subject' => 'Math', 'sea_section' => 'Section II', 'sequence_order' => 34,
             'topic' => 'Number Patterns: Pattern Rules and Missing Elements'],
            ['subject' => 'Math', 'sea_section' => 'Section II', 'sequence_order' => 35,
             'topic' => 'Number Relationships: Algebraic Thinking with Unknown Numbers'],
            ['subject' => 'Math', 'sea_section' => 'Section II', 'sequence_order' => 36,
             'topic' => 'Fractions: One-step and Multi-step Word Problems'],
            ['subject' => 'Math', 'sea_section' => 'Section II', 'sequence_order' => 37,
             'topic' => 'Decimals: Real-world Problems with Four Operations'],
            ['subject' => 'Math', 'sea_section' => 'Section II', 'sequence_order' => 38,
             'topic' => 'Percent: One-step and Multi-step Percent Problems'],
            ['subject' => 'Math', 'sea_section' => 'Section II', 'sequence_order' => 39,
             'topic' => 'Problem Solving: Profit, Loss, Best Buy, Discount and VAT'],
            ['subject' => 'Math', 'sea_section' => 'Section II', 'sequence_order' => 40,
             'topic' => 'Problem Solving: Direct Proportion and Unequal Sharing'],
            ['subject' => 'Math', 'sea_section' => 'Section II', 'sequence_order' => 41,
             'topic' => 'Geometry: Constructing Polygons and Composite Shapes'],
            ['subject' => 'Math', 'sea_section' => 'Section II', 'sequence_order' => 42,
             'topic' => 'Geometry: Solving Problems with Solids and Plane Shapes'],
            ['subject' => 'Math', 'sea_section' => 'Section II', 'sequence_order' => 43,
             'topic' => 'Measurement: Perimeter of Compound Shapes'],
            ['subject' => 'Math', 'sea_section' => 'Section II', 'sequence_order' => 44,
             'topic' => 'Measurement: Area of Compound Shapes'],
            ['subject' => 'Math', 'sea_section' => 'Section II', 'sequence_order' => 45,
             'topic' => 'Measurement: Real-life Problems — Volume, Capacity, Mass and Time'],
            ['subject' => 'Math', 'sea_section' => 'Section II', 'sequence_order' => 46,
             'topic' => 'Statistics: Mean/Average — Calculation and Problems'],
            ['subject' => 'Math', 'sea_section' => 'Section II', 'sequence_order' => 47,
             'topic' => 'Statistics: Analysing Data to Draw Conclusions and Make Decisions'],

            // =============================================
            // MATHEMATICS - Section III (4 mark items)
            // =============================================

            ['subject' => 'Math', 'sea_section' => 'Section III', 'sequence_order' => 48,
             'topic' => 'Number: Multi-step Real-life Problem (Whole Numbers and Money)'],
            ['subject' => 'Math', 'sea_section' => 'Section III', 'sequence_order' => 49,
             'topic' => 'Geometry: Multi-step Problem Involving Shapes and Properties'],
            ['subject' => 'Math', 'sea_section' => 'Section III', 'sequence_order' => 50,
             'topic' => 'Measurement: Multi-step Problem Involving Area, Perimeter or Volume'],
            ['subject' => 'Math', 'sea_section' => 'Section III', 'sequence_order' => 51,
             'topic' => 'Statistics: Multi-step Data Analysis Problem'],

            // =============================================
            // ENGLISH LANGUAGE ARTS - Section I
            // Spelling, Punctuation, Capitalisation, Grammar
            // =============================================

            ['subject' => 'English Editing', 'sea_section' => 'Section I', 'sequence_order' => 52,
             'topic' => 'Spelling: Plural Forms — y to i, f to v, and -es endings'],
            ['subject' => 'English Editing', 'sea_section' => 'Section I', 'sequence_order' => 53,
             'topic' => 'Spelling: Doubling Final Consonant Before Adding Endings'],
            ['subject' => 'English Editing', 'sea_section' => 'Section I', 'sequence_order' => 54,
             'topic' => 'Spelling: Dropping Silent -e Before Adding Endings'],
            ['subject' => 'English Editing', 'sea_section' => 'Section I', 'sequence_order' => 55,
             'topic' => 'Spelling: ie/ei Words, Silent Letters and Common Homophones'],
            ['subject' => 'English Editing', 'sea_section' => 'Section I', 'sequence_order' => 56,
             'topic' => 'Spelling: Prefixes, Suffixes and Root Words'],
            ['subject' => 'English Editing', 'sea_section' => 'Section I', 'sequence_order' => 57,
             'topic' => 'Spelling: Synonyms, Antonyms, Homographs and Multiple-meaning Words'],

            ['subject' => 'English Editing', 'sea_section' => 'Section I', 'sequence_order' => 58,
             'topic' => 'Punctuation: Full Stop, Question Mark and Exclamation Mark'],
            ['subject' => 'English Editing', 'sea_section' => 'Section I', 'sequence_order' => 59,
             'topic' => 'Punctuation: Apostrophes in Contractions and Possessives'],
            ['subject' => 'English Editing', 'sea_section' => 'Section I', 'sequence_order' => 60,
             'topic' => 'Punctuation: Quotation Marks, Colons and Commas'],
            ['subject' => 'English Editing', 'sea_section' => 'Section I', 'sequence_order' => 61,
             'topic' => 'Capitalisation: Proper Nouns, Titles, Quotations and Headlines'],

            ['subject' => 'English Editing', 'sea_section' => 'Section I', 'sequence_order' => 62,
             'topic' => 'Grammar: Parts of Speech — Nouns, Pronouns, Adjectives, Adverbs'],
            ['subject' => 'English Editing', 'sea_section' => 'Section I', 'sequence_order' => 63,
             'topic' => 'Grammar: Verb Tense — Simple, Continuous and Perfect Forms'],
            ['subject' => 'English Editing', 'sea_section' => 'Section I', 'sequence_order' => 64,
             'topic' => 'Grammar: Subject-Verb Agreement and Concord'],
            ['subject' => 'English Editing', 'sea_section' => 'Section I', 'sequence_order' => 65,
             'topic' => 'Grammar: Prepositions and Conjunctions in Context'],
            ['subject' => 'English Editing', 'sea_section' => 'Section I', 'sequence_order' => 66,
             'topic' => 'Grammar: Compound and Complex Sentences'],
            ['subject' => 'English Editing', 'sea_section' => 'Section I', 'sequence_order' => 67,
             'topic' => 'Grammar: Modals — can, may, should, would, could, might'],
            ['subject' => 'English Editing', 'sea_section' => 'Section I', 'sequence_order' => 68,
             'topic' => 'Grammar: Comparative and Superlative Forms of Adjectives and Adverbs'],

            // =============================================
            // ENGLISH COMPREHENSION - Section II
            // =============================================

            // Non-fiction / Fiction Text
            ['subject' => 'English Comprehension', 'sea_section' => 'Section II', 'sequence_order' => 69,
             'topic' => 'Reading Comprehension: Identifying Main Idea in Non-fiction Text'],
            ['subject' => 'English Comprehension', 'sea_section' => 'Section II', 'sequence_order' => 70,
             'topic' => 'Reading Comprehension: Identifying Supporting Details'],
            ['subject' => 'English Comprehension', 'sea_section' => 'Section II', 'sequence_order' => 71,
             'topic' => 'Reading Comprehension: Contextual Meaning of Words and Phrases'],
            ['subject' => 'English Comprehension', 'sea_section' => 'Section II', 'sequence_order' => 72,
             'topic' => 'Reading Comprehension: Cause and Effect Relationships'],
            ['subject' => 'English Comprehension', 'sea_section' => 'Section II', 'sequence_order' => 73,
             'topic' => 'Reading Comprehension: Inferring Meaning and Making Connections'],
            ['subject' => 'English Comprehension', 'sea_section' => 'Section II', 'sequence_order' => 74,
             'topic' => 'Reading Comprehension: Writer\'s Purpose and Audience'],
            ['subject' => 'English Comprehension', 'sea_section' => 'Section II', 'sequence_order' => 75,
             'topic' => 'Reading Comprehension: Evaluating Texts and Supporting Personal Views'],

            // Poetry
            ['subject' => 'English Comprehension', 'sea_section' => 'Section II', 'sequence_order' => 76,
             'topic' => 'Poetry: Retrieving Explicitly Stated Information'],
            ['subject' => 'English Comprehension', 'sea_section' => 'Section II', 'sequence_order' => 77,
             'topic' => 'Poetry: Figures of Speech — Simile, Metaphor, Personification'],
            ['subject' => 'English Comprehension', 'sea_section' => 'Section II', 'sequence_order' => 78,
             'topic' => 'Poetry: Words that Appeal to the Senses and Create Imagery'],
            ['subject' => 'English Comprehension', 'sea_section' => 'Section II', 'sequence_order' => 79,
             'topic' => 'Poetry: Mood, Tone and the Writer\'s Point of View'],
            ['subject' => 'English Comprehension', 'sea_section' => 'Section II', 'sequence_order' => 80,
             'topic' => 'Poetry: Drawing Conclusions and Making Judgements on Characters'],
            ['subject' => 'English Comprehension', 'sea_section' => 'Section II', 'sequence_order' => 81,
             'topic' => 'Poetry: Connecting Literature to Real-life Situations'],
            ['subject' => 'English Comprehension', 'sea_section' => 'Section II', 'sequence_order' => 82,
             'topic' => 'Poetry: Offering Solutions to Conflicts and Evaluating Appreciation'],

            // Graphic Text
            ['subject' => 'English Comprehension', 'sea_section' => 'Section II', 'sequence_order' => 83,
             'topic' => 'Graphic Text: Comprehending Overt Messages in Media Texts'],
            ['subject' => 'English Comprehension', 'sea_section' => 'Section II', 'sequence_order' => 84,
             'topic' => 'Graphic Text: Identifying Implied Messages and Design Elements'],
            ['subject' => 'English Comprehension', 'sea_section' => 'Section II', 'sequence_order' => 85,
             'topic' => 'Graphic Text: Purpose and Audience of Media Texts'],
            ['subject' => 'English Comprehension', 'sea_section' => 'Section II', 'sequence_order' => 86,
             'topic' => 'Graphic Text: Evaluating Techniques Used in Media Texts'],

            // =============================================
            // ELA WRITING - Section III
            // =============================================

            ['subject' => 'English Editing', 'sea_section' => 'Section III', 'sequence_order' => 87,
             'topic' => 'ELA Writing: Narrative Writing — Story Structure and Descriptive Language'],
            ['subject' => 'English Editing', 'sea_section' => 'Section III', 'sequence_order' => 88,
             'topic' => 'ELA Writing: Expository Writing — Reports and Factual Detail'],
            ['subject' => 'English Editing', 'sea_section' => 'Section III', 'sequence_order' => 89,
             'topic' => 'ELA Writing: Figurative Language — Simile, Metaphor, Sensory Detail'],
            ['subject' => 'English Editing', 'sea_section' => 'Section III', 'sequence_order' => 90,
             'topic' => 'ELA Writing: Organisation, Coherence and Sentence Variety'],
        ];

        foreach ($modules as $module) {
            SyllabusModule::create($module);
        }
    }
}