import fs from 'node:fs/promises';

const baseUrl = 'http://127.0.0.1:8010';
const outputDir = new URL('../docs/screenshots/', import.meta.url);
const delay = (ms) => new Promise((resolve) => setTimeout(resolve, ms));
const mode = process.argv[2] ?? 'all';

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

    if (!message.id || !pending.has(message.id)) {
        return;
    }

    const { resolve, reject } = pending.get(message.id);
    pending.delete(message.id);

    if (message.error) {
        reject(new Error(message.error.message));
    } else {
        resolve(message.result);
    }
});

await new Promise((resolve, reject) => {
    socket.addEventListener('open', resolve, { once: true });
    socket.addEventListener('error', reject, { once: true });
});

function command(method, params = {}) {
    const id = ++messageId;

    return new Promise((resolve, reject) => {
        pending.set(id, { resolve, reject });
        socket.send(JSON.stringify({ id, method, params }));
    });
}

async function evaluate(expression) {
    return command('Runtime.evaluate', {
        expression,
        awaitPromise: true,
        returnByValue: true,
    });
}

async function navigate(path) {
    await command('Page.navigate', { url: `${baseUrl}${path}` });
    await delay(900);
    await evaluate(`(() => {
        const style = document.createElement('style');
        style.textContent = '*,*::before,*::after{animation:none!important;transition:none!important}@media (min-width:1024px){body>div.min-h-dvh{height:auto!important;min-height:100vh!important;overflow:visible!important}body>div.min-h-dvh>div:last-child{height:auto!important;min-height:100vh!important;overflow:visible!important}main#main-content{height:auto!important;min-height:calc(100vh - 4.5rem)!important;overflow:visible!important}.table-scroll,.report-table-scroll{max-height:none!important}}';
        document.head.appendChild(style);
        window.scrollTo(0, 0);
    })()`);
    await delay(250);
}

async function setViewport(width, height, mobile = false) {
    await command('Emulation.setDeviceMetricsOverride', {
        width,
        height,
        deviceScaleFactor: 1,
        mobile,
        screenWidth: width,
        screenHeight: height,
    });
}

async function screenshot(name, fullPage = true) {
    if (!fullPage) {
        const metrics = await command('Page.getLayoutMetrics');
        const width = Math.max(1, Math.ceil(metrics.cssVisualViewport?.clientWidth ?? 390));
        const height = Math.max(1, Math.ceil(metrics.cssVisualViewport?.clientHeight ?? 844));
        const result = await command('Page.captureScreenshot', {
            format: 'png',
            fromSurface: true,
            captureBeyondViewport: false,
            clip: { x: 0, y: 0, width, height, scale: 1 },
        });

        await fs.writeFile(new URL(`${name}.png`, outputDir), Buffer.from(result.data, 'base64'));
        process.stdout.write(`${name}.png (${width}x${height})\n`);
        return;
    }

    const { contentSize } = await command('Page.getLayoutMetrics');
    const width = Math.max(1, Math.ceil(contentSize.width));
    const height = Math.max(1, Math.ceil(contentSize.height));
    const result = await command('Page.captureScreenshot', {
        format: 'png',
        fromSurface: true,
        captureBeyondViewport: true,
        clip: { x: 0, y: 0, width, height, scale: 1 },
    });

    await fs.writeFile(new URL(`${name}.png`, outputDir), Buffer.from(result.data, 'base64'));
    process.stdout.write(`${name}.png (${width}x${height})\n`);
}

async function login(username) {
    await command('Network.clearBrowserCookies');
    await navigate('/login');
    await evaluate(`(() => {
        const login = document.querySelector('[name="login"]');
        const password = document.querySelector('[name="password"]');
        login.value = ${JSON.stringify(username)};
        password.value = 'Sekolah232!';
        login.dispatchEvent(new Event('input', { bubbles: true }));
        password.dispatchEvent(new Event('input', { bubbles: true }));
        document.querySelector('form').requestSubmit();
    })()`);
    await delay(1000);

    const location = await evaluate('location.pathname');
    if (location.result.value !== '/dashboard') {
        throw new Error(`Login ${username} gagal, halaman saat ini: ${location.result.value}`);
    }
}

