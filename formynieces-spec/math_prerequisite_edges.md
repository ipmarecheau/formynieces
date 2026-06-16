# Math Prerequisite Graph — Dense Edge Set (Slice 2, Part 1)

**For review.** Each row is a directed edge **B requires A**, meaning: a student who has
mastered module B has necessarily mastered module A. The diagnostic uses these to infer
mastery — when an anchor certifies B, the engine may infer A is mastered too (subject to
the conservative walk-back: a failed harder item un-marks the chain between).

Module ids 1–51 are Math. Read each edge as **`requires` ← `prerequisite`**.
Strike any edge you disagree with; flag any missing one.

---

## Within-strand chains (the backbone)

### Number Concepts (1–5)
| Module (B) | requires (A) | why |
|---|---|---|
| 2 Expanded Notation | 1 Place Value | expanded form is place value written out |
| 3 Rounding | 1 Place Value | rounding depends on place understanding |
| 5 Ordering/Comparing | 1 Place Value | comparing magnitudes needs place value |
| 4 Factors/Multiples/Primes/Squares | 1 Place Value | number sense foundation |

### Whole Number Operations (6–8)
| Module (B) | requires (A) | why |
|---|---|---|
| 6 Addition/Subtraction | 1 Place Value | regrouping is place-value based |
| 7 Multiplication | 6 Add/Subtract | multiplication is repeated addition |
| 7 Multiplication | 4 Factors/Multiples | multiplication underpins factors |
| 8 Division | 7 Multiplication | division is inverse of multiplication |
| 8 Division | 6 Add/Subtract | long division uses subtraction |

### Number Patterns & Relationships (9–11)
| Module (B) | requires (A) | why |
|---|---|---|
| 10 Pattern Rules/Missing Elements | 9 Repeating/Increasing Patterns | rules generalise observed patterns |
| 10 Pattern Rules | 6 Add/Subtract | step rules use operations |
| 11 Algebraic Thinking (unknowns) | 10 Pattern Rules | unknowns formalise pattern rules |
| 11 Algebraic Thinking | 8 Division | solving for unknowns inverts operations |

### Fractions (12–16)
| Module (B) | requires (A) | why |
|---|---|---|
| 12 Equivalent/Simplify | 4 Factors/Multiples | simplifying uses common factors |
| 13 Add/Subtract Fractions | 12 Equivalent | common denominators need equivalence |
| 14 Multiply/Divide Fractions | 12 Equivalent | simplification within operations |
| 14 Multiply/Divide Fractions | 8 Division | division of fractions |
| 15 Fractions of a Collection | 14 Multiply/Divide | "of" = multiplication |
| 16 Fraction Word Problems | 13 Add/Subtract | multi-step uses all fraction ops |
| 16 Fraction Word Problems | 15 Fractions of a Collection | applied collection problems |

### Decimals (17–21)
| Module (B) | requires (A) | why |
|---|---|---|
| 17 Decimal Place Value | 1 Place Value | decimals extend the place system |
| 17 Decimal Place Value | 12 Equivalent Fractions | decimals are fractions of ten |
| 18 Ordering/Rounding Decimals | 17 Decimal Place Value | comparing needs place value |
| 18 Ordering/Rounding Decimals | 3 Rounding (whole) | rounding rules transfer |
| 19 Add/Subtract Decimals | 17 Decimal Place Value | align by place |
| 19 Add/Subtract Decimals | 6 Add/Subtract (whole) | same algorithm |
| 20 Multiply/Divide Decimals | 19 Add/Subtract Decimals | builds on decimal computation |
| 20 Multiply/Divide Decimals | 7 Multiplication | same algorithm |
| 20 Multiply/Divide Decimals | 8 Division | same algorithm |
| 21 Decimal Real-world Problems | 20 Multiply/Divide Decimals | applies all four decimal ops |
| 21 Decimal Real-world Problems | 19 Add/Subtract Decimals | applies all four decimal ops |

### Percent (22–24)
| Module (B) | requires (A) | why |
|---|---|---|
| 22 Convert F/D/Percent | 12 Equivalent Fractions | percent is a fraction of 100 |
| 22 Convert F/D/Percent | 17 Decimal Place Value | decimal↔percent conversion |
| 23 Percent of a Quantity | 22 Convert F/D/Percent | needs conversion first |
| 23 Percent of a Quantity | 15 Fractions of a Collection | "percent of" = fraction of |
| 24 Percent Problems | 23 Percent of a Quantity | multi-step percent |

### Problem Solving / Multi-step Number (25–27)
| Module (B) | requires (A) | why |
|---|---|---|
| 25 Profit/Loss/Discount/VAT | 23 Percent of a Quantity | money percents |
| 25 Profit/Loss/Discount/VAT | 21 Decimal Real-world | money is decimal |
| 26 Direct Proportion/Unequal Sharing | 14 Multiply/Divide Fractions | ratio reasoning |
| 26 Direct Proportion/Unequal Sharing | 8 Division | sharing = division |
| 27 Multi-step Whole Number & Money (S3) | 25 Profit/Loss | culminating money problem |
| 27 Multi-step Whole Number & Money (S3) | 21 Decimal Real-world | culminating money problem |
| 27 Multi-step Whole Number & Money (S3) | 26 Direct Proportion | multi-step reasoning |

