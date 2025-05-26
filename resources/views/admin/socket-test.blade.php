@extends('layouts.app')

@php
function protocol() {
    return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
}
@endphp

@section('title', 'Socket.IO Test')

@section('content')
<div class="container-fluid">
    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <div class="float-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Socket.IO Test</li>
                    </ol>
                </div>
                <h4 class="page-title">Socket.IO Connection Test</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Real-time Notification Testing</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="alert alert-info">
                                <h5 class="alert-heading">About this page</h5>
                                <p>This page helps you test the real-time notification system using Socket.IO. Use the buttons below to test different aspects of the system.</p>
                            </div>

                            <div class="d-grid gap-2">
                                <button id="test-connection" class="btn btn-primary">
                                    <i class="iconoir-wifi me-1"></i> Test Socket Connection
                                </button>

                                <button id="send-test-notification" class="btn btn-success">
                                    <i class="iconoir-bell me-1"></i> Send Test Notification
                                </button>

                                <button id="check-server-status" class="btn btn-info">
                                    <i class="iconoir-server me-1"></i> Check Socket Server Status
                                </button>
                            </div>

                            <div class="mt-4">
                                <h5>Connection Status</h5>
                                <div id="connection-status" class="alert alert-secondary">
                                    Waiting for connection test...
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <h5>Test Log</h5>
                            <div id="test-log" class="border rounded p-3 bg-light" style="height: 350px; overflow-y: auto; font-family: monospace;">
                                <div class="log-entry text-muted">--- Log Started ---</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Socket Server Details</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Configuration</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th>Socket Server URL</th>
                                    <td><code id="socket-url">{{ env('SOCKET_SERVER_URL', protocol().'://'.request()->getHost().':6001') }}</code></td>
                                </tr>
                                <tr>
                                    <th>Socket Connection URL</th>
                                    <td><code id="socket-connect-url">{{ protocol().'://'.request()->getHost().':6002' }}</code></td>
                                </tr>
                                <tr>
                                    <th>Channel</th>
                                    <td><code>admin-notifications</code></td>
                                </tr>
                                <tr>
                                    <th>Event</th>
                                    <td><code>new-reservation</code></td>
                                </tr>
                            </table>
                        </div>

                        <div class="col-md-6" id="server-status-container">
                            <h5>Server Status</h5>
                            <div class="alert alert-secondary">
                                Click "Check Socket Server Status" to see server details
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Troubleshooting</h4>
                </div>
                <div class="card-body">
                    <div class="accordion" id="troubleshootingAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#networkDiagnostics">
                                    <i class="iconoir-network me-2"></i> Network Diagnostics
                                </button>
                            </h2>
                            <div id="networkDiagnostics" class="accordion-collapse collapse show" data-bs-parent="#troubleshootingAccordion">
                                <div class="accordion-body">
                                    <div id="network-info" class="mb-3">
                                        <button id="run-network-diagnostics" class="btn btn-outline-primary btn-sm">
                                            <i class="iconoir-refresh me-1"></i> Run Network Diagnostics
                                        </button>
                                    </div>
                                    <div id="network-results">
                                        <div class="alert alert-secondary">Run diagnostics to check network connectivity</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#commonIssues">
                                    <i class="iconoir-warning-triangle me-2"></i> Common Issues
                                </button>
                            </h2>
                            <div id="commonIssues" class="accordion-collapse collapse" data-bs-parent="#troubleshootingAccordion">
                                <div class="accordion-body">
                                    <div class="list-group">
                                        <div class="list-group-item list-group-item-action">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h6 class="mb-1">Socket.IO server is not running</h6>
                                            </div>
                                            <p class="mb-1">Make sure the Socket.IO server is running with the command:</p>
                                            <pre class="bg-light p-2"><code>node socket-server.js</code></pre>
                                            <small>Check if you can access the status page at <code id="status-url">http://localhost:6001/status</code></small>
                                        </div>

                                        <div class="list-group-item list-group-item-action">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h6 class="mb-1">CORS (Cross-Origin Resource Sharing) issues</h6>
                                            </div>
                                            <p class="mb-1">If you see CORS errors in the console, check the CORS configuration in the Socket.IO server.</p>
                                            <small>Make sure the origin in the server's corsOptions matches your application's URL.</small>
                                        </div>

                                        <div class="list-group-item list-group-item-action">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h6 class="mb-1">Firewall blocking the Socket.IO ports</h6>
                                            </div>
                                            <p class="mb-1">Make sure ports 6001 and 6002 are open in your firewall.</p>
                                            <small>On Windows, check Windows Firewall settings. On Linux, check iptables rules.</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#clientDebug">
                                    <i class="iconoir-developer me-2"></i> Client-Side Debug Info
                                </button>
                            </h2>
                            <div id="clientDebug" class="accordion-collapse collapse" data-bs-parent="#troubleshootingAccordion">
                                <div class="accordion-body">
                                    <div class="mb-3">
                                        <button id="collect-debug-info" class="btn btn-outline-info btn-sm">
                                            <i class="iconoir-desktop me-1"></i> Collect Debug Info
                                        </button>
                                    </div>
                                    <div id="client-debug-info">
                                        <div class="alert alert-secondary">Click the button to collect client-side debug information</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
