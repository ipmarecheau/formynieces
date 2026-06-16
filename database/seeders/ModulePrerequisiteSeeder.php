<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Slice 2a — Prerequisite graph seeder.
 *
 * Loads the dense prerequisite graph (Math + ELA) into `module_prerequisites`.
 * Each row is a directed edge "B requires A": module_id = B (the harder module),
 * prerequisite_module_id = A (the prerequisite). The diagnostic engine walks these
 * edges to infer mastery — see diagnostic.feature. The graph is DENSE by design;
 * the CAUTION lives in the engine (conservative propagation), not here.
 *
 * Source of truth: math_prerequisite_edges.md (86 edges) + ela_prerequisite_edges.md
 * (64 edges), both reviewed/approved by Isaac (see 15JUN26 handoff §5).
 *
 * Invariants (asserted by ModulePrerequisiteSeederTest):
 *   - every id references a real syllabus_modules.id (1..90)
 *   - no self-loops (module_id !== prerequisite_module_id)
 *   - the directed graph is ACYCLIC (a cycle would break mastery inference)
 *   - exactly self::EXPECTED_EDGE_COUNT edges are seeded
 *
 * IMPORTANT: modules MUST be seeded before this runs (foreign keys). DatabaseSeeder
 * calls SyllabusModuleSeeder first, then this.
 */
class ModulePrerequisiteSeeder extends Seeder
{
    /** Total edges in the approved graph: 86 Math + 64 ELA. */
    public const EXPECTED_EDGE_COUNT = 150;

    /**
     * Math edges (modules 1..51). Each pair is [B, A] meaning "B requires A".
     * Mirrors math_prerequisite_edges.md row-for-row.
     */
    private const MATH_EDGES = [
        // Number Concepts (1–5)
        [2, 1], [3, 1], [5, 1], [4, 1],
        // Whole Number Operations (6–8)
        [6, 1], [7, 6], [7, 4], [8, 7], [8, 6],
        // Number Patterns & Relationships (9–11)
        [10, 9], [10, 6], [11, 10], [11, 8],
        // Fractions (12–16)
        [12, 4], [13, 12], [14, 12], [14, 8], [15, 14], [16, 13], [16, 15],
        // Decimals (17–21)
        [17, 1], [17, 12], [18, 17], [18, 3], [19, 17], [19, 6],
        [20, 19], [20, 7], [20, 8], [21, 20], [21, 19],
        // Percent (22–24)
        [22, 12], [22, 17], [23, 22], [23, 15], [24, 23],
        // Problem Solving / Multi-step Number (25–27)
        [25, 23], [25, 21], [26, 14], [26, 8],
        [27, 25], [27, 21], [27, 26],
        // Geometry (28–35)
        [29, 28], [30, 28], [31, 28], [32, 29], [32, 9],
        [33, 29], [33, 31], [34, 33], [34, 30], [35, 34], [35, 29],
        // Measurement (36–45)
        [37, 36], [37, 6], [38, 36], [38, 7], [39, 37],
        [40, 38], [40, 39], [41, 38], [41, 7], [42, 36], [43, 36],
        [44, 41], [44, 42], [44, 43], [45, 40], [45, 44],
        // Statistics (46–51)
        [47, 46], [48, 46], [49, 48], [49, 8], [49, 6],
        [50, 49], [50, 47], [51, 50], [51, 49],
        // Cross-strand "applied" edges (the dense additions)
        [16, 8], [21, 23], [24, 26], [25, 24], [27, 49], [45, 38], [51, 21],
    ];

    /**
     * ELA edges (modules 52..90). Each pair is [B, A] meaning "B requires A".
     * Mirrors ela_prerequisite_edges.md row-for-row.
     */
    private const ELA_EDGES = [
        // Section I — Spelling (52–57)
        [53, 52], [54, 53], [55, 54], [56, 54], [56, 52], [57, 56], [57, 55],
        // Punctuation (58–60)
        [59, 58], [60, 59], [60, 58],
        // Capitalisation (61)
        [61, 60], [61, 58],
        // Grammar (62–68)
        [63, 62], [64, 62], [64, 63], [65, 62], [66, 65], [66, 64],
        [67, 63], [68, 62],
        // Cross-strand within Section I
        [62, 57], [66, 60],
        // Section II — Reading Comprehension (73–79)
        [74, 73], [75, 73], [75, 57], [76, 74], [77, 76], [77, 75],
        [78, 77], [78, 73], [79, 78], [79, 77], [73, 65],
        // Poetry (80–86)
        [81, 80], [82, 81], [83, 82], [83, 81], [84, 83], [84, 77],
        [85, 84], [86, 85], [86, 79], [80, 74],
        // Graphic Text (87–90)
        [88, 87], [88, 77], [89, 88], [89, 78], [90, 89], [90, 79], [87, 73],
        // Writing (69–72) — edges INTO writing
        [69, 66], [69, 63], [69, 60], [70, 73], [70, 74], [70, 66],
        [71, 81], [71, 82], [72, 66], [72, 69], [72, 70],
        // Writing — edges OUT of writing (the debatable / strikeable ones)
        [71, 69], [86, 71], [79, 70],
    ];

    public function run(): void
    {
        $now = now();

        $rows = [];
        foreach ([...self::MATH_EDGES, ...self::ELA_EDGES] as [$module, $prerequisite]) {
            $rows[] = [
                'module_id'              => $module,
                'prerequisite_module_id' => $prerequisite,
                'created_at'             => $now,
                'updated_at'             => $now,
            ];
        }

        // Idempotent reseed: clear then insert. Chunked to stay well under SQLite's
        // variable limit, though 150 rows is trivially within it.
        DB::table('module_prerequisites')->delete();

        foreach (array_chunk($rows, 100) as $chunk) {
            DB::table('module_prerequisites')->insert($chunk);
        }
    }
}
