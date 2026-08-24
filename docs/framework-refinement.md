# Framework Refinement — Vidal Diagnostic

Date: 2026-08-16
Reference: `websites/vidal-studio`
Test page: Elementor page 133

## Method

The source and widget page were compared at 390×844, 768×1024, and 1280×900. Differences were classified before implementation. A framework change required evidence of recurrence across unrelated widgets; one-site details remained page- or widget-specific.

## Discrepancy inventory

| Difference | Classification | Decision |
|---|---|---|
| Services, Portfolio, Process, Pricing, FAQ, and Contact lost deliberate line breaks and italic phrases | MISSING REUSABLE COMPONENT | Implement shared `SectionHeading` capability |
| Hero already represented `<br>/<em>` through WYSIWYG | CONFIGURATION / FRAGILE ESCAPE HATCH | Preserve for backward compatibility; new section widgets should prefer structured heading controls |
| Services and Portfolio stacked intro copy below headings on desktop | STYLING / DEFAULT | Correct scoped CSS defaults; no new control or component |
| Hero viewport sizing, mobile copy size, overlay, and CTA diverged | RESPONSIVE / STYLING / DEFAULT | Restore canonical widget defaults and preserve existing controls |
| Content sections used 80px/60px rather than 120px/82px rhythm | PAGE CONFIGURATION | Reconfigure page 133 containers; do not hard-code page rhythm into widgets |
| FAQ source has an “Ask a question” intro link | WIDGET-SPECIFIC LIMITATION | Future additive FAQ control; not a generic component yet |
| Contact source has consent, helper text, and success behavior | CONTENT / BEHAVIOR / ACCESSIBILITY | Contact-specific follow-up, not heading/component scope |
| Footer source has Privacy/Terms and a utility note | CONTENT / WIDGET-SPECIFIC LIMITATION | Additive Footer utility-row controls are justified later |
| Source has skip link and back-to-top control | ACCESSIBILITY / PAGE UTILITY | Evaluate at theme/page-shell level; do not embed in every section |
| Pricing hard-codes `/ project` | CONFIGURATION / WIDGET-SPECIFIC LIMITATION | Add a structured suffix control later with legacy fallback |
| Cards share visual traits but retain different semantics | REFERENCE-SPECIFIC / PREMATURE ABSTRACTION | Do not create a universal Card widget |
| Services/FAQ contain structured children | BEHAVIOR | Current repeaters are appropriate; true nesting is not justified by this page |

## Repetition finding

Six unrelated sections repeated the same eyebrow/title/intro renderer and seven repeated the same heading typography. The single missing capability explained the largest visible source/live divergence: on desktop, several source headings occupied two intentionally composed lines while the widget page collapsed them into one natural line.

## Implemented capability

`Elementor\Component\SectionHeading` provides:

- additive `title_accent` and `title_after` text controls;
- responsive semantic Accent Placement (`Inline / Natural Wrap` or `New Line`);
- native Elementor accent color and typography controls;
- escaped semantic `<span>` and `<em>` rendering;
- configurable widget BEM classes and intro suffix;
- exact legacy plain-title output when additive fields are empty;
- a shared, conditionally loaded stylesheet.

Consumers: Services, Portfolio, Process, Pricing, FAQ, and Contact.

## Why this is internal—not nested

The author is editing semantic pieces of one heading, not freely composing arbitrary widgets. A nested Elementor tree would add Navigator depth and editor complexity without useful composition. True nesting remains appropriate only when authors need arbitrary children inside layout slots (for example flexible tabs, carousels, or content grids).

## Compatibility

Existing IDs (`eyebrow`, `title`, `intro`) are unchanged. Existing saved pages with no new settings render a plain heading without an `<em>`, preserving output and styling. New controls are additive. Widget slugs, repeater structures, and registration are unchanged.

## Validation

- PHPCS clean
- PHPStan clean
- PHPUnit: 95 tests / 609 assertions
- Live page: shared component CSS and generated `post-133.css` return successfully
- 390, 768, 1280px: source-equivalent semantic emphasis and line composition
- Hero heights match the source at 750px phone, 760px tablet, and 784px desktop
- Services and Portfolio use source-equivalent split headings above 1100px
- Page 133 uses 120px desktop and 82px tablet/mobile content-section rhythm
- zero horizontal overflow
- zero JavaScript errors

## Deferred—not ignored

The next evidence-backed candidates are Contact form parity, Footer utility rows, and Pricing suffix configuration. They should be implemented as focused widget extensions unless reuse across unrelated widgets is demonstrated. No universal Card, Button, List, or nested-component layer is warranted by current evidence.
