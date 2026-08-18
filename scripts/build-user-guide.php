<?php

declare(strict_types=1);

use League\CommonMark\GithubFlavoredMarkdownConverter;

require dirname(__DIR__).'/vendor/autoload.php';

$root = dirname(__DIR__);
$markdownPath = $root.'/docs/buku-panduan-penggunaan.md';
$htmlPath = $root.'/docs/buku-panduan-penggunaan.html';
$markdown = file_get_contents($markdownPath);

if ($markdown === false) {
    throw new RuntimeException('Buku panduan Markdown tidak dapat dibaca.');
}

$converter = new GithubFlavoredMarkdownConverter([
    'html_input' => 'strip',
    'allow_unsafe_links' => false,
]);

$content = $converter->convert($markdown)->getContent();
$html = <<<'HTML'
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Buku Panduan Penggunaan - SD Negeri 232 Maluku Tengah</title>
    <style>
        @page { size: A4; margin: 16mm 15mm 18mm; }
        :root { color-scheme: light; --ink: #172033; --muted: #52647f; --line: #d9e2ec; --sky: #0284c7; --soft: #f0f9ff; }
        * { box-sizing: border-box; }
        body { margin: 0 auto; max-width: 980px; color: var(--ink); background: white; font: 15px/1.65 Arial, sans-serif; }
        h1, h2, h3 { color: #0f172a; line-height: 1.25; }
        h1 { margin: 2rem 0 1rem; padding-bottom: .55rem; border-bottom: 3px solid #38bdf8; font-size: 2rem; }
        h2 { margin-top: 1.8rem; font-size: 1.45rem; }
        h3 { margin-top: 1.4rem; font-size: 1.15rem; }
        p, li { color: #334155; }
        a { color: #0369a1; }
        img { display: block; max-width: 100%; height: auto; margin: 1rem auto 1.5rem; border: 1px solid var(--line); border-radius: 10px; box-shadow: 0 5px 18px rgba(15, 23, 42, .08); }
        table { width: 100%; margin: 1rem 0 1.5rem; border-collapse: collapse; font-size: .92rem; }
        th, td { padding: .55rem .65rem; border: 1px solid var(--line); text-align: left; vertical-align: top; }
        th { background: #e0f2fe; color: #0c4a6e; }
        blockquote { margin: 1rem 0; padding: .7rem 1rem; border-left: 4px solid #0ea5e9; background: var(--soft); }
        blockquote p { margin: 0; }
        code { padding: .1rem .3rem; border-radius: 4px; background: #e2e8f0; }
        hr { margin: 2rem 0; border: 0; border-top: 1px solid var(--line); }
        body > h1:first-child { margin-top: 10vh; padding: 2rem; border: 0; border-radius: 18px; color: white; background: linear-gradient(135deg, #0369a1, #0ea5e9); font-size: 2.4rem; text-align: center; }
        body > h1:first-child + h2 { text-align: center; }
        @media print {
            body { max-width: none; font-size: 10.5pt; }
            h1 { break-before: page; }
            body > h1:first-child { break-before: auto; }
            h2, h3 { break-after: avoid; }
            img, table, blockquote { break-inside: avoid; }
            img { max-height: 225mm; object-fit: contain; box-shadow: none; }
            a { color: inherit; text-decoration: none; }
        }
    </style>
</head>
<body>
HTML;

$html .= $content."\n</body>\n</html>\n";

if (file_put_contents($htmlPath, $html) === false) {
    throw new RuntimeException('Buku panduan HTML tidak dapat ditulis.');
}

echo "Buku panduan HTML dibuat: {$htmlPath}\n";
