/**
 * Socket.IO Connection Test
 *
 * This script tests the connection to the Socket.IO server
 * and verifies that events are properly received.
 */

// Immediately log that the script has loaded
console.log('Socket.IO test script loaded');

// Global socket variable for debugging and direct testing
let socket;

// Direct test of socket.io communication (bypassing Laravel)
function testDirectSocketMessage() {
    displayStatus('info', 'Testing direct Socket.IO message (client-side only)');

    // Create a simple test notification
    const testNotification = {
        id: Date.now(),
        message: `Direct test notification at ${new Date().toLocaleTimeString()}`,
        type: 'test',
        status: 'unread'
    };

    // Manually emit an event to ourselves to test if event handlers work
    try {
        console.log('Emitting test notification event directly');

        // Send to all connected sockets on this channel (including ourselves)
        socket.emit('new-reservation', {
            notification: testNotification,
            timestamp: new Date().toISOString()
        });

        displayStatus('success', 'Direct test message sent, check for notification alert');
    } catch (e) {
        console.error('Error in direct socket test:', e);
        displayStatus('error', `Direct socket test failed: ${e.message}`);
    }
}

// Test function to check Socket.IO connection
function testSocketConnection() {
    console.log('Testing Socket.IO connection...');

    if (typeof io === 'undefined') {
        displayStatus('error', 'Socket.IO library not loaded');
        return;
    }

    // Connect to the socket server
    const socketUrl = window.location.protocol + '//' + window.location.hostname + ':6002';
    displayStatus('info', `Connecting to Socket.IO server at ${socketUrl}`);

    // Use the global socket variable
    socket = io(socketUrl, {
        transports: ['websocket', 'polling'],
        reconnection: true,
        reconnectionAttempts: 10,
        reconnectionDelay: 1000,
        timeout: 20000
    });

    // Connection events
    socket.on('connect', () => {
        displayStatus('success', `Connected to Socket.IO server with ID: ${socket.id}`);

        // Subscribe to the admin notifications channel
        socket.emit('subscribe', 'admin-notifications');
        displayStatus('info', 'Subscribed to admin-notifications channel');

        // Debug which events we're listening for
        const eventNames = socket._callbacks ? Object.keys(socket._callbacks).filter(key => key.startsWith('$')) : [];
        console.log('Listening for these events:', eventNames.map(name => name.substring(1)));
        displayStatus('info', `Listening for ${eventNames.length} events: ${eventNames.map(name => name.substring(1)).join(', ')}`);
    });

    socket.on('connect_error', (error) => {
        displayStatus('error', `Connection error: ${error.message}`);
    });

    socket.on('subscribed', (data) => {
        displayStatus('success', `Successfully subscribed to channel: ${data.channel}`);
    });

        // Function to handle notification display
    function handleNotification(data, eventName) {
        console.log(`Real-time notification received (${eventName}):`, data);

        let message = 'New notification received';

        // Enhanced display of notification data
        if (data && data.notification) {
            message = `New notification: ${data.notification.message || 'No message'} (ID: ${data.notification.id})`;
        }

        displayStatus('event', `${message} (via ${eventName})`);

        // Create a visual alert for the notification
        const alertBox = document.createElement('div');
        alertBox.className = 'alert alert-primary alert-dismissible fade show';
        alertBox.innerHTML = `
            <strong>New Notification!</strong>
            ${data.notification ? data.notification.message : 'A new notification has arrived'}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;

        // Insert at top of page
        const container = document.getElementById('socket-test-container');
        container.insertBefore(alertBox, container.firstChild);

        // Auto-dismiss after 10 seconds
        setTimeout(() => {
            alertBox.classList.remove('show');
        }, 10000);
    }

    // Listen for different event names
    socket.on('new-reservation', (data) => handleNotification(data, 'new-reservation'));
    socket.on('App\\Events\\NewReservationNotification', (data) => handleNotification(data, 'App\\Events\\NewReservationNotification'));
    socket.on('notification', (data) => handleNotification(data, 'notification'));

    // Debug received messages on any channel
    socket.onAny((eventName, ...args) => {
        console.log(`Received event: ${eventName}`, args);
        displayStatus('info', `Received event: ${eventName}`);
    });

    socket.on('disconnect', (reason) => {
        displayStatus('warning', `Disconnected from server. Reason: ${reason}`);
    });

    // Add button to send test event
    const testButton = document.createElement('button');
    testButton.textContent = 'Send Test Notification';
    testButton.className = 'btn btn-primary mt-3';
    testButton.onclick = sendTestNotification;
    document.getElementById('socket-test-container').appendChild(testButton);

    // Add button for direct socket test (bypass Laravel)
    const directTestButton = document.createElement('button');
    directTestButton.textContent = 'Direct Socket Test';
    directTestButton.className = 'btn btn-success mt-3 ms-2';
    directTestButton.onclick = testDirectSocketMessage;
    document.getElementById('socket-test-container').appendChild(directTestButton);
}

// Get CSRF token safely
function getCSRFToken() {
    const token = document.querySelector('meta[name="csrf-token"]');
    if (!token) {
        displayStatus('error', 'CSRF token not found! Make sure the meta tag exists.');
        console.error('CSRF token meta tag not found');
        return null;
    }
    return token.getAttribute('content');
}

// Send a test notification via the server API
function sendTestNotification() {
    displayStatus('info', 'Sending test notification request...');
    console.log('Initiating test notification request');

    // Check for CSRF token
    const csrfToken = getCSRFToken();
    if (!csrfToken) return;

    // Get the correct URL
    const baseUrl = document.querySelector('meta[name="base-url"]')?.getAttribute('content') || '';
    const notificationUrl = `${baseUrl}/admin/send-test-notification`;

    // Log the full URL for debugging
    console.log(`Full notification URL: ${notificationUrl}`);

    console.log(`Sending request to: ${notificationUrl}`);
    displayStatus('info', `Request URL: ${notificationUrl}`);

    fetch(notificationUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'  // Add this to tell Laravel we want JSON response
        },
        // Add empty body to ensure proper POST request
        body: JSON.stringify({})
    })
    .then(async response => {
        // Get raw response for debugging
        const text = await response.text();

        // Add more detailed debugging for raw response
        console.log('Response status:', response.status);
        console.log('Response headers:', [...response.headers.entries()]);
        console.log('Raw server response (first 1000 chars):', text.substring(0, 1000));

        // Display exact character codes to debug invisible characters
        const firstChars = text.substring(0, 10);
        console.log('First 10 characters (char codes):',
            Array.from(firstChars).map(c => `${c} (${c.charCodeAt(0)})`));

        // Check content type
        const contentType = response.headers.get('content-type');
        console.log('Content-Type:', contentType);

        try {
            // Try to parse JSON response
            const jsonData = JSON.parse(text);
            return jsonData;
        } catch (e) {
            console.error('JSON parse error:', e);
            displayStatus('error', `Error parsing response: ${e.message}`);
            displayStatus('error', `Response status: ${response.status}`);
            displayStatus('error', `Content-Type: ${contentType}`);
            displayStatus('error', `Raw response: ${text.substring(0, 300).replace(/</g, '&lt;').replace(/>/g, '&gt;')}${text.length > 300 ? '...' : ''}`);
            throw new Error('Invalid JSON response from server');
        }
    })
    .then(data => {
        console.log('Parsed response data:', data);

        if (data && data.success) {
            let message = `Test notification sent successfully: ${data.message || ''}`;

            // Handle notification object safely
            if (data.notification) {
                message += ` (ID: ${data.notification.id})`;
            }

            displayStatus('success', message);
        } else {
            displayStatus('error', `Error: ${data.message || 'Unknown error'}`);
        }
    })
    .catch(error => {
        console.error('Request error:', error);
        displayStatus('error', `Error sending test notification: ${error.message}`);
    });
}

// Helper function to display status messages
function displayStatus(type, message) {
    const container = document.getElementById('socket-test-log');
    if (!container) return;

    const entry = document.createElement('div');
    entry.className = `alert alert-${
        type === 'success' ? 'success' :
        type === 'error' ? 'danger' :
        type === 'warning' ? 'warning' :
        type === 'event' ? 'info' : 'secondary'
    } mb-2`;

    entry.innerHTML = `
        <div class="d-flex">
            <div class="me-2">[${new Date().toLocaleTimeString()}]</div>
            <div>${message}</div>
        </div>
    `;

    container.appendChild(entry);

    // Auto-scroll to bottom
    container.scrollTop = container.scrollHeight;
}

// Initialize when DOM is fully loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, testing socket connection');

    // Create test UI if it doesn't exist
    if (!document.getElementById('socket-test-container')) {
        const container = document.createElement('div');
        container.id = 'socket-test-container';
        container.className = 'p-4 bg-light rounded shadow-sm position-fixed';
        container.style.bottom = '20px';
        container.style.right = '20px';
        container.style.zIndex = '9999';
        container.style.maxWidth = '500px';
        container.style.maxHeight = '80vh';
        container.style.overflowY = 'auto';

        container.innerHTML = `
            <h4 class="mb-3">Socket.IO Connection Tester</h4>
            <div id="socket-test-log" style="max-height: 300px; overflow-y: auto;"></div>
        `;

        document.body.appendChild(container);
    }

    // Start test
    testSocketConnection();
});
