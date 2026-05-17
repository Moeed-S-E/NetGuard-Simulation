//

function updateClock() {
    const now = new Date();
    const hms = now.toTimeString().split(' ')[0];
    const utc = now.toUTCString().split(' ')[4] + ' UTC';
    const el1 = document.getElementById('clock');
    const el2 = document.getElementById('statusbar-clock');
    if (el1) el1.textContent = hms;
    if (el2) el2.textContent = utc;
}
updateClock();
setInterval(updateClock, 1000);

// ─── Chart.js setup ───────────────────────────────────────────────────────────
const MAX_POINTS = 15;

const ctx = document.getElementById('metricsChart').getContext('2d');

// Retro amber grid lines matching CSS vars
Chart.defaults.color = '#4a3a28';
Chart.defaults.borderColor = 'rgba(245,158,11,0.08)';

const metricsChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: [],
        datasets: [
            {
                label: 'CPU %',
                data: [],
                borderColor: '#f59e0b',
                backgroundColor: 'rgba(245,158,11,0.06)',
                borderWidth: 1.5,
                pointRadius: 2,
                pointBackgroundColor: '#f59e0b',
                tension: 0.35,
                fill: true,
            },
            {
                label: 'Memory MB',
                data: [],
                borderColor: '#06b6d4',
                backgroundColor: 'rgba(6,182,212,0.05)',
                borderWidth: 1.5,
                pointRadius: 2,
                pointBackgroundColor: '#06b6d4',
                tension: 0.35,
                fill: true,
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        animation: { duration: 400 },
        interaction: { mode: 'index', intersect: false },
        plugins: {
            legend: { display: false },  // custom legend already in HTML
            tooltip: {
                backgroundColor: '#101418',
                borderColor: 'rgba(245,158,11,0.3)',
                borderWidth: 1,
                titleColor: '#f59e0b',
                bodyColor: '#9a8060',
                titleFont: { family: "'Orbitron', monospace", size: 10 },
                bodyFont:  { family: "'Share Tech Mono', monospace", size: 11 },
                padding: 10,
            }
        },
        scales: {
            y: {
                min: 0,
                grid: {
                    color: 'rgba(245,158,11,0.07)',
                    drawBorder: false,
                },
                ticks: {
                    font: { family: "'Share Tech Mono', monospace", size: 9 },
                    color: '#4a3a28',
                    maxTicksLimit: 6,
                }
            },
            x: {
                grid: { color: 'rgba(245,158,11,0.05)', drawBorder: false },
                ticks: {
                    font: { family: "'Share Tech Mono', monospace", size: 9 },
                    color: '#4a3a28',
                    maxRotation: 0,
                    maxTicksLimit: 8,
                }
            }
        }
    }
});

// ─── Chart helpers ────────────────────────────────────────────────────────────

/**
 * Push a new data point and trim to MAX_POINTS.
 * @param {string} label  e.g. "14:02:31"
 * @param {number} cpu    cpu_usage integer
 * @param {number} memory memory_usage integer
 */
function pushChartPoint(label, cpu, memory) {
    metricsChart.data.labels.push(label);
    metricsChart.data.datasets[0].data.push(cpu);
    metricsChart.data.datasets[1].data.push(memory);

    if (metricsChart.data.labels.length > MAX_POINTS) {
        metricsChart.data.labels.shift();
        metricsChart.data.datasets[0].data.shift();
        metricsChart.data.datasets[1].data.shift();
    }
    metricsChart.update('none'); // skip animation on poll updates
}

// ─── Node status cards ────────────────────────────────────────────────────────

const NODE_ID_MAP = {
    // Keys must match node names coming from the backend (case-insensitive match below)
    'auth service':  'auth',
    'api gateway':   'gateway',
    'db service':    'db',
};

function getNodeDomId(nodeName) {
    return NODE_ID_MAP[nodeName.toLowerCase()] ?? nodeName.toLowerCase().replace(/\s+/g, '-');
}

