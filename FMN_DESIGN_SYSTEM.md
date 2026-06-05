# ForMyNieces — UI Design System

A reference guide for all visual and layout decisions in the ForMyNieces dashboard. Follow these rules when building new views, components, or pages.

---

## 1. Design Philosophy

**Audience:** Girl learners aged 9–11, and their parents.

**Tone:** Cozy, magical, encouraging. The UI should feel like a warm, safe space — not a clinical test environment. Think soft RPG progression, not corporate dashboard.

**Core principles:**
- Warm and playful, never cold or sterile
- Progress should feel celebratory, not pressuring
- Every screen should feel achievable and low-stress
- No daisyUI or Tailwind component classes in dashboard views — all styling is custom CSS for full control

---

## 2. Typography

| Role | Font | Weight | Size |
|------|------|--------|------|
| Display / Headings | Fredoka One | 400 (only weight) | 1.15rem – 1.6rem |
| Body / UI | Nunito | 400, 700, 800 | 0.7rem – 1rem |

**Import:**
```html
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Fredoka+One&display=swap" rel="stylesheet">
```

**Rules:**
- `font-family: 'Fredoka One', cursive` — section titles, hero headings, stat numbers, brand name only
- `font-family: 'Nunito', sans-serif` — everything else
- Apply `font-family: 'Nunito', sans-serif` globally via `* { font-family: 'Nunito', sans-serif; }`
- Never use system fonts, Inter, or Roboto

---

## 3. Colour Palette

### Primary Brand Colours

| Name | Hex | Usage |
|------|-----|-------|
| Purple 600 | `#9333ea` | Brand colour, links, section titles, active states |
| Purple 400 | `#a855f7` | Gradient start, button backgrounds |
| Pink 600 | `#db2777` | Gradient end, accent |
| Pink 400 | `#ec4899` | Button hover, highlights |
| Purple 100 | `#f3e8ff` | Card borders, dividers |
| Purple 50 | `#fdf4ff` | Page background |

### Subject Colours

| Subject | Background | Text | Usage |
|---------|-----------|------|-------|
| Math | `#d1fae5` | `#065f46` | Badge, node dot |
| English Editing | `#fce7f3` | `#9d174d` | Badge, node dot |
| English Comprehension | `#ede9fe` | `#4c1d95` | Badge, node dot |

### Status Colours

| Status | Dot Colour | Border Colour | Usage |
|--------|-----------|---------------|-------|
| Mastered | `#10b981` | `#a7f3d0` | Node border, status dot |
| Diagnostic Passed | `#8b5cf6` | `#ddd6fe` | Node border, status dot |
| Not Started | `#d1d5db` | `#f3e8ff` | Node border, status dot, 0.7 opacity |

### Semantic UI Colours

| State | Background | Text | Border |
|-------|-----------|------|--------|
| In Progress | `#fff7ed` | `#c2410c` | `#fed7aa` |
| Completed | `#f0fdf4` | `#166534` | `#bbf7d0` |
| Alert/Info | `#fdf4ff` | `#7c3aed` | `#e9d5ff` |

---

## 4. Gradients

Use gradients sparingly — hero cards and primary buttons only. Never use them on borders or text.

| Name | Value | Usage |
|------|-------|-------|
| Hero / Primary | `linear-gradient(135deg, #9333ea 0%, #db2777 100%)` | Hero card background |
| Button Primary | `linear-gradient(135deg, #a855f7, #ec4899)` | Primary action buttons |
| Math Tab Active | `linear-gradient(135deg, #059669, #34d399)` | Math tab selected state |
| Editing Tab Active | `linear-gradient(135deg, #db2777, #f472b6)` | English Editing tab selected state |
| Comprehension Tab Active | `linear-gradient(135deg, #7c3aed, #a78bfa)` | Comprehension tab selected state |
| Progress Fill | `linear-gradient(90deg, #9333ea, #db2777)` | Progress bar fill |
| Roadmap Line | `linear-gradient(to bottom, #e9d5ff, #fce7f3)` | Vertical roadmap spine |
| Page Background | Two radial gradients — `#ffe4f3` top-left, `#e8d5ff` bottom-right at 40% opacity | Body background atmosphere |

---

## 5. Layout

### Page Wrapper

All content is constrained to a centered max-width container:

```css
.fmn-page {
    max-width: 760px;
    margin: 0 auto;
    padding: 1.25rem 1rem 3rem;
}
```

Never allow content to stretch full-width on desktop. The `760px` max-width ensures readability on large screens while staying comfortable on tablet.

### Breakpoints

| Breakpoint | Width | Behaviour |
|------------|-------|-----------|
| Mobile | `< 480px` | Single column, nav greeting hidden, stats 2-col |
| Tablet | `480px – 768px` | Stats 4-col, full nav |
| Desktop | `> 768px` | Full layout, roadmap nodes narrower |

