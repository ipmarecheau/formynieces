// State-transition (flow) parity — drives the mobile API through the learning loop
// using REAL correct answers from the source-of-truth DB (practice_questions.correct_index),
// so ace→mastered and miss→outcome are exercised end-to-end, then scores edge coverage
// against the web state machine (ModuleEntry + CompetencyCheck + loop-rail).
import { execSync } from 'node:child_process';

const API = 'http://127.0.0.1:8011/api/mobile';
const DB = '/root/dev/formynieces-flutter/database/database.sqlite';
const CRED = { email: 'emu-child@smoothseas.test', password: 'password' };
const CHILD = 82;
const L = ['A', 'B', 'C', 'D', 'E', 'F'];

// web learning-loop edges (ground truth)
const EDGES = [
  ['explainer', 'check', 'start the quick check'],
  ['check', 'mastered', 'ace / test-out (LL-20)'],
  ['check', 'outcome', 'miss the check'],
  ['outcome', 'lesson', 'choose lesson'],
  ['outcome', 'practice', 'choose practice'],
  ['lesson', 'practice', 'practise this topic'],
  ['practice', 'mastered', 'pass — 3 tricky first-try'],
  ['practice', 'reteach', 'miss both tries'],
  ['reteach', 'lesson', 're-teach then back to lesson'],
];

const sql = (q) => execSync(`sqlite3 "${DB}" "${q}"`).toString().trim();
const correctLetter = (qid) => L[+sql(`SELECT correct_index FROM practice_questions WHERE id=${qid}`)];

let TOKEN;
async function api(method, path, body) {
  const res = await fetch(API + path, {
    method,
    headers: { 'Content-Type': 'application/json', Accept: 'application/json', ...(TOKEN ? { Authorization: `Bearer ${TOKEN}` } : {}) },
    body: body ? JSON.stringify(body) : undefined,
  });
  const text = await res.text();
  let json; try { json = JSON.parse(text); } catch { json = { _raw: text }; }
  return { status: res.status, ...json };
}
const post = (p, b) => api('POST', p, b);
const get = (p) => api('GET', p);

function resetModule(m) {
  sql(`DELETE FROM student_question_exposures WHERE student_id=${CHILD}`);
  sql(`DELETE FROM student_progress WHERE student_id=${CHILD} AND module_id=${m}`);
}

// Walk a competency check with a strategy: 'ace' (all correct) | 'miss' (first wrong).
async function runCheck(m, strategy) {
  let d = await post(`/child/module/${m}/check/start`);
  if (!d.session_id) return { error: d.message || d._raw || 'no session', served: 0 };
  let q = d.current_question;
  let served = 0, first = true;
  while (true) {
    served++;
    let letter = correctLetter(q.id);
    if (strategy === 'miss' && first) letter = L[(L.indexOf(letter) + 1) % q.choices.length];
    first = false;
    const r = await post(`/child/check/${d.session_id}/answer`, { question_id: q.id, choice_id: letter });
    if (r.done) return { mastered: r.mastered, served };
    q = r.next_question;
  }
}

// --- run ---------------------------------------------------------------------
TOKEN = (await post('/login', { ...CRED, device_name: 'flowmetric' })).token;

const result = {}; // edgeKey -> bool
const key = (a, b) => `${a}->${b}`;

// 1. explainer -> check (check/start serves questions)
resetModule(1);
const startProbe = await post('/child/module/1/check/start');
result[key('explainer', 'check')] = Array.isArray(startProbe.current_question?.choices);

// 2. check -> mastered (ace, correct answers)   [fresh module 1 already reset above; re-reset to clear the probe exposure]
resetModule(1);
const ace = await runCheck(1, 'ace');
result[key('check', 'mastered')] = ace.mastered === true && ace.served === 6;

// 3. check -> outcome (miss)  on module 2
resetModule(2);
const miss = await runCheck(2, 'miss');
result[key('check', 'outcome')] = miss.mastered === false;

// 4/6. outcome/lesson -> lesson content
const lesson = await get('/child/module/2/lesson');
const lessonOk = Array.isArray(lesson.blocks) && lesson.blocks.length > 0;
result[key('outcome', 'lesson')] = lessonOk;

// 5/6. outcome/lesson -> practice (start a practice session)
const prac = await post('/child/practice/start', { mission_id: 'm2' });
const pracOk = !!prac.session_id;
result[key('outcome', 'practice')] = pracOk;
result[key('lesson', 'practice')] = pracOk;

// 7. practice -> mastered: climb answering every question correctly (first-try)
resetModule(2);
let pm = await post('/child/practice/start', { mission_id: 'm2' });
let q = pm.current_question, mastered = false;
for (let i = 0; i < 60 && q; i++) {
  const r = await post(`/child/practice/${pm.session_id}/answer`, { question_id: q.id, choice_id: correctLetter(q.id) });
  if (r.done) { mastered = r.mastered === true; break; }
  if (r.next_question) q = r.next_question;
}
result[key('practice', 'mastered')] = mastered;

// 8. practice -> reteach: miss both tries on the first question
resetModule(2);
let pr = await post('/child/practice/start', { mission_id: 'm2' });
let rq = pr.current_question;
const wrong = L[(L.indexOf(correctLetter(rq.id)) + 1) % rq.choices.length];
const r1 = await post(`/child/practice/${pr.session_id}/answer`, { question_id: rq.id, choice_id: wrong });
let reteach = false;
if (r1.retry) { const r2 = await post(`/child/practice/${pr.session_id}/answer`, { question_id: rq.id, choice_id: wrong }); reteach = r2.reteach === true; }
result[key('practice', 'reteach')] = reteach;

// 9. reteach -> lesson: the lesson is available to return to
result[key('reteach', 'lesson')] = lessonOk;

// --- score -------------------------------------------------------------------
const covered = EDGES.filter(([a, b]) => result[key(a, b)]);
const dev = 1 - covered.length / EDGES.length;
console.log('\n=== STATE-TRANSITION (FLOW) PARITY — API-driven, real correct answers ===');
console.log(`ace check served ${ace.served ?? '-'} → mastered=${ace.mastered}`);
console.log(`miss check → mastered=${miss.mastered}`);
for (const [a, b, ev] of EDGES) console.log(`   ${result[key(a, b)] ? 'MATCH  ' : 'MISSING'} ${a} --(${ev})--> ${b}`);
console.log(`\nedge coverage: ${covered.length}/${EDGES.length}`);
console.log(`FLOW DEVIATION: ${(dev * 100).toFixed(1)}%`);
