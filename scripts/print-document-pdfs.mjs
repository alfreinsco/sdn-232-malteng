import fs from 'node:fs/promises';
import { pathToFileURL } from 'node:url';
import { resolve } from 'node:path';

const root = resolve(import.meta.dirname, '..');
const documents = [
    ['docs/buku-panduan-penggunaan.html', 'docs/buku-panduan-penggunaan.pdf'],
    ['docs/black-box-testing.html', 'docs/black-box-testing.pdf'],
    ['docs/laporan-pengerjaan-akhir.html', 'docs/laporan-pengerjaan-akhir.pdf'],
];
const delay = (ms) => new Promise((resolveDelay) => setTimeout(resolveDelay, ms));
const targets = await fetch('http://127.0.0.1:9223/json/list').then((response) => response.json());
const target = targets.find((item) => item.type === 'page');

if (!target) {
    throw new Error('Target halaman Chrome tidak ditemukan.');
}

const socket = new WebSocket(target.webSocketDebuggerUrl);
const pending = new Map();
let messageId = 0;

socket.addEventListener('message', (event) => {
    const message = JSON.parse(event.data);
    const handler = pending.get(message.id);
    if (!handler) return;
    pending.delete(message.id);
    message.error ? handler.reject(new Error(message.error.message)) : handler.resolve(message.result);
});

await new Promise((resolveSocket, reject) => {
    socket.addEventListener('open', resolveSocket, { once: true });
    socket.addEventListener('error', reject, { once: true });
});

function command(method, params = {}) {
    const id = ++messageId;
    return new Promise((resolveCommand, reject) => {
        pending.set(id, { resolve: resolveCommand, reject });
        socket.send(JSON.stringify({ id, method, params }));
    });
}

await command('Page.enable');

for (const [htmlPath, pdfPath] of documents) {
    const source = resolve(root, htmlPath);
    const output = resolve(root, pdfPath);
    await command('Page.navigate', { url: pathToFileURL(source).href });
    await delay(800);
    const result = await command('Page.printToPDF', {
        printBackground: true,
        preferCSSPageSize: true,
        displayHeaderFooter: false,
        transferMode: 'ReturnAsBase64',
    });
    await fs.writeFile(output, Buffer.from(result.data, 'base64'));
    process.stdout.write(`${pdfPath}\n`);
}

socket.close();