function applyNodeStatus(domId, status, latestMetric, activeAlerts) {
    const card        = document.getElementById(`node-${domId}`);
    const statusText  = document.getElementById(`status-${domId}`);
    if (!card || !statusText) return;

    // Update the big status text
    statusText.textContent = status;

    // Reset all state classes then apply appropriate one
    card.classList.remove('animate-flash-red', 'border-amber-hot');

    if (status === 'Critical') {
        card.classList.add('animate-flash-red');
        statusText.style.cssText = 'font-family:"Orbitron",monospace;font-size:20px;font-weight:700;color:#ef4444;text-shadow:0 0 12px rgba(239,68,68,0.8);line-height:1;';
        setBadge(card, 'CRITICAL', 'badge-offline');
    } else if (status === 'Warning') {
        card.style.borderColor = 'rgba(245,158,11,0.6)';
        card.style.boxShadow   = '0 0 20px rgba(245,158,11,0.25)';
        statusText.style.cssText = 'font-family:"Orbitron",monospace;font-size:20px;font-weight:700;color:#f59e0b;text-shadow:0 0 12px rgba(245,158,11,0.6);line-height:1;';
        setBadge(card, 'WARNING', 'badge-loading');
    } else {
        card.style.borderColor = '';
        card.style.boxShadow   = '';
        statusText.style.cssText = 'font-family:"Orbitron",monospace;font-size:20px;font-weight:700;color:#22c55e;text-shadow:0 0 12px rgba(34,197,94,0.5);line-height:1;';
        setBadge(card, 'ONLINE', 'badge-online');
    }

    // Update REQ/S in node-meta if latest_metric present
    if (latestMetric) {
        const reqEl = card.querySelector('.req-val');
        if (reqEl) reqEl.textContent = latestMetric.request_rate ?? '—';
    }

    // Update alert count badge if element exists
    const alertBadge = card.querySelector('.alert-count');
    if (alertBadge) {
        alertBadge.textContent = activeAlerts > 0 ? `${activeAlerts} ALERT${activeAlerts > 1 ? 'S' : ''}` : '';
        alertBadge.style.display = activeAlerts > 0 ? 'inline-block' : 'none';
    }
}

function setBadge(card, text, cls) {
    const badge = card.querySelector('.status-badge');
    if (!badge) return;
    badge.className = `status-badge ${cls}`;
    // Preserve inner dot, update text node
    const dot = badge.querySelector('.badge-dot');
    badge.innerHTML = '';
    if (dot) badge.appendChild(dot);
    badge.appendChild(document.createTextNode(' ' + text));
}

// ─── Incident feed ────────────────────────────────────────────────────────────

function alertTagClass(type) {
    const t = (type || '').toLowerCase();
    if (t.includes('ddos') || t.includes('attack') || t.includes('critical'))  return 'tag-critical';
    if (t.includes('anomaly') || t.includes('spike') || t.includes('leak'))    return 'tag-warning';
    return 'tag-info';
}

