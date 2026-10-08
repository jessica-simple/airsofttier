# BeTheme Advanced Filters: Change Explanation

## Scope

The custom Advanced Filters behavior is implemented in:

- `themes/betheme-child/js/mfn-opt-expandable.js`
- `themes/betheme-child/style.css`

No files under `themes/betheme/` are changed.

## Root cause

BeTheme uses `.mfn-opt-expandable` as its own Show More/Show Less bookkeeping class. Its native handler searches all descendant `li` elements, not only direct children of the top-level filter list.

The previous child script added `.mfn-opt-expandable` to nested parents. When Show Less ran, BeTheme therefore hid and removed that class from nested child items. The custom reset then could no longer reliably find every open child, and later initialization could leave stale `.is-open` classes in place.

## Fix

### Separate native and custom markers

- BeTheme retains ownership of `.mfn-opt-expandable`.
- Custom nested items use `.mfn-opt-expandable--has-children`.
- CSS and delegated child-toggle handling use the custom marker.
- Nested child items are no longer included in BeTheme's Show More/Show Less bookkeeping.

### Preserve native Show More state across AJAX

The script remembers whether a filter's native expander is active before a checkbox refresh. After BeTheme replaces the filter DOM, it restores the expander label/class and synchronizes only the direct top-level options using BeTheme's existing `mfn-opt-hidden` and `mfn-opt-expandable` classes.

The script does not reorder categories or rebuild BeTheme's Show More/Show Less algorithm. Native Show Less therefore returns the original server-rendered top-level set and order.

### Reset custom child state on Show Less

After BeTheme handles Show Less, the script:

- clears the child expansion state map;
- removes `.is-open` from every `.mfn-opt-expandable--has-children` item; and
- updates each toggle's `aria-expanded` and `aria-label` values.

Because the custom marker remains present while BeTheme changes its own class, every child item remains discoverable during the reset. Later AJAX or MutationObserver initialization has no saved child-open state to restore.

### Reset All and accessibility

Reset All clears both custom state maps without preventing BeTheme's native handler. Custom buttons retain `aria-controls`, `aria-expanded`, and action-specific labels. The configured unwanted labels are hidden on their individual `<li>` options rather than hiding the entire filter list.

## Expected behavior

- See More and See Less continue to be controlled by BeTheme.
- Checkbox-triggered AJAX refreshes preserve an active See More state.
- See Less restores the original top-level category set and order.
- See Less collapses all custom child categories.
- Repeated See More -> checkbox -> See Less cycles do not progressively alter the list.
- Reset All clears stale custom state and leaves child categories collapsed.

## Validation checklist

Verify these flows in the browser with live BeTheme markup and AJAX:

1. Initial state -> See More -> See Less.
2. Check category -> See More -> check another category -> See Less.
3. See More -> check category -> AJAX refresh.
4. See More -> check category -> See Less -> AJAX refresh.
5. Open one child category -> check category -> AJAX refresh.
6. Open multiple child categories -> See Less.
7. Check category -> Reset All.
8. Repeat See More -> check -> See Less at least five times.

Inspect direct top-level `<li>` count/order, `.mfn-expanded`, `.mfn-opt-hidden`, `.mfn-opt-expandable`, `.mfn-opt-expandable--has-children.is-open`, and toggle ARIA attributes after each refresh.