const rolePages = {
    admin: {
        username: 'admin',
        pages: [
            ['dashboard', '/dashboard'],
            ['tahun-ajaran', '/tahun-ajaran'],
            ['semester', '/semester'],
            ['guru', '/guru'],
            ['siswa', '/siswa'],
            ['kelas', '/kelas'],
            ['anggota-kelas', '/kelas/1/siswa'],
            ['mata-pelajaran', '/mata-pelajaran'],
            ['jam-pelajaran', '/jam-pelajaran'],
            ['pengajaran', '/pengajaran'],
            ['penempatan-siswa', '/penempatan-siswa'],
            ['jadwal-pelajaran', '/jadwal-pelajaran'],
            ['nilai-siswa', '/nilai-siswa'],
            ['laporan-jadwal', '/laporan/jadwal'],
            ['laporan-nilai', '/laporan/nilai'],
            ['pengguna', '/pengguna'],
            ['pengaturan-sekolah', '/pengaturan-sekolah'],
            ['profil', '/profil'],
        ],
    },
    guru: {
        username: 'guru1',
        pages: [
            ['dashboard', '/dashboard'],
            ['jadwal-mengajar', '/jadwal-pelajaran'],
            ['input-nilai', '/nilai-siswa'],
            ['laporan-nilai', '/laporan/nilai'],
            ['profil', '/profil'],
        ],
    },
    siswa: {
        username: 'siswa',
        pages: [
            ['dashboard', '/dashboard'],
            ['jadwal-pelajaran', '/jadwal-pelajaran'],
            ['nilai-saya', '/nilai-siswa'],
            ['laporan-nilai', '/laporan/nilai'],
            ['profil', '/profil'],
        ],
    },
    kepala: {
        username: 'kepala',
        pages: [
            ['dashboard', '/dashboard'],
            ['monitoring-jadwal', '/jadwal-pelajaran'],
            ['monitoring-nilai', '/nilai-siswa'],
            ['laporan-jadwal', '/laporan/jadwal'],
            ['laporan-nilai', '/laporan/nilai'],
            ['profil', '/profil'],
        ],
    },
};

await fs.mkdir(outputDir, { recursive: true });
await command('Page.enable');
await command('Runtime.enable');
await command('Network.enable');
if (['all', 'desktop'].includes(mode)) {
    await setViewport(1440, 1000);
    await command('Network.clearBrowserCookies');
    await navigate('/login');
    await screenshot('00-login');

    for (const [role, config] of Object.entries(rolePages)) {
        await login(config.username);

        for (const [name, path] of config.pages) {
            await navigate(path);
            await screenshot(`${role}-${name}`);
        }
    }
}

if (['all', 'extras'].includes(mode)) {
    await setViewport(1440, 1000);
    await login('guru1');
    await navigate('/nilai-siswa');
    await evaluate(`(() => {
        const trigger = document.querySelector('.filters button[aria-haspopup="listbox"]');
        trigger?.click();
    })()`);
    await delay(150);
    await evaluate(`(() => {
        const options = document.querySelectorAll('.filters [role="listbox"] button');
        options[1]?.click();
    })()`);
    await delay(1200);
    await screenshot('guru-input-nilai-terisi');

    await login('admin');
    await navigate('/guru');
    await evaluate(`(() => {
        const button = [...document.querySelectorAll('button')].find((item) => item.textContent.trim().startsWith('Tambah Guru'));
        button?.click();
    })()`);
    await delay(500);
    await screenshot('admin-form-tambah-guru');
}

if (['all', 'mobile'].includes(mode)) {
    for (const role of ['guru', 'siswa']) {
        const config = rolePages[role];
        await setViewport(390, 844, true);
        await login(config.username);

        for (const [name, path] of config.pages.slice(0, 3)) {
            await navigate(path);
            await screenshot(`mobile-${role}-${name}`, false);
        }
    }

    await setViewport(390, 844, true);
    await login('guru1');
    await navigate('/nilai-siswa');
    await evaluate(`(() => {
        const trigger = document.querySelector('.filters button[aria-haspopup="listbox"]');
        trigger?.click();
    })()`);
    await delay(150);
    await evaluate(`(() => {
        const options = document.querySelectorAll('.filters [role="listbox"] button');
        options[1]?.click();
    })()`);
    await delay(1200);
    await screenshot('mobile-guru-input-nilai-terisi', false);

    await setViewport(390, 844, true);
    await login('admin');
    await navigate('/siswa');
    await screenshot('mobile-admin-siswa', false);
    await evaluate(`(() => {
        const table = document.querySelector('.table-scroll');
        if (table) table.scrollLeft = table.scrollWidth;
    })()`);
    await delay(200);
    await screenshot('mobile-admin-siswa-aksi', false);

    await setViewport(390, 844, true);
    await login('admin');
    await navigate('/dashboard');
    await evaluate(`(() => {
        const button = document.querySelector('button[aria-label="Buka menu"]');
        button?.click();
    })()`);
    await delay(300);
    await screenshot('mobile-admin-menu', false);
}

socket.close();
