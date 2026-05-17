const ctx = document.getElementById('metricsChart').getContext('2d');
const maxDataPoints = 10; 

const metricsChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: [], 
        datasets: [
            {
                label: 'CPU Usage (%)',
                data: [],
                borderColor: '#3b82f6', 
                tension: 0.3,
                fill: false
            },
            {
                label: 'Memory Usage (%)',
                data: [],
                borderColor: '#10b981',
                tension: 0.3,
                fill: false
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: { min: 0, max: 100, grid: { color: '#374151' } },
            x: { grid: { color: '#374151' } }
        }
    }
});

setInterval(() => {
    fetchNodes();
    fetchMetrics();
    fetchAlerts();
}, 3000);

function fetchNodes() {
    fetch('/api/nodes')
        .then(res => res.json())
        .then(nodes => {

            nodes.forEach(node => {
                const card = document.getElementById(`node-${node.name}`);
                const statusText = document.getElementById(`status-${node.name}`);
                
                if (card && statusText) {
                    statusText.innerText = node.status;
                    
                    if (node.status === 'Critical') {
                        card.className = "p-4 rounded-xl text-white font-bold animate-flash-red";
                        statusText.className = "text-2xl font-bold text-white mt-2";
                    } else if (node.status === 'Warning') {
                        card.className = "bg-amber-900/50 border border-amber-500 p-4 rounded-xl text-amber-200";
                        statusText.className = "text-2xl font-bold text-amber-400 mt-2";
                    } else {
                        card.className = "bg-gray-800 border border-gray-700 p-4 rounded-xl";
                        statusText.className = "text-2xl font-bold text-green-400 mt-2";
                    }
                }
            });
        })
        .catch(err => console.error("Error fetching nodes:", err));
}

function fetchMetrics() {
    fetch('/api/metrics')
        .then(res => res.json())
        .then(data => {

            const time = data.timestamp || new Date().toLocaleTimeString();
            
            metricsChart.data.labels.push(time);
            metricsChart.data.datasets[0].data.push(data.cpu);
            metricsChart.data.datasets[1].data.push(data.memory);

            if (metricsChart.data.labels.length > maxDataPoints) {
                metricsChart.data.labels.shift();
                metricsChart.data.datasets[0].data.shift();
                metricsChart.data.datasets[1].data.shift();
            }
            
            metricsChart.update();
        })
        .catch(err => console.error("Error fetching metrics:", err));
}

function fetchAlerts() {
    fetch('/api/alerts')
        .then(res => res.json())
        .then(alerts => {
            const feed = document.getElementById('incident-feed');
            feed.innerHTML = ''; 
            
            if (alerts.length === 0) {
                feed.innerHTML = '<li class="text-sm text-gray-500 italic">No incidents reported yet. System stable.</li>';
                return;
            }

            alerts.forEach(alert => {
                const li = document.createElement('li');
                li.className = "text-sm bg-gray-900 border-l-4 border-red-500 p-2 rounded text-red-300 flex justify-between";
                li.innerHTML = `<span>🚨 <strong>${alert.type}</strong>: ${alert.message}</span> <span class="text-xs text-gray-500">${alert.time}</span>`;
                feed.appendChild(li);
            });
        })
        .catch(err => console.error("Error fetching alerts:", err));
}

function triggerSimulation(type) {
    fetch(`/simulate/attack`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify({ attack_type: type })
    })
    .then(res => {
        if(res.ok) {
            alert(`Simulation sent: ${type.toUpperCase()}. Watch your dashboard charts update!`);
        } else {
            alert("Backend endpoint found, but simulation failed to trigger.");
        }
    })
    .catch(err => alert("Could not reach simulation backend. Ensure Moeed's routes are active."));
}