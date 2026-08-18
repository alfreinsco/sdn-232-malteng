<?php

declare(strict_types=1);

use Carbon\Carbon;
use League\CommonMark\GithubFlavoredMarkdownConverter;

require dirname(__DIR__).'/vendor/autoload.php';

$root = dirname(__DIR__);
$markdownPath = $root.'/docs/black-box-testing.md';
$htmlPath = $root.'/docs/black-box-testing.html';
$markdown = file_get_contents($markdownPath);

if ($markdown === false) {
    throw new RuntimeException('Dokumen black box testing tidak dapat dibaca.');
}

$converter = new GithubFlavoredMarkdownConverter([
    'html_input' => 'strip',
    'allow_unsafe_links' => false,
]);

$content = $converter->convert($markdown)->getContent();
$generatedAt = Carbon::now('Asia/Jayapura')->locale('id')->translatedFormat('d F Y H:i').' WIT';
$logoPath = '../public/logo-malteng.png';

$html = <<<HTML
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Black Box Testing - SD Negeri 232 Maluku Tengah</title>
    <style>
        @page { size: A4 landscape; margin: 8mm 9mm 9mm; }
        * { box-sizing: border-box; }
        body { margin: 0; color: #172033; background: white; font: 9px/1.25 Arial, sans-serif; }
        .document-header { display: grid; grid-template-columns: 48px 1fr 180px; gap: 11px; align-items: center; margin-bottom: 8px; padding-bottom: 7px; border-bottom: 3px solid #0284c7; }
        .document-header img { display: block; width: 38px; height: 46px; object-fit: contain; }
        .school { margin: 0; color: #0f172a; font-size: 14px; font-weight: 700; text-transform: uppercase; }
        .system { margin: 2px 0 0; color: #475569; font-size: 9px; }
        .meta { color: #64748b; text-align: right; }
        h1 { margin: 0 0 7px; color: #0f172a; font-size: 17px; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        thead { display: table-header-group; }
        tr { break-inside: avoid; }
        th, td { padding: 3px 4px; border: 1px solid #94a3b8; text-align: left; vertical-align: top; overflow-wrap: anywhere; }
        th { color: #0c4a6e; background: #e0f2fe; font-size: 8px; text-transform: uppercase; }
        th:nth-child(1), td:nth-child(1) { width: 4%; text-align: center; }
        th:nth-child(2), td:nth-child(2) { width: 10%; }
        th:nth-child(3), td:nth-child(3) { width: 20%; }
        th:nth-child(4), td:nth-child(4) { width: 19%; }
        th:nth-child(5), td:nth-child(5) { width: 29%; }
        th:nth-child(6), td:nth-child(6) { width: 18%; font-weight: 700; }
        tbody tr:nth-child(even) { background: #f8fafc; }
        code { color: #075985; font: 8px Consolas, monospace; }
        .footer-note { margin-top: 6px; color: #64748b; font-size: 8px; }
    </style>
</head>
<body>
    <header class="document-header">
        <img src="{$logoPath}" alt="Logo Kabupaten Maluku Tengah">
        <div>
            <p class="school">SD Negeri 232 Maluku Tengah</p>
            <p class="system">Sistem Informasi Jadwal Pelajaran dan Nilai Siswa</p>
        </div>
        <div class="meta">Dokumen Pengujian<br>Dibuat: {$generatedAt}</div>
    </header>
    {$content}
    <p class="footer-note">Status "Lulus otomatis" telah diverifikasi melalui automated test. Status "Siap uji manual" disediakan sebagai panduan pengujian langsung oleh pengguna.</p>
</body>
</html>
HTML;

if (file_put_contents($htmlPath, $html) === false) {
    throw new RuntimeException('Dokumen black box testing HTML tidak dapat ditulis.');
}

echo "Black box testing HTML dibuat: {$htmlPath}\n";
