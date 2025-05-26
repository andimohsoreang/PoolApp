/**
 * Real-time Notification System
 * Using Socket.IO to receive notifications
 */

// Initialize Echo and Socket connection
const initNotificationSocket = () => {
    // Check if socket.io is available
    if (typeof io === 'undefined') {
        console.error('Socket.IO is not loaded');
        return;
    }

    console.log('Initializing notification socket connection...');

    // Connect to the socket server (port 6002 for socket connections)
    const socketUrl = window.location.protocol + '//' + window.location.hostname + ':6002';
    console.log('Connecting to socket server at:', socketUrl);

    const socket = io(socketUrl, {
        transports: ['websocket', 'polling'],
        reconnection: true,
        reconnectionAttempts: 10,
        reconnectionDelay: 1000,
        reconnectionDelayMax: 5000,
        timeout: 20000,
        autoConnect: true,
        withCredentials: true
    });

    // Connection events
    socket.on('connect_error', (error) => {
        console.error('Socket connection error:', error.message);
        // Try fallback to polling if websocket fails
        if (socket.io.opts.transports[0] === 'websocket') {
            console.log('Fallback to polling transport');
            socket.io.opts.transports = ['polling', 'websocket'];
        }
    });

    socket.on('connect_timeout', () => {
        console.error('Socket connection timeout');
    });

    socket.on('reconnect', (attemptNumber) => {
        console.log('Socket reconnected on attempt:', attemptNumber);
        // Resubscribe to channels after reconnection
        socket.emit('subscribe', 'admin-notifications');
        console.log('Resubscribed to admin-notifications channel');
    });

    socket.on('reconnect_attempt', (attemptNumber) => {
        console.log('Socket reconnection attempt:', attemptNumber);
    });

    socket.on('subscribed', (data) => {
        console.log('Successfully subscribed to channel:', data.channel);
    });

    socket.on('connected', (data) => {
        console.log('Socket connection confirmed:', data);
    });

    // Listen for admin notifications
    socket.on('connect', () => {
        console.log('Connected to notification socket on port 6002 with ID:', socket.id);

        // Subscribe to the admin notifications channel
        socket.emit('subscribe', 'admin-notifications');
        console.log('Subscribed to admin-notifications channel');

        // Listen for new reservation events
        socket.on('new-reservation', (data) => {
            console.log('New reservation notification received:', data);

            // Update notification badge count
            updateNotificationCount();

            // Show notification alert
            showNotificationAlert(data);

            // Add notification to the dropdown if it's visible
            addNotificationToDropdown(data);

            // Play notification sound
            playNotificationSound();

            // Refresh notification list if we're on the notifications page
            if (window.location.pathname.includes('/admin/notifications')) {
                refreshNotificationList(data);
            }
        });
    });

    socket.on('disconnect', (reason) => {
        console.log('Disconnected from notification socket. Reason:', reason);
    });

    socket.on('error', (error) => {
        console.error('Socket error:', error);
    });
};

// Update the notification count badges
const updateNotificationCount = () => {
    // Make an AJAX call to get the current unread count
    console.log('Updating notification count...');

    fetch('/admin/notifications/count')
        .then(response => response.json())
        .then(data => {
            console.log('Notification count response:', data);

            // Update the topbar badge
            const topbarBadge = document.querySelector('.notification-icon');
            if (topbarBadge) {
                topbarBadge.style.display = data.unread > 0 ? 'block' : 'none';
            }

            // Update the dropdown header
            const dropdownHeader = document.querySelector('.dropdown-item-text');
            if (dropdownHeader) {
                dropdownHeader.innerHTML = `Notifications ${data.unread > 0 ? `(${data.unread})` : ''}`;
            }

            // Update sidebar badges
            const sidebarBadge = document.querySelector('#sidebarNotifications .ms-auto');
            if (sidebarBadge && data.unread > 0) {
                sidebarBadge.textContent = data.unread;
                sidebarBadge.style.display = 'inline-block';
            } else if (sidebarBadge) {
                sidebarBadge.style.display = 'none';
            }

            // Update notification counts in the filter sidebar
            const totalBadge = document.querySelector('.list-group-item:nth-child(1) .badge-count');
            const unreadBadge = document.querySelector('.list-group-item:nth-child(2) .badge-count');
            const reservationBadge = document.querySelector('.list-group-item:nth-child(3) .badge-count');

            if (totalBadge && data.total) {
                totalBadge.textContent = data.total;
            }

            if (unreadBadge) {
                unreadBadge.textContent = data.unread;
            }

            if (reservationBadge && data.reservation) {
                reservationBadge.textContent = data.reservation;
            }
        })
        .catch(error => console.error('Error fetching notification count:', error));
};

