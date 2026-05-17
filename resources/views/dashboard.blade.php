<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- Required for POST simulation requests --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>NetGuard // Simulation Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&family=Orbitron:wght@400;700;900&family=Rajdhani:wght@300;400;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="grid-bg boot-anim">

    <header>
        <div class="logo-block">
            <div class="logo-icon"><span>NG</span></div>
            <div class="logo-text">
                <h1 class="glow-amber">NETGUARD</h1>
                <p>SIMULATION &nbsp;DASHBOARD &nbsp;v2.4.1</p>
            </div>
        </div>

        <div class="header-meta">
            <div class="header-tag">SYS &nbsp;<span id="clock">--:--:--</span></div>
            <div class="header-tag">NODE &nbsp;<span>PKT-07</span></div>
            <div class="live-indicator">
                <div class="live-dot"></div>
                LIVE STREAM
            </div>
        </div>
    </header>

    <div style="padding: 14px 2rem 0;">
        <div class="ticker-wrap">
            <div class="ticker-label">ALERT FEED</div>
            <div class="ticker-inner">
                &nbsp; ▶ &nbsp; SYSTEM NOMINAL &nbsp;|&nbsp; ALL NODES RESPONDING &nbsp;|&nbsp; PERIMETER SECURE &nbsp;|&nbsp; PACKETS MONITORED: 4,291,847 &nbsp;|&nbsp; FIREWALL ACTIVE &nbsp;|&nbsp; TLS 1.3 ENFORCED &nbsp;|&nbsp; IDS ONLINE &nbsp;|&nbsp; ZERO ANOMALIES DETECTED &nbsp;|&nbsp; UPTIME 99.97% &nbsp;|&nbsp;
                &nbsp; ▶ &nbsp; SYSTEM NOMINAL &nbsp;|&nbsp; ALL NODES RESPONDING &nbsp;|&nbsp; PERIMETER SECURE &nbsp;|&nbsp; PACKETS MONITORED: 4,291,847 &nbsp;|&nbsp; FIREWALL ACTIVE &nbsp;|&nbsp; TLS 1.3 ENFORCED &nbsp;|&nbsp; IDS ONLINE &nbsp;|&nbsp; ZERO ANOMALIES DETECTED &nbsp;|&nbsp; UPTIME 99.97% &nbsp;|&nbsp;
            </div>
        </div>
    </div>

    <div style="padding: 0 2rem 2rem; margin-top: 8px;">
        <div class="dashboard-grid">

            <aside style="display: flex; flex-direction: column; gap: 16px;">

                <!-- Simulation Control Panel -->
                <div class="sim-panel panel-corner">
                    <div class="section-label">Simulation Control</div>

                    <div style="margin-bottom: 16px;">
                        <p style="font-family: 'Share Tech Mono', monospace; font-size: 10px; color: #4a3a28; letter-spacing: 0.12em; line-height: 1.7; text-transform: uppercase;">
                            Inject synthetic threat vectors into the live monitoring pipeline.
                        </p>
                    </div>

                    <button onclick="triggerSimulation('ddos')" class="sim-btn sim-btn-ddos">
                        <div class="btn-icon">⚡</div>
                        <div style="text-align:left;">
                            <div>Simulate DDoS</div>
                            <div style="font-size:8px; opacity:0.5; font-weight:400; letter-spacing:0.1em; margin-top:2px;">VOLUME FLOOD ATTACK</div>
                        </div>
                    </button>

                    <button onclick="triggerSimulation('memory-leak')" class="sim-btn sim-btn-memleak">
                        <div class="btn-icon">⚠</div>
                        <div style="text-align:left;">
                            <div>Memory Leak</div>
                            <div style="font-size:8px; opacity:0.5; font-weight:400; letter-spacing:0.1em; margin-top:2px;">HEAP OVERFLOW SIM</div>
                        </div>
                    </button>

                    {{-- Added in this patch --}}
                    <button onclick="triggerSimulation('cpuspike')" class="sim-btn sim-btn-cpu">
                        <div class="btn-icon">▲</div>
                        <div style="text-align:left;">
                            <div>CPU Spike</div>
                            <div style="font-size:8px; opacity:0.5; font-weight:400; letter-spacing:0.1em; margin-top:2px;">PROCESS OVERLOAD SIM</div>
                        </div>
                    </button>

                    <div class="sim-divider"></div>

                    <div class="sim-warning">
                        <p>⚠ CAUTION: Events trigger real-time responses in all connected monitoring subsystems.</p>
                    </div>
                    {{-- JS injects #sim-notice here after simulate calls --}}
                </div>

                <!-- System Overview Panel -->
                <div class="sim-panel panel-corner">
                    <div class="section-label">System Overview</div>

                    <div style="display: flex; flex-direction: column; gap: 14px;">
                        <div>
                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <span style="font-family:'Share Tech Mono',monospace; font-size:10px; color:#4a3a28; letter-spacing:0.15em; text-transform:uppercase;">Firewall</span>
                                <span style="font-family:'Share Tech Mono',monospace; font-size:10px; color:var(--green-on);">ACTIVE</span>
                            </div>
                            <div class="progress-wrap">
                                <div class="progress-bar" style="width:100%; background: linear-gradient(90deg, #16a34a, #22c55e);"></div>
                            </div>
                        </div>
                        <div>
                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <span style="font-family:'Share Tech Mono',monospace; font-size:10px; color:#4a3a28; letter-spacing:0.15em; text-transform:uppercase;">IDS Engine</span>
                                <span style="font-family:'Share Tech Mono',monospace; font-size:10px; color:var(--green-on);">SCANNING</span>
                            </div>
                            <div class="progress-wrap">
                                <div class="progress-bar" style="width:82%;"></div>
                            </div>
                        </div>
                        <div>
                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <span style="font-family:'Share Tech Mono',monospace; font-size:10px; color:#4a3a28; letter-spacing:0.15em; text-transform:uppercase;">Threat DB</span>
                                <span style="font-family:'Share Tech Mono',monospace; font-size:10px; color:var(--cyan-arc);">SYNC 97%</span>
                            </div>
                            <div class="progress-wrap">
                                <div class="progress-bar" style="width:97%; background: linear-gradient(90deg, var(--cyan-arc), #22d3ee);"></div>
                            </div>
                        </div>
                        <div>
                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <span style="font-family:'Share Tech Mono',monospace; font-size:10px; color:#4a3a28; letter-spacing:0.15em; text-transform:uppercase;">Packet Buffer</span>
                                <span style="font-family:'Share Tech Mono',monospace; font-size:10px; color:var(--amber);">64%</span>
                            </div>
                            <div class="progress-wrap">
                                <div class="progress-bar" style="width:64%;"></div>
                            </div>
                        </div>
                    </div>
                </div>

            </aside>

            <main style="display: flex; flex-direction: column; gap: 16px; min-width: 0;">

                <!-- Stat strip -->
                <div class="stat-strip">
                    <div class="stat-cell">
                        <div class="stat-val">99.97<span style="font-size:10px;">%</span></div>
                        <div class="stat-label">Uptime</div>
                    </div>
                    <div class="stat-cell">
                        <div class="stat-val" style="color: var(--cyan-arc); text-shadow: 0 0 10px rgba(6,182,212,0.4);">4.29M</div>
                        <div class="stat-label">Packets Tracked</div>
                    </div>
                    <div class="stat-cell">
                        {{-- JS updates this value and color dynamically --}}
                        <div class="stat-val" style="color: var(--green-on); text-shadow: 0 0 10px rgba(34,197,94,0.4);">0</div>
                        <div class="stat-label">Active Threats</div>
                    </div>
                </div>

                <!-- Node Status Cards -->
                <div>
                    <div class="section-label">Node Status Monitor</div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">

                        {{-- node-{domId} and status-{domId} must match NODE_ID_MAP in dashboard.js --}}

                        <div id="node-auth" class="node-card panel-corner">
                            <div class="node-card-accent"></div>
                            <div style="padding-left: 10px;">
                                <div class="node-header">
                                    <div class="node-title">Auth Service</div>
                                    <div class="status-badge badge-loading">
                                        <div class="badge-dot"></div>
                                        INIT
                                    </div>
                                </div>
                                <div id="status-auth" class="node-status-text">Loading...</div>
                                <div class="node-meta">
                                    <div class="node-meta-item">PORT <span>8443</span></div>
                                    <div class="node-meta-item">PROTO <span>HTTPS</span></div>
                                    <div class="node-meta-item">REQ/S <span class="req-val">—</span></div>
                                </div>
                                <div class="alert-count"></div>
                            </div>
                        </div>

                        <div id="node-gateway" class="node-card panel-corner">
                            <div class="node-card-accent"></div>
                            <div style="padding-left: 10px;">
                                <div class="node-header">
                                    <div class="node-title">API Gateway</div>
                                    <div class="status-badge badge-loading">
                                        <div class="badge-dot"></div>
                                        INIT
                                    </div>
                                </div>
                                <div id="status-gateway" class="node-status-text">Loading...</div>
                                <div class="node-meta">
                                    <div class="node-meta-item">PORT <span>443</span></div>
                                    <div class="node-meta-item">PROTO <span>REST</span></div>
                                    <div class="node-meta-item">REQ/S <span class="req-val">—</span></div>
                                </div>
                                <div class="alert-count"></div>
                            </div>
                        </div>

                        <div id="node-db" class="node-card panel-corner">
                            <div class="node-card-accent"></div>
                            <div style="padding-left: 10px;">
                                <div class="node-header">
                                    <div class="node-title">DB Service</div>
                                    <div class="status-badge badge-loading">
                                        <div class="badge-dot"></div>
                                        INIT
                                    </div>
                                </div>
                                <div id="status-db" class="node-status-text">Loading...</div>
                                <div class="node-meta">
                                    <div class="node-meta-item">PORT <span>5432</span></div>
                                    <div class="node-meta-item">PROTO <span>SQLite</span></div>
                                    <div class="node-meta-item">REQ/S <span class="req-val">—</span></div>
                                </div>
                                <div class="alert-count"></div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Chart -->
                <div class="chart-panel panel-corner">
                    <div class="chart-header">
                        <div class="chart-title">System Metrics &mdash; CPU &amp; Memory</div>
                        <div class="chart-legend">
                            <div class="legend-item">
                                <div class="legend-dot" style="background: #f59e0b;"></div>
                                CPU USAGE
                            </div>
                            <div class="legend-item">
                                <div class="legend-dot" style="background: #06b6d4;"></div>
                                MEMORY MB
                            </div>
                        </div>
                    </div>
                    <div style="height: 200px; position: relative;">
                        <canvas id="metricsChart"></canvas>
                    </div>
                </div>

                <!-- Incident Feed -->
                <div class="incident-panel panel-corner">
                    <div class="section-label" style="margin-bottom: 14px;">Active Security Incidents</div>
                    <ul id="incident-feed" style="list-style: none; padding: 0; margin: 0;">
                        <li class="incident-empty">
                            [ NO INCIDENTS LOGGED ] &nbsp; SYSTEM NOMINAL
                        </li>
                    </ul>
                </div>

            </main>
        </div>
    </div>

    <div class="status-bar">
        <div>NETGUARD &nbsp;v2.4.1 &nbsp;// &nbsp;BUILD 20240419</div>
        <div>ENCRYPTION &nbsp;<span>AES-256-GCM</span> &nbsp;// &nbsp;TLS &nbsp;<span>1.3</span></div>
        <div>JURISDICTION &nbsp;<span>GLOBAL</span> &nbsp;// &nbsp;MODE &nbsp;<span>ACTIVE DEFENSE</span></div>
        <div id="statusbar-clock">--:--:-- UTC</div>
    </div>

    <script></script>
</body>
</html>