### Grid — Stats

```css
grid-template-columns: repeat(4, minmax(0, 1fr));  /* tablet/desktop */
grid-template-columns: repeat(2, minmax(0, 1fr));  /* mobile < 480px */
gap: 10px;
```

**Rule:** Never use more than 4 columns in a stat grid. Never use `repeat(3, ...)` — use 2 or 4 only.

---

## 6. Components

### Navbar (`.fmn-nav`)

```css
height: 58px;
background: rgba(255,255,255,0.92);
backdrop-filter: blur(10px);
border-bottom: 1.5px solid #f3e8ff;
position: sticky; top: 0; z-index: 100;
```

- Brand name: Fredoka One, `1.4rem`, `#9333ea`
- Greeting text: Nunito 700, `0.85rem`, `#a78bfa` — hidden on mobile `< 480px`
- Logout button uses `.fmn-btn-ghost`

### Hero Card (`.fmn-hero`)

```css
background: linear-gradient(135deg, #9333ea 0%, #db2777 100%);
border-radius: 20px;
padding: 1.5rem;
color: white;
position: relative; overflow: hidden;
```

- Decorative `✦` glyph in `::after`, `opacity: 0.1`, positioned right-center
- Title: Fredoka One `1.6rem`
- Subtitle: Nunito `0.88rem`, `opacity: 0.88`
- Progress bar track: `rgba(255,255,255,0.25)`, height `10px`
- Progress fill: `background: white`

### Cards (`.fmn-card`)

```css
background: white;
border: 1.5px solid #f3e8ff;
border-radius: 18px;
padding: 1.1rem 1.25rem;
margin-bottom: 1.25rem;
```

No box shadows on standard cards. Use border only.

### Stat Cards (`.fmn-stat`)

```css
background: white;
border: 1.5px solid #f3e8ff;
border-radius: 16px;
padding: 0.9rem 0.5rem;
text-align: center;
```

- Number: Fredoka One `1.9rem`, `#9333ea`
- Label: Nunito 800, `0.68rem`, `#c4b5fd`, uppercase, `letter-spacing: 0.06em`

### Buttons (`.fmn-btn`)

Base class:
```css
display: inline-flex; align-items: center; gap: 6px;
padding: 9px 20px;
border-radius: 999px;
font-family: 'Nunito', sans-serif; font-weight: 800; font-size: 0.85rem;
cursor: pointer; border: none;
transition: transform 0.15s, box-shadow 0.15s;
```

| Variant | Background | Text | Hover |
|---------|-----------|------|-------|
| `.fmn-btn-primary` | Purple→Pink gradient | white | `translateY(-2px)` + purple shadow |
| `.fmn-btn-ghost` | white | `#9333ea` | `background: #fdf4ff` |
| `.fmn-btn-sm` | — | — | `padding: 7px 16px; font-size: 0.8rem` |

### Badges (`.fmn-badge`)

```css
display: inline-block;
padding: 3px 12px; border-radius: 999px;
font-size: 0.7rem; font-weight: 800;
text-transform: uppercase; letter-spacing: 0.05em;
```

Always use subject colour pairs. Never use generic gray badges.

### Status Pills (`.fmn-pill`)

```css
display: inline-flex; align-items: center; gap: 4px;
padding: 4px 12px; border-radius: 999px;
font-size: 0.78rem; font-weight: 700;
```

Use semantic colour pairs from the Status Colours table.

### Section Titles (`.fmn-section-title`)

```css
font-family: 'Fredoka One', cursive;
font-size: 1.15rem; color: #7c3aed;
display: flex; align-items: center; gap: 7px;
margin: 0 0 0.85rem;
```

Always precede a section title with a relevant emoji.

### Alert / Info Box (`.fmn-alert`)

```css
background: #fdf4ff;
border: 1.5px solid #e9d5ff;
border-radius: 14px;
padding: 1rem 1.25rem;
color: #7c3aed; font-weight: 700; font-size: 0.88rem;
```

---

## 7. Tab Bar (`.fmn-tabs`)

```css
display: flex; gap: 8px;
overflow-x: auto; padding-bottom: 4px;
scrollbar-width: none;
```

Individual tabs:
```css
flex-shrink: 0;
padding: 7px 16px; border-radius: 999px;
font-size: 0.82rem; font-weight: 800;
border: 1.5px solid #e9d5ff;
background: white; color: #7c3aed;
transition: all 0.18s;
```

Active state applies the subject gradient as background with `border-color: transparent`.

Tabs are powered by Alpine.js `x-data` / `@click` / `:data-active`. No JavaScript framework required.