// Show notification alert
const showNotificationAlert = (data) => {
    console.log('Showing notification alert...');

    // Create the notification element
    const notification = document.createElement('div');
    notification.className = 'toast show';
    notification.role = 'alert';
    notification.setAttribute('aria-live', 'assertive');
    notification.setAttribute('aria-atomic', 'true');
    notification.style.position = 'fixed';
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.zIndex = '9999';
    notification.style.minWidth = '300px';

    notification.innerHTML = `
        <div class="toast-header bg-info text-white">
            <strong class="me-auto">${data.type.charAt(0).toUpperCase() + data.type.slice(1)} Notification</strong>
            <small>${data.created_at}</small>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
            ${data.message}
            <div class="mt-2 pt-2 border-top">
                <a href="/admin/notifications/${data.id}" class="btn btn-sm btn-info">View Details</a>
            </div>
        </div>
    `;

    // Add to the DOM
    document.body.appendChild(notification);

    // Set auto-hide timeout
    setTimeout(() => {
        notification.remove();
    }, 10000);

    // Add click handler for close button
    notification.querySelector('.btn-close').addEventListener('click', () => {
        notification.remove();
    });
};

// Add notification to dropdown
const addNotificationToDropdown = (data) => {
    const notificationMenu = document.querySelector('.notification-menu');

    if (notificationMenu) {
        // Create new notification element
        const notificationItem = document.createElement('a');
        notificationItem.href = `/admin/notifications/${data.id}`;
        notificationItem.className = 'dropdown-item py-3 bg-light';

        notificationItem.innerHTML = `
            <small class="float-end text-muted ps-2">${data.created_at}</small>
            <div class="media">
                <div class="avatar-md bg-info-subtle">
                    <i class="iconoir-calendar font-22 text-info"></i>
                </div>
                <div class="media-body align-self-center ms-2 text-truncate">
                    <h6 class="my-0 fw-semibold text-dark">${data.type.charAt(0).toUpperCase() + data.type.slice(1)}</h6>
                    <small class="text-muted mb-0">${data.message.length > 40 ? data.message.substring(0, 40) + '...' : data.message}</small>
                </div>
            </div>
        `;

        // Insert at the top of the notification menu
        if (notificationMenu.firstChild) {
            notificationMenu.insertBefore(notificationItem, notificationMenu.firstChild);
        } else {
            notificationMenu.appendChild(notificationItem);
        }

        // Remove "No notifications" message if it exists
        const emptyMessage = notificationMenu.querySelector('.dropdown-item.py-3.text-center');
        if (emptyMessage) {
            emptyMessage.remove();
        }
    }
};

