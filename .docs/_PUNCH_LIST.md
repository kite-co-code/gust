# Code Punch List

Items surfaced during audits that need investigation or fixing in code. These are separate from spec updates — they are real gaps, bugs, or missing artefacts.

---

## Open Items

*(None currently open.)*

---

## Recently Fixed

### Banner — `block.json` disabled, ACF group orphaned — **[FIXED]**
`components/banner/block.json.disabled` exists (block intentionally disabled), but `group_component_banner.json` still carried a location rule targeting `acf/banner`. Removed the orphaned location rule (set to `[]`) and added a description noting that the banner is rendered programmatically. The component works fine as a Partial.

### `TripData::getStatusLabel()` did not handle `sold_out_private` — **[FIXED]**
`getStatusLabel()` returned "Book Now" for `sold_out_private` via the default arm of the match. Added the explicit `'sold_out_private' => 'Private Group'` arm so the section nav secondary CTA renders the correct label. Canonical label sourced from `TripDates.php` which already uses "Private Group" for the same status.

---

## Previously Fixed Items

*(Moved here from WEBSITE-SPEC.md punch list for reference)*

- Trip Dates sold_out_label never reaches template — **[FIXED]**
- Trip Dates price/nights computed but suppressed — **[FIXED]**
- Routes comment wrong — **[FIXED]**
- TaxonomyFilters::transform() reads unused $args['object'] — **[FIXED]**
- Card::transform() fallback read_more_text — **[FIXED]**
- Cards.slider_on_mobile conditional logic undocumented — **[FIXED]**
- Cards card_background_color and tag runtime args — **[FIXED]**
- Footer ACF group not exported to JSON — **[RESOLVED]** — `acf-json/group_608ab0e3c02c4.json` is the Footer group (6 fields, location `acf-options-footer`)
- Taxonomy term ACF fields not in JSON — **[RESOLVED]** — all five files exist in `Theme/Modules/Trips/acf-json/` (`group_country_taxonomy.json`, `group_location_taxonomy.json`, `group_skill_level_taxonomy.json`, `group_swim_type_taxonomy.json`, `group_trip_style_taxonomy.json`)
