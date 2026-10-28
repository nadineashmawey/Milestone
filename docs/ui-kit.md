# Milestone UI kit

The shared tokens and Blade components every page uses. See them all, with every state, at `/ui-kit` (local environment only). For how to start a page, read [shared-foundation.md](shared-foundation.md) first.

## Tokens

All values live in `resources/css/tokens.css` as CSS custom properties. Use them with `var()` instead of hard-coding colours or sizes.

| Group | Examples |
| --- | --- |
| Brand colours (SRS §12.3) | `--color-royal-blue` `#1F4FFF`, `--color-violet` `#B565F0`, `--color-yellow` `#FFD93B`, `--color-coral` `#EE6B6B`, `--color-light-blue` `#4D7CFF`, `--color-green` `#8FD18A` |
| Text and surfaces | `--color-ink` `#1E1F3B`, `--color-ink-2` `#3A3D5C`, `--color-muted` `#5B6580`, `--color-faint` `#707A93`, `--color-icon` `#9AA3B8`, `--color-border` `#D5DAE8`, `--color-line` `#E1E5EF`, `--color-lavender` `#E8EBF5`, `--color-page` `#F5F6FB`, `--color-surface` `#FFFFFF` |
| Roles | `--color-primary`, `--color-primary-hover`, `--color-text`, and the paired status colours `--color-{info,success,warning,danger,review}-{bg,text}` |
| Fixed in both modes | `--color-white` (text on coloured fills), `--color-night` (sections that stay dark, dark text on yellow, green, violet and coral), `--color-on-night`, `--color-on-night-muted`, `--color-periwinkle`, `--color-shadow` |
| Links | `--color-link` and `--color-link-hover` for blue text. `--color-primary` is for fills with white text. |
| Type | `--font-family` (Plus Jakarta Sans 400, 500, 600, 700, 800), sizes `--font-micro` (11px) to `--font-display` (44px), `--font-page-title` for every page's h1, weights `--weight-regular/medium/semibold/strong/bold`, line heights `--line-tight` to `--line-relaxed` |
| Spacing | `--space-1` (4px) to `--space-16` (64px) |
| Radii | `--radius-xs` (6px), `-sm` (8), `-md` (10), `-control` (12), `-lg` (14), `-xl` (18), `-card` (20), `-2xl` (24), `-sidebar` (28), `-pill` |
| Shadows | `--shadow-sm`, `--shadow-raised`, `--shadow-card`, `--shadow-popover`, `--shadow-lg`, and `--focus-ring` |

Status colours always come as a pair: put `-text` on `-bg`, never on white or on another background.

## Theme rules

The whole frontend uses one theme: the Milestone design system from SRS §12.3 and the Figma reference. `tokens.css` is its only source.

**Do**

- Use a token for every colour, font size, weight, radius and shadow: `color: var(--color-muted)`, `font-size: var(--font-small)`, `border-radius: var(--radius-control)`, `box-shadow: var(--shadow-card)`.
- For a transparent tint of a token, use `color-mix(in srgb, var(--color-ink) 6%, transparent)`.
- Give a page's main heading `var(--font-page-title)`. The base `h1` already does.
- In markup, Tailwind utilities map to the tokens: `font-sans`, `text-ink`, `text-muted`, `bg-page`, `bg-surface`, `text-blue`, `rounded-md`, `rounded-2xl`, `shadow-lg`. The default Tailwind palette is switched off, so classes like `text-gray-500` do nothing.
- Need a new value everyone shares? Add it to `tokens.css`, agree it with the team, and use it from there.

**Don't**

- Write hex, `rgb()` or named colours outside `tokens.css`.
- Set another `font-family`, or load a font anywhere except the single Google Fonts import in `app.css`. Weights 400 to 800 are loaded, so don't use 900 or in-between weights like 650.
- Redefine a token (`--color-…`, `--font-…`, `--radius-…`) in a page stylesheet. Use your own page-prefixed variable instead, set from a token: `--issue-blue: var(--color-royal-blue)`.
- Go below `--font-xs` (12px) for real text. `--font-micro` is for decorative previews only.

`tests/Feature/ThemeTest.php` enforces these rules: one font, loaded once, colours only from tokens, and no redefined tokens. If something genuinely can't use a token, add it to the short, commented allowlist in that test and tell the team why. Today the only entries are the corkboard's wood tones on Jana's task board.

## Dark mode

Every page supports dark mode with no page CSS of its own.

