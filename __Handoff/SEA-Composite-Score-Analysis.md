# SEA Composite Score — How Subject Performance Combines

A summary of how the Trinidad & Tobago Secondary Entrance Assessment (SEA) combines the three papers into a single composite score, what can and cannot be computed from public information, and what it implies for study strategy.

---

## 1. The Three Papers

| Paper | Max Raw Score | Weight |
|---|---|---|
| Mathematics | 75 | 100% |
| English Language Arts (ELA) | 64 | 60% |
| ELA Writing | 20 | 40% |

The weighting ratio is **100 : 60 : 40** (Math : ELA : Writing).

---

## 2. How the Composite Is Built — Four Stages

### Stage 1 — Raw scores
Each candidate earns a raw mark on each paper:

$$R_M \in [0, 75] \qquad R_E \in [0, 64] \qquad R_W \in [0, 20]$$

### Stage 2 — Scale Math and ELA to 100
$$\text{Scaled score} = \text{Raw score} \times \frac{100}{\text{Maximum raw score}}$$

$$X_M = R_M \times \frac{100}{75} \qquad X_E = R_E \times \frac{100}{64} \qquad X_W = R_W \ (\text{unchanged})$$

Confirmed by the booklet notation "75 (100)" / "64 (100)" and the sample report's scaled values of 95 and 96.

### Stage 3 — Convert each scaled score to a standard score
This step "utilises the variance in each paper" to preserve a student's **relative standing**.

**Step 3a — z-score** (standard deviations above/below the national mean):
$$z = \frac{X - \mu}{\sigma}$$

**Step 3b — rescale onto the Ministry's standard scale:**
$$\text{StdScore} = (z \times \text{SD}_{\text{target}}) + \text{Mean}_{\text{target}}$$

Combined:
$$\text{StdScore} = \left(\frac{X - \mu}{\sigma}\right) \times \text{SD}_{\text{target}} + \text{Mean}_{\text{target}}$$

where $\mu$ = national mean for that paper that year, $\sigma$ = national standard deviation, and the target constants set the final scale.

### Stage 4 — Weight and sum
$$\text{Composite} = (w_M \times \text{StdScore}_M) + (w_E \times \text{StdScore}_E) + (w_W \times \text{StdScore}_W)$$

### Full expression
$$\text{Composite} = \sum_{p \,\in\, \{M,E,W\}} w_p \left[ \left(\frac{X_p - \mu_p}{\sigma_p}\right) \times \text{SD}_{\text{target}} + \text{Mean}_{\text{target}} \right]$$

---

## 3. What Is Known vs. Unknown

| Quantity | Known? | Source |
|---|---|---|
| Raw maximums (75, 64, 20) | ✅ Yes | Booklets |
| Scale-to-100 step | ✅ Yes | Booklets + sample report |
| Weights 100:60:40 | ✅ Yes | Assessment Framework |
| z-score / variance method | ✅ Yes | Framework wording |
| National mean $\mu_p$ per paper | ❌ No | Released per year, not in booklets |
| National SD $\sigma_p$ per paper | ❌ No | Not published |
| $\text{Mean}_{\text{target}}$, $\text{SD}_{\text{target}}$ | ❌ No | Not published |

**Consequence:** You cannot reproduce the sample report's composite of **234.567** from the three printed student scores (95, 96, 19) alone — the hidden per-paper national statistics are baked in.

---

## 4. Simplified Form — Composite as a Function of Subject Scores

Collapsing all unknown constants, each standard score is **linear** in its scaled score:

$$\text{StdScore}_p = a_p X_p + b_p \qquad \text{where} \quad a_p = \frac{\text{SD}_{\text{target}}}{\sigma_p}, \quad b_p = \text{Mean}_{\text{target}} - \frac{\text{SD}_{\text{target}} \cdot \mu_p}{\sigma_p}$$

Therefore the composite reduces to:

$$\boxed{\ \text{Composite} = k_M X_M + k_E X_E + k_W X_W + C\ }$$