<style>
    .log-entry {
        margin-bottom: 5px;
        border-bottom: 1px dotted #eee;
        padding-bottom: 5px;
    }
    .log-entry:last-child {
        border-bottom: none;
    }
    .log-time {
        color: #777;
        font-size: 0.8em;
        margin-right: 10px;
    }
    .log-success {
        color: #0f5132;
    }
    .log-error {
        color: #842029;
    }
    .log-info {
        color: #084298;
    }
    .log-warning {
        color: #664d03;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="{{ asset('js/test-socket.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Configure toastr
    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "timeOut": "5000"
    };

    // Variables
    const logContainer = document.getElementById('test-log');
    const connectionStatus = document.getElementById('connection-status');
    let socket = null;

    // Add log entry
    function addLog(message, type = 'info') {
        const entry = document.createElement('div');
        entry.className = `log-entry log-${type}`;

        const now = new Date();
        const timeStr = now.toLocaleTimeString();

        entry.innerHTML = `<span class="log-time">[${timeStr}]</span> ${message}`;
        logContainer.appendChild(entry);
        logContainer.scrollTop = logContainer.scrollHeight;
    }

    // Update connection status
    function updateConnectionStatus(status, message) {
        connectionStatus.className = `alert alert-${status}`;
        connectionStatus.innerHTML = message;
    }

    // Test connection button
    document.getElementById('test-connection').addEventListener('click', function() {
        addLog('Testing Socket.IO connection...', 'info');
        updateConnectionStatus('warning', 'Connecting...');

        // Close existing connection if any
        if (socket) {
            socket.disconnect();
            socket = null;
            addLog('Closed existing connection', 'warning');
        }

        // Get connection URL from the page
        const socketUrl = document.getElementById('socket-connect-url').textContent;
        addLog(`Connecting to ${socketUrl}`, 'info');

        try {
            socket = io(socketUrl, {
                transports: ['websocket', 'polling'],
                reconnection: true,
                reconnectionAttempts: 3,
                reconnectionDelay: 1000,
                timeout: 10000
            });

            // Connection events
            socket.on('connect', function() {
                addLog(`Connected successfully with ID: ${socket.id}`, 'success');
                updateConnectionStatus('success', `<i class="iconoir-check me-1"></i> Connected to Socket.IO server with ID: ${socket.id}`);

                // Subscribe to admin notifications channel
                socket.emit('subscribe', 'admin-notifications');
                addLog('Subscribed to admin-notifications channel', 'info');
            });

            socket.on('connect_error', function(error) {
                addLog(`Connection error: ${error.message}`, 'error');
                updateConnectionStatus('danger', `<i class="iconoir-warning-triangle me-1"></i> Connection error: ${error.message}`);
            });

            socket.on('subscribed', function(data) {
                addLog(`Successfully subscribed to channel: ${data.channel}`, 'success');
            });

            socket.on('new-reservation', function(data) {
                addLog(`Received notification: ${JSON.stringify(data)}`, 'success');
                // Show notification
                toastr.success(data.message, 'New Notification');
            });

            socket.on('disconnect', function(reason) {
                addLog(`Disconnected: ${reason}`, 'warning');
                updateConnectionStatus('warning', `<i class="iconoir-disconnect me-1"></i> Disconnected: ${reason}`);
            });

            socket.on('error', function(error) {
                addLog(`Socket error: ${error}`, 'error');
            });
        } catch (error) {
            addLog(`Error initializing Socket.IO: ${error.message}`, 'error');
            updateConnectionStatus('danger', `<i class="iconoir-warning-triangle me-1"></i> Error initializing Socket.IO: ${error.message}`);
        }
    });

    // Send test notification button
    document.getElementById('send-test-notification').addEventListener('click', function() {
        addLog('Sending test notification...', 'info');

        fetch('{{ route("admin.send-test-notification") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                addLog(`Test notification sent: ${data.notification.message}`, 'success');
            } else {
                addLog(`Error sending notification: ${data.message}`, 'error');
            }
        })
        .catch(error => {
            addLog(`Error: ${error.message}`, 'error');
        });
    });

    // Check server status button
    document.getElementById('check-server-status').addEventListener('click', function() {
        addLog('Checking socket server status...', 'info');

        const socketUrl = document.getElementById('socket-url').textContent;
        const statusUrl = `${socketUrl}/status`;

        fetch(statusUrl)
        .then(response => response.json())
        .then(data => {
            addLog(`Server status: ${data.status}, Connections: ${data.connections}`, 'info');

            // Update status container
            const statusContainer = document.getElementById('server-status-container');
            let channelsHtml = '';

            if (data.channels) {
                for (const [channel, info] of Object.entries(data.channels)) {
                    channelsHtml += `
                        <div class="mb-2">
                            <h6>Channel: ${channel}</h6>
                            <div>Subscribers: ${info.count}</div>
                            <div>Socket IDs: ${info.subscribers.join(', ')}</div>
                        </div>
                    `;
                }
            }

            statusContainer.innerHTML = `
                <h5>Server Status</h5>
                <div class="alert alert-${data.status === 'online' ? 'success' : 'danger'}">
                    <div><strong>Status:</strong> ${data.status}</div>
                    <div><strong>Active Connections:</strong> ${data.connections}</div>
                    <div><strong>Uptime:</strong> ${Math.floor(data.uptime / 60)} minutes</div>
                    <div><strong>Time:</strong> ${new Date(data.timestamp).toLocaleString()}</div>
                </div>

                <h5>Channels</h5>
                <div class="alert alert-info">
                    ${channelsHtml || 'No active channels'}
                </div>
            `;
        })
        .catch(error => {
            addLog(`Error checking server status: ${error.message}`, 'error');
            document.getElementById('server-status-container').innerHTML = `
                <h5>Server Status</h5>
                <div class="alert alert-danger">
                    <i class="iconoir-warning-triangle me-1"></i> Error connecting to socket server: ${error.message}
                </div>
            `;
        });
    });

    // Network Diagnostics button
    document.getElementById('run-network-diagnostics').addEventListener('click', function() {
        const networkResults = document.getElementById('network-results');
        networkResults.innerHTML = `<div class="alert alert-info">Running network diagnostics...</div>`;

        // Get URLs to test
        const socketServerUrl = document.getElementById('socket-url').textContent;
        const socketConnectUrl = document.getElementById('socket-connect-url').textContent;

        // Update status URL to match current host
        document.getElementById('status-url').textContent = socketServerUrl + '/status';

        // Create results container
        let resultsHtml = `<div class="table-responsive"><table class="table table-bordered">
            <thead>
                <tr>
                    <th>Endpoint</th>
                    <th>Status</th>
                    <th>Result</th>
                </tr>
            </thead>
            <tbody>`;

        // Test status endpoint
        const statusUrl = `${socketServerUrl}/status`;
        addLog(`Testing connection to ${statusUrl}`, 'info');

        // Test ping endpoint
        const pingUrl = `${socketServerUrl}/ping`;

        // Run tests in parallel
        Promise.allSettled([
            fetch(statusUrl, { mode: 'cors' }),
            fetch(pingUrl, { mode: 'cors' })
        ])
        .then(results => {
            // Process status result
            const statusResult = results[0];
            if (statusResult.status === 'fulfilled' && statusResult.value.ok) {
                resultsHtml += `
                    <tr class="table-success">
                        <td>${statusUrl}</td>
                        <td>Success</td>
                        <td>Socket server is running and accessible</td>
                    </tr>`;
                addLog('Socket server status endpoint is accessible', 'success');
            } else {
                resultsHtml += `
                    <tr class="table-danger">
                        <td>${statusUrl}</td>
                        <td>Failed</td>
                        <td>${statusResult.status === 'rejected' ? statusResult.reason.message : 'Server returned an error'}</td>
                    </tr>`;
                addLog('Socket server status endpoint is not accessible', 'error');
            }

            // Process ping result
            const pingResult = results[1];
            if (pingResult.status === 'fulfilled' && pingResult.value.ok) {
                resultsHtml += `
                    <tr class="table-success">
                        <td>${pingUrl}</td>
                        <td>Success</td>
                        <td>Socket server ping is working</td>
                    </tr>`;
                addLog('Socket server ping endpoint is accessible', 'success');
            } else {
                resultsHtml += `
                    <tr class="table-danger">
                        <td>${pingUrl}</td>
                        <td>Failed</td>
                        <td>${pingResult.status === 'rejected' ? pingResult.reason.message : 'Server returned an error'}</td>
                    </tr>`;
                addLog('Socket server ping endpoint is not accessible', 'error');
            }

            // Add WebSocket check
            resultsHtml += `
                <tr>
                    <td>${socketConnectUrl} (WebSocket)</td>
                    <td colspan="2">
                        <button id="test-websocket" class="btn btn-sm btn-outline-primary">
                            Test WebSocket Connection
                        </button>
                        <span id="websocket-result" class="ms-2">Click to test</span>
                    </td>
                </tr>`;

            resultsHtml += `</tbody></table></div>`;

            // Add network info
            const networkInfo = navigator.connection ? `
                <div class="alert alert-info mt-3">
                    <h6>Network Information</h6>
                    <div><strong>Connection Type:</strong> ${navigator.connection.effectiveType || 'Unknown'}</div>
                    <div><strong>Downlink:</strong> ${navigator.connection.downlink || 'Unknown'} Mbps</div>
                    <div><strong>RTT:</strong> ${navigator.connection.rtt || 'Unknown'} ms</div>
                </div>` : '';

            // Display results
            networkResults.innerHTML = resultsHtml + networkInfo;

            // Add WebSocket test handler
            document.getElementById('test-websocket').addEventListener('click', function() {
                const resultSpan = document.getElementById('websocket-result');
                resultSpan.textContent = 'Connecting...';
                resultSpan.className = 'ms-2 text-warning';

                try {
                    const testSocket = io(socketConnectUrl, {
                        transports: ['websocket'],
                        timeout: 5000,
                        reconnection: false
                    });

                    testSocket.on('connect', function() {
                        resultSpan.textContent = 'Connected successfully';
                        resultSpan.className = 'ms-2 text-success';
                        addLog(`WebSocket connection to ${socketConnectUrl} successful`, 'success');

                        // Disconnect after 2 seconds
                        setTimeout(() => {
                            testSocket.disconnect();
                        }, 2000);
                    });

                    testSocket.on('connect_error', function(error) {
                        resultSpan.textContent = `Connection error: ${error.message}`;
                        resultSpan.className = 'ms-2 text-danger';
                        addLog(`WebSocket connection to ${socketConnectUrl} failed: ${error.message}`, 'error');
                    });

                    testSocket.on('disconnect', function() {
                        addLog('WebSocket test connection closed', 'info');
                    });
                } catch (error) {
                    resultSpan.textContent = `Error: ${error.message}`;
                    resultSpan.className = 'ms-2 text-danger';
                    addLog(`Error creating WebSocket connection: ${error.message}`, 'error');
                }
            });

        })
        .catch(error => {
            networkResults.innerHTML = `
                <div class="alert alert-danger">
                    <strong>Error running network diagnostics:</strong> ${error.message}
                </div>`;
            addLog(`Error running network diagnostics: ${error.message}`, 'error');
        });
    });

    // Client-side debug info button
    document.getElementById('collect-debug-info').addEventListener('click', function() {
        const debugInfoDiv = document.getElementById('client-debug-info');

        // Collect debug information
        const debugInfo = {
            browser: {
                userAgent: navigator.userAgent,
                language: navigator.language,
                platform: navigator.platform,
                cookiesEnabled: navigator.cookieEnabled,
                doNotTrack: navigator.doNotTrack
            },
            window: {
                innerWidth: window.innerWidth,
                innerHeight: window.innerHeight,
                outerWidth: window.outerWidth,
                outerHeight: window.outerHeight,
                devicePixelRatio: window.devicePixelRatio
            },
            screen: {
                width: screen.width,
                height: screen.height,
                availWidth: screen.availWidth,
                availHeight: screen.availHeight,
                colorDepth: screen.colorDepth
            },
            location: {
                protocol: window.location.protocol,
                host: window.location.host,
                pathname: window.location.pathname,
                search: window.location.search,
                hash: window.location.hash
            },
            socketIO: {
                loaded: typeof io !== 'undefined',
                version: typeof io !== 'undefined' ? io.version : 'Not loaded'
            },
            scripts: Array.from(document.scripts).map(script => script.src || 'inline script').filter(src => src !== 'inline script')
        };

        // Display the debug information
        debugInfoDiv.innerHTML = `
            <div class="alert alert-info">
                <h6>Client-Side Debug Information</h6>
                <p>The following information can help diagnose Socket.IO connection issues:</p>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered">
                    <tr>
                        <th colspan="2" class="table-primary">Browser Information</th>
                    </tr>
                    <tr>
                        <td>User Agent</td>
                        <td>${debugInfo.browser.userAgent}</td>
                    </tr>
                    <tr>
                        <td>Platform</td>
                        <td>${debugInfo.browser.platform}</td>
                    </tr>
                    <tr>
                        <th colspan="2" class="table-primary">Socket.IO</th>
                    </tr>
                    <tr>
                        <td>Socket.IO Loaded</td>
                        <td>${debugInfo.socketIO.loaded ? 'Yes' : 'No'}</td>
                    </tr>
                    <tr>
                        <td>Socket.IO Version</td>
                        <td>${debugInfo.socketIO.version}</td>
                    </tr>
                    <tr>
                        <th colspan="2" class="table-primary">Location</th>
                    </tr>
                    <tr>
                        <td>URL</td>
                        <td>${window.location.href}</td>
                    </tr>
                    <tr>
                        <td>Protocol</td>
                        <td>${debugInfo.location.protocol}</td>
                    </tr>
                    <tr>
                        <td>Host</td>
                        <td>${debugInfo.location.host}</td>
                    </tr>
                </table>
            </div>

            <div class="alert alert-secondary mt-3">
                <h6>Socket.IO Script Check</h6>
                ${document.querySelector('script[src*="socket.io"]')
                    ? '<p class="text-success"><i class="iconoir-check me-1"></i> Socket.IO script is properly loaded.</p>'
                    : '<p class="text-danger"><i class="iconoir-warning-triangle me-1"></i> Socket.IO script not found on the page!</p>'}
            </div>

            <div class="mt-3">
                <a href="https://socket.io/docs/v4/troubleshooting-connection-issues/" target="_blank" class="btn btn-outline-secondary btn-sm">
                    <i class="iconoir-book me-1"></i> Socket.IO Troubleshooting Guide
                </a>
            </div>
        `;

        // Log debug info
        addLog('Client-side debug information collected', 'info');
    });

    // Initial log
    addLog('Socket test page loaded', 'info');
    addLog('Click "Test Connection" to connect to the Socket.IO server', 'info');
});
</script>
@endpush