- **How it works:** the toggle (navbar, and on `/ui-kit`) sets `class="dark"` and `data-theme="dark"` on `<html>` and saves the choice in `localStorage` (`milestone-theme`). With no saved choice, the system setting decides. `resources/views/partials/theme-init.blade.php`, included once in `layouts/base`, applies it before the first paint, so there's no light flash. The dark block at the end of `tokens.css` redefines the colour tokens, and everything reading them switches.
- **Make a new page dark-mode ready:** use tokens for every colour, as the theme rules above already require. Cards and fields: `--color-surface`. Page background: `--color-page`. Text: `--color-ink` or `--color-muted`. Lines: `--color-border` or `--color-line`. Blue text: `--color-link`. In markup: `bg-surface`, `bg-page`, `text-ink`, `text-muted`, `border-border`, never `bg-white` or `text-gray-500`. Tailwind's `dark:` variant also follows the switch.
- **Things that should stay dark or coloured** in both modes (a dark hero, a yellow card with dark text): use `--color-night` for the dark fill or the dark text, `--color-white` for text on it, and `--color-on-night` / `--color-on-night-muted` for paragraphs on night.
- **Don't** write `:root[data-theme='dark']` or `.dark` rules in a page file, or define dark colours anywhere except `tokens.css`. `tests/Feature/ThemeTest.php` checks this and that dark text passes WCAG AA.

## Components

All components live in `resources/views/components/`. Every prop not listed as required is optional.

### Button

```blade
<x-button>Save</x-button>                              {{-- primary --}}
<x-button variant="secondary">Cancel</x-button>
<x-button variant="dark">Continue</x-button>
<x-button variant="danger">Delete draft</x-button>
<x-button variant="ghost">Sign in</x-button>
<x-button size="small">Small</x-button>                {{-- or size="large" --}}
<x-button type="submit" loading>Sending…</x-button>    {{-- disabled + aria-busy + spinner --}}
<x-button href="{{ route('help') }}">Help</x-button>   {{-- renders an <a> with button styles --}}
```

The default `type` is `button`, so set `type="submit"` inside forms. Use `href` for navigation, never a button with a click handler.

### Form fields

```blade
<x-input id="report-name" name="name" label="Name" :value="old('name')" required />

<x-select id="report-category" name="category" label="What is it about?"
    placeholder="Choose one" :options="$categories" :selected="old('category')" />

<x-textarea id="report-description" name="description" label="What happened?"
    rows="5" :value="old('description')" help="At least 20 characters." />
```

- `options` is an array of `value => label`.
- Each field shows an `error` you pass in, or else the Laravel validation error for its `name`. When an error is shown, the control gets `aria-invalid="true"` and is linked to the message with `aria-describedby`.
- Fields don't fill in `old()` input for you. Pass `:value="old('name')"` or `:selected="old('category')"`.
- Give every field a unique `id`.

### Badge

```blade
<x-badge tone="success">Done</x-badge>
```

Tones are `neutral` (default), `info`, `success`, `warning`, `danger` and `review`. The text must say the status, because colour alone isn't enough.

### Card, page header, section heading

```blade
<x-card title="Team progress">…</x-card>   {{-- title renders as h2 --}}
<x-page-header title="Courses" description="Your academic courses."><x-button>New course</x-button></x-page-header>
<x-section-heading eyebrow="FAQ" title="Questions people ask first." description="…" />
```

`x-section-heading` renders an `h2`. Pass `level="3"` when it sits inside an `h2` section, and `title-id="…"` when a section needs `aria-labelledby`.

### Alert

```blade
<x-alert tone="success" title="Report sent">Your reference is MS-4821.</x-alert>
<x-alert tone="error" title="We couldn’t send your report.">Your details are still here.</x-alert>
```

Tones are `info` (default), `success` and `error`. Error alerts get `role="alert"` and the others get `role="status"`. Each one has an icon and a written title, so the message never relies on colour alone.

### Empty state

```blade
<x-empty-state title="No articles match “grades”.">
    <p>Try a shorter word.</p>
    <x-slot:action><x-button variant="secondary">Clear search</x-button></x-slot:action>
</x-empty-state>
```

## Rules for every page

- **Focus:** keep the visible focus ring from `base.css`. Don't remove `outline` on `:focus-visible`.
- **No colour-only meaning:** pair every colour with text or an icon.
- **360px wide:** check there's no horizontal scroll at 360px, 768px and 1440px.
- **Scoped styles:** put page styles in `resources/css/pages/<page>.css` with a page prefix (for example `.courses-page__…`). Don't copy or override shared component rules.
- **Shared changes:** agree any change to the shared files with the team first, and only add opt-in options so existing pages render the same.
