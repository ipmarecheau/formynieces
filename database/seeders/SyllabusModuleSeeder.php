<?php

namespace Database\Seeders;

use App\Models\SyllabusModule;
use Illuminate\Database\Seeder;

class SyllabusModuleSeeder extends Seeder
{
    public function run(): void
    {
        SyllabusModule::truncate();

        $modules = [

            // ============================================================
            // MATHEMATICS
            // ============================================================

            [
                'subject' => 'Math', 'sea_section' => 'Section I',
                'sequence_order' => 1, 'pacing_week' => 1,
                'topic' => 'Number Concepts: Place Value up to One Million',
                'description' => 'Tests the student\'s ability to read, write and identify the place value of digits in whole numbers up to 1,000,000. Students must understand ones, tens, hundreds, thousands, ten-thousands, hundred-thousands and millions.',
                'resources' => [
                    ['title' => 'Place Value Chart — Math is Fun', 'url' => 'https://www.mathsisfun.com/place-value.html'],
                    ['title' => 'Place Value up to Millions — Khan Academy', 'url' => 'https://www.khanacademy.org/math/cc-fourth-grade-math/imp-place-value-and-rounding-2/imp-place-value/v/place-value-1'],
                    ['title' => 'Caribbean Primary Mathematics Bk 5 — Chapter 1', 'url' => null],
                ],
            ],
            [
                'subject' => 'Math', 'sea_section' => 'Section I',
                'sequence_order' => 2, 'pacing_week' => 1,
                'topic' => 'Number Concepts: Expanded Notation',
                'description' => 'Tests the ability to express whole numbers in expanded form (e.g. 304,251 = 300,000 + 4,000 + 200 + 50 + 1) and convert expanded notation back to standard numerals.',
                'resources' => [
                    ['title' => 'Expanded Form — Math is Fun', 'url' => 'https://www.mathsisfun.com/definitions/expanded-form.html'],
                    ['title' => 'Writing Numbers in Expanded Form — Khan Academy', 'url' => 'https://www.khanacademy.org/math/cc-fourth-grade-math/imp-place-value-and-rounding-2/imp-place-value/v/writing-a-number-in-expanded-form'],
                    ['title' => 'Caribbean Primary Mathematics Bk 5 — Chapter 1', 'url' => null],
                ],
            ],
            [
                'subject' => 'Math', 'sea_section' => 'Section I',
                'sequence_order' => 3, 'pacing_week' => 1,
                'topic' => 'Number Concepts: Rounding to the Nearest Thousand',
                'description' => 'Tests the ability to round whole numbers to the nearest thousand using standard rounding rules. Students must identify which thousand a number is closest to.',
                'resources' => [
                    ['title' => 'Rounding Numbers — Math is Fun', 'url' => 'https://www.mathsisfun.com/rounding-numbers.html'],
                    ['title' => 'Rounding Whole Numbers — Khan Academy', 'url' => 'https://www.khanacademy.org/math/cc-fourth-grade-math/imp-place-value-and-rounding-2/imp-rounding-whole-numbers/v/rounding-whole-numbers-1'],
                    ['title' => 'Caribbean Primary Mathematics Bk 5 — Chapter 1', 'url' => null],
                ],
            ],
            [
                'subject' => 'Math', 'sea_section' => 'Section I',
                'sequence_order' => 4, 'pacing_week' => 2,
                'topic' => 'Number Concepts: Factors, Multiples, Primes and Square Numbers',
                'description' => 'Tests knowledge of factors (numbers that divide evenly into another), multiples (products of a number), prime numbers (divisible only by 1 and itself), composite numbers, and square numbers up to 144.',
                'resources' => [
                    ['title' => 'Factors and Multiples — Math is Fun', 'url' => 'https://www.mathsisfun.com/numbers/factors-multiples.html'],
                    ['title' => 'Prime Numbers — Khan Academy', 'url' => 'https://www.khanacademy.org/math/pre-algebra/pre-algebra-factors-multiples/pre-algebra-prime-numbers/v/prime-numbers'],
                    ['title' => 'Caribbean Primary Mathematics Bk 5 — Chapter 2', 'url' => null],
                ],
            ],
            [
                'subject' => 'Math', 'sea_section' => 'Section I',
                'sequence_order' => 5, 'pacing_week' => 2,
                'topic' => 'Number Concepts: Ordering and Comparing Whole Numbers',
                'description' => 'Tests the ability to compare whole numbers using > and < symbols, and arrange sets of numbers in ascending or descending order with reference to place value.',
                'resources' => [
                    ['title' => 'Comparing and Ordering Numbers — Math is Fun', 'url' => 'https://www.mathsisfun.com/equal-less-greater.html'],
                    ['title' => 'Comparing Multi-digit Numbers — Khan Academy', 'url' => 'https://www.khanacademy.org/math/cc-fourth-grade-math/imp-place-value-and-rounding-2/imp-comparing-multi-digit-numbers/v/comparing-multi-digit-numbers'],
                    ['title' => 'Caribbean Primary Mathematics Bk 5 — Chapter 1', 'url' => null],
                ],
            ],
            [
                'subject' => 'Math', 'sea_section' => 'Section I',
                'sequence_order' => 6, 'pacing_week' => 2,
                'topic' => 'Whole Number Operations: Addition and Subtraction',
                'description' => 'Tests addition (up to 4-digit numbers, up to 4 addends) and subtraction (minuend up to 4 digits). Includes money problems using dollars only or cents only, best buy, profit and loss.',
                'resources' => [
                    ['title' => 'Addition and Subtraction — Math is Fun', 'url' => 'https://www.mathsisfun.com/numbers/addition.html'],
                    ['title' => 'Multi-digit Addition — Khan Academy', 'url' => 'https://www.khanacademy.org/math/cc-fourth-grade-math/imp-addition-and-subtraction/imp-multi-digit-addition/v/addition-with-regrouping'],
                    ['title' => 'Caribbean Primary Mathematics Bk 5 — Chapter 3', 'url' => null],
                ],
            ],
            [
                'subject' => 'Math', 'sea_section' => 'Section I',
                'sequence_order' => 7, 'pacing_week' => 3,
                'topic' => 'Whole Number Operations: Multiplication',
                'description' => 'Tests multiplication of 2-, 3- and 4-digit numbers by 1- or 2-digit multipliers. Includes real-life problems and explaining multiplication procedures using words and diagrams.',
                'resources' => [
                    ['title' => 'Multiplication — Math is Fun', 'url' => 'https://www.mathsisfun.com/numbers/multiplication-table.html'],
                    ['title' => 'Multi-digit Multiplication — Khan Academy', 'url' => 'https://www.khanacademy.org/math/cc-fourth-grade-math/multiplying-by-2-digit-numbers'],
                    ['title' => 'Caribbean Primary Mathematics Bk 5 — Chapter 4', 'url' => null],
                ],
            ],
            [
                'subject' => 'Math', 'sea_section' => 'Section I',
                'sequence_order' => 8, 'pacing_week' => 3,
                'topic' => 'Whole Number Operations: Division',
                'description' => 'Tests division of 2-, 3- and 4-digit numbers by 1- or 2-digit divisors with and without remainders. Includes interpreting remainders in context and real-life division problems.',
                'resources' => [
                    ['title' => 'Division — Math is Fun', 'url' => 'https://www.mathsisfun.com/numbers/division.html'],
                    ['title' => 'Long Division — Khan Academy', 'url' => 'https://www.khanacademy.org/math/arithmetic/arith-review-multiply-divide/arith-review-long-division/v/long-division-without-remainder'],
                    ['title' => 'Caribbean Primary Mathematics Bk 5 — Chapter 4', 'url' => null],
                ],
            ],
            [
                'subject' => 'Math', 'sea_section' => 'Section II',
                'sequence_order' => 9, 'pacing_week' => 3,
                'topic' => 'Number Patterns: Repeating, Increasing and Decreasing Patterns',
                'description' => 'Tests the ability to identify, describe and extend repeating patterns (3–5 elements), increasing patterns and decreasing patterns involving numbers and shapes.',
                'resources' => [
                    ['title' => 'Number Patterns — Math is Fun', 'url' => 'https://www.mathsisfun.com/numberpatterns.html'],
                    ['title' => 'Patterns — Khan Academy', 'url' => 'https://www.khanacademy.org/math/pre-algebra/pre-algebra-arith-prop/pre-algebra-patterns/v/practice-finding-patterns'],
                    ['title' => 'Caribbean Primary Mathematics Bk 5 — Chapter 5', 'url' => null],
                ],
            ],
            [
                'subject' => 'Math', 'sea_section' => 'Section II',
                'sequence_order' => 10, 'pacing_week' => 4,
                'topic' => 'Number Patterns: Pattern Rules and Missing Elements',
                'description' => 'Tests the ability to determine a pattern rule, insert missing elements, predict subsequent elements, and identify errors in a given pattern.',
                'resources' => [
                    ['title' => 'Finding Pattern Rules — Math is Fun', 'url' => 'https://www.mathsisfun.com/algebra/sequences-finding-rule.html'],
                    ['title' => 'Number Patterns — Khan Academy', 'url' => 'https://www.khanacademy.org/math/pre-algebra/pre-algebra-arith-prop/pre-algebra-patterns/e/patterns_1'],
                    ['title' => 'Caribbean Primary Mathematics Bk 5 — Chapter 5', 'url' => null],
                ],
            ],
            [
                'subject' => 'Math', 'sea_section' => 'Section II',
                'sequence_order' => 11, 'pacing_week' => 4,
                'topic' => 'Number Relationships: Algebraic Thinking with Unknown Numbers',
                'description' => 'Tests the ability to solve number sentences with one unknown number represented by a symbol (e.g. □ + 5 = 12). Includes explaining reasoning and exploring number relationships.',
                'resources' => [
                    ['title' => 'Intro to Variables — Khan Academy', 'url' => 'https://www.khanacademy.org/math/pre-algebra/pre-algebra-equations-expressions/pre-algebra-variables/v/what-is-a-variable'],
                    ['title' => 'Solving Simple Equations — Math is Fun', 'url' => 'https://www.mathsisfun.com/algebra/equations-solving.html'],
                    ['title' => 'Caribbean Primary Mathematics Bk 5 — Chapter 5', 'url' => null],
                ],
            ],
            [
                'subject' => 'Math', 'sea_section' => 'Section I',
                'sequence_order' => 12, 'pacing_week' => 4,
                'topic' => 'Fractions: Equivalent Fractions and Simplification',
                'description' => 'Tests recognition and generation of equivalent fractions using models and rules. Students must reduce fractions to their lowest equivalent form and understand proper fractions, improper fractions and mixed numbers.',
                'resources' => [
                    ['title' => 'Equivalent Fractions — Math is Fun', 'url' => 'https://www.mathsisfun.com/equivalent_fractions.html'],
                    ['title' => 'Equivalent Fractions — Khan Academy', 'url' => 'https://www.khanacademy.org/math/cc-fourth-grade-math/imp-fractions-2/imp-equivalent-fractions/v/equivalent-fractions'],
                    ['title' => 'Caribbean Primary Mathematics Bk 5 — Chapter 6', 'url' => null],
                ],
            ],
            [
                'subject' => 'Math', 'sea_section' => 'Section I',
                'sequence_order' => 13, 'pacing_week' => 5,
                'topic' => 'Fractions: Addition and Subtraction',
                'description' => 'Tests addition and subtraction of fractions with same denominators, one denominator as a multiple of the other, and mixed numbers. Includes adding fractions to whole numbers and subtracting fractions from whole numbers.',
                'resources' => [
                    ['title' => 'Adding Fractions — Math is Fun', 'url' => 'https://www.mathsisfun.com/fractions_addition.html'],
                    ['title' => 'Adding and Subtracting Fractions — Khan Academy', 'url' => 'https://www.khanacademy.org/math/cc-fourth-grade-math/imp-fractions-2/imp-adding-and-subtracting-fractions-with-unlike-denominators/v/adding-fractions-with-unlike-denominators'],
                    ['title' => 'Caribbean Primary Mathematics Bk 5 — Chapter 6', 'url' => null],
                ],
            ],
            [
                'subject' => 'Math', 'sea_section' => 'Section I',
                'sequence_order' => 14, 'pacing_week' => 5,
                'topic' => 'Fractions: Multiplication and Division',
                'description' => 'Tests multiplication of fractions by whole numbers, fraction by fraction, and mixed numbers. Also covers division of whole numbers by fractions, fractions by whole numbers, and fractions by fractions.',
                'resources' => [
                    ['title' => 'Multiplying Fractions — Math is Fun', 'url' => 'https://www.mathsisfun.com/fractions_multiplication.html'],
                    ['title' => 'Multiplying Fractions — Khan Academy', 'url' => 'https://www.khanacademy.org/math/arithmetic/fraction-arithmetic/arith-review-multiply-fractions/v/multiplying-fractions'],
                    ['title' => 'Caribbean Primary Mathematics Bk 5 — Chapter 6', 'url' => null],
                ],
            ],
            [
                'subject' => 'Math', 'sea_section' => 'Section I',
                'sequence_order' => 15, 'pacing_week' => 5,
                'topic' => 'Fractions: Fractions of a Collection',
                'description' => 'Tests the ability to calculate a fraction of a collection or set (e.g. ¾ of 24 = 18), find the whole given a unit fraction part, and place fractions including mixed numbers on a number line.',
                'resources' => [
                    ['title' => 'Fractions of a Number — Math is Fun', 'url' => 'https://www.mathsisfun.com/fractions_of_number.html'],
                    ['title' => 'Fractions as Division — Khan Academy', 'url' => 'https://www.khanacademy.org/math/cc-fifth-grade-math/imp-fractions-3/imp-fractions-as-division/v/fractions-as-division'],
                    ['title' => 'Caribbean Primary Mathematics Bk 5 — Chapter 6', 'url' => null],
                ],
            ],
            [
                'subject' => 'Math', 'sea_section' => 'Section II',
                'sequence_order' => 16, 'pacing_week' => 6,
                'topic' => 'Fractions: One-step and Multi-step Word Problems',
                'description' => 'Tests the ability to solve one-step and multi-step real-life problems involving fractions, using the four operations. Includes problems involving mixed numbers and improper fractions in context.',
                'resources' => [
                    ['title' => 'Fraction Word Problems — Khan Academy', 'url' => 'https://www.khanacademy.org/math/cc-fifth-grade-math/imp-fractions-3/imp-fractions-word-problems/e/fractions_word_problems'],
                    ['title' => 'Fraction Problem Solving — Math is Fun', 'url' => 'https://www.mathsisfun.com/fractions-index.html'],
                    ['title' => 'Caribbean Primary Mathematics Bk 5 — Chapter 6', 'url' => null],
                ],
            ],
            [
                'subject' => 'Math', 'sea_section' => 'Section I',
                'sequence_order' => 17, 'pacing_week' => 6,
                'topic' => 'Decimals: Place Value and Expanded Notation',
                'description' => 'Tests knowledge of decimal place value (tenths, hundredths), expressing decimals in expanded notation, and connecting decimals to base ten fractions. Students must state the place value and value of digits in decimal numbers.',
                'resources' => [
                    ['title' => 'Decimals — Math is Fun', 'url' => 'https://www.mathsisfun.com/decimals.html'],
                    ['title' => 'Decimal Place Value — Khan Academy', 'url' => 'https://www.khanacademy.org/math/cc-fifth-grade-math/imp-decimals-3/imp-decimal-place-value-intro/v/decimal-place-value'],
                    ['title' => 'Caribbean Primary Mathematics Bk 5 — Chapter 7', 'url' => null],
                ],
            ],
            [
                'subject' => 'Math', 'sea_section' => 'Section I',
                'sequence_order' => 18, 'pacing_week' => 6,
                'topic' => 'Decimals: Ordering and Rounding',
                'description' => 'Tests comparing and ordering decimals up to hundredths in ascending and descending order. Also covers rounding decimals to the nearest whole number and to the nearest tenth.',
                'resources' => [
                    ['title' => 'Rounding Decimals — Math is Fun', 'url' => 'https://www.mathsisfun.com/rounding-numbers.html'],
                    ['title' => 'Comparing Decimals — Khan Academy', 'url' => 'https://www.khanacademy.org/math/cc-fifth-grade-math/imp-decimals-3/imp-comparing-decimals/v/comparing-decimals-example'],
                    ['title' => 'Caribbean Primary Mathematics Bk 5 — Chapter 7', 'url' => null],
                ],
            ],
            [
                'subject' => 'Math', 'sea_section' => 'Section I',
                'sequence_order' => 19, 'pacing_week' => 7,
                'topic' => 'Decimals: Addition and Subtraction',
                'description' => 'Tests the algorithm for adding and subtracting decimals to hundredths, solving real-world problems involving money transactions, and applying decimal knowledge to record measurements.',
                'resources' => [
                    ['title' => 'Adding Decimals — Math is Fun', 'url' => 'https://www.mathsisfun.com/adding-decimals.html'],
                    ['title' => 'Adding Decimals — Khan Academy', 'url' => 'https://www.khanacademy.org/math/cc-fifth-grade-math/imp-decimals-3/imp-adding-decimals/v/adding-decimals'],
                    ['title' => 'Caribbean Primary Mathematics Bk 5 — Chapter 7', 'url' => null],
                ],
            ],
            [
                'subject' => 'Math', 'sea_section' => 'Section I',
                'sequence_order' => 20, 'pacing_week' => 7,
                'topic' => 'Decimals: Multiplication and Division',
                'description' => 'Tests multiplication of a decimal by a whole number, tenths by tenths, and division of a decimal by a whole number (up to 2 decimal places). Includes recognising number patterns when multiplying/dividing by 10 or 100.',
                'resources' => [
                    ['title' => 'Multiplying Decimals — Math is Fun', 'url' => 'https://www.mathsisfun.com/multiplying-decimals.html'],
                    ['title' => 'Multiplying Decimals — Khan Academy', 'url' => 'https://www.khanacademy.org/math/cc-fifth-grade-math/imp-decimals-3/imp-multiplying-decimals/v/multiplying-decimals'],
                    ['title' => 'Caribbean Primary Mathematics Bk 5 — Chapter 7', 'url' => null],
                ],
            ],
            [
                'subject' => 'Math', 'sea_section' => 'Section II',
                'sequence_order' => 21, 'pacing_week' => 7,
                'topic' => 'Decimals: Real-world Problems with Four Operations',
                'description' => 'Tests solving one-step and multi-step real-world problems involving decimals using all four operations, including money transactions, bills, best buy, profit and loss.',
                'resources' => [
                    ['title' => 'Decimal Word Problems — Khan Academy', 'url' => 'https://www.khanacademy.org/math/cc-fifth-grade-math/imp-decimals-3/imp-decimals-word-problems/e/decimal_word_problems'],
                    ['title' => 'Decimal Problem Solving — Math is Fun', 'url' => 'https://www.mathsisfun.com/decimals-index.html'],
                    ['title' => 'Caribbean Primary Mathematics Bk 5 — Chapter 7', 'url' => null],
                ],
            ],
            [
                'subject' => 'Math', 'sea_section' => 'Section I',
                'sequence_order' => 22, 'pacing_week' => 8,
                'topic' => 'Percent: Converting Between Fractions, Decimals and Percent',
                'description' => 'Tests the ability to express percents as fractions (50%=½, 25%=¼, 20%=⅕, 10%=1/10) and decimals (0.5, 0.25, 0.2, 0.1). Students must compare and order fractions, decimals and percents.',
                'resources' => [
                    ['title' => 'Percentages — Math is Fun', 'url' => 'https://www.mathsisfun.com/percentage.html'],
                    ['title' => 'Intro to Percentages — Khan Academy', 'url' => 'https://www.khanacademy.org/math/pre-algebra/pre-algebra-ratios-rates/pre-algebra-intro-percents/v/intro-to-percents'],
                    ['title' => 'Caribbean Primary Mathematics Bk 5 — Chapter 8', 'url' => null],
                ],
            ],
            [
                'subject' => 'Math', 'sea_section' => 'Section I',
                'sequence_order' => 23, 'pacing_week' => 8,
                'topic' => 'Percent: Calculating Percent of a Quantity',
                'description' => 'Tests calculating simple percentages of quantities (e.g. 10% of $200 = $20) and expressing a quantity as a percentage of another. Students must apply mental strategies to estimate discounts.',
                'resources' => [
                    ['title' => 'Percentage of a Number — Math is Fun', 'url' => 'https://www.mathsisfun.com/percentage-calculator.html'],
                    ['title' => 'Finding a Percentage — Khan Academy', 'url' => 'https://www.khanacademy.org/math/pre-algebra/pre-algebra-ratios-rates/pre-algebra-percent-problems/v/finding-percentages'],
                    ['title' => 'Caribbean Primary Mathematics Bk 5 — Chapter 8', 'url' => null],
                ],
            ],
            [
                'subject' => 'Math', 'sea_section' => 'Section II',
                'sequence_order' => 24, 'pacing_week' => 8,
                'topic' => 'Percent: One-step and Multi-step Percent Problems',
                'description' => 'Tests solving one-step and multi-step problems involving percent in real-life contexts. Includes interpreting fractions, decimals and percents in everyday contexts (e.g. ¾ hr = 45 mins).',
                'resources' => [
                    ['title' => 'Percent Word Problems — Khan Academy', 'url' => 'https://www.khanacademy.org/math/pre-algebra/pre-algebra-ratios-rates/pre-algebra-percent-problems/e/percentage_word_problems_1'],
                    ['title' => 'Percentage Problems — Math is Fun', 'url' => 'https://www.mathsisfun.com/percentage-calculator.html'],
                    ['title' => 'Caribbean Primary Mathematics Bk 5 — Chapter 8', 'url' => null],
                ],
            ],
            [
                'subject' => 'Math', 'sea_section' => 'Section II',
                'sequence_order' => 25, 'pacing_week' => 9,
                'topic' => 'Problem Solving: Profit, Loss, Best Buy, Discount and VAT',
                'description' => 'Tests solving real-life problems involving profit and loss, best buy comparisons, discount, savings, salaries, wages, loans, simple interest and VAT using whole numbers, fractions, decimals and percents.',
                'resources' => [
                    ['title' => 'Profit and Loss — Math is Fun', 'url' => 'https://www.mathsisfun.com/money/profit-loss.html'],
                    ['title' => 'Taxes and Discounts — Khan Academy', 'url' => 'https://www.khanacademy.org/math/pre-algebra/pre-algebra-ratios-rates/pre-algebra-percent-applications/v/tax-and-tip-word-problems'],
                    ['title' => 'Caribbean Primary Mathematics Bk 5 — Chapter 9', 'url' => null],
                ],
            ],
            [
                'subject' => 'Math', 'sea_section' => 'Section II',
                'sequence_order' => 26, 'pacing_week' => 9,
                'topic' => 'Problem Solving: Direct Proportion and Unequal Sharing',
                'description' => 'Tests solving problems involving direct proportions (if 3 items cost $15, 7 items cost?) and unequal sharing problems where quantities are divided in different ratios without using formal ratio notation.',
                'resources' => [
                    ['title' => 'Proportions — Math is Fun', 'url' => 'https://www.mathsisfun.com/algebra/proportions.html'],
                    ['title' => 'Ratio and Proportion — Khan Academy', 'url' => 'https://www.khanacademy.org/math/pre-algebra/pre-algebra-ratios-rates'],
                    ['title' => 'Caribbean Primary Mathematics Bk 5 — Chapter 9', 'url' => null],
                ],
            ],
            [
                'subject' => 'Math', 'sea_section' => 'Section III',
                'sequence_order' => 27, 'pacing_week' => 9,
                'topic' => 'Number: Multi-step Real-life Problem (Whole Numbers and Money)',
                'description' => 'A 4-mark Section III item requiring multi-step problem solving with whole numbers and money. Tests the ability to select appropriate operations, show working clearly, and arrive at a correct final answer in context.',
                'resources' => [
                    ['title' => 'Multi-step Word Problems — Khan Academy', 'url' => 'https://www.khanacademy.org/math/cc-fourth-grade-math/imp-addition-and-subtraction/imp-multi-step-word-problems/e/multi-step-word-problems-with-whole-numbers'],
                    ['title' => 'Past SEA Papers — TTUTA Resources', 'url' => null],
                    ['title' => 'Caribbean Primary Mathematics Bk 5 — Chapter 9', 'url' => null],
                ],
            ],
            [
                'subject' => 'Math', 'sea_section' => 'Section I',
                'sequence_order' => 28, 'pacing_week' => 10,
                'topic' => 'Geometry: Properties of Solids and Plane Shapes',
                'description' => 'Tests the ability to recognise and name solids (cube, cuboid, cylinder, cone, sphere, prism, pyramid) and plane shapes from pictorial representations. Students must describe properties including faces, edges and vertices.',
                'resources' => [
                    ['title' => '3D Shapes — Math is Fun', 'url' => 'https://www.mathsisfun.com/geometry/index.html'],
                    ['title' => 'Shapes — Khan Academy', 'url' => 'https://www.khanacademy.org/math/basic-geo/basic-geo-shapes'],
                    ['title' => 'Caribbean Primary Mathematics Bk 5 — Chapter 10', 'url' => null],
                ],
            ],
            [
                'subject' => 'Math', 'sea_section' => 'Section I',
                'sequence_order' => 29, 'pacing_week' => 10,
                'topic' => 'Geometry: Classifying Triangles and Quadrilaterals',
                'description' => 'Tests classification of triangles (scalene, right-angled, isosceles, equilateral) by sides and angles, and quadrilaterals (rectangle, square, trapezium, parallelogram, rhombus) by their attributes.',
                'resources' => [
                    ['title' => 'Types of Triangles — Math is Fun', 'url' => 'https://www.mathsisfun.com/triangle.html'],
                    ['title' => 'Quadrilaterals — Math is Fun', 'url' => 'https://www.mathsisfun.com/quadrilaterals.html'],
                    ['title' => 'Caribbean Primary Mathematics Bk 5 — Chapter 10', 'url' => null],
                ],
            ],
            [
                'subject' => 'Math', 'sea_section' => 'Section I',
                'sequence_order' => 30, 'pacing_week' => 10,
                'topic' => 'Geometry: Symmetry and Lines of Symmetry',
                'description' => 'Tests whether plane shapes, letters and numerals are symmetrical, determining the number of lines of symmetry in regular and irregular shapes, and completing a symmetrical shape given half and a line of symmetry.',
                'resources' => [
                    ['title' => 'Symmetry — Math is Fun', 'url' => 'https://www.mathsisfun.com/geometry/symmetry.html'],
                    ['title' => 'Lines of Symmetry — Khan Academy', 'url' => 'https://www.khanacademy.org/math/basic-geo/basic-geo-transformations-congruence/basic-geo-symmetry/v/line-of-symmetry'],
                    ['title' => 'Caribbean Primary Mathematics Bk 5 — Chapter 10', 'url' => null],
                ],
            ],
            [
                'subject' => 'Math', 'sea_section' => 'Section I',
                'sequence_order' => 31, 'pacing_week' => 11,
                'topic' => 'Geometry: Angles — Right, Acute and Obtuse',
                'description' => 'Tests the ability to identify, name and describe angles (right, acute, obtuse) on faces of solids and plane shapes. Includes describing amounts of turn (whole, three-quarter, half, quarter) and drawing shapes with various angle sizes.',
                'resources' => [
                    ['title' => 'Angles — Math is Fun', 'url' => 'https://www.mathsisfun.com/angles.html'],
                    ['title' => 'Types of Angles — Khan Academy', 'url' => 'https://www.khanacademy.org/math/basic-geo/basic-geo-angle/basic-geo-angles/v/acute-right-and-obtuse-angles'],
                    ['title' => 'Caribbean Primary Mathematics Bk 5 — Chapter 10', 'url' => null],
                ],
            ],
            [
                'subject' => 'Math', 'sea_section' => 'Section I',
                'sequence_order' => 32, 'pacing_week' => 11,
                'topic' => 'Geometry: Geometric Patterns',
                'description' => 'Tests the ability to name, distinguish, recognise and complete repeating, increasing and decreasing patterns using solids or plane shapes. Students must determine pattern rules and describe given patterns.',
                'resources' => [
                    ['title' => 'Geometric Patterns — Math is Fun', 'url' => 'https://www.mathsisfun.com/numberpatterns.html'],
                    ['title' => 'Shape Patterns — Khan Academy', 'url' => 'https://www.khanacademy.org/math/cc-fourth-grade-math/plane-figures/imp-lines-line-segments-and-rays/e/recognizing_rays_lines_and_line_segments'],
                    ['title' => 'Caribbean Primary Mathematics Bk 5 — Chapter 10', 'url' => null],
                ],
            ],
            [
                'subject' => 'Math', 'sea_section' => 'Section II',
                'sequence_order' => 33, 'pacing_week' => 11,
                'topic' => 'Geometry: Constructing Polygons and Composite Shapes',
                'description' => 'Tests construction and drawing of regular and irregular polygons (triangles, quadrilaterals, pentagons, hexagons, octagons) using principles of parallel and perpendicular lines, angles and number of sides.',
                'resources' => [
                    ['title' => 'Polygons — Math is Fun', 'url' => 'https://www.mathsisfun.com/geometry/polygons.html'],
                    ['title' => 'Drawing Polygons — Khan Academy', 'url' => 'https://www.khanacademy.org/math/basic-geo/basic-geo-shapes/basic-geo-classifying-shapes/e/recognizing-polygons'],
                    ['title' => 'Caribbean Primary Mathematics Bk 5 — Chapter 10', 'url' => null],
                ],
            ],
            [
                'subject' => 'Math', 'sea_section' => 'Section II',
                'sequence_order' => 34, 'pacing_week' => 12,
                'topic' => 'Geometry: Solving Problems with Solids and Plane Shapes',
                'description' => 'Tests solving multi-step problems involving properties of solids and plane shapes. Students must classify and compare shapes, giving reasons for their classification.',
                'resources' => [
                    ['title' => 'Geometry Problem Solving — Math is Fun', 'url' => 'https://www.mathsisfun.com/geometry/index.html'],
                    ['title' => 'Geometry — Khan Academy', 'url' => 'https://www.khanacademy.org/math/basic-geo'],
                    ['title' => 'Past SEA Papers — TTUTA Resources', 'url' => null],
                ],
            ],
            [
                'subject' => 'Math', 'sea_section' => 'Section III',
                'sequence_order' => 35, 'pacing_week' => 12,
                'topic' => 'Geometry: Multi-step Problem Involving Shapes and Properties',
                'description' => 'A 4-mark Section III item requiring multi-step reasoning about shapes. Students must show full working and apply knowledge of shape properties, symmetry, angles and patterns together.',
                'resources' => [
                    ['title' => 'Geometry Challenges — Math is Fun', 'url' => 'https://www.mathsisfun.com/geometry/index.html'],
                    ['title' => 'Past SEA Papers — TTUTA Resources', 'url' => null],
                    ['title' => 'Caribbean Primary Mathematics Bk 5 — Chapter 10', 'url' => null],
                ],
            ],
            [
                'subject' => 'Math', 'sea_section' => 'Section I',
                'sequence_order' => 36, 'pacing_week' => 12,
                'topic' => 'Measurement: Linear Measure and Conversion',
                'description' => 'Tests selecting appropriate units for measuring lengths/distances, measuring in millimetres, centimetres, metres and kilometres, and converting between units (mm↔cm, cm↔m, km↔m). Includes applying decimal knowledge to record measurements.',
                'resources' => [
                    ['title' => 'Metric Length — Math is Fun', 'url' => 'https://www.mathsisfun.com/measure/metric-length.html'],
                    ['title' => 'Unit Conversion — Khan Academy', 'url' => 'https://www.khanacademy.org/math/cc-fourth-grade-math/imp-measurement-and-data-2/imp-converting-units-of-length/v/converting-units-length'],
                    ['title' => 'Caribbean Primary Mathematics Bk 5 — Chapter 11', 'url' => null],
                ],
            ],
            [
                'subject' => 'Math', 'sea_section' => 'Section I',
                'sequence_order' => 37, 'pacing_week' => 13,
                'topic' => 'Measurement: Perimeter of Squares and Rectangles',
                'description' => 'Tests developing and using formulae for perimeter of squares and rectangles. Students must calculate and compare perimeters, write and explain the formulae, and find perimeters of simple composite figures.',
                'resources' => [
                    ['title' => 'Perimeter — Math is Fun', 'url' => 'https://www.mathsisfun.com/geometry/perimeter.html'],
                    ['title' => 'Perimeter — Khan Academy', 'url' => 'https://www.khanacademy.org/math/cc-third-grade-math/imp-measurement/imp-perimeter/v/perimeter-of-a-shape'],
                    ['title' => 'Caribbean Primary Mathematics Bk 5 — Chapter 11', 'url' => null],
                ],
            ],
            [
                'subject' => 'Math', 'sea_section' => 'Section I',
                'sequence_order' => 38, 'pacing_week' => 13,
                'topic' => 'Measurement: Area of Squares and Rectangles',
                'description' => 'Tests developing and using formulae to calculate area of squares and rectangles (A = l × w). Students must draw different shapes of a given area, calculate area on grids, and compare/order areas.',
                'resources' => [
                    ['title' => 'Area — Math is Fun', 'url' => 'https://www.mathsisfun.com/area.html'],
                    ['title' => 'Area — Khan Academy', 'url' => 'https://www.khanacademy.org/math/cc-third-grade-math/imp-measurement/imp-area/v/introduction-to-area-and-unit-squares'],
                    ['title' => 'Caribbean Primary Mathematics Bk 5 — Chapter 11', 'url' => null],
                ],
            ],
            [
                'subject' => 'Math', 'sea_section' => 'Section II',
                'sequence_order' => 39, 'pacing_week' => 13,
                'topic' => 'Measurement: Perimeter of Compound Shapes',
                'description' => 'Tests finding perimeters of composite figures that can be dissected into rectangles and squares. Includes constructing rectangles for a given perimeter and solving real-life perimeter problems involving compound shapes.',
                'resources' => [
                    ['title' => 'Perimeter of Composite Figures — Math is Fun', 'url' => 'https://www.mathsisfun.com/geometry/perimeter.html'],
                    ['title' => 'Perimeter of Composite Figures — Khan Academy', 'url' => 'https://www.khanacademy.org/math/cc-third-grade-math/imp-measurement/imp-perimeter/e/perimeter-1'],
                    ['title' => 'Past SEA Papers — TTUTA Resources', 'url' => null],
                ],
            ],
            [
                'subject' => 'Math', 'sea_section' => 'Section II',
                'sequence_order' => 40, 'pacing_week' => 14,
                'topic' => 'Measurement: Area of Compound Shapes',
                'description' => 'Tests calculating the area of compound shapes by dissecting into rectangles and squares. Includes solving problems involving both area and perimeter of plane shapes in real-life contexts.',
                'resources' => [
                    ['title' => 'Area of Compound Shapes — Math is Fun', 'url' => 'https://www.mathsisfun.com/area.html'],
                    ['title' => 'Area of Composite Figures — Khan Academy', 'url' => 'https://www.khanacademy.org/math/cc-sixth-grade-math/cc-6th-geometry-topic/cc-6th-area-of-polygons/v/area-of-composite-figures'],
                    ['title' => 'Past SEA Papers — TTUTA Resources', 'url' => null],
                ],
            ],
            [
                'subject' => 'Math', 'sea_section' => 'Section I',
                'sequence_order' => 41, 'pacing_week' => 14,
                'topic' => 'Measurement: Volume and Capacity',
                'description' => 'Tests identifying cubic centimetre and cubic metre as standard units for volume, the relationship between litres and millilitres (1L = 1000 ml = 1000 cm³), calculating volume of cubes and cuboids, and solving capacity problems.',
                'resources' => [
                    ['title' => 'Volume — Math is Fun', 'url' => 'https://www.mathsisfun.com/measure/volume.html'],
                    ['title' => 'Volume and Surface Area — Khan Academy', 'url' => 'https://www.khanacademy.org/math/cc-fifth-grade-math/imp-geometry-3/imp-volume-1/v/volume-of-a-rectangular-prism-or-cuboid'],
                    ['title' => 'Caribbean Primary Mathematics Bk 5 — Chapter 11', 'url' => null],
                ],
            ],
            [
                'subject' => 'Math', 'sea_section' => 'Section I',
                'sequence_order' => 42, 'pacing_week' => 14,
                'topic' => 'Measurement: Mass and Weight Conversion',
                'description' => 'Tests converting kilograms to grams, measuring and comparing mass in kg and g, and solving problems involving different units of mass (e.g. total mass of items weighing 50g, 750g and 2.5kg).',
                'resources' => [
                    ['title' => 'Metric Mass — Math is Fun', 'url' => 'https://www.mathsisfun.com/measure/metric-mass.html'],
                    ['title' => 'Converting Units of Mass — Khan Academy', 'url' => 'https://www.khanacademy.org/math/cc-fourth-grade-math/imp-measurement-and-data-2/imp-converting-units-of-mass/v/converting-metric-units-of-mass'],
                    ['title' => 'Caribbean Primary Mathematics Bk 5 — Chapter 11', 'url' => null],
                ],
            ],
            [
                'subject' => 'Math', 'sea_section' => 'Section I',
                'sequence_order' => 43, 'pacing_week' => 15,
                'topic' => 'Measurement: Time — Reading and Converting',
                'description' => 'Tests reading time in five-minute intervals on digital and analog clocks, matching times on 12-hour and 24-hour clocks, converting hours to minutes, and interpreting simple time schedules and calendars.',
                'resources' => [
                    ['title' => 'Telling Time — Math is Fun', 'url' => 'https://www.mathsisfun.com/time.html'],
                    ['title' => 'Telling Time — Khan Academy', 'url' => 'https://www.khanacademy.org/math/cc-third-grade-math/imp-measurement/imp-time/v/telling-time-exercise-example'],
                    ['title' => 'Caribbean Primary Mathematics Bk 5 — Chapter 11', 'url' => null],
                ],
            ],
            [
                'subject' => 'Math', 'sea_section' => 'Section II',
                'sequence_order' => 44, 'pacing_week' => 15,
                'topic' => 'Measurement: Real-life Problems — Volume, Capacity, Mass and Time',
                'description' => 'Tests solving computational and real-life problems combining volume, capacity, mass and time. Includes elapsed time calculations, proportional reasoning with time, and multi-unit conversion problems.',
                'resources' => [
                    ['title' => 'Measurement Word Problems — Khan Academy', 'url' => 'https://www.khanacademy.org/math/cc-fourth-grade-math/imp-measurement-and-data-2'],
                    ['title' => 'Measurement Problems — Math is Fun', 'url' => 'https://www.mathsisfun.com/measure/index.html'],
                    ['title' => 'Past SEA Papers — TTUTA Resources', 'url' => null],
                ],
            ],
            [
                'subject' => 'Math', 'sea_section' => 'Section III',
                'sequence_order' => 45, 'pacing_week' => 15,
                'topic' => 'Measurement: Multi-step Problem Involving Area, Perimeter or Volume',
                'description' => 'A 4-mark Section III item requiring multi-step reasoning about measurement. Students must show full working, select appropriate formulae, and solve a complex real-life measurement problem.',
                'resources' => [
                    ['title' => 'Past SEA Papers — TTUTA Resources', 'url' => null],
                    ['title' => 'Measurement Challenges — Math is Fun', 'url' => 'https://www.mathsisfun.com/measure/index.html'],
                    ['title' => 'Caribbean Primary Mathematics Bk 5 — Chapter 11', 'url' => null],
                ],
            ],
            [
                'subject' => 'Math', 'sea_section' => 'Section I',
                'sequence_order' => 46, 'pacing_week' => 16,
                'topic' => 'Statistics: Tally Charts, Frequency Tables and Bar Graphs',
                'description' => 'Tests representing data using tally charts, frequency tables, bar graphs and block graphs using various scale factors. Students must determine suitable scales and record them in a key.',
                'resources' => [
                    ['title' => 'Bar Graphs — Math is Fun', 'url' => 'https://www.mathsisfun.com/data/bar-graphs.html'],
                    ['title' => 'Reading Bar Charts — Khan Academy', 'url' => 'https://www.khanacademy.org/math/cc-third-grade-math/imp-data-2/imp-bar-graphs/v/creating-bar-charts'],
                    ['title' => 'Caribbean Primary Mathematics Bk 5 — Chapter 12', 'url' => null],
                ],
            ],
            [
                'subject' => 'Math', 'sea_section' => 'Section I',
                'sequence_order' => 47, 'pacing_week' => 16,
                'topic' => 'Statistics: Interpreting Pictographs and Block Graphs',
                'description' => 'Tests interpreting findings displayed in pictographs and block graphs. Students must read scales, draw conclusions from visual data, and answer questions about data representation.',
                'resources' => [
                    ['title' => 'Pictographs — Math is Fun', 'url' => 'https://www.mathsisfun.com/data/pictographs.html'],
                    ['title' => 'Reading Pictographs — Khan Academy', 'url' => 'https://www.khanacademy.org/math/cc-third-grade-math/imp-data-2/imp-picture-graphs/v/reading-picture-graphs-exercise'],
                    ['title' => 'Caribbean Primary Mathematics Bk 5 — Chapter 12', 'url' => null],
                ],
            ],
            [
                'subject' => 'Math', 'sea_section' => 'Section I',
                'sequence_order' => 48, 'pacing_week' => 16,
                'topic' => 'Statistics: Mode of a Data Set',
                'description' => 'Tests determining the mode (most frequently occurring value) of a given set of data. Students must identify the mode from raw data, tables and graphs.',
                'resources' => [
                    ['title' => 'Mean, Median, Mode — Math is Fun', 'url' => 'https://www.mathsisfun.com/mean.html'],
                    ['title' => 'Mode — Khan Academy', 'url' => 'https://www.khanacademy.org/math/statistics-probability/summarizing-quantitative-data/mean-median-basics/v/statistics-intro-mean-median-and-mode'],
                    ['title' => 'Caribbean Primary Mathematics Bk 5 — Chapter 12', 'url' => null],
                ],
            ],
            [
                'subject' => 'Math', 'sea_section' => 'Section II',
                'sequence_order' => 49, 'pacing_week' => 17,
                'topic' => 'Statistics: Mean/Average — Calculation and Problems',
                'description' => 'Tests determining and using the rule for calculating the mean of a data set (sum ÷ count). Students must solve problems involving mean/average in real-life contexts.',
                'resources' => [
                    ['title' => 'Mean (Average) — Math is Fun', 'url' => 'https://www.mathsisfun.com/mean.html'],
                    ['title' => 'Mean — Khan Academy', 'url' => 'https://www.khanacademy.org/math/statistics-probability/summarizing-quantitative-data/mean-median-basics/v/mean-median-and-mode'],
                    ['title' => 'Caribbean Primary Mathematics Bk 5 — Chapter 12', 'url' => null],
                ],
            ],
            [
                'subject' => 'Math', 'sea_section' => 'Section II',
                'sequence_order' => 50, 'pacing_week' => 17,
                'topic' => 'Statistics: Analysing Data to Draw Conclusions and Make Decisions',
                'description' => 'Tests using analysed data to solve problems, draw conclusions and make decisions. Includes evaluating decisions based on data analysis and communicating findings using appropriate statistical vocabulary.',
                'resources' => [
                    ['title' => 'Data Analysis — Math is Fun', 'url' => 'https://www.mathsisfun.com/data/index.html'],
                    ['title' => 'Statistics and Probability — Khan Academy', 'url' => 'https://www.khanacademy.org/math/statistics-probability'],
                    ['title' => 'Past SEA Papers — TTUTA Resources', 'url' => null],
                ],
            ],
            [
                'subject' => 'Math', 'sea_section' => 'Section III',
                'sequence_order' => 51, 'pacing_week' => 17,
                'topic' => 'Statistics: Multi-step Data Analysis Problem',
                'description' => 'A 4-mark Section III item requiring multi-step statistical analysis. Students must read, interpret and draw conclusions from data presented in tables or graphs, showing full reasoning.',
                'resources' => [
                    ['title' => 'Past SEA Papers — TTUTA Resources', 'url' => null],
                    ['title' => 'Data and Statistics — Khan Academy', 'url' => 'https://www.khanacademy.org/math/statistics-probability'],
                    ['title' => 'Caribbean Primary Mathematics Bk 5 — Chapter 12', 'url' => null],
                ],
            ],

            // ============================================================
            // ENGLISH EDITING
            // ============================================================

            [
                'subject' => 'English Editing', 'sea_section' => 'Section I',
                'sequence_order' => 52, 'pacing_week' => 1,
                'topic' => 'Spelling: Plural Forms — y to i, f to v, and -es endings',
                'description' => 'Tests correct spelling of plural forms where y changes to i before -es (e.g. story→stories), f/fe changes to v before -es (e.g. leaf→leaves), and words requiring -es endings (e.g. box→boxes).',
                'resources' => [
                    ['title' => 'Spelling Rules — BBC Bitesize', 'url' => 'https://www.bbc.co.uk/bitesize/topics/zt62mnb'],
                    ['title' => 'Plural Spelling Rules — Spelling City', 'url' => 'https://www.spellingcity.com/plural-nouns.html'],
                    ['title' => 'Primary English Bk 5 — T&T Curriculum', 'url' => null],
                ],
            ],
            [
                'subject' => 'English Editing', 'sea_section' => 'Section I',
                'sequence_order' => 53, 'pacing_week' => 2,
                'topic' => 'Spelling: Doubling Final Consonant Before Adding Endings',
                'description' => 'Tests the rule of doubling the final consonant before adding endings when a word ends in a short vowel + single consonant (e.g. run→running, hop→hopped, bag→baggy).',
                'resources' => [
                    ['title' => 'Doubling Rule — BBC Bitesize', 'url' => 'https://www.bbc.co.uk/bitesize/topics/zt62mnb'],
                    ['title' => 'Spelling Patterns — Spelling City', 'url' => 'https://www.spellingcity.com'],
                    ['title' => 'Primary English Bk 5 — T&T Curriculum', 'url' => null],
                ],
            ],
            [
                'subject' => 'English Editing', 'sea_section' => 'Section I',
                'sequence_order' => 54, 'pacing_week' => 3,
                'topic' => 'Spelling: Dropping Silent -e Before Adding Endings',
                'description' => 'Tests correct application of the drop-e rule: when a word ends in silent -e, drop the -e before adding vowel endings (e.g. make→making, ice→icy). Includes -ie to -y before -ing (e.g. die→dying).',
                'resources' => [
                    ['title' => 'Drop the E Rule — BBC Bitesize', 'url' => 'https://www.bbc.co.uk/bitesize/topics/zt62mnb'],
                    ['title' => 'Spelling Rules Practice — Spelling City', 'url' => 'https://www.spellingcity.com'],
                    ['title' => 'Primary English Bk 5 — T&T Curriculum', 'url' => null],
                ],
            ],
            [
                'subject' => 'English Editing', 'sea_section' => 'Section I',
                'sequence_order' => 55, 'pacing_week' => 4,
                'topic' => 'Spelling: ie/ei Words, Silent Letters and Common Homophones',
                'description' => 'Tests correct spelling of ie/ei words (e.g. believe, receive), words with silent letters (e.g. knight, wrench), words with hard and soft c and g, and common homophones (e.g. their/there/they\'re, to/too/two).',
                'resources' => [
                    ['title' => 'ie or ei? — BBC Bitesize', 'url' => 'https://www.bbc.co.uk/bitesize/topics/zt62mnb'],
                    ['title' => 'Homophones — Spelling City', 'url' => 'https://www.spellingcity.com/homophones.html'],
                    ['title' => 'Primary English Bk 5 — T&T Curriculum', 'url' => null],
                ],
            ],
            [
                'subject' => 'English Editing', 'sea_section' => 'Section I',
                'sequence_order' => 56, 'pacing_week' => 5,
                'topic' => 'Spelling: Prefixes, Suffixes and Root Words',
                'description' => 'Tests making new words by adding prefixes (un-, re-, pre-, dis-, mis-) and suffixes (-ful, -less, -ness, -ment, -ly) to root words. Includes the rule that -full drops one l when added to a base word.',
                'resources' => [
                    ['title' => 'Prefixes and Suffixes — BBC Bitesize', 'url' => 'https://www.bbc.co.uk/bitesize/topics/z8jtv9q'],
                    ['title' => 'Root Words — Vocabulary.com', 'url' => 'https://www.vocabulary.com'],
                    ['title' => 'Primary English Bk 5 — T&T Curriculum', 'url' => null],
                ],
            ],
            [
                'subject' => 'English Editing', 'sea_section' => 'Section I',
                'sequence_order' => 57, 'pacing_week' => 6,
                'topic' => 'Spelling: Synonyms, Antonyms, Homographs and Multiple-meaning Words',
                'description' => 'Tests understanding of synonyms (words with similar meanings), antonyms (opposites), homographs (words spelled the same but with different meanings), and words with multiple meanings used in context.',
                'resources' => [
                    ['title' => 'Synonyms and Antonyms — Merriam-Webster for Kids', 'url' => 'https://www.merriam-webster.com/games/kids-vocabulary'],
                    ['title' => 'Vocabulary — Khan Academy', 'url' => 'https://www.khanacademy.org/ela/cc-2nd-reading-vocab'],
                    ['title' => 'Primary English Bk 5 — T&T Curriculum', 'url' => null],
                ],
            ],
            [
                'subject' => 'English Editing', 'sea_section' => 'Section I',
                'sequence_order' => 58, 'pacing_week' => 7,
                'topic' => 'Punctuation: Full Stop, Question Mark and Exclamation Mark',
                'description' => 'Tests correct use of full stops at the end of statements, question marks at the end of questions, and exclamation marks for exclamations and commands. Students must identify and correct errors in context.',
                'resources' => [
                    ['title' => 'Punctuation — BBC Bitesize', 'url' => 'https://www.bbc.co.uk/bitesize/topics/zvwwxnb'],
                    ['title' => 'End Punctuation — Khan Academy', 'url' => 'https://www.khanacademy.org/ela/cc-2nd-reading-vocab/cc-2nd-conventions/cc-2nd-punctuation/v/using-commas-in-a-list-punctuation-khan-academy'],
                    ['title' => 'Primary English Bk 5 — T&T Curriculum', 'url' => null],
                ],
            ],
            [
                'subject' => 'English Editing', 'sea_section' => 'Section I',
                'sequence_order' => 59, 'pacing_week' => 8,
                'topic' => 'Punctuation: Apostrophes in Contractions and Possessives',
                'description' => 'Tests correct use of apostrophes in contractions (e.g. it\'s, can\'t, they\'re) and possessives (e.g. the girl\'s book, the children\'s toys). Students must distinguish between possessive apostrophes and contractions.',
                'resources' => [
                    ['title' => 'Apostrophes — BBC Bitesize', 'url' => 'https://www.bbc.co.uk/bitesize/topics/zvwwxnb'],
                    ['title' => 'Possessives and Contractions — Khan Academy', 'url' => 'https://www.khanacademy.org/ela/cc-2nd-reading-vocab/cc-2nd-conventions/cc-2nd-punctuation'],
                    ['title' => 'Primary English Bk 5 — T&T Curriculum', 'url' => null],
                ],
            ],
            [
                'subject' => 'English Editing', 'sea_section' => 'Section I',
                'sequence_order' => 60, 'pacing_week' => 9,
                'topic' => 'Punctuation: Quotation Marks, Colons and Commas',
                'description' => 'Tests use of quotation marks for dialogue and direct speech, colons to introduce lists or explanations, and commas in lists, compound sentences and after introductory phrases. Students edit texts in context.',
                'resources' => [
                    ['title' => 'Commas and Quotation Marks — BBC Bitesize', 'url' => 'https://www.bbc.co.uk/bitesize/topics/zvwwxnb'],
                    ['title' => 'Punctuation — Khan Academy', 'url' => 'https://www.khanacademy.org/ela/cc-2nd-reading-vocab/cc-2nd-conventions/cc-2nd-punctuation'],
                    ['title' => 'Primary English Bk 5 — T&T Curriculum', 'url' => null],
                ],
            ],
            [
                'subject' => 'English Editing', 'sea_section' => 'Section I',
                'sequence_order' => 61, 'pacing_week' => 10,
                'topic' => 'Capitalisation: Proper Nouns, Titles, Quotations and Headlines',
                'description' => 'Tests correct capitalisation of: first word in a sentence, proper nouns (names of people, places, organisations), titles of books/poems/chapters, first word in a quotation, and important words in headlines.',
                'resources' => [
                    ['title' => 'Capitalisation Rules — BBC Bitesize', 'url' => 'https://www.bbc.co.uk/bitesize/topics/zvwwxnb'],
                    ['title' => 'Capitalisation — Grammar Book', 'url' => 'https://www.grammarbook.com/punctuation/capital.asp'],
                    ['title' => 'Primary English Bk 5 — T&T Curriculum', 'url' => null],
                ],
            ],
            [
                'subject' => 'English Editing', 'sea_section' => 'Section I',
                'sequence_order' => 62, 'pacing_week' => 11,
                'topic' => 'Grammar: Parts of Speech — Nouns, Pronouns, Adjectives, Adverbs',
                'description' => 'Tests knowledge of common, proper, collective and abstract nouns; personal, possessive, reflexive and relative pronouns; adjectives in comparative and superlative forms; and adverbs in comparative and superlative forms.',
                'resources' => [
                    ['title' => 'Parts of Speech — BBC Bitesize', 'url' => 'https://www.bbc.co.uk/bitesize/topics/zrqqtfr'],
                    ['title' => 'Grammar — Khan Academy', 'url' => 'https://www.khanacademy.org/ela/cc-2nd-reading-vocab/cc-2nd-conventions'],
                    ['title' => 'Primary English Bk 5 — T&T Curriculum', 'url' => null],
                ],
            ],
            [
                'subject' => 'English Editing', 'sea_section' => 'Section I',
                'sequence_order' => 63, 'pacing_week' => 12,
                'topic' => 'Grammar: Verb Tense — Simple, Continuous and Perfect Forms',
                'description' => 'Tests correct use of verbal forms: simple present, simple past, simple future, present continuous, and past perfect tense. Students must use the correct form of the verb and use regular and irregular verb forms accurately.',
                'resources' => [
                    ['title' => 'Verb Tenses — BBC Bitesize', 'url' => 'https://www.bbc.co.uk/bitesize/topics/zrqqtfr'],
                    ['title' => 'Verb Tenses — Khan Academy', 'url' => 'https://www.khanacademy.org/ela/cc-2nd-reading-vocab/cc-2nd-conventions/cc-2nd-grammar/v/the-simple-tenses-grammar'],
                    ['title' => 'Primary English Bk 5 — T&T Curriculum', 'url' => null],
                ],
            ],
            [
                'subject' => 'English Editing', 'sea_section' => 'Section I',
                'sequence_order' => 64, 'pacing_week' => 13,
                'topic' => 'Grammar: Subject-Verb Agreement and Concord',
                'description' => 'Tests choosing verbs that agree with subjects in number (singular/plural), ensuring noun-pronoun concord, subject-pronoun agreement, and concord in sentences containing parenthetical phrases.',
                'resources' => [
                    ['title' => 'Subject-Verb Agreement — BBC Bitesize', 'url' => 'https://www.bbc.co.uk/bitesize/topics/zrqqtfr'],
                    ['title' => 'Subject-Verb Agreement — Grammar Book', 'url' => 'https://www.grammarbook.com/grammar/subjectVerb.asp'],
                    ['title' => 'Primary English Bk 5 — T&T Curriculum', 'url' => null],
                ],
            ],
            [
                'subject' => 'English Editing', 'sea_section' => 'Section I',
                'sequence_order' => 65, 'pacing_week' => 14,
                'topic' => 'Grammar: Prepositions and Conjunctions in Context',
                'description' => 'Tests use of prepositions in context (on, in, at, by, through, between, etc.) and conjunctions to combine ideas and sentences (and, but, or, because, although, when, if, since).',
                'resources' => [
                    ['title' => 'Prepositions — BBC Bitesize', 'url' => 'https://www.bbc.co.uk/bitesize/topics/zrqqtfr'],
                    ['title' => 'Conjunctions — Khan Academy', 'url' => 'https://www.khanacademy.org/ela/cc-2nd-reading-vocab/cc-2nd-conventions/cc-2nd-grammar'],
                    ['title' => 'Primary English Bk 5 — T&T Curriculum', 'url' => null],
                ],
            ],
            [
                'subject' => 'English Editing', 'sea_section' => 'Section I',
                'sequence_order' => 66, 'pacing_week' => 15,
                'topic' => 'Grammar: Compound and Complex Sentences',
                'description' => 'Tests forming compound sentences using coordinating conjunctions, and complex sentences using a subordinating conjunction to join a main clause and subordinate clause. Students identify and correct sentence structure errors in context.',
                'resources' => [
                    ['title' => 'Compound Sentences — BBC Bitesize', 'url' => 'https://www.bbc.co.uk/bitesize/topics/zrqqtfr'],
                    ['title' => 'Types of Sentences — Khan Academy', 'url' => 'https://www.khanacademy.org/ela/cc-2nd-reading-vocab/cc-2nd-conventions/cc-2nd-grammar'],
                    ['title' => 'Primary English Bk 5 — T&T Curriculum', 'url' => null],
                ],
            ],
            [
                'subject' => 'English Editing', 'sea_section' => 'Section I',
                'sequence_order' => 67, 'pacing_week' => 16,
                'topic' => 'Grammar: Modals — can, may, should, would, could, might',
                'description' => 'Tests correct use of modal verbs (can, may, should, would, could, might) to express ability, possibility, permission, obligation and condition in sentences. Also covers past and present participles.',
                'resources' => [
                    ['title' => 'Modal Verbs — BBC Bitesize', 'url' => 'https://www.bbc.co.uk/bitesize/topics/zrqqtfr'],
                    ['title' => 'Modal Verbs — British Council', 'url' => 'https://learnenglishkids.britishcouncil.org/grammar-practice'],
                    ['title' => 'Primary English Bk 5 — T&T Curriculum', 'url' => null],
                ],
            ],
            [
                'subject' => 'English Editing', 'sea_section' => 'Section I',
                'sequence_order' => 68, 'pacing_week' => 17,
                'topic' => 'Grammar: Comparative and Superlative Forms of Adjectives and Adverbs',
                'description' => 'Tests forming comparative (e.g. taller, more beautiful) and superlative (e.g. tallest, most beautiful) forms of adjectives and adverbs. Students must recognise the function of these forms in context.',
                'resources' => [
                    ['title' => 'Comparatives and Superlatives — BBC Bitesize', 'url' => 'https://www.bbc.co.uk/bitesize/topics/zrqqtfr'],
                    ['title' => 'Adjectives — British Council', 'url' => 'https://learnenglishkids.britishcouncil.org/grammar-practice'],
                    ['title' => 'Primary English Bk 5 — T&T Curriculum', 'url' => null],
                ],
            ],
            [
                'subject' => 'English Editing', 'sea_section' => 'Section III',
                'sequence_order' => 69, 'pacing_week' => 18,
                'topic' => 'ELA Writing: Narrative Writing — Story Structure and Descriptive Language',
                'description' => 'Tests the ability to write a narrative story with a clear beginning, middle and end. Assesses use of descriptive language, sensory details, interesting characters and settings, and engaging plot development.',
                'resources' => [
                    ['title' => 'Story Writing — BBC Bitesize', 'url' => 'https://www.bbc.co.uk/bitesize/topics/zkxgr82'],
                    ['title' => 'Narrative Writing Tips — ReadWriteThink', 'url' => 'https://www.readwritethink.org'],
                    ['title' => 'Past SEA Writing Papers — TTUTA Resources', 'url' => null],
                ],
            ],
            [
                'subject' => 'English Editing', 'sea_section' => 'Section III',
                'sequence_order' => 70, 'pacing_week' => 19,
                'topic' => 'ELA Writing: Expository Writing — Reports and Factual Detail',
                'description' => 'Tests the ability to write a clear expository piece using factual details, formal language and tone. Assesses logical organisation, appropriate vocabulary and use of evidence.',
                'resources' => [
                    ['title' => 'Report Writing — BBC Bitesize', 'url' => 'https://www.bbc.co.uk/bitesize/topics/zkxgr82'],
                    ['title' => 'Expository Writing — ReadWriteThink', 'url' => 'https://www.readwritethink.org'],
                    ['title' => 'Past SEA Writing Papers — TTUTA Resources', 'url' => null],
                ],
            ],
            [
                'subject' => 'English Editing', 'sea_section' => 'Section III',
                'sequence_order' => 71, 'pacing_week' => 20,
                'topic' => 'ELA Writing: Figurative Language — Simile, Metaphor, Sensory Detail',
                'description' => 'Tests incorporating figurative language and sensory details in creative writing to enhance meaning and imagery.',
                'resources' => [
                    ['title' => 'Figurative Language — BBC Bitesize', 'url' => 'https://www.bbc.co.uk/bitesize/topics/zkxgr82'],
                    ['title' => 'Figurative Language — ReadWriteThink', 'url' => 'https://www.readwritethink.org'],
                    ['title' => 'Primary English Bk 5 — T&T Curriculum', 'url' => null],
                ],
            ],
            [
                'subject' => 'English Editing', 'sea_section' => 'Section III',
                'sequence_order' => 72, 'pacing_week' => 21,
                'topic' => 'ELA Writing: Organisation, Coherence and Sentence Variety',
                'description' => 'Tests demonstrating effective organisation of ideas, writing coherently with logical flow, and generating a variety of sentence types.',
                'resources' => [
                    ['title' => 'Essay Structure — BBC Bitesize', 'url' => 'https://www.bbc.co.uk/bitesize/topics/zkxgr82'],
                    ['title' => 'Paragraph Writing — ReadWriteThink', 'url' => 'https://www.readwritethink.org'],
                    ['title' => 'Past SEA Writing Papers — TTUTA Resources', 'url' => null],
                ],
            ],
            [
                'subject' => 'English Comprehension', 'sea_section' => 'Section II',
                'sequence_order' => 73, 'pacing_week' => 1,
                'topic' => 'Reading Comprehension: Identifying Main Idea in Non-fiction Text',
                'description' => 'Tests identifying the main idea both explicitly stated and implied in non-fiction texts. Students must distinguish main idea from supporting details.',
                'resources' => [
                    ['title' => 'Main Idea — BBC Bitesize', 'url' => 'https://www.bbc.co.uk/bitesize/topics/zs44jxs'],
                    ['title' => 'Finding Main Idea — Khan Academy', 'url' => 'https://www.khanacademy.org/ela/cc-2nd-reading-vocab/cc-2nd-informational-text'],
                    ['title' => 'Primary English Bk 5 — T&T Curriculum', 'url' => null],
                ],
            ],
            [
                'subject' => 'English Comprehension', 'sea_section' => 'Section II',
                'sequence_order' => 74, 'pacing_week' => 2,
                'topic' => 'Reading Comprehension: Identifying Supporting Details',
                'description' => 'Tests identifying supporting details in non-fiction texts and showing their relationship to the main idea.',
                'resources' => [
                    ['title' => 'Supporting Details — ReadWriteThink', 'url' => 'https://www.readwritethink.org'],
                    ['title' => 'Text Structure — Khan Academy', 'url' => 'https://www.khanacademy.org/ela/cc-2nd-reading-vocab/cc-2nd-informational-text'],
                    ['title' => 'Primary English Bk 5 — T&T Curriculum', 'url' => null],
                ],
            ],
            [
                'subject' => 'English Comprehension', 'sea_section' => 'Section II',
                'sequence_order' => 75, 'pacing_week' => 3,
                'topic' => 'Reading Comprehension: Contextual Meaning of Words and Phrases',
                'description' => 'Tests determining meaning of words and phrases using context clues, word structure clues and background knowledge.',
                'resources' => [
                    ['title' => 'Context Clues — Khan Academy', 'url' => 'https://www.khanacademy.org/ela/cc-2nd-reading-vocab/cc-2nd-vocab/cc-2nd-context-clues'],
                    ['title' => 'Vocabulary in Context — ReadWriteThink', 'url' => 'https://www.readwritethink.org'],
                    ['title' => 'Primary English Bk 5 — T&T Curriculum', 'url' => null],
                ],
            ],
            [
                'subject' => 'English Comprehension', 'sea_section' => 'Section II',
                'sequence_order' => 76, 'pacing_week' => 4,
                'topic' => 'Reading Comprehension: Cause and Effect Relationships',
                'description' => 'Tests explaining cause and effect relationships in texts and understanding that texts have purposes and are written for specific audiences.',
                'resources' => [
                    ['title' => 'Cause and Effect — BBC Bitesize', 'url' => 'https://www.bbc.co.uk/bitesize/topics/zs44jxs'],
                    ['title' => 'Cause and Effect — Khan Academy', 'url' => 'https://www.khanacademy.org/ela/cc-2nd-reading-vocab/cc-2nd-informational-text'],
                    ['title' => 'Primary English Bk 5 — T&T Curriculum', 'url' => null],
                ],
            ],
            [
                'subject' => 'English Comprehension', 'sea_section' => 'Section II',
                'sequence_order' => 77, 'pacing_week' => 5,
                'topic' => 'Reading Comprehension: Inferring Meaning and Making Connections',
                'description' => 'Tests making inferences where answers are implied but not explicitly stated. Students use context clues to infer meanings and connect information across text.',
                'resources' => [
                    ['title' => 'Making Inferences — Khan Academy', 'url' => 'https://www.khanacademy.org/ela/cc-2nd-reading-vocab/cc-2nd-informational-text'],
                    ['title' => 'Reading Strategies — ReadWriteThink', 'url' => 'https://www.readwritethink.org'],
                    ['title' => 'Primary English Bk 5 — T&T Curriculum', 'url' => null],
                ],
            ],
            [
                'subject' => 'English Comprehension', 'sea_section' => 'Section II',
                'sequence_order' => 78, 'pacing_week' => 6,
                'topic' => "Reading Comprehension: Writer's Purpose and Audience",
                'description' => "Tests understanding that texts have purposes and are written for specific audiences. Students must examine the writer's point of view.",
                'resources' => [
                    ['title' => "Author's Purpose — Khan Academy", 'url' => 'https://www.khanacademy.org/ela/cc-2nd-reading-vocab/cc-2nd-informational-text'],
                    ['title' => 'Point of View — ReadWriteThink', 'url' => 'https://www.readwritethink.org'],
                    ['title' => 'Primary English Bk 5 — T&T Curriculum', 'url' => null],
                ],
            ],
            [
                'subject' => 'English Comprehension', 'sea_section' => 'Section II',
                'sequence_order' => 79, 'pacing_week' => 7,
                'topic' => 'Reading Comprehension: Evaluating Texts and Supporting Personal Views',
                'description' => 'Tests expressing preferences and supporting views by reference to the text, and making connections between literature and real-life situations.',
                'resources' => [
                    ['title' => 'Text Evaluation — Khan Academy', 'url' => 'https://www.khanacademy.org/ela/cc-2nd-reading-vocab'],
                    ['title' => 'Critical Reading — ReadWriteThink', 'url' => 'https://www.readwritethink.org'],
                    ['title' => 'Primary English Bk 5 — T&T Curriculum', 'url' => null],
                ],
            ],
            [
                'subject' => 'English Comprehension', 'sea_section' => 'Section II',
                'sequence_order' => 80, 'pacing_week' => 8,
                'topic' => 'Poetry: Retrieving Explicitly Stated Information',
                'description' => 'Tests retrieving information explicitly stated in poems, identifying sensory language, and identifying figures of speech in literary texts.',
                'resources' => [
                    ['title' => 'Reading Poetry — BBC Bitesize', 'url' => 'https://www.bbc.co.uk/bitesize/topics/z4mmn39'],
                    ['title' => 'Poetry Analysis — ReadWriteThink', 'url' => 'https://www.readwritethink.org'],
                    ['title' => 'Primary English Bk 5 — T&T Curriculum', 'url' => null],
                ],
            ],
            [
                'subject' => 'English Comprehension', 'sea_section' => 'Section II',
                'sequence_order' => 81, 'pacing_week' => 9,
                'topic' => 'Poetry: Figures of Speech — Simile, Metaphor, Personification',
                'description' => 'Tests identifying and interpreting simile, metaphor and personification in poetry, and explaining the effect of these literary devices.',
                'resources' => [
                    ['title' => 'Figurative Language in Poetry — BBC Bitesize', 'url' => 'https://www.bbc.co.uk/bitesize/topics/z4mmn39'],
                    ['title' => 'Similes and Metaphors — Khan Academy', 'url' => 'https://www.khanacademy.org/ela/cc-2nd-reading-vocab/cc-2nd-lit/cc-2nd-craft-structure'],
                    ['title' => 'Primary English Bk 5 — T&T Curriculum', 'url' => null],
                ],
            ],
            [
                'subject' => 'English Comprehension', 'sea_section' => 'Section II',
                'sequence_order' => 82, 'pacing_week' => 10,
                'topic' => 'Poetry: Words that Appeal to the Senses and Create Imagery',
                'description' => 'Tests identifying imagery in literary texts and understanding how poets use sensory language to create vivid mental pictures.',
                'resources' => [
                    ['title' => 'Imagery in Poetry — BBC Bitesize', 'url' => 'https://www.bbc.co.uk/bitesize/topics/z4mmn39'],
                    ['title' => 'Literary Devices — ReadWriteThink', 'url' => 'https://www.readwritethink.org'],
                    ['title' => 'Primary English Bk 5 — T&T Curriculum', 'url' => null],
                ],
            ],
            [
                'subject' => 'English Comprehension', 'sea_section' => 'Section II',
                'sequence_order' => 83, 'pacing_week' => 11,
                'topic' => "Poetry: Mood, Tone and the Writer's Point of View",
                'description' => "Tests exploring mood, identifying tone in poems and prose, and examining the writer's point of view.",
                'resources' => [
                    ['title' => 'Mood and Tone — BBC Bitesize', 'url' => 'https://www.bbc.co.uk/bitesize/topics/z4mmn39'],
                    ['title' => "Author's Point of View — Khan Academy", 'url' => 'https://www.khanacademy.org/ela/cc-2nd-reading-vocab/cc-2nd-lit/cc-2nd-craft-structure'],
                    ['title' => 'Primary English Bk 5 — T&T Curriculum', 'url' => null],
                ],
            ],
            [
                'subject' => 'English Comprehension', 'sea_section' => 'Section II',
                'sequence_order' => 84, 'pacing_week' => 12,
                'topic' => 'Poetry: Drawing Conclusions and Making Judgements on Characters',
                'description' => 'Tests drawing conclusions about characters and events based on evidence in literary text, and making judgements on character behaviour with supporting evidence.',
                'resources' => [
                    ['title' => 'Character Analysis — ReadWriteThink', 'url' => 'https://www.readwritethink.org'],
                    ['title' => 'Literary Analysis — Khan Academy', 'url' => 'https://www.khanacademy.org/ela/cc-2nd-reading-vocab/cc-2nd-lit'],
                    ['title' => 'Primary English Bk 5 — T&T Curriculum', 'url' => null],
                ],
            ],
            [
                'subject' => 'English Comprehension', 'sea_section' => 'Section II',
                'sequence_order' => 85, 'pacing_week' => 13,
                'topic' => 'Poetry: Connecting Literature to Real-life Situations',
                'description' => 'Tests making connections between literature and real-life situations, using personal knowledge to respond to texts.',
                'resources' => [
                    ['title' => 'Text-to-Self Connections — ReadWriteThink', 'url' => 'https://www.readwritethink.org'],
                    ['title' => 'Thematic Connections — Khan Academy', 'url' => 'https://www.khanacademy.org/ela/cc-2nd-reading-vocab/cc-2nd-lit'],
                    ['title' => 'Primary English Bk 5 — T&T Curriculum', 'url' => null],
                ],
            ],
            [
                'subject' => 'English Comprehension', 'sea_section' => 'Section II',
                'sequence_order' => 86, 'pacing_week' => 14,
                'topic' => 'Poetry: Offering Solutions to Conflicts and Evaluating Appreciation',
                'description' => 'Tests offering solutions to major conflicts in literary texts and responding with evaluation and appreciation, supported by textual reference.',
                'resources' => [
                    ['title' => 'Literary Appreciation — BBC Bitesize', 'url' => 'https://www.bbc.co.uk/bitesize/topics/z4mmn39'],
                    ['title' => 'Reading Responses — ReadWriteThink', 'url' => 'https://www.readwritethink.org'],
                    ['title' => 'Primary English Bk 5 — T&T Curriculum', 'url' => null],
                ],
            ],
            [
                'subject' => 'English Comprehension', 'sea_section' => 'Section II',
                'sequence_order' => 87, 'pacing_week' => 15,
                'topic' => 'Graphic Text: Comprehending Overt Messages in Media Texts',
                'description' => 'Tests comprehending the content and overt messages in graphic texts and selected media through visible elements.',
                'resources' => [
                    ['title' => 'Media Literacy — CommonSense Media', 'url' => 'https://www.commonsense.org/education/digital-citizenship/media-literacy'],
                    ['title' => 'Reading Graphic Texts — ReadWriteThink', 'url' => 'https://www.readwritethink.org'],
                    ['title' => 'Primary English Bk 5 — T&T Curriculum', 'url' => null],
                ],
            ],
            [
                'subject' => 'English Comprehension', 'sea_section' => 'Section II',
                'sequence_order' => 88, 'pacing_week' => 16,
                'topic' => 'Graphic Text: Identifying Implied Messages and Design Elements',
                'description' => 'Tests identifying implied messages in graphic texts based on design elements, and recognising how different media forms use particular techniques.',
                'resources' => [
                    ['title' => 'Media Messages — CommonSense Media', 'url' => 'https://www.commonsense.org/education/digital-citizenship/media-literacy'],
                    ['title' => 'Visual Literacy — ReadWriteThink', 'url' => 'https://www.readwritethink.org'],
                    ['title' => 'Primary English Bk 5 — T&T Curriculum', 'url' => null],
                ],
            ],
            [
                'subject' => 'English Comprehension', 'sea_section' => 'Section II',
                'sequence_order' => 89, 'pacing_week' => 17,
                'topic' => 'Graphic Text: Purpose and Audience of Media Texts',
                'description' => 'Tests explaining the purpose of selected media texts and identifying their intended audience across different media forms.',
                'resources' => [
                    ['title' => 'Media Purpose — CommonSense Media', 'url' => 'https://www.commonsense.org/education/digital-citizenship/media-literacy'],
                    ['title' => 'Audience and Purpose — ReadWriteThink', 'url' => 'https://www.readwritethink.org'],
                    ['title' => 'Primary English Bk 5 — T&T Curriculum', 'url' => null],
                ],
            ],
            [
                'subject' => 'English Comprehension', 'sea_section' => 'Section II',
                'sequence_order' => 90, 'pacing_week' => 18,
                'topic' => 'Graphic Text: Evaluating Techniques Used in Media Texts',
                'description' => 'Tests analysing selected media to understand how messages are presented and evaluating the techniques used in media texts.',
                'resources' => [
                    ['title' => 'Media Analysis — CommonSense Media', 'url' => 'https://www.commonsense.org/education/digital-citizenship/media-literacy'],
                    ['title' => 'Critical Media Literacy — ReadWriteThink', 'url' => 'https://www.readwritethink.org'],
                    ['title' => 'Primary English Bk 5 — T&T Curriculum', 'url' => null],
                ],
            ],
        ];

        foreach ($modules as $module) {
            SyllabusModule::create($module);
        }
    }
}