### Geometry (28–35)
| Module (B) | requires (A) | why |
|---|---|---|
| 29 Classifying Triangles/Quads | 28 Properties of Solids/Plane Shapes | classification needs properties |
| 30 Symmetry | 28 Properties of Shapes | symmetry is a shape property |
| 31 Angles (right/acute/obtuse) | 28 Properties of Shapes | angle vocabulary |
| 32 Geometric Patterns | 29 Classifying Shapes | patterns built from classified shapes |
| 32 Geometric Patterns | 9 Repeating Patterns | pattern logic shared with Number |
| 33 Constructing Polygons/Composite | 29 Classifying Shapes | construct from known shapes |
| 33 Constructing Polygons/Composite | 31 Angles | construction uses angles |
| 34 Solving Problems with Shapes | 33 Constructing Polygons | applied construction |
| 34 Solving Problems with Shapes | 30 Symmetry | applied properties |
| 35 Multi-step Shape Problem (S3) | 34 Solving Problems with Shapes | culminating geometry |
| 35 Multi-step Shape Problem (S3) | 29 Classifying Shapes | culminating geometry |

### Measurement (36–45)
| Module (B) | requires (A) | why |
|---|---|---|
| 37 Perimeter of Squares/Rectangles | 36 Linear Measure/Conversion | perimeter is linear sum |
| 37 Perimeter | 6 Add/Subtract | summing sides |
| 38 Area of Squares/Rectangles | 36 Linear Measure | area from lengths |
| 38 Area | 7 Multiplication | area = l × w |
| 39 Perimeter of Compound Shapes | 37 Perimeter | compose perimeters |
| 40 Area of Compound Shapes | 38 Area | compose areas |
| 40 Area of Compound Shapes | 39 Perimeter of Compound | decomposition skill |
| 41 Volume and Capacity | 38 Area | volume extends area |
| 41 Volume and Capacity | 7 Multiplication | v = l×w×h |
| 42 Mass and Weight Conversion | 36 Linear Measure/Conversion | unit conversion skill |
| 43 Time — Reading/Converting | 36 Linear Measure/Conversion | unit conversion skill |
| 44 Real-life Vol/Cap/Mass/Time | 41 Volume/Capacity | applied measurement |
| 44 Real-life Vol/Cap/Mass/Time | 42 Mass | applied measurement |
| 44 Real-life Vol/Cap/Mass/Time | 43 Time | applied measurement |
| 45 Multi-step Area/Perim/Vol (S3) | 40 Area of Compound | culminating measurement |
| 45 Multi-step Area/Perim/Vol (S3) | 44 Real-life Measurement | culminating measurement |

### Statistics (46–51)
| Module (B) | requires (A) | why |
|---|---|---|
| 47 Pictographs/Block Graphs | 46 Tally/Frequency/Bar | reading graphs builds on tables |
| 48 Mode | 46 Tally/Frequency | mode read from frequency |
| 49 Mean/Average | 48 Mode | progression of measures of centre |
| 49 Mean/Average | 8 Division | mean = sum ÷ count |
| 49 Mean/Average | 6 Add/Subtract | summing data |
| 50 Analysing Data to Conclude | 49 Mean | interpret using measures |
| 50 Analysing Data to Conclude | 47 Pictographs | interpret representations |
| 51 Multi-step Data Analysis (S3) | 50 Analysing Data | culminating statistics |
| 51 Multi-step Data Analysis (S3) | 49 Mean | culminating statistics |

---

## Cross-strand "applied" edges (the dense additions)

These are the weaker-but-plausible edges that make the graph dense. They capture that
the multi-step and word-problem modules draw on operations from other strands. Review
these especially — they are the ones most likely to cause over-inference.

| Module (B) | requires (A) | why (weaker) |
|---|---|---|
| 16 Fraction Word Problems | 8 Division | word problems mix operations |
| 21 Decimal Real-world Problems | 23 Percent of a Quantity | money problems often involve percent |
| 24 Percent Problems | 26 Direct Proportion | proportion underlies percent change |
| 25 Profit/Loss/Discount/VAT | 24 Percent Problems | applied percent |
| 27 Multi-step Money (S3) | 49 Mean | some money problems average |
| 45 Multi-step Measurement (S3) | 38 Area | foundational area |
| 51 Multi-step Data (S3) | 21 Decimal Real-world | data often decimal |

---

## Summary

- **Backbone edges:** ~70 (strong within-strand + clear cross-strand)
- **Dense cross-strand additions:** ~7 (the weaker applied edges above)
- **Total Math edges:** ~77

The Section III "multi-step" modules (27, 35, 45, 51) are the apex nodes of each strand —
they require the most and are where the hardest anchors will sit. The foundational nodes
(1 Place Value, 6 Add/Subtract) are required by the most modules — they're the roots.

**Review actions:** strike edges you reject, flag any missing. Once Math is settled,
ELA (52–90) follows in the same format.
