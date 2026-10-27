<?php

namespace Tests\Feature;

use Symfony\Component\Finder\Finder;
use Tests\TestCase;

/**
 * Keeps the whole frontend on one theme (docs/ui-kit.md, "Theme rules"):
 * one font, loaded once, and colours only from resources/css/tokens.css.
 */
class ThemeTest extends TestCase
{
    /**
     * Colours allowed outside tokens.css, each with the reason. Keep this short.
     *
     * @var array<string, list<string>>
     */
    private const ALLOWED_COLOURS = [
        // Jana's corkboard task board: warm wood shadows under the pinned notes. No token is close (ΔE > 26),
        // and a cool ink shadow made the board look flat, so these stay until the team agrees a "wood" token.
        'resources/css/student-projects.css' => [
            'rgb(54 42 25 / 16%)', 'rgb(54 42 25 / 10%)', 'rgb(48 26 20 / 32%)', 'rgb(48 26 20 / 28%)', 'rgb(40 25 15 / 45%)', // note shadows
            '#261b13', '#34231c', // dark wood shading on the pin heads
            '#9e8f70',            // taupe dashed outline of an empty slot on the board
        ],
    ];

    /**
     * @return iterable<string, string> relative path => contents, with comments removed
     */
    private function files(string $directory, string $pattern): iterable
    {
        foreach ((new Finder)->files()->in(base_path($directory))->name($pattern) as $file) {
            $path = $directory.'/'.str_replace('\\', '/', $file->getRelativePathname());
            $contents = $file->getContents();
            $contents = preg_replace('~/\*.*?\*/~s', '', $contents);   // CSS comments
            $contents = preg_replace('~\{\{--.*?--\}\}~s', '', $contents); // Blade comments

            yield $path => $contents;
        }
    }

    public function test_plus_jakarta_sans_is_the_only_font(): void
    {
        $offenders = [];
        foreach ([...$this->files('resources/css', '*.css'), ...$this->files('resources/views', '*.blade.php')] as $path => $contents) {
            preg_match_all('/(?<![\w-])font-family\s*:\s*([^;}"]+)/', $contents, $matches); // not the --font-family token itself
            foreach ($matches[1] as $value) {
                $value = trim($value);
                // The token itself, inherit, and monospace for code samples are allowed.
                if (! preg_match('/^(var\(--font-family\)|inherit|ui-monospace\b.*|monospace)$/', $value)) {
                    $offenders[] = "{$path}: font-family: {$value}";
                }
            }
            preg_match_all('/(?<![\w-])font\s*:\s*([^;}]+)/', $contents, $shorthand);
            foreach ($shorthand[1] as $value) {
                $value = trim($value);
                if ($value !== 'inherit' && ! str_contains($value, 'var(--font-family)')) {
                    $offenders[] = "{$path}: font: {$value}";
                }
            }
        }

        $this->assertSame([], $offenders, 'Use font-family: var(--font-family) (or inherit).');
    }

    public function test_the_font_loads_once_with_every_weight_the_ui_uses(): void
    {
        $loads = [];
        foreach ([...$this->files('resources/css', '*.css'), ...$this->files('resources/views', '*.blade.php')] as $path => $contents) {
            if (preg_match_all('/fonts\.googleapis\.com|fonts\.bunny\.net|@font-face|use\.typekit/', $contents, $m)) {
                $loads[$path] = count($m[0]);
            }
        }

        $this->assertSame(['resources/css/app.css' => 1], $loads, 'The font loads once, in resources/css/app.css.');
        $this->assertStringContainsString('family=Plus+Jakarta+Sans:wght@400;500;600;700;800', file_get_contents(resource_path('css/app.css')));
    }

    public function test_colours_only_come_from_tokens(): void
    {
        $offenders = [];
        foreach ($this->files('resources/css', '*.css') as $path => $contents) {
            if ($path === 'resources/css/tokens.css') {
                continue;
            }
            $contents = preg_replace('/url\([^)]*\)/', 'url()', $contents); // encoded SVG data URLs aren't theme colours
            preg_match_all('/#[0-9a-fA-F]{3,8}\b|\b(?:rgba?|hsla?)\([^)]*\)/', $contents, $matches);
            foreach ($matches[0] as $colour) {
                if (! in_array($colour, self::ALLOWED_COLOURS[$path] ?? [], true)) {
                    $offenders[] = "{$path}: {$colour}";
                }
            }
        }

        foreach ($this->files('resources/views', '*.blade.php') as $path => $contents) {
            $contents = preg_replace('/&#\d+;|&#x[0-9a-f]+;/i', '', $contents); // HTML entities like &#128451;
            preg_match_all('/(?<![\w&-])#[0-9a-fA-F]{3}(?:[0-9a-fA-F]{3})?\b(?=[\s;"\')])/', $contents, $matches);
            foreach ($matches[0] as $colour) {
                $offenders[] = "{$path}: {$colour}";
            }
        }

        $this->assertSame([], array_values(array_unique($offenders)), 'Use a colour token from resources/css/tokens.css.');
    }

    public function test_only_tokens_css_defines_the_theme_tokens(): void
    {
        $tokens = file_get_contents(resource_path('css/tokens.css'));
        preg_match_all('/(--(?:color|font|weight|radius|shadow|space|line)-[\w-]+)\s*:/', $tokens, $names);

        $offenders = [];
        foreach ($this->files('resources/css', '*.css') as $path => $contents) {
            if ($path === 'resources/css/tokens.css') {
                continue;
            }
            // The navbar's dark mode may redefine colour tokens inside .ms-navbar only.
            $contents = preg_replace("/:root\[data-theme='dark'\] \.ms-navbar \{[^}]*\}/", '', $contents);
            // Tailwind's @theme maps utilities onto the tokens by name.
            $contents = preg_replace('/@theme inline reference \{.*?\n\}/s', '', $contents);
            foreach ($names[1] as $name) {
                if (preg_match('/(?<![\w-])'.preg_quote($name, '/').'\s*:/', $contents)) {
                    $offenders[] = "{$path}: redefines {$name}";
                }
            }
        }

        $this->assertSame([], $offenders, 'Change shared values in tokens.css, not in a page file.');
    }

    public function test_tailwind_utilities_use_the_tokens(): void
    {
        $app = file_get_contents(resource_path('css/app.css'));

        // "reference" keeps Tailwind from re-emitting same-name keys like --color-ink: var(--color-ink), which would be circular.
        $this->assertStringContainsString('@theme inline reference {', $app);

        foreach (['--font-*: initial;', '--color-*: initial;', '--radius-*: initial;', '--shadow-*: initial;', '--font-sans: var(--font-family);', '--color-ink: var(--color-ink);'] as $line) {
            $this->assertStringContainsString($line, $app);
        }
    }
}