function renderAlerts(alerts) {
    const feed = document.getElementById('incident-feed');
    if (!feed) return;

    if (!alerts || alerts.length === 0) {
        feed.innerHTML = '<li class="incident-empty">[ NO INCIDENTS LOGGED ] &nbsp; SYSTEM NOMINAL</li>';
        // Also update the stat strip "Active Threats" counter
        updateThreatCount(0);
        return;
    }

    feed.innerHTML = '';
    alerts.forEach(alert => {
        const li = document.createElement('li');
        li.className = 'incident-item';

        const timeStr = alert.created_at
            ? new Date(alert.created_at).toTimeString().split(' ')[0]
            : '—';

        const nodeLabel = alert.node?.name ?? `Node #${alert.node_id}`;

        li.innerHTML = `
            <span class="incident-tag ${alertTagClass(alert.type)}">${escHtml(alert.type)}</span>
            <div style="flex:1; min-width:0;">
                <div style="color:#c8a87a; font-size:11px; letter-spacing:0.05em; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                    ${escHtml(alert.description)}
                </div>
                <div style="margin-top:3px; font-size:9px; color:#4a3a28; letter-spacing:0.12em;">
                    NODE: ${escHtml(nodeLabel)} &nbsp;|&nbsp; ${timeStr}
                    ${!alert.is_resolved ? `&nbsp;|&nbsp;<button onclick="resolveAlert(${alert.id}, this)" style="color:#6b5b3e;border:1px solid rgba(245,158,11,0.2);background:transparent;font-family:'Share Tech Mono',monospace;font-size:9px;letter-spacing:0.1em;padding:1px 6px;cursor:pointer;border-radius:1px;" onmouseover="this.style.color='#f59e0b'" onmouseout="this.style.color='#6b5b3e'">RESOLVE</button>` : '<span style="color:#22c55e;">✓ RESOLVED</span>'}
                </div>
            </div>`;

        feed.appendChild(li);
    });

    updateThreatCount(alerts.filter(a => !a.is_resolved).length);
}

function updateThreatCount(n) {
    // Updates the "Active Threats" stat cell in the stat-strip
    const cells = document.querySelectorAll('.stat-val');
    // Third cell is "Active Threats" based on HTML order
    if (cells[2]) {
        cells[2].textContent = n;
        cells[2].style.color = n > 0 ? '#ef4444' : '#22c55e';
        cells[2].style.textShadow = n > 0
            ? '0 0 10px rgba(239,68,68,0.5)'
            : '0 0 10px rgba(34,197,94,0.4)';
    }
}

// ─── Ticker update ────────────────────────────────────────────────────────────

function updateTicker(alerts) {
    const inner = document.querySelector('.ticker-inner');
    if (!inner) return;

    if (!alerts || alerts.length === 0) {
        // Already shows nominal text from HTML — leave it
        return;
    }

    const parts = alerts.map(a =>
        `▶ &nbsp; ${escHtml(a.type).toUpperCase()}: ${escHtml(a.description).substring(0, 60)}… &nbsp;|&nbsp;`
    ).join('&nbsp;');

    // Duplicate for seamless loop
    inner.innerHTML = parts + '&nbsp;' + parts;
}

// ─── Fetch functions ──────────────────────────────────────────────────────────

function fetchNodes() {
    fetch('/api/nodes')
        .then(res => res.json())
        .then(json => {
            if (!json.success || !json.data) return;
            json.data.forEach(node => {
                const domId = getNodeDomId(node.name);
                applyNodeStatus(domId, node.status, node.latest_metric, node.active_alerts);
            });
        })
        .catch(err => console.warn('[NetGuard] fetchNodes error:', err));
}

function fetchMetrics() {
    fetch('/api/metrics')
        .then(res => res.json())
        .then(json => {
            if (!json.success || !json.data || json.data.length === 0) return;

            // Use the first node's metric for the chart (or average all nodes)
            const first = json.data[0];
            const label = new Date().toTimeString().split(' ')[0];

            pushChartPoint(label, first.cpu_usage, first.memory_usage);
        })
        .catch(err => console.warn('[NetGuard] fetchMetrics error:', err));
}

function fetchAlerts() {
    fetch('/api/alerts')
        .then(res => res.json())
        .then(json => {
            if (!json.success) return;
            renderAlerts(json.data ?? []);
            updateTicker(json.data ?? []);
        })
        .catch(err => console.warn('[NetGuard] fetchAlerts error:', err));
}

// ─── Resolve alert ────────────────────────────────────────────────────────────

function resolveAlert(alertId, btn) {
    btn.textContent = 'RESOLVING…';
    btn.disabled = true;

    fetch(`/api/alerts/${alertId}/resolve`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken(),
        }
    })
    .then(res => res.json())
    .then(json => {
        if (json.success) {
            fetchAlerts();  // refresh feed immediately
            fetchNodes();   // node may have recovered to Healthy
        }
    })
    .catch(err => console.warn('[NetGuard] resolveAlert error:', err));
}