---

## 8. Roadmap Component

The learning journey roadmap is a **vertical path** — never zigzag or horizontal on any screen size.

### Structure

```
.fmn-roadmap (position: relative)
  .fmn-roadmap-line  ← absolute vertical line, left: 19px
  .fmn-roadmap-item  ← flex row: dot + content card
    .fmn-node-dot    ← 40×40 circle
    .fmn-node-content← flex card with topic + meta + score + status dot
```

### Roadmap Line

```css
position: absolute; left: 19px; top: 0; bottom: 0;
width: 2px;
background: linear-gradient(to bottom, #e9d5ff, #fce7f3);
z-index: 0;
```

### Node Dot (`.fmn-node-dot`)

```css
width: 40px; height: 40px; border-radius: 50%;
display: flex; align-items: center; justify-content: center;
font-size: 1.1rem; flex-shrink: 0;
border: 2px solid white;
box-shadow: 0 0 0 2px #e9d5ff;  /* default ring */
```

Subject fill classes: `.dot-math`, `.dot-editing`, `.dot-comprehension`
Status ring classes: `.dot-mastered` (`#6ee7b7`), `.dot-diagnostic` (`#c4b5fd`), `.dot-notstarted` (opacity 0.6)

### Status Icons

| Status | Icon |
|--------|------|
| Mastered | 🌟 |
| Diagnostic Passed | 📖 |
| Not Started | ○ |

### Node Content Card (`.fmn-node-content`)

```css
background: white; border: 1.5px solid #f3e8ff;
border-radius: 14px; padding: 10px 14px;
flex: 1; min-width: 0;
display: flex; align-items: center; justify-content: space-between;
```

Status border overrides: `.node-mastered` → `#a7f3d0`, `.node-diagnostic` → `#ddd6fe`, `.node-notstarted` → opacity 0.7

---

## 9. Parent Portal Additions

### Avatar Circle (`.fmn-avatar`)

```css
width: 46px; height: 46px; border-radius: 50%;
background: linear-gradient(135deg, #a855f7, #ec4899);
color: white; font-family: 'Fredoka One', cursive; font-size: 1rem;
display: flex; align-items: center; justify-content: center;
```

Content: first two characters of the student's name, uppercased.

### Progress Bar (Parent version)

Uses inline styles directly — same track/fill pattern as hero card but with purple track (`#f3e8ff`) instead of white-transparent.

---

## 10. Box Model Rules

- **Border radius:** `999px` for pills/badges/buttons, `20px` for hero cards, `18px` for main cards, `16px` for stat cards, `14px` for node content cards, `12px` for small inset surfaces
- **Borders:** Always `1.5px solid` — never `1px` or `2px` except active tab focus rings
- **No box shadows** on cards. Use border colour to define depth.
- **No gradients on borders or text** — gradients are for backgrounds only
- `box-sizing: border-box` applied globally via `*, *::before, *::after`

---

## 11. Spacing Scale

| Token | Value | Usage |
|-------|-------|-------|
| xs | `4px` | Badge padding vertical, gap between inline elements |
| sm | `8px` | Tab gap, small internal padding |
| md | `10px–12px` | Grid gap, node padding |
| lg | `1rem (16px)` | Card padding, section spacing |
| xl | `1.25rem (20px)` | Page section margin-bottom |
| 2xl | `1.5rem (24px)` | Hero card padding |
| page | `1.25rem 1rem 3rem` | Page wrapper padding |

---

## 12. Interactivity

- **Alpine.js** handles all tab switching and conditional visibility (`x-data`, `@click`, `x-show`, `:data-active`)
- **No jQuery.** No Vue. No React. Blade + Alpine only.
- Button hover: `transform: translateY(-2px)` + subject-appropriate shadow
- Tab transition: `transition: all 0.18s`
- Progress bar fill: `transition: width 1.2s ease` (CSS only, no JS animation)
- Roadmap items filtered with `x-show` — Alpine handles show/hide without re-rendering

---

## 13. Do's and Don'ts

| Do | Don't |
|----|-------|
| Use Fredoka One for display headings and numbers | Use Inter, Roboto, or system fonts |
| Use the purple→pink gradient for hero and primary actions | Use gradients on borders, dividers, or text |
| Keep the page max-width at 760px | Let content stretch full-width on desktop |
| Use subject colour pairs consistently | Mix subject colours across different subjects |
| Use `1.5px solid` borders for all cards | Use `1px` or `2px` borders on standard cards |
| Hide non-essential nav elements on mobile | Stack the entire navbar vertically |
| Use `fmn-alert` for empty states | Show blank white space with no message |
| Keep stat grids to 2 or 4 columns only | Use 3-column stat grids |