// Refresh notification list if on notifications page
const refreshNotificationList = (data) => {
    console.log('Refreshing notification list with new data:', data);

    // Check if we're on the notifications page
    if (!window.location.pathname.includes('/admin/notifications')) {
        console.log('Not on notifications page, skipping refresh');
        return;
    }

    // Check if the notifications list container exists
    const notificationsList = document.getElementById('notifications-list');
    if (!notificationsList) {
        console.log('Notifications list container not found');
        return;
    }

    // Remove empty notification message if it exists
    const emptyMessage = document.getElementById('empty-notification-message');
    if (emptyMessage) {
        emptyMessage.remove();
    }

    // Create a new notification item
    const newNotification = document.createElement('div');
    newNotification.className = 'list-group-item notification-item unread highlight-new';
    newNotification.setAttribute('data-id', data.id);

    // Build the notification HTML content
    let reservationInfo = '';
    if (data.reservation && data.reservation.id) {
        reservationInfo = `
            <div class="mt-2">
                <small class="text-muted">
                    Reservation: <a href="/admin/reservations/show/${data.reservation.id}" class="text-primary">
                        #${data.reservation.code}
                    </a>
                </small>
            </div>
        `;
    }

    newNotification.innerHTML = `
        <div class="d-flex w-100 justify-content-between align-items-center mb-2">
            <div>
                <span class="notification-type bg-${data.type === 'reservation' ? 'info' : (data.type === 'payment' ? 'success' : 'secondary')} text-white">
                    ${data.type.charAt(0).toUpperCase() + data.type.slice(1)}
                </span>
            </div>
            <small class="text-muted">${data.created_at}</small>
        </div>
        <div class="d-flex w-100">
            <div class="flex-grow-1">
                <a href="/admin/notifications/${data.id}" class="text-decoration-none text-dark">
                    <h6 class="mb-1">${data.message}</h6>
                </a>
            </div>
            <div class="ms-3 d-flex">
                <form action="/admin/notifications/${data.id}/mark-read" method="POST" class="me-1">
                    <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').getAttribute('content')}">
                    <button type="submit" class="btn btn-sm btn-outline-primary" title="Mark as read">
                        <i class="iconoir-check"></i>
                    </button>
                </form>
                <form action="/admin/notifications/${data.id}/destroy" method="POST" class="delete-form">
                    <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').getAttribute('content')}">
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                        <i class="iconoir-trash"></i>
                    </button>
                </form>
            </div>
        </div>
        ${reservationInfo}
    `;

    // Insert at the top of the notifications list
    if (notificationsList.firstChild) {
        notificationsList.insertBefore(newNotification, notificationsList.firstChild);
    } else {
        notificationsList.appendChild(newNotification);
    }

    // Play a sound effect
    playNotificationSound();

    // Remove highlight after animation completes
    setTimeout(() => {
        newNotification.classList.remove('highlight-new');
    }, 3000);

    // Update counter in the sidebar
    updateNotificationCountInSidebar();
};

// Helper function to update the notification counters in the sidebar
const updateNotificationCountInSidebar = () => {
    // Make an AJAX call to get the current counts
    fetch('/admin/notifications/count')
        .then(response => response.json())
        .then(data => {
            console.log('Updated notification counts:', data);

            // Update total count
            const totalBadge = document.querySelector('.list-group-item:nth-child(1) .badge-count');
            if (totalBadge && data.total) {
                totalBadge.textContent = data.total;
            }

            // Update unread count
            const unreadBadge = document.querySelector('.list-group-item:nth-child(2) .badge-count');
            if (unreadBadge) {
                unreadBadge.textContent = data.unread;
            }

            // Update reservation count
            const reservationBadge = document.querySelector('.list-group-item:nth-child(3) .badge-count');
            if (reservationBadge && data.reservation) {
                reservationBadge.textContent = data.reservation;
            }
        })
        .catch(error => console.error('Error fetching notification counts:', error));
};

// Play notification sound
const playNotificationSound = () => {
    try {
        const audio = new Audio('/notification-sound.mp3');
        audio.play().catch(err => console.log('Audio play error:', err));
    } catch (err) {
        console.error('Error playing notification sound:', err);
    }
};

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM loaded, checking for notification elements');

    // Check if we're on an admin page
    if (document.querySelector('#sidebarNotifications') || window.location.pathname.includes('/admin/notifications')) {
        console.log('Admin page or notification page detected, initializing notification socket');
        initNotificationSocket();
    }
});