// ─── Simulation buttons (FR-4) ────────────────────────────────────────────────

/**
 * Called by onclick on simulation buttons in HTML.
 * @param {'ddos'|'memory-leak'|'cpuspike'} type
 */
function triggerSimulation(type) {
    const endpointMap = {
        'ddos':        '/api/simulate/attack',
        'memory-leak': '/api/simulate/memoryleak',
        'cpuspike':    '/api/simulate/cpuspike',
    };

    const labelMap = {
        'ddos':        'DDoS ATTACK',
        'memory-leak': 'MEMORY LEAK',
        'cpuspike':    'CPU SPIKE',
    };

    const endpoint = endpointMap[type];
    if (!endpoint) {
        console.warn('[NetGuard] Unknown simulation type:', type);
        return;
    }

    // Visual feedback: flash the button
    const btns = document.querySelectorAll('.sim-btn');
    btns.forEach(b => { b.disabled = true; b.style.opacity = '0.5'; });

    fetch(endpoint, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken(),
        },
        body: JSON.stringify({ attack_type: type }),
    })
    .then(res => res.json())
    .then(json => {
        if (json.success) {
            // Immediately refresh all panels — don't wait for the 3 s poll
            fetchNodes();
            fetchMetrics();
            fetchAlerts();
            flashSimNotice(labelMap[type] + ' — INJECTED INTO PIPELINE');
        } else {
            flashSimNotice('SIMULATION FAILED — CHECK BACKEND', true);
        }
    })
    .catch(() => flashSimNotice('BACKEND UNREACHABLE — IS SERVER RUNNING?', true))
    .finally(() => {
        btns.forEach(b => { b.disabled = false; b.style.opacity = '1'; });
    });
}

/** Injects a brief status notice beneath the sim buttons. */
function flashSimNotice(msg, isError = false) {
    let notice = document.getElementById('sim-notice');
    if (!notice) {
        notice = document.createElement('div');
        notice.id = 'sim-notice';
        notice.style.cssText = `
            font-family: 'Share Tech Mono', monospace;
            font-size: 10px;
            letter-spacing: 0.15em;
            padding: 8px 12px;
            border-radius: 1px;
            border: 1px solid;
            margin-top: 10px;
            text-transform: uppercase;
            transition: opacity 0.4s;
        `;
        // Insert after the sim-warning div
        const simWarning = document.querySelector('.sim-warning');
        if (simWarning) simWarning.after(notice);
    }

    notice.textContent = '▶ ' + msg;
    notice.style.opacity = '1';
    notice.style.borderColor = isError ? 'rgba(239,68,68,0.5)' : 'rgba(245,158,11,0.5)';
    notice.style.color       = isError ? '#f87171'              : '#f59e0b';
    notice.style.background  = isError ? 'rgba(239,68,68,0.06)' : 'rgba(245,158,11,0.06)';

    clearTimeout(notice._hideTimer);
    notice._hideTimer = setTimeout(() => { notice.style.opacity = '0'; }, 4000);
}

// ─── Utilities ────────────────────────────────────────────────────────────────

function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
}

function escHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

// ─── Expose functions called from inline onclick handlers ─────────────────────
// Vite bundles this file as an ES module, so functions are not global by default.
// The Blade template uses onclick="triggerSimulation(...)" and onclick="resolveAlert(...)"
// which need these on the window object.

window.triggerSimulation = triggerSimulation;
window.resolveAlert     = resolveAlert;

// ─── Boot sequence ────────────────────────────────────────────────────────────

(function boot() {
    fetchNodes();
    fetchMetrics();
    fetchAlerts();

    // H-3: Poll every 3000ms as per PRD FR-1
    setInterval(() => {
        fetchNodes();
        fetchMetrics();
        fetchAlerts();
    }, 3000);
})();