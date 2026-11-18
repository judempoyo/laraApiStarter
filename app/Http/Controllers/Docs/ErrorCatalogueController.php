<?php

declare(strict_types=1);

namespace App\Http\Controllers\Docs;

use App\Enums\ErrorCode;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ErrorCatalogueController extends Controller
{
    /**
     * Display the error catalogue either as JSON or as an interactive HTML document.
     */
    public function index(Request $request): Response|JsonResponse
    {
        $locale = app()->getLocale();
        $catalogue = [];
        $categories = [];
        $statuses = [];

        foreach (ErrorCode::cases() as $case) {
            $code = $case->value;
            $status = $case->httpStatus();
            $category = $case->category();
            $title = $case->title($locale);
            $description = $case->description($locale);
            $example = $case->examplePayload($locale);

            $catalogue[$code] = [
                'code' => $code,
                'status' => $status,
                'category' => $category,
                'title' => $title,
                'description' => $description,
                'url' => url("/docs/errors#{$code}"),
                'example' => $example,
            ];

            $categories[$category] = ($categories[$category] ?? 0) + 1;
            $statuses[$status] = ($statuses[$status] ?? 0) + 1;
        }

        ksort($categories);
        ksort($statuses);

        if ($request->wantsJson()) {
            return ApiResponse::success($catalogue, 'API error catalogue', 200, [
                'locale' => $locale,
                'count' => count($catalogue),
            ]);
        }

        $html = $this->renderHtml($catalogue, $categories, $statuses, $locale);

        return response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    /**
     * Render an interactive HTML catalogue with dark/light mode, live filters, and language switcher.
     *
     * @param  array<string, array<string, mixed>>  $catalogue
     * @param  array<string, int>  $categories
     * @param  array<int, int>  $statuses
     */
    protected function renderHtml(array $catalogue, array $categories, array $statuses, string $locale): string
    {
        $t = fn (string $key, array $replace = []): string => (string) __("errors.ui.{$key}", $replace, $locale);

        $totalCount = count($catalogue);
        $pageTitle = htmlspecialchars($t('page_title'));
        $heading = htmlspecialchars($t('heading'));
        $subtitle = htmlspecialchars($t('subtitle'));
        $searchPlaceholder = htmlspecialchars($t('search_placeholder'));
        $badgeRfc = htmlspecialchars($t('badge_rfc'));
        $apiDocs = htmlspecialchars($t('api_docs'));
        $github = htmlspecialchars($t('github'));
        $noResults = htmlspecialchars($t('no_results'));
        $allCategories = htmlspecialchars($t('all_categories'));
        $allStatuses = htmlspecialchars($t('all_statuses'));
        $copyLinkText = htmlspecialchars($t('copy_link'));
        $copyCodeText = htmlspecialchars($t('copy_code'));
        $copyPayloadText = htmlspecialchars($t('copy_payload'));
        $copiedText = htmlspecialchars($t('copied'));
        $examplePayloadText = htmlspecialchars($t('example_payload'));

        $categoryOptions = '';
        foreach ($categories as $cat => $count) {
            $catEsc = htmlspecialchars($cat);
            $categoryOptions .= "<option value=\"{$catEsc}\">{$catEsc} ({$count})</option>\n";
        }

        $statusOptions = '';
        foreach ($statuses as $stat => $count) {
            $statusOptions .= "<option value=\"{$stat}\">HTTP {$stat} ({$count})</option>\n";
        }

        $cardsHtml = '';
        foreach ($catalogue as $item) {
            $code = htmlspecialchars((string) $item['code']);
            $status = (int) $item['status'];
            $category = htmlspecialchars((string) $item['category']);
            $title = htmlspecialchars((string) $item['title']);
            $desc = htmlspecialchars((string) $item['description']);
            $anchorUrl = htmlspecialchars((string) $item['url']);
            $json = (string) json_encode($item['example'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $jsonExample = preg_replace_callback(
                '/("(?:\\\\.|[^"\\\\])*")(?=\s*:)|("(?:\\\\.|[^"\\\\])*")|(-?\d+(?:\.\d+)?(?:[eE][+-]?\d+)?)|\b(true|false|null)\b|([{}\[\],:])/',
                static function (array $matches): string {
                    $token = $matches[0];
                    $class = match (true) {
                        isset($matches[1]) && $matches[1] !== '' => 'json-key',
                        isset($matches[2]) && $matches[2] !== '' => 'json-string',
                        isset($matches[3]) && $matches[3] !== '' => 'json-number',
                        isset($matches[4]) && $matches[4] !== '' => 'json-literal',
                        default => 'json-punctuation',
                    };

                    return '<span class="'.$class.'">'.htmlspecialchars($token, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'</span>';
                },
                $json
            ) ?? htmlspecialchars($json, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

            $badgeClass = match (true) {
                $status >= 500 => 'badge-500',
                $status === 404 => 'badge-404',
                $status === 401 || $status === 403 => 'badge-auth',
                $status === 429 => 'badge-rate',
                $status === 422 => 'badge-val',
                default => 'badge-400',
            };

            $cardsHtml .= <<<HTML
            <article id="{$code}" class="error-card" data-code="{$code}" data-status="{$status}" data-category="{$category}" data-search="{$code} {$status} {$title} {$desc} {$category}">
                <div class="card-top">
                    <div class="card-meta">
                        <span class="badge {$badgeClass}">HTTP {$status}</span>
                        <span class="category-pill">{$category}</span>
                    </div>
                    <div class="card-actions">
                        <button type="button" class="action-btn" aria-label="{$copyLinkText}" data-tooltip="{$copyLinkText}" onclick="copyToClipboard('{$anchorUrl}', this)">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>
                        </button>
                        <button type="button" class="action-btn" aria-label="{$copyCodeText}" data-tooltip="{$copyCodeText}" onclick="copyToClipboard('{$code}', this)">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                        </button>
                    </div>
                </div>

                <div class="card-identity">
                    <h2 class="error-code"><a href="#{$code}">#</a>{$code}</h2>
                    <h3 class="error-title">{$title}</h3>
                </div>

                <p class="error-desc">{$desc}</p>

                <details class="payload-details">
                    <summary class="payload-summary">
                        <span>{$examplePayloadText}</span>
                        <button type="button" class="copy-payload-btn" aria-label="{$copyPayloadText}" onclick="copyPayload(event, this)">{$copyPayloadText}</button>
                    </summary>
                    <pre class="payload-code"><code>{$jsonExample}</code></pre>
                </details>
            </article>
HTML;
        }

        $enActive = $locale === 'en' ? 'active' : '';
        $frActive = $locale === 'fr' ? 'active' : '';

        return <<<HTML
<!DOCTYPE html>
<html lang="{$locale}" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$pageTitle}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <style>

        :root {
            color-scheme: dark;
            --bg: #0f0f0f;
            --surface: #1a1a1a;
            --surface-raised: #272727;
            --surface-code: #141414;
            --line: #2d2d2d;
            --line-strong: #454545;
            --text: #e7e7e7;
            --text-soft: #a4a4a4;
            --text-muted: #797979;
            --accent: #00aeff;
            --accent-strong: #42c3ff;
            --accent-ink: #07131b;
            --danger: #f07878;
            --warning: #e4b65f;
            --violet: #b5a0ef;
            --blue: #83bce8;
            --json-key: #80c8f0;
            --json-string: #a8cf8a;
            --json-number: #c1a4f2;
            --json-literal: #e58d9b;
            --json-punctuation: #858585;
            --radius: 9px;
            --radius-sm: 6px;
            --mono: "IBM Plex Mono", "SFMono-Regular", Consolas, monospace;
            --sans: "DM Sans", Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        [data-theme="light"] {
            color-scheme: light;
            --bg: #ffffff;
            --surface: #f6f6f6;
            --surface-raised: #e7e7e7;
            --surface-code: #f8f8f8;
            --line: #dfdfdf;
            --line-strong: #c7c7c7;
            --text: #1b1b1b;
            --text-soft: #707070;
            --text-muted: #8e8e8e;
            --accent: #0099ff;
            --accent-strong: #007dcc;
            --accent-ink: #ffffff;
            --danger: #bd4545;
            --warning: #986a19;
            --violet: #7553b5;
            --blue: #356b96;
            --json-key: #286fa0;
            --json-string: #397a43;
            --json-number: #7553b5;
            --json-literal: #b7475b;
            --json-punctuation: #858585;
        }

        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            margin: 0;
            min-height: 100vh;
            padding: 0 28px 72px;
            background: var(--bg);
            color: var(--text);
            font-family: var(--sans);
            font-size: 15px;
            line-height: 1.6;
            transition: background-color .2s ease, color .2s ease;
        }
        button, input, select { font: inherit; }
        button, a, select { -webkit-tap-highlight-color: transparent; }
        a { color: inherit; }
        button:focus-visible, a:focus-visible, input:focus-visible, select:focus-visible,
        summary:focus-visible {
            outline: 2px solid var(--accent);
            outline-offset: 3px;
        }
        ::selection { background: var(--accent); color: var(--accent-ink); }

        .container { width: min(1120px, 100%); margin: 0 auto; }

        /* Header */
        .top-bar {
            min-height: 76px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            border-bottom: 1px solid var(--line);
            margin-bottom: 66px;
        }
        .brand {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            color: var(--text);
            text-decoration: none;
            min-width: 0;
        }
        .brand-logo {
            width: 34px;
            height: 34px;
            display: grid;
            place-items: center;
            border: 1px solid var(--accent);
            border-radius: 8px 8px 8px 2px;
            background: var(--accent);
            color: var(--accent-ink);
            font-family: var(--mono);
            font-size: 17px;
            font-weight: 700;
        }
        .brand-text h1 {
            margin: 0;
            font-size: 14px;
            font-weight: 700;
            letter-spacing: -.025em;
            line-height: 1.25;
        }
        .brand-text span {
            display: block;
            margin-top: 3px;
            color: var(--text-muted);
            font: 10px/1.3 var(--mono);
            letter-spacing: .035em;
        }
        .nav-controls { display: flex; align-items: center; gap: 7px; }
        .link-pill {
            min-height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 0 11px;
            border: 1px solid var(--line);
            border-radius: var(--radius-sm);
            background: transparent;
            color: var(--text-soft);
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
            transition: border-color .16s, color .16s, background .16s;
        }
        .link-pill:hover {
            border-color: var(--line-strong);
            background: var(--surface);
            color: var(--text);
        }
        .segmented {
            display: inline-flex;
            gap: 2px;
            padding: 3px;
            border: 1px solid var(--line);
            border-radius: var(--radius-sm);
            background: var(--surface);
        }
        .seg-btn {
            min-width: 31px;
            padding: 5px 7px;
            border-radius: 4px;
            color: var(--text-muted);
            text-align: center;
            text-decoration: none;
            font: 10px/1.2 var(--mono);
            transition: background .16s, color .16s;
        }
        .seg-btn:hover { color: var(--text); }
        .seg-btn.active { background: var(--surface-raised); color: var(--accent); }
        .theme-btn {
            width: 35px;
            height: 35px;
            display: grid;
            place-items: center;
            border: 1px solid var(--line);
            border-radius: var(--radius-sm);
            background: transparent;
            color: var(--text-soft);
            cursor: pointer;
            transition: all .16s;
        }
        .theme-btn:hover { border-color: var(--line-strong); color: var(--accent); }

        /* Intro */
        .hero {
            position: relative;
            max-width: 760px;
            margin: 0 0 38px;
        }
        .badge-rfc {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 18px;
            color: var(--accent);
            font: 10px/1.4 var(--mono);
            letter-spacing: .12em;
            text-transform: uppercase;
        }
        .badge-rfc::before {
            content: "";
            width: 19px;
            height: 1px;
            background: currentColor;
        }
        .hero h2 {
            max-width: 700px;
            margin: 0 0 13px;
            font-size: clamp(36px, 5.2vw, 58px);
            font-weight: 600;
            letter-spacing: -.065em;
            line-height: 1.04;
        }
        .hero p {
            max-width: 610px;
            margin: 0;
            color: var(--text-soft);
            font-size: 15px;
            line-height: 1.75;
        }

        /* Filters */
        .filter-panel {
            margin: 0 0 25px;
            padding: 15px;
            border: 1px solid var(--line);
            border-radius: 10px;
            background: var(--surface);
        }
        .search-row {
            display: grid;
            grid-template-columns: minmax(220px, 1fr) minmax(180px, 220px) minmax(150px, 190px);
            gap: 9px;
        }
        .search-wrapper { position: relative; min-width: 0; }
        .search-icon {
            position: absolute;
            top: 50%;
            left: 13px;
            transform: translateY(-50%);
            color: var(--text-muted);
            pointer-events: none;
        }
        .search-input, .filter-select {
            width: 100%;
            height: 44px;
            border: 1px solid var(--line);
            border-radius: 7px;
            background: var(--bg);
            color: var(--text);
            font-size: 13px;
            outline: none;
            transition: border-color .16s, box-shadow .16s, background .16s;
        }
        .search-input { padding: 0 48px 0 40px; }
        .search-input::placeholder { color: var(--text-muted); }
        .search-input:focus, .filter-select:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px color-mix(in srgb, var(--accent) 13%, transparent);
        }
        .search-kbd {
            position: absolute;
            top: 50%;
            right: 12px;
            transform: translateY(-50%);
            padding: 2px 6px;
            border: 1px solid var(--line);
            border-radius: 4px;
            background: var(--surface);
            color: var(--text-muted);
            font: 10px var(--mono);
            pointer-events: none;
        }

        /* Native select enhancement. Unsupported browsers retain a usable native select. */
        .filter-select {
            padding: 0 38px 0 12px;
            cursor: pointer;
        }
        @supports (appearance: base-select) {
            .filter-select,
            .filter-select::picker(select) {
                appearance: base-select;
            }
            .filter-select {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
            }
            .filter-select::picker-icon {
                color: var(--text-muted);
                transition: rotate .18s ease, color .18s ease;
            }
            .filter-select:open::picker-icon {
                rotate: 180deg;
                color: var(--accent);
            }
            .filter-select::picker(select) {
                top: calc(anchor(bottom) + 7px);
                border: 1px solid var(--line-strong);
                border-radius: 8px;
                padding: 5px;
                background: var(--surface-raised);
                color: var(--text);
                box-shadow: 0 14px 35px rgb(0 0 0 / .28);
                min-width: anchor-size(width);
                max-height: 280px;
                overflow-y: auto;
                scrollbar-width: thin;
                scrollbar-color: var(--line-strong) var(--surface-raised);
            }
            .filter-select option {
                min-height: 36px;
                display: flex;
                align-items: center;
                padding: 7px 10px;
                border-radius: 5px;
                background: transparent;
                color: var(--text-soft);
                font-size: 13px;
                user-select: none;
            }
            .filter-select option:hover {
                background: var(--bg);
                color: var(--text);
            }
            .filter-select option:checked {
                background: color-mix(in srgb, var(--accent) 15%, var(--surface-raised));
                color: var(--accent);
                font-weight: 600;
            }
            [data-theme="light"] .filter-select option:checked {
                background: #e6edda;
                color: #40571c;
            }
            .filter-select::picker(select)::-webkit-scrollbar { width: 6px; }
            .filter-select::picker(select)::-webkit-scrollbar-track {
                background: var(--surface-raised);
                border-radius: 20px;
            }
            .filter-select::picker(select)::-webkit-scrollbar-thumb {
                background: var(--line-strong);
                border: 1px solid var(--surface-raised);
                border-radius: 20px;
            }
            .filter-select::picker(select)::-webkit-scrollbar-thumb:hover {
                background: var(--accent);
            }
        }
        .filter-stats {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-top: 12px;
            padding-top: 11px;
            border-top: 1px solid var(--line);
            color: var(--text-muted);
            font: 10px/1.5 var(--mono);
        }
        #counter { color: var(--accent); }
        .filter-stats span:last-child { text-align: right; }

        /* Catalogue */
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            align-items: start;
        }
        .error-card {
            min-width: 0;
            padding: 17px 18px 15px;
            border: 1px solid var(--line);
            border-radius: 9px;
            background: var(--surface);
            scroll-margin-top: 24px;
            transition: border-color .18s, background .18s, transform .18s;
        }
        .error-card:hover {
            border-color: var(--line-strong);
            background: var(--surface-raised);
        }
        .error-card:target {
            border-color: var(--accent);
            box-shadow: 0 0 0 2px color-mix(in srgb, var(--accent) 16%, transparent);
        }
        .card-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 15px;
        }
        .card-meta { display: flex; align-items: center; flex-wrap: wrap; gap: 6px; }
        .badge {
            display: inline-flex;
            align-items: center;
            min-height: 22px;
            padding: 2px 7px;
            border: 1px solid currentColor;
            border-radius: 4px;
            font: 10px/1.2 var(--mono);
            letter-spacing: .015em;
        }
        .badge-auth { color: var(--warning); background: color-mix(in srgb, var(--warning) 9%, transparent); border-color: color-mix(in srgb, var(--warning) 35%, transparent); }
        .badge-404 { color: var(--warning); background: color-mix(in srgb, var(--warning) 9%, transparent); border-color: color-mix(in srgb, var(--warning) 35%, transparent); }
        .badge-val { color: var(--violet); background: color-mix(in srgb, var(--violet) 9%, transparent); border-color: color-mix(in srgb, var(--violet) 35%, transparent); }
        .badge-rate { color: var(--warning); background: color-mix(in srgb, var(--warning) 9%, transparent); border-color: color-mix(in srgb, var(--warning) 35%, transparent); }
        .badge-500 { color: var(--danger); background: color-mix(in srgb, var(--danger) 9%, transparent); border-color: color-mix(in srgb, var(--danger) 35%, transparent); }
        .badge-400 { color: var(--blue); background: color-mix(in srgb, var(--blue) 9%, transparent); border-color: color-mix(in srgb, var(--blue) 35%, transparent); }
        .category-pill {
            padding: 2px 7px;
            border: 1px solid var(--line);
            border-radius: 4px;
            color: var(--text-muted);
            font: 10px/1.4 var(--mono);
        }

        [data-tooltip] { position: relative; }
        [data-tooltip]::after {
            content: attr(data-tooltip);
            position: absolute;
            z-index: 30;
            left: 50%;
            bottom: calc(100% + 8px);
            transform: translate(-50%, 3px);
            width: max-content;
            max-width: 190px;
            padding: 6px 9px;
            border: 1px solid var(--line-strong);
            border-radius: 5px;
            background: var(--surface-raised);
            color: var(--text);
            box-shadow: 0 7px 20px rgb(0 0 0 / .22);
            font: 11px/1.35 var(--sans);
            white-space: nowrap;
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition: opacity .12s ease, transform .12s ease, visibility .12s;
        }
        [data-tooltip]:hover::after,
        [data-tooltip]:focus-visible::after {
            opacity: 1;
            visibility: visible;
            transform: translate(-50%, 0);
        }
        .theme-btn[data-tooltip]::after {
            left: auto;
            right: 0;
            transform: translateY(3px);
        }
        .theme-btn[data-tooltip]:hover::after,
        .theme-btn[data-tooltip]:focus-visible::after {
            transform: translateY(0);
        }

        .card-actions { display: flex; align-items: center; gap: 4px; }
        .action-btn {
            width: 29px;
            height: 29px;
            display: grid;
            place-items: center;
            padding: 0;
            border: 1px solid transparent;
            border-radius: 5px;
            background: transparent;
            color: var(--text-muted);
            cursor: pointer;
            transition: all .15s;
        }
        .action-btn:hover {
            border-color: var(--line-strong);
            background: var(--bg);
            color: var(--accent);
        }
        .card-identity { margin-bottom: 7px; }
        .error-code {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            margin: 0;
            color: var(--text);
            font: 600 15px/1.4 var(--mono);
            letter-spacing: -.035em;
        }
        .error-code a {
            color: var(--text-muted);
            font-size: 12px;
            text-decoration: none;
        }
        .error-code a:hover { color: var(--accent); }
        .error-title {
            margin: 5px 0 0;
            color: var(--accent);
            font-size: 15px;
            font-weight: 600;
            letter-spacing: -.02em;
            line-height: 1.35;
        }
        .error-desc {
            margin: 0 0 14px;
            color: var(--text-soft);
            font-size: 13px;
            line-height: 1.65;
        }

        /* Example payload */
        .payload-details {
            overflow: hidden;
            border: 1px solid var(--line);
            border-radius: 7px;
            background: var(--surface-code);
        }
        .payload-summary {
            min-height: 38px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 6px 10px;
            background: color-mix(in srgb, var(--surface-raised) 55%, transparent);
            color: var(--text-muted);
            cursor: pointer;
            list-style: none;
            font: 10px/1.4 var(--mono);
            user-select: none;
        }
        .payload-summary::-webkit-details-marker { display: none; }
        .payload-summary::after {
            content: "Show";
            order: 1;
            margin-left: auto;
            color: var(--text-muted);
            font: 14px/1 var(--mono);
        }
        .payload-details[open] .payload-summary::after { content: "Hide"; }
        .payload-summary:hover { color: var(--text); }
        .copy-payload-btn {
            order: 2;
            padding: 4px 7px;
            border: 1px solid var(--line);
            border-radius: 4px;
            background: var(--surface);
            color: var(--text-soft);
            cursor: pointer;
            font: 10px/1.2 var(--sans);
            transition: all .15s;
        }
        .copy-payload-btn:hover { border-color: var(--line-strong); color: var(--accent); }
        .payload-code {
            display: block;
            width: 100%;
            max-width: 100%;
            max-height: 320px;
            overflow: auto;
            margin: 0;
            padding: 14px 16px;
            border-top: 1px solid var(--line);
            background: var(--surface-code);
            color: var(--text-soft);
            direction: ltr;
            text-align: left;
            font: 12px/1.75 var(--mono);
            tab-size: 2;
            scrollbar-width: thin;
            scrollbar-color: var(--line-strong) transparent;
        }
        .payload-code code {
            display: block;
            min-width: max-content;
            font: inherit;
            white-space: pre;
        }
        .payload-code .json-key { color: var(--json-key); }
        .payload-code .json-string { color: var(--json-string); }
        .payload-code .json-number { color: var(--json-number); }
        .payload-code .json-literal { color: var(--json-literal); font-weight: 600; }
        .payload-code .json-punctuation { color: var(--json-punctuation); }
        .payload-code::-webkit-scrollbar { width: 7px; height: 7px; }
        .payload-code::-webkit-scrollbar-track { background: transparent; }
        .payload-code::-webkit-scrollbar-thumb {
            border: 2px solid var(--surface-code);
            border-radius: 10px;
            background: var(--line-strong);
        }
        .payload-code::-webkit-scrollbar-thumb:hover { background: var(--accent); }

        .no-results {
            display: none;
            margin-top: 12px;
            padding: 38px 20px;
            border: 1px dashed var(--line-strong);
            border-radius: 9px;
            background: var(--surface);
            text-align: center;
        }
        .no-results p { margin: 0 0 13px; color: var(--text-soft); font-size: 13px; }
        .no-results .link-pill { cursor: pointer; }

        @media (max-width: 760px) {
            body { padding: 0 18px 48px; }
            .top-bar { margin-bottom: 48px; }
            .search-row { grid-template-columns: 1fr 1fr; }
            .search-wrapper { grid-column: 1 / -1; }
            .cards-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 520px) {
            body { padding-inline: 14px; }
            .top-bar {
                align-items: flex-start;
                flex-direction: column;
                gap: 13px;
                padding: 16px 0;
                margin-bottom: 38px;
            }
            .nav-controls { width: 100%; flex-wrap: wrap; }
            .nav-controls .link-pill { flex: 1; }
            .hero { margin-bottom: 27px; }
            .hero h2 { font-size: clamp(34px, 10vw, 46px); }
            .hero p { font-size: 13px; }
            .filter-panel { padding: 11px; }
            .search-row { grid-template-columns: 1fr; }
            .search-wrapper { grid-column: auto; }
            .filter-stats { align-items: flex-start; flex-direction: column; gap: 5px; }
            .filter-stats span:last-child { text-align: left; }
            .error-card { padding: 15px; }
        }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                scroll-behavior: auto !important;
                transition-duration: .01ms !important;
                animation-duration: .01ms !important;
            }
        }

    </style>
</head>
<body>
    <div class="container">
        <!-- Top bar navigation -->
        <header class="top-bar">
            <a href="https://github.com/judempoyo/laraApiStarter" target="_blank" class="brand">
                <div class="brand-logo">L</div>
                <div class="brand-text">
                    <h1>LaraApiStarter</h1>
                    <span>v2.0 • RFC 7807 Specification</span>
                </div>
            </a>

            <div class="nav-controls">
                <a href="/docs" class="link-pill" target="_blank">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
                    <span>{$apiDocs}</span>
                </a>
                <a href="https://github.com/judempoyo/laraApiStarter" class="link-pill" target="_blank">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 9 18.13V22"></path></svg>
                    <span>{$github}</span>
                </a>

                <!-- Language Switcher -->
                <div class="segmented" aria-label="Change language">
                    <a href="?lang=en" class="seg-btn {$enActive}" onclick="switchLanguage('en', event)">EN</a>
                    <a href="?lang=fr" class="seg-btn {$frActive}" onclick="switchLanguage('fr', event)">FR</a>
                </div>

                <!-- Theme Toggle -->
                <button type="button" class="theme-btn" id="themeToggle" aria-label="Toggle dark/light theme" data-tooltip="Toggle theme" onclick="toggleTheme()">
                    <svg id="themeIcon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>
                </button>
            </div>
        </header>

        <!-- Hero Section -->
        <section class="hero">
            <span class="badge-rfc">{$badgeRfc}</span>
            <h2>{$heading}</h2>
            <p>{$subtitle}</p>
        </section>

        <!-- Filter and Search Panel -->
        <section class="filter-panel">
            <div class="search-row">
                <div class="search-wrapper">
                    <svg class="search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    <input type="text" id="searchInput" class="search-input" placeholder="{$searchPlaceholder}" oninput="filterErrors()">
                    <span class="search-kbd">/</span>
                </div>

                <select id="categoryFilter" class="filter-select" onchange="filterErrors()">
                    <option value="">{$allCategories}</option>
                    {$categoryOptions}
                </select>

                <select id="statusFilter" class="filter-select" onchange="filterErrors()">
                    <option value="">{$allStatuses}</option>
                    {$statusOptions}
                </select>
            </div>

            <div class="filter-stats">
                <span id="counter">{$totalCount} / {$totalCount}</span>
                <span style="font-family: 'Fira Code', monospace; font-size: 0.8rem;">Content-Type: application/problem+json</span>
            </div>
        </section>

        <!-- Error Cards Grid -->
        <main class="cards-grid" id="cardsGrid">
            {$cardsHtml}
        </main>

        <div class="no-results" id="noResults">
            <p>{$noResults}</p>
            <button type="button" class="link-pill" onclick="resetFilters()">Reset filters</button>
        </div>
    </div>

    <script>
        // Theme initialization
        (function() {
            const saved = localStorage.getItem('theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            const theme = saved || (prefersDark ? 'dark' : 'light');
            document.documentElement.setAttribute('data-theme', theme);
            updateThemeIcon(theme);
        })();

        function toggleTheme() {
            const current = document.documentElement.getAttribute('data-theme') || 'dark';
            const target = current === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', target);
            localStorage.setItem('theme', target);
            updateThemeIcon(target);
        }

        function updateThemeIcon(theme) {
            const icon = document.getElementById('themeIcon');
            if (theme === 'dark') {
                icon.innerHTML = '<circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>';
            } else {
                icon.innerHTML = '<path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>';
            }
        }

        function switchLanguage(lang, event) {
            event.preventDefault();
            localStorage.setItem('preferred_locale', lang);
            const url = new URL(window.location.href);
            url.searchParams.set('lang', lang);
            window.location.href = url.toString();
        }

        // Live Filtering
        function filterErrors() {
            const query = (document.getElementById('searchInput').value || '').toLowerCase().trim();
            const category = document.getElementById('categoryFilter').value;
            const status = document.getElementById('statusFilter').value;

            const cards = document.querySelectorAll('.error-card');
            let visible = 0;

            cards.forEach(card => {
                const searchData = card.getAttribute('data-search').toLowerCase();
                const cardCategory = card.getAttribute('data-category');
                const cardStatus = card.getAttribute('data-status');

                const matchesQuery = !query || searchData.includes(query);
                const matchesCategory = !category || cardCategory === category;
                const matchesStatus = !status || cardStatus === status;

                if (matchesQuery && matchesCategory && matchesStatus) {
                    card.style.display = 'block';
                    visible++;
                } else {
                    card.style.display = 'none';
                }
            });

            document.getElementById('counter').innerText = visible + ' / ' + cards.length;
            document.getElementById('noResults').style.display = visible === 0 ? 'block' : 'none';
        }

        function resetFilters() {
            document.getElementById('searchInput').value = '';
            document.getElementById('categoryFilter').value = '';
            document.getElementById('statusFilter').value = '';
            filterErrors();
        }

        // Keyboard Shortcut ('/' to search)
        document.addEventListener('keydown', (e) => {
            if (e.key === '/' && document.activeElement.tagName !== 'INPUT') {
                e.preventDefault();
                document.getElementById('searchInput').focus();
            }
        });

        // Copy helpers
        function copyToClipboard(text, btn) {
            navigator.clipboard.writeText(text).then(() => {
                const originalHtml = btn.innerHTML;
                btn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>';
                setTimeout(() => { btn.innerHTML = originalHtml; }, 1800);
            });
        }

        function copyPayload(event, btn) {
            event.preventDefault();
            event.stopPropagation();
            const details = btn.closest('.payload-details');
            const codeEl = details.querySelector('code');
            const text = codeEl.innerText;
            navigator.clipboard.writeText(text).then(() => {
                const originalText = btn.innerText;
                btn.innerText = '{$copiedText}';
                setTimeout(() => { btn.innerText = originalText; }, 1800);
            });
        }
    </script>
</body>
</html>
HTML;
    }
}