where:
- $k_p = w_p \cdot a_p$ — the **effective slope** (composite points gained per scaled mark)
- $C = w_M b_M + w_E b_E + w_W b_W$ — a single fixed per-cohort constant

**Key insight:** All hidden cohort statistics fold into just **four unknown coefficients** ($k_M, k_E, k_W, C$). These can be recovered by **linear regression** on real (subject score → composite) data — no need for the Ministry's hidden constants.

*Caveats:* coefficients are cohort-specific (σ changes yearly); assumes the pipeline is genuinely linear.

---

## 5. Ranking Subjects by Impact

There are **two different meanings** of "impact":

### A) Total impact (by weight) — unambiguous
$$\text{Math (100)} > \text{ELA (60)} > \text{Writing (40)}$$

### B) Impact per raw mark — more subtle and more strategic

The impact of one additional **raw** mark chains through all three scaling forces:

$$\frac{\partial \text{Composite}}{\partial R_p} = \underbrace{w_p}_{\text{weight}} \times \underbrace{\frac{\text{SD}_{\text{target}}}{\sigma_p}}_{\text{standardization}} \times \underbrace{\frac{100}{\text{Max}_p}}_{\text{scale-to-100}}$$

**Known forces (weight × scale factor):**

| Subject | Weight | Scale factor (100/Max) | Weight × Scale |
|---|---|---|---|
| Mathematics | 100 | 1.33 | **133** |
| ELA | 60 | 1.56 | **94** |
| Writing | 40 | 5.00 | **200** |

On known forces alone, **Writing has the highest impact per raw mark** — its tiny 20-point scale means each mark is a large slice of the paper, more than offsetting its low weight.

**Unknown force (spread $\sigma_p$):** dividing by $\sigma_p$ means tightly-bunched papers get *amplified* per-mark impact. Likely ordering on a common 0–100 scale:
- Math: wide spread (40-item objective paper) → large $\sigma_M$ → dampened
- Writing: coarse rubric scoring, scores bunch → small $\sigma_W$ → amplified
- ELA: in between

If $\sigma_M > \sigma_E > \sigma_W$ holds, **both** forces push Writing's per-mark impact to the top.

### Illustrative example (invented σ values, not real)
Assuming $\sigma_M = 22$, $\sigma_E = 18$, $\sigma_W = 14$:

| Subject | Numerator | ÷ σ | Relative impact per raw mark |
|---|---|---|---|
| Writing | 200 | ÷ 14 | **14.3** ← highest |
| Math | 133 | ÷ 22 | **6.0** |
| ELA | 94 | ÷ 18 | **5.2** ← lowest |

Under these illustrative numbers, one raw Writing mark is worth more than **double** a raw Math mark.

---

## 6. Strategic Takeaways

- **Total weight:** Math dominates (Math > ELA > Writing). Math should be the overall priority.
- **Per-mark value:** Writing is likely **underrated per unit of effort** — each raw mark is worth ~5× a Math mark pre-standardization, and a tight cohort spread probably amplifies it further.
- **Practical guidance:** *Math for total weight, but don't neglect Writing — its marks are individually the most valuable, and it's often where the easiest composite gains hide.*
- **Empirical path:** Fit the four-coefficient linear model ($k_M, k_E, k_W, C$) against real data to settle the ranking definitively and recover a working composite calculator.

---

## 7. Honest Limitations

1. The σ ordering is an empirical claim about cohort behaviour, not a certainty — a hard, discriminating Writing prompt could widen $\sigma_W$.
2. All of this assumes the Ministry's pipeline is the linear z-score → weight process the booklets describe, with no hidden non-linear step.
3. "Impact per mark" is the *marginal* value of one point — not the same as where a weak student should start (diminishing returns, achievability, and baseline ability all matter).
4. The exact target scale constants and per-year national means/SDs are not published, so no exact closed-form calculator is possible from public data alone.

---

## Sources

- *Assessment Framework for the Secondary Entrance Assessment 2025–2028*, Ministry of Education, Trinidad & Tobago
- *Secondary Entrance Assessment 2023 Information Booklet*, Ministry of Education, Trinidad & Tobago
- Standard score / z-score statistical methodology (general references)
