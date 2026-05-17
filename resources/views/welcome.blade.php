<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NetGuard // Simulation Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&family=Orbitron:wght@400;700;900&family=Rajdhani:wght@300;400;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --amber: #f59e0b;
            --amber-dim: #92400e;
            --amber-glow: rgba(245, 158, 11, 0.18);
            --amber-glow-strong: rgba(245, 158, 11, 0.35);
            --red-hot: #ef4444;
            --red-dim: #7f1d1d;
            --green-on: #22c55e;
            --cyan-arc: #06b6d4;
            --bg-deep: #070809;
            --bg-panel: #0c0e10;
            --bg-card: #101418;
            --border-dim: rgba(245, 158, 11, 0.15);
            --border-mid: rgba(245, 158, 11, 0.3);
            --border-hot: rgba(245, 158, 11, 0.6);
            --scanline-opacity: 0.035;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            background-color: var(--bg-deep);
            color: #d4b896;
            font-family: 'Rajdhani', sans-serif;
            font-size: 15px;
            min-height: 100vh;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background: repeating-linear-gradient(
                0deg,
                transparent,
                transparent 2px,
                rgba(0,0,0, var(--scanline-opacity)) 2px,
                rgba(0,0,0, var(--scanline-opacity)) 4px
            );
            pointer-events: none;
            z-index: 9999;
        }

        body::after {
            content: '';
            position: fixed;
            inset: 0;
            background: radial-gradient(ellipse at center, transparent 60%, rgba(0,0,0,0.55) 100%);
            pointer-events: none;
            z-index: 9998;
        }

        .grid-bg {
            background-image:
                linear-gradient(rgba(245,158,11,0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(245,158,11,0.04) 1px, transparent 1px);
            background-size: 40px 40px;
        }

        .font-mono   { font-family: 'Share Tech Mono', monospace; }
        .font-orb    { font-family: 'Orbitron', monospace; }
        .font-raj    { font-family: 'Rajdhani', sans-serif; }

        .text-amber  { color: var(--amber); }
        .text-amber-dim { color: #b45309; }
        .glow-amber  { text-shadow: 0 0 8px rgba(245,158,11,0.7), 0 0 24px rgba(245,158,11,0.3); }
        .glow-green  { text-shadow: 0 0 8px rgba(34,197,94,0.8); }
        .glow-red    { text-shadow: 0 0 8px rgba(239,68,68,0.9), 0 0 20px rgba(239,68,68,0.4); }
        .glow-cyan   { text-shadow: 0 0 8px rgba(6,182,212,0.8); }

        .border-amber-glow { border-color: var(--border-mid); box-shadow: 0 0 0 1px var(--border-dim) inset, 0 0 16px var(--amber-glow); }
        .border-amber-hot  { border-color: var(--border-hot); box-shadow: 0 0 0 1px rgba(245,158,11,0.1) inset, 0 0 24px var(--amber-glow-strong); }

        .panel {
            background: var(--bg-card);
            border: 1px solid var(--border-dim);
            border-radius: 2px;
            position: relative;
            overflow: hidden;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        .panel::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--amber), transparent);
            opacity: 0.4;
        }
        .panel:hover {
            border-color: var(--border-mid);
            box-shadow: 0 0 20px var(--amber-glow);
        }

        .panel-corner::after,
        .panel-corner::before {
            content: '';
            position: absolute;
            width: 10px; height: 10px;
            border-color: var(--amber);
            border-style: solid;
            opacity: 0.7;
        }
        .panel-corner::before { top: -1px; left: -1px; border-width: 2px 0 0 2px; }
        .panel-corner::after  { bottom: -1px; right: -1px; border-width: 0 2px 2px 0; }

        header {
            background: var(--bg-panel);
            border-bottom: 1px solid var(--border-mid);
            padding: 0 2rem;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
        }
        header::after {
            content: '';
            position: absolute;
            bottom: -3px; left: 0; right: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent 0%, var(--amber) 30%, var(--amber) 70%, transparent 100%);
            opacity: 0.25;
        }

        .logo-block {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .logo-icon {
            width: 36px; height: 36px;
            border: 1.5px solid var(--amber);
            display: flex; align-items: center; justify-content: center;
            transform: rotate(45deg);
            box-shadow: 0 0 12px rgba(245,158,11,0.5);
            flex-shrink: 0;
        }
        .logo-icon span {
            transform: rotate(-45deg);
            font-family: 'Orbitron', monospace;
            font-size: 12px;
            font-weight: 900;
            color: var(--amber);
        }

        .logo-text h1 {
            font-family: 'Orbitron', monospace;
            font-size: 18px;
            font-weight: 700;
            color: var(--amber);
            letter-spacing: 0.12em;
            line-height: 1;
        }
        .logo-text p {
            font-family: 'Share Tech Mono', monospace;
            font-size: 10px;
            color: #6b5b3e;
            letter-spacing: 0.25em;
            text-transform: uppercase;
            margin-top: 2px;
        }

        .live-indicator {
            display: flex;
            align-items: center;
            gap: 10px;
            font-family: 'Share Tech Mono', monospace;
            font-size: 12px;
            color: var(--green-on);
            letter-spacing: 0.15em;
        }
        .live-dot {
            width: 8px; height: 8px;
            border-radius: 50%;
            background: var(--green-on);
            box-shadow: 0 0 8px var(--green-on), 0 0 16px rgba(34,197,94,0.5);
            animation: livePulse 1.8s ease-in-out infinite;
        }
        @keyframes livePulse {
            0%, 100% { opacity: 1; box-shadow: 0 0 8px var(--green-on), 0 0 16px rgba(34,197,94,0.5); }
            50%       { opacity: 0.5; box-shadow: 0 0 4px var(--green-on); }
        }

        .header-meta {
            display: flex;
            align-items: center;
            gap: 24px;
        }
        .header-tag {
            font-family: 'Share Tech Mono', monospace;
            font-size: 10px;
            letter-spacing: 0.2em;
            color: #5a4a30;
            text-transform: uppercase;
        }
        .header-tag span {
            color: #9a7a50;
        }

        .section-label {
            font-family: 'Share Tech Mono', monospace;
            font-size: 10px;
            letter-spacing: 0.3em;
            text-transform: uppercase;
            color: #6b5b3e;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 14px;
        }
        .section-label::before {
            content: '//';
            color: var(--amber);
            opacity: 0.6;
        }
        .section-label::after {
            content: '';
            flex: 1;
            height: 1px;
            background: linear-gradient(90deg, var(--border-dim), transparent);
        }

        .node-card {
            background: var(--bg-card);
            border: 1px solid var(--border-dim);
            border-radius: 2px;
            padding: 18px 20px;
            position: relative;
            overflow: hidden;
            transition: all 0.4s ease;
            cursor: default;
        }
        .node-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: linear-gradient(135deg, rgba(245,158,11,0.04) 0%, transparent 60%);
            pointer-events: none;
        }
        .node-card-accent {
            position: absolute;
            left: 0; top: 0; bottom: 0;
            width: 3px;
            background: linear-gradient(180deg, var(--amber), transparent);
            opacity: 0.6;
        }

        .node-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 14px;
        }
        .node-title {
            font-family: 'Orbitron', monospace;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.2em;
            color: #9a7a50;
            text-transform: uppercase;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 3px 10px;
            border-radius: 1px;
            font-family: 'Share Tech Mono', monospace;
            font-size: 10px;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            border: 1px solid;
        }
        .badge-online  { background: rgba(34,197,94,0.1);  border-color: rgba(34,197,94,0.4);  color: var(--green-on); }
        .badge-offline { background: rgba(239,68,68,0.1);  border-color: rgba(239,68,68,0.4);  color: #f87171; }
        .badge-loading { background: rgba(245,158,11,0.1); border-color: rgba(245,158,11,0.3); color: #f59e0b; }
        .badge-dot {
            width: 5px; height: 5px;
            border-radius: 50%;
            background: currentColor;
            animation: livePulse 1.5s infinite;
        }

        .node-status-text {
            font-family: 'Orbitron', monospace;
            font-size: 20px;
            font-weight: 700;
            letter-spacing: 0.05em;
            color: var(--amber);
            text-shadow: 0 0 12px rgba(245,158,11,0.5);
            line-height: 1;
        }

        .node-meta {
            margin-top: 10px;
            display: flex;
            gap: 16px;
        }
        .node-meta-item {
            font-family: 'Share Tech Mono', monospace;
            font-size: 10px;
            color: #4a3a28;
            letter-spacing: 0.1em;
        }
        .node-meta-item span {
            color: #7a6040;
        }

        @keyframes flashRed {
            0%, 100% { background-color: rgba(239,68,68,0.18); border-color: rgba(239,68,68,0.7); box-shadow: 0 0 20px rgba(239,68,68,0.4); }
            50%       { background-color: rgba(127,29,29,0.25); border-color: rgba(239,68,68,0.3); box-shadow: 0 0 8px rgba(239,68,68,0.2); }
        }
        .animate-flash-red {
            animation: flashRed 0.9s ease-in-out infinite;
        }

        @keyframes criticalPulse {
            0%, 100% { opacity: 1; }
            50%       { opacity: 0.4; }
        }
        .critical-blink {
            animation: criticalPulse 0.7s ease-in-out infinite;
            color: var(--red-hot) !important;
        }

        .chart-panel {
            background: var(--bg-card);
            border: 1px solid var(--border-dim);
            border-radius: 2px;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }
        .chart-panel::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--amber), transparent);
            opacity: 0.3;
        }

        .chart-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 18px;
        }
        .chart-title {
            font-family: 'Orbitron', monospace;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.2em;
            color: #9a7a50;
            text-transform: uppercase;
        }
        .chart-legend {
            display: flex;
            gap: 16px;
        }
        .legend-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-family: 'Share Tech Mono', monospace;
            font-size: 10px;
            letter-spacing: 0.12em;
            color: #6b5b3e;
        }
        .legend-dot {
            width: 8px; height: 2px;
            border-radius: 1px;
        }

        /* ── INCIDENT FEED ── */
        .incident-panel {
            background: var(--bg-card);
            border: 1px solid var(--border-dim);
            border-radius: 2px;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }
        .incident-panel::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--amber), transparent);
            opacity: 0.3;
        }

        #incident-feed {
            max-height: 180px;
            overflow-y: auto;
            space-y: 8px;
            scrollbar-width: thin;
            scrollbar-color: #3a2a18 transparent;
        }
        #incident-feed::-webkit-scrollbar { width: 4px; }
        #incident-feed::-webkit-scrollbar-track { background: transparent; }
        #incident-feed::-webkit-scrollbar-thumb { background: #3a2a18; border-radius: 2px; }

        .incident-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 10px 12px;
            border-left: 2px solid var(--amber);
            background: rgba(245,158,11,0.04);
            margin-bottom: 6px;
            font-family: 'Share Tech Mono', monospace;
            font-size: 11px;
            color: #9a8060;
            letter-spacing: 0.05em;
            transition: background 0.2s;
        }
        .incident-item:hover { background: rgba(245,158,11,0.07); }

        .incident-tag {
            font-size: 9px;
            padding: 2px 6px;
            border: 1px solid;
            border-radius: 1px;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            white-space: nowrap;
            flex-shrink: 0;
        }
        .tag-critical { border-color: rgba(239,68,68,0.5); color: #f87171; background: rgba(239,68,68,0.08); }
        .tag-warning  { border-color: rgba(245,158,11,0.5); color: var(--amber); background: rgba(245,158,11,0.06); }
        .tag-info     { border-color: rgba(6,182,212,0.4);  color: var(--cyan-arc);  background: rgba(6,182,212,0.06); }

        .incident-empty {
            font-family: 'Share Tech Mono', monospace;
            font-size: 11px;
            color: #3a2a18;
            letter-spacing: 0.15em;
            padding: 20px 0;
            text-align: center;
        }

        .sim-panel {
            background: var(--bg-card);
            border: 1px solid var(--border-dim);
            border-radius: 2px;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }
        .sim-panel::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--amber), transparent);
            opacity: 0.3;
        }

        .sim-btn {
            width: 100%;
            padding: 12px 16px;
            border-radius: 2px;
            font-family: 'Orbitron', monospace;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            border: 1px solid;
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
            overflow: hidden;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .sim-btn::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.04) 0%, transparent 100%);
        }

        .sim-btn-ddos {
            background: rgba(239,68,68,0.08);
            border-color: rgba(239,68,68,0.5);
            color: #f87171;
        }
        .sim-btn-ddos:hover {
            background: rgba(239,68,68,0.15);
            border-color: rgba(239,68,68,0.8);
            box-shadow: 0 0 20px rgba(239,68,68,0.3);
            color: #fca5a5;
        }
        .sim-btn-ddos:active { transform: scale(0.98); }

        .sim-btn-memleak {
            background: rgba(245,158,11,0.08);
            border-color: rgba(245,158,11,0.4);
            color: var(--amber);
        }
        .sim-btn-memleak:hover {
            background: rgba(245,158,11,0.14);
            border-color: rgba(245,158,11,0.7);
            box-shadow: 0 0 20px rgba(245,158,11,0.25);
            color: #fbbf24;
        }
        .sim-btn-memleak:active { transform: scale(0.98); }

        .btn-icon {
            width: 22px; height: 22px;
            border: 1px solid currentColor;
            display: flex; align-items: center; justify-content: center;
            font-size: 11px;
            flex-shrink: 0;
            opacity: 0.8;
        }

        .sim-divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--border-dim), transparent);
            margin: 16px 0;
        }

        .sim-warning {
            background: rgba(239,68,68,0.06);
            border: 1px solid rgba(239,68,68,0.2);
            padding: 10px 12px;
            border-radius: 1px;
            margin-top: 14px;
        }
        .sim-warning p {
            font-family: 'Share Tech Mono', monospace;
            font-size: 9px;
            letter-spacing: 0.12em;
            color: #7f1d1d;
            line-height: 1.6;
            text-transform: uppercase;
        }

        .stat-strip {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1px;
            background: var(--border-dim);
            border: 1px solid var(--border-dim);
            border-radius: 2px;
            overflow: hidden;
            margin-bottom: 20px;
        }
        .stat-cell {
            background: var(--bg-card);
            padding: 12px 16px;
            text-align: center;
        }
        .stat-val {
            font-family: 'Orbitron', monospace;
            font-size: 18px;
            font-weight: 700;
            color: var(--amber);
            letter-spacing: 0.05em;
            text-shadow: 0 0 10px rgba(245,158,11,0.4);
        }
        .stat-label {
            font-family: 'Share Tech Mono', monospace;
            font-size: 9px;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: #4a3a28;
            margin-top: 3px;
        }

        .status-bar {
            background: var(--bg-panel);
            border-top: 1px solid var(--border-dim);
            padding: 8px 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-family: 'Share Tech Mono', monospace;
            font-size: 9px;
            letter-spacing: 0.15em;
            color: #3a2a18;
            text-transform: uppercase;
        }
        .status-bar span { color: #5a4a30; }

        @keyframes bootFlicker {
            0%   { opacity: 0; }
            10%  { opacity: 0.9; }
            12%  { opacity: 0.2; }
            14%  { opacity: 0.8; }
            20%  { opacity: 1; }
            100% { opacity: 1; }
        }
        .boot-anim { animation: bootFlicker 1.2s ease-out forwards; }

        .ticker-wrap {
            overflow: hidden;
            height: 28px;
            background: rgba(245,158,11,0.06);
            border: 1px solid var(--border-dim);
            border-radius: 2px;
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        .ticker-label {
            background: var(--amber);
            color: #0c0e10;
            font-family: 'Orbitron', monospace;
            font-size: 8px;
            font-weight: 900;
            letter-spacing: 0.2em;
            padding: 0 10px;
            height: 100%;
            display: flex;
            align-items: center;
            flex-shrink: 0;
        }
        .ticker-inner {
            display: flex;
            animation: tickerScroll 30s linear infinite;
            white-space: nowrap;
            font-family: 'Share Tech Mono', monospace;
            font-size: 10px;
            color: #6b5b3e;
            letter-spacing: 0.1em;
            padding-left: 20px;
        }
        @keyframes tickerScroll {
            0%   { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }

        .progress-wrap {
            background: rgba(245,158,11,0.06);
            border: 1px solid var(--border-dim);
            height: 4px;
            border-radius: 1px;
            overflow: hidden;
            margin-top: 4px;
        }
        .progress-bar {
            height: 100%;
            border-radius: 1px;
            background: linear-gradient(90deg, var(--amber), #fbbf24);
            box-shadow: 0 0 8px rgba(245,158,11,0.6);
            transition: width 0.5s ease;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: 260px 1fr;
            gap: 16px;
        }

        @media (max-width: 900px) {
            .dashboard-grid { grid-template-columns: 1fr; }
        }
    </style>
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

                    <div class="sim-divider"></div>

                    <div class="sim-warning">
                        <p>⚠ CAUTION: Events trigger real-time responses in all connected monitoring subsystems.</p>
                    </div>
                </div>

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
                        <div class="stat-val" style="color: var(--green-on); text-shadow: 0 0 10px rgba(34,197,94,0.4);">0</div>
                        <div class="stat-label">Active Threats</div>
                    </div>
                </div>

                <div>
                    <div class="section-label">Node Status Monitor</div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">

                        <div id="node-auth" class="node-card panel-corner" style="transition: all 0.4s ease;">
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
                                    <div class="node-meta-item">REQ/S <span>—</span></div>
                                </div>
                            </div>
                        </div>

                        <div id="node-gateway" class="node-card panel-corner" style="transition: all 0.4s ease;">
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
                                    <div class="node-meta-item">REQ/S <span>—</span></div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

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
                                MEMORY
                            </div>
                        </div>
                    </div>
                    <div style="height: 200px; position: relative;">
                        <canvas id="metricsChart"></canvas>
                    </div>
                </div>

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

    <script>
        function updateClock() {
            const now = new Date();
            const t = now.toUTCString().split(' ')[4] + ' UTC';
            document.getElementById('clock').textContent = now.toTimeString().split(' ')[0];
            document.getElementById('statusbar-clock').textContent = t;
        }
        updateClock();
        setInterval(updateClock, 1000);
    </script>

    <script src="/js/dashboard.js"></script>
</body>
</html>