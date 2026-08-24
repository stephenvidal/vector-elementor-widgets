# Control Mapping Contract

How the Derby-standard component system maps onto Elementor controls. This is
the canonical translation table — a future agent converting a new section
consults this doc instead of making architectural decisions.

## The core mapping

| Derby hook | Elementor control | Notes |
|---|---|---|
| `data-copy="key"` (plain) | `TEXT` / `TEXTAREA` | Single copy string |
| `data-copy` (rich/markup) | `WYSIWYG` | Only where the source actually has markup |
| `data-list` / `data-cards` / `data-gallery` / `data-reviews` / `data-faq` / `data-process` / `data-why` / `data-menu` / `data-stats` / `data-portfolio` | `REPEATER` | Each array item = one repeater row; row fields = the item's sub-hooks |
| `data-image="owner"` | `MEDIA` | Attachment ID |
| `data-copy-href` (CTA label + target) | `TEXT` + `URL` | Two controls |
| `data-variant-switch` / `data-variant-content` (N-state toggle) | `SELECT` variant + per-variant `REPEATER` gated by `condition` | See below |

## The N-state toggle (the signature Derby component)

The Derby variant switcher (`monthly/annual/lifetime`, `hourly/fixed/retainer`,
`pickup/delivery/catering`, ...) becomes:

- A `SELECT` control named `variant` with the N options.
- One `REPEATER` per variant, each gated:
  ```php
  'condition' => array( 'variant' => 'monthly' ),
  ```

The sliding indicator is CSS-driven, reading a `data-variant` attribute on the
widget wrapper. This preserves "one component, N meanings" — a new site picks
fresh variant labels/values, no code change.

## Design tokens → Elementor global colors

The Derby `:root` tokens map to named Elementor global colors in the site kit:

| Derby token | Elementor global color id |
|---|---|
| `--brand` | `brand` |
| `--brand-dark` | `brand-dark` |
| `--accent` | `accent` |
| `--ink` | `ink` |
| `--muted` | `muted` |
| `--paper` | `paper` |
| `--surface` | `surface` |
| `--soft` | `soft` |
| `--line` | `line` |
| `--hero-overlay` | `hero-overlay` |

Widgets reference these via `__globals__` (e.g.
`settings.__globals__.title_color = "globals/colors?id=brand"`), never
hardcoded colors. See `theming.md`.

## Component → widget control trees

### Feature Grid (`vew-feature-grid`) — Phase 3

**Content**
- `eyebrow` — TEXT
- `title` — TEXT
- `intro` — TEXTAREA
- `features` — REPEATER (row fields `title`, `text`, `detail`, `number`); first card renders large (spans 2 rows)

**Style**
- `fg_heading_color` — COLOR
- `fg_card_bg` — COLOR
- `fg_card_title_color` — COLOR
- `fg_card_detail_color` — COLOR

### Testimonials (`vew-testimonials`) — Phase 3

**Content**
- `eyebrow` — TEXT
- `title` — TEXT
- `reviews` — REPEATER (row fields `quote`, `author`, `role`, `stars`[NUMBER 1-5])

**Style**
- `ts_heading_color` — COLOR
- `ts_star_color` — COLOR
- `ts_card_border_color` — COLOR

### FAQ (`vew-faq`) — Phase 3 (accessible accordion)

**Content**
- `eyebrow` — TEXT
- `title` — TEXT
- `intro` — TEXTAREA
- `items` — REPEATER (row fields `question`, `answer`)

**Style**
- `faq_heading_color` — COLOR
- `faq_question_color` — COLOR
- `faq_answer_color` — COLOR

**JS:** `vew-faq` script handles the accordion toggle (aria-expanded + hidden),
loaded only when the widget is present.

**Note on control-id uniqueness:** Elementor scopes controls per-widget, so
`eyebrow`/`title`/`intro` repeat across widgets intentionally. The
ControlSectionIdsTest enforces uniqueness WITHIN each widget's own control
set (grouped by widget name prefix).

### Hero (`vew-hero`) — Phase 2

**Content**
- `eyebrow` — TEXT
- `headline` — WYSIWYG (allows `<br>`/`<em>` emphasis per Derby)
- `copy` — TEXTAREA
- `primary_cta_text` — TEXT · `primary_cta_url` — URL
- `secondary_cta_text` — TEXT · `secondary_cta_url` — URL
- `trust_list` — REPEATER (row field `text`)

**Style**
- `hero_background_color` — COLOR
- `hero_overlay_opacity` — SLIDER
- `hero_heading_color` — COLOR
- `hero_eyebrow_color` — COLOR
- `hero_copy_color` — COLOR

**Note:** Hero style control ids are `hero_*`-prefixed to keep control ids
globally unique (the CTA banner uses `background`/`heading_color`/etc.). The
ControlSectionIdsTest enforces global uniqueness across all control classes.

### CTA Banner (`vew-cta-banner`)

**Content**
- `heading` — TEXT
- `description` — TEXTAREA
- `primary_text` — TEXT · `primary_url` — URL
- `secondary_text` — TEXT · `secondary_url` — URL

**Style**
- `background` — COLOR
- `heading_color` — COLOR
- `description_color` — COLOR
- `primary_button_color` — COLOR

**Responsive** — component CSS owns the ≤820px collapse; Elementor responsive
controls used for value overrides only.

## Rule: responsive controls never own the structural collapse

The layout collapse (grids → single column at ≤820px) is **always** in the
component CSS, never Elementor's responsive controls. Elementor responsive
controls are for value overrides (font sizes, spacing, alignment). This keeps
the Derby no-overflow guarantee while using Elementor's default breakpoints.

## Structured section headings

For section headings that need safe partial emphasis, consume
`Elementor\Component\SectionHeading` instead of accepting raw HTML. Preserve
the existing `eyebrow`, `title`, and `intro` controls, then register the shared
content/style controls and render through the component. The additive fields
are `title_accent`, `title_after`, and responsive `title_accent_display`.
Accent placement uses Elementor-generated responsive CSS (`inline` for natural
wrapping or `block` for a deliberate new line). Accent color and typography use
native Elementor style controls. Do not rename existing persisted IDs.
