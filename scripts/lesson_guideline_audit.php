<?php
// Guideline audit against the lesson-authoring five non-negotiables (the checkable parts).
// Usage: php scripts/lesson_guideline_audit.php <start> <count>   (1-based over sorted bundles)
$dir = __DIR__.'/../database/data/lessons';
$files = glob($dir.'/*.json');
sort($files);
$start = max(1, (int) ($argv[1] ?? 1));
$count = (int) ($argv[2] ?? 10);
$slice = array_slice($files, $start - 1, $count);

$INTERACTIVE = ['check', 'fillblank', 'markwords', 'matchpairs', 'ordersteps'];

foreach ($slice as $f) {
    $lessons = json_decode(file_get_contents($f), true);
    foreach ((array) $lessons as $L) {
        $mod = $L['module'] ?? basename($f);
        $blocks = $L['blocks'] ?? [];
        $n = count($blocks);
        $inter = array_values(array_filter($blocks, fn ($b) => in_array($b['type'] ?? '', $INTERACTIVE, true)));
        $ni = count($inter);
        $cogs = array_filter(array_map(fn ($b) => $b['cognition'] ?? null, $blocks));
        $issues = [];

        // 1. Shape: 6–10 blocks, >=2 interactive, >=half interactive
        if ($n < 6 || $n > 10) { $issues[] = "blocks=$n (want 6-10)"; }
        if ($ni < 2) { $issues[] = "interactive=$ni (want >=2)"; }
        if ($n > 0 && $ni / $n < 0.5) { $issues[] = 'interactive ratio '.round($ni / $n * 100)."% (<50%)"; }

        // 2. KAR coverage
        $miss = array_diff(['knowing', 'applying', 'reasoning'], array_unique($cogs));
        if ($miss) { $issues[] = 'KAR missing: '.implode('/', $miss); }
        if (count(array_filter($blocks, fn ($b) => empty($b['cognition']) && in_array($b['type'] ?? '', $INTERACTIVE, true)))) {
            $issues[] = 'interactive block(s) untagged cognition';
        }

        // 3. Re-teach fields on every interactive block
        foreach ($inter as $b) {
            if (empty($b['rule']) || count($b['practiceItems'] ?? []) < 4) {
                $issues[] = "'{$b['type']}' rule/practiceItems<4";
                break;
            }
        }

        // 4. AI-evaluation fields on reasoning/graded blocks
        foreach ($blocks as $b) {
            $isReasoning = ($b['cognition'] ?? '') === 'reasoning';
            if (! $isReasoning) { continue; }
            $need = ['principle', 'canonical_solution', 'rubric', 'misconceptions', 'sample_answers'];
            $lack = array_values(array_filter($need, fn ($k) => empty($b[$k])));
            if ($lack) { $issues[] = "reasoning '{$b['type']}' lacks: ".implode(',', $lack); }
        }

        // 5. Objective mapping present
        if (empty($L['objectives_direct'])) { $issues[] = 'no objectives_direct'; }

        $flag = $issues ? '⚠️ ' : '✅ ';
        echo $flag.str_pad($mod, 11)." blocks=$n inter=$ni  ".($issues ? implode('; ', $issues) : 'clean')."\n";
    }
}
