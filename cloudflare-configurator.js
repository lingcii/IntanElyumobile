const fs = require('fs');
const path = require('path');

const ROOT = __dirname;
const TMP = process.env.TEMP || process.env.TMP || 'C:\\Temp';

function readLog(filePath) {
    try {
        return fs.readFileSync(filePath, 'utf8');
    } catch { return ''; }
}

function extractUrl(logText) {
    // Match Cloudflare, localhost.run (lhr.life), or pinggy.io tunnel URLs
    const patterns = [
        /https:\/\/[a-zA-Z0-9-]+\.trycloudflare\.com/,
        /https:\/\/[a-zA-Z0-9-]+\.lhr\.life/,
        /https:\/\/[a-zA-Z0-9-]+\.a\.pinggy\.io/,
        /https:\/\/[a-zA-Z0-9-]+\.pinggy\.io/,
    ];
    for (const re of patterns) {
        const m = logText.match(re);
        if (m) return m[0];
    }
    return null;
}

function replaceInFile(filePath, search, replacement) {
    if (!fs.existsSync(filePath)) return false;
    let content = fs.readFileSync(filePath, 'utf8');
    if (!content.includes(search)) return false;
    content = content.replace(search, replacement);
    fs.writeFileSync(filePath, content, 'utf8');
    return true;
}

async function run() {
    const isRailway = process.argv.includes('railway') || process.argv.includes('--railway');
    
    if (isRailway) {
        console.log('=> Targeting Deployed Railway Backend: https://intanelyu-production.up.railway.app');
    } else {
        console.log('Waiting for Cloudflare tunnels to establish...');
    }

    let frontendUrl = null;
    let backendUrl = isRailway ? 'https://intanelyu-production.up.railway.app' : null;
    const maxWait = 30000;
    const start = Date.now();

    while (Date.now() - start < maxWait) {
        const frontendLog = readLog(path.join(TMP, 'cf-frontend.log'));
        if (!frontendUrl) frontendUrl = extractUrl(frontendLog);

        if (!isRailway) {
            const backendLog = readLog(path.join(TMP, 'cf-backend.log'));
            if (!backendUrl) backendUrl = extractUrl(backendLog);
        }

        if (frontendUrl && backendUrl) break;
        await new Promise(r => setTimeout(r, 1000));
    }

    if (!frontendUrl || !backendUrl) {
        console.error('Could not find Cloudflare tunnel URLs. Check logs:');
        console.error('  ' + path.join(TMP, 'cf-frontend.log'));
        if (!isRailway) {
            console.error('  ' + path.join(TMP, 'cf-backend.log'));
        }
        process.exit(1);
    }

    console.log('=> Found Frontend: ' + frontendUrl);
    console.log('=> Using Backend:  ' + backendUrl);

    // 1. Update capacitor.config.json
    const capacitorPath = path.join(ROOT, 'Frontend', 'Mobile', 'capacitor.config.json');
    const androidAssetPath = path.join(ROOT, 'Frontend', 'Mobile', 'android', 'app', 'src', 'main', 'assets', 'capacitor.config.json');

    if (fs.existsSync(capacitorPath)) {
        let capConfig = JSON.parse(fs.readFileSync(capacitorPath, 'utf8'));
        capConfig.server = capConfig.server || {};
        capConfig.server.url = frontendUrl;
        capConfig.server.cleartext = true;
        fs.writeFileSync(capacitorPath, JSON.stringify(capConfig, null, 2));
        if (fs.existsSync(androidAssetPath)) {
            fs.writeFileSync(androidAssetPath, JSON.stringify(capConfig, null, 2));
        }
        console.log('Updated capacitor.config.json and Android Assets!');
    }

    // 2. Update cors.php
    const corsPath = path.join(ROOT, 'backend', 'config', 'cors.php');
    if (fs.existsSync(corsPath)) {
        let corsContent = fs.readFileSync(corsPath, 'utf8');
        const oldFrontend = corsContent.match(/\/\/ Auto-Injected (?:Ngrok|Cloudflare) URL\n\s*'[^']+'/);
        if (oldFrontend) {
            corsContent = corsContent.replace(
                oldFrontend[0],
                "// Auto-Injected Cloudflare URL\n        '" + frontendUrl + "'"
            );
        } else {
            corsContent = corsContent.replace(
                "// Cloudflare tunnel (remote access / staging)",
                "// Auto-Injected Cloudflare URL\n        '" + frontendUrl + "',\n\n        // Cloudflare tunnel (remote access / staging)"
            );
        }

        const oldBackend = corsContent.match(/\/\/ Auto-Injected Backend URL\n\s*'[^']+'/);
        if (!oldBackend) {
            corsContent = corsContent.replace(
                "'capacitor://localhost',",
                "'capacitor://localhost',\n\n        // Auto-Injected Backend URL\n        '" + backendUrl + "',"
            );
        }

        fs.writeFileSync(corsPath, corsContent, 'utf8');
        console.log('Updated cors.php');
    }

    // 3. Update frontend source files
    const srcPath = path.join(ROOT, 'Frontend', 'Mobile', 'src');
    function walkSync(dir) {
        const files = [];
        fs.readdirSync(dir).forEach(file => {
            const full = path.join(dir, file);
            if (fs.statSync(full).isDirectory()) {
                files.push(...walkSync(full));
            } else {
                files.push(full);
            }
        });
        return files;
    }

    if (fs.existsSync(srcPath)) {
        const files = walkSync(srcPath);
        let updateCount = 0;

        files.forEach(file => {
            if (!file.endsWith('.php') && !file.endsWith('.js')) return;
            let content = fs.readFileSync(file, 'utf8');
            let original = content;

            // Replace var/let/const backendUrl patterns
            const patterns = [
                [/var backendUrl\s*=\s*(?:window\.backendUrl\s*\|\|\s*)?['"][^'"]*['"]/g,
                 `var backendUrl = window.backendUrl || '${backendUrl}'`],
                [/let backendUrl\s*=\s*(?:window\.backendUrl\s*\|\|\s*)?['"][^'"]*['"]/g,
                 `let backendUrl = window.backendUrl || '${backendUrl}'`],
                [/const backendUrl\s*=\s*(?:window\.backendUrl\s*\|\|\s*)?['"][^'"]*['"]/g,
                 `const backendUrl = window.backendUrl || '${backendUrl}'`],
                [/window\.backendUrl\s*=\s*['"][^'"]*['"]/g,
                 `window.backendUrl = '${backendUrl}'`],
            ];

            patterns.forEach(([regex, replacement]) => {
                content = content.replace(regex, replacement);
            });

            if (file.endsWith('router.php')) {
                content = content.replace(/\$backendUrl\s*=\s*['"][^'"]*['"];/, `$backendUrl = '${backendUrl}';`);
            }

            if (content !== original) {
                fs.writeFileSync(file, content, 'utf8');
                updateCount++;
            }
        });
        console.log('Updated Backend URLs in ' + updateCount + ' frontend files.');
    }

    console.log('=========================================');
    console.log('SUCCESS! Cloudflare Tunnels configured.');
    console.log('Frontend Mobile App Access URL: ' + frontendUrl);
    console.log('Backend API Access URL: ' + backendUrl);
    console.log('=========================================');
}

setTimeout(run, 2000);
