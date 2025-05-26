/**
 * Walk-in Transaction Management Script
 *
 * Handles all client-side functionality for the walk-in page, including:
 * - Table detail modal display
 * - Countdown timers for active sessions
 * - Availability checking before booking
 * - Table status updates
 */

/**
 * Format durasi dari jam desimal menjadi format jam dan menit
 * @param {number} durationInHours - Durasi dalam jam (desimal)
 * @returns {string} - Durasi yang sudah diformat
 */
function formatDuration(durationInHours) {
    if (isNaN(durationInHours)) return "Format waktu tidak valid";

    const hours = Math.floor(durationInHours);
    const minutes = Math.round((durationInHours - hours) * 60);

    if (hours === 0) {
        return `${minutes} menit`;
    } else if (minutes === 0) {
        return `${hours} jam`;
    } else {
        return `${hours} jam ${minutes} menit`;
    }
}

document.addEventListener("DOMContentLoaded", function () {
    console.log("DOM content loaded - initializing walkin.js");

    // DOM Elements
    const elements = {
        tableItems: document.querySelectorAll(".table-clickable"),
        tableDetailsModal: document.getElementById("tableDetailsModal"),
        tableDetailsContent: document.getElementById("tableDetailsContent"),
        selectedDate: document.getElementById("date"),
    };

    // Initialize components
    initializeTableDetailHandlers();
    initializeTimeSlotLinks();
    initializeCountdownTimers();

    /**
     * Initialize table detail event handlers
     */
    function initializeTableDetailHandlers() {
        // Add click handler to all table items
        elements.tableItems.forEach((item) => {
            item.addEventListener("click", function (e) {
                // Prevent click if target is a button or link (for the booking button)
                if (
                    e.target.tagName === "BUTTON" ||
                    e.target.tagName === "A" ||
                    e.target.closest("button") ||
                    e.target.closest("a")
                ) {
                    return;
                }

                const tableId = this.getAttribute("data-table-id");
                const date = elements.selectedDate.value;

                loadTableDetails(tableId, date);
            });
        });
    }

    /**
     * Initialize countdown timers for active tables
     */
    function initializeCountdownTimers() {
        // Ensure walkinData is available from the page
        if (
            typeof walkinData === "undefined" ||
            !walkinData.transactionEndTimes
        ) {
            console.error("No transaction end times data available");
            return;
        }

        console.log("Transaction end times:", walkinData.transactionEndTimes);

        const transactionEndTimes = walkinData.transactionEndTimes;
        const selectedDate = walkinData.selectedDate;
        const now = new Date();

        // Loop through all transactions with end times
        for (const tableId in transactionEndTimes) {
            console.log(`Initializing countdown for table ${tableId}`);

            const endTimestamp = transactionEndTimes[tableId];
            console.log(`End timestamp for table ${tableId}:`, endTimestamp);

            const endTime = new Date(endTimestamp);
            console.log(`End time Date object:`, endTime);

            const countdownElement = document.getElementById(
                `countdown-${tableId}`
            );

            if (!countdownElement) {
                console.error(`Countdown element for table ${tableId} not found`);
                continue;
            }

            console.log(`Found countdown element for table ${tableId}:`, countdownElement);

            // Find the corresponding start time from existing transactions
            let startTime = null;
            let transactionDate = selectedDate;

            // Look for the transaction in existingTransactions if available
            if (window.existingTransactions) {
                for (const transaction of window.existingTransactions) {
                    if (
                        transaction.billard_table_id === tableId &&
                        transaction.status === "in_progress"
                    ) {
                        // Get the start time
                        if (transaction.start_time) {
                            const [hours, minutes] = transaction.start_time
                                .split(":")
                                .map(Number);
                            startTime = new Date();
                            startTime.setHours(hours, minutes, 0, 0);

                            // If transaction has a date property, use it
                            if (transaction.date) {
                                transactionDate = transaction.date;
                            }
                            break;
                        }
                    }
                }
            }

            // Create transaction dates for comparison
            const transactionDateObj = new Date(transactionDate);
            const todayDate = new Date();
            todayDate.setHours(0, 0, 0, 0);
            transactionDateObj.setHours(0, 0, 0, 0);

            // Check if transaction date is in the future
            const isTransactionDateFuture = transactionDateObj > todayDate;

            // Check if start time is in the future (for today's transactions)
            const isStartTimeFuture = startTime ? startTime > now : false;

            // If transaction is in the future (either different date or future start time today)
            if (isTransactionDateFuture || isStartTimeFuture) {
                // For future transactions, show "Mulai dalam: XX:XX:XX" instead of countdown
                countdownElement.innerHTML =
                    '<span class="text-primary">Belum dimulai</span>';

                // If we have start time, show time until start
                if (startTime && !isTransactionDateFuture) {
                    const updateStartCountdown = function () {
                        const currentTime = new Date();
                        const diffMs = startTime - currentTime;

                        if (diffMs <= 0) {
                            // Time to start, refresh the page
                            location.reload();
                            return;
                        }

                        // Calculate hours, minutes, seconds until start
                        const hours = Math.floor(diffMs / (1000 * 60 * 60));
                        const minutes = Math.floor(
                            (diffMs % (1000 * 60 * 60)) / (1000 * 60)
                        );
                        const seconds = Math.floor(
                            (diffMs % (1000 * 60)) / 1000
                        );

                        // Format and display
                        countdownElement.innerHTML = `<span class="text-primary">Mulai dalam: ${String(
                            hours
                        ).padStart(2, "0")}:${String(minutes).padStart(
                            2,
                            "0"
                        )}:${String(seconds).padStart(2, "0")}</span>`;
                    };

                    // Initial update
                    updateStartCountdown();

                    // Set interval for updates
                    setInterval(updateStartCountdown, 1000);
                }

                continue; // Skip the regular countdown for future transactions
            }

            // For current/active transactions, show the regular countdown
            const updateCountdown = function () {
                try {
                    const currentTime = new Date();
                    const diff = endTime - currentTime;

                    if (diff <= 0) {
                        // Time's up
                        countdownElement.textContent = "Waktu habis";
                        countdownElement.className = "countdown critical";

                        // Reload the page after 5 seconds
                        setTimeout(() => {
                            location.reload();
                        }, 5000);
                        return;
                    }

                    // Calculate hours, minutes, seconds
                    const hours = Math.floor(diff / (1000 * 60 * 60));
                    const minutes = Math.floor(
                        (diff % (1000 * 60 * 60)) / (1000 * 60)
                    );
                    const seconds = Math.floor((diff % (1000 * 60)) / 1000);

                    // Format and display
                    countdownElement.textContent = [
                        String(hours).padStart(2, "0"),
                        String(minutes).padStart(2, "0"),
                        String(seconds).padStart(2, "0"),
                    ].join(":");

                    // Add visual indication based on remaining time
                    if (hours === 0 && minutes <= 5) {
                        countdownElement.className = "countdown critical";
                    } else if (hours === 0 && minutes <= 15) {
                        countdownElement.className = "countdown warning";
                    } else {
                        countdownElement.className = "countdown";
                    }
                } catch (error) {
                    console.error("Error updating countdown:", error);
                    countdownElement.textContent = "Error waktu";
                }
            };

            // Initial update
            updateCountdown();

            // Set interval for updates
            setInterval(updateCountdown, 1000);
        }
    }

    /**
     * Initialize slot time link handlers to prevent direct navigation
     * and check availability first
     */
    function initializeTimeSlotLinks() {
        // Find all slot time links
        const slotTimeLinks = document.querySelectorAll(".slot-time-link");

        slotTimeLinks.forEach((link) => {
            link.addEventListener("click", function (e) {
                e.preventDefault();

                // Get original URL
                const originalUrl = this.getAttribute("href");

                // Parse URL and parameters
                const url = new URL(originalUrl, window.location.origin);
                const tableId = url.searchParams.get("table_id");
                const date = url.searchParams.get("date");
                const startTime = url.searchParams.get("start_time");

                // Check availability first
                checkTimeSlotAvailability(tableId, date, startTime, 1)
                    .then((response) => {
                        if (response.success && response.data.is_available) {
                            // If available, proceed to create page
                            window.location.href = originalUrl;
                        } else {
                            // If not available, show error message
                            const errorMsg = response.data.conflict_info
                                ? `Slot waktu ini telah dibooking oleh ${response.data.conflict_info.customer} dari ${response.data.conflict_info.start_time} sampai ${response.data.conflict_info.end_time}`
                                : "Slot waktu ini tidak tersedia";

                            if (typeof toastr !== "undefined") {
                                toastr.error(errorMsg);
                            } else {
                                alert(errorMsg);
                            }
                        }
                    })
                    .catch((error) => {
                        console.error("Error checking availability:", error);

                        if (typeof toastr !== "undefined") {
                            toastr.error(
                                "Terjadi kesalahan saat memeriksa ketersediaan. Silakan coba lagi."
                            );
                        } else {
                            alert(
                                "Terjadi kesalahan saat memeriksa ketersediaan. Silakan coba lagi."
                            );
                        }
                    });
            });
        });
    }

    /**
     * Load table details via AJAX
     */
    function loadTableDetails(tableId, date) {
        const baseUrl =
            document
                .querySelector('meta[name="base-url"]')
                ?.getAttribute("content") || "";

        // Show loading state in modal
        elements.tableDetailsContent.innerHTML = `
            <div class="text-center p-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Memuat detail meja...</p>
            </div>
        `;

        // Show modal while loading
        const tableDetailsModal = new bootstrap.Modal(
            elements.tableDetailsModal
        );
        tableDetailsModal.show();

        // Fetch table details
        fetch(
            `${baseUrl}/admin/walkin/table-details?table_id=${tableId}&date=${date}`,
            {
                headers: {
                    Accept: "application/json",
                },
            }
        )
            .then((response) => {
                if (!response.ok) {
                    throw new Error(`Server error: ${response.status}`);
                }
                return response.json();
            })
            .then((data) => {
                if (data.success) {
                    displayTableDetails(data.data, date);
                } else {
                    throw new Error(
                        data.message || "Gagal memuat detail meja."
                    );
                }
            })
            .catch((error) => {
                console.error("Error fetching table details:", error);

                elements.tableDetailsContent.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    Error: ${
                        error.message ||
                        "Terjadi kesalahan saat memuat detail meja."
                    }
                </div>
            `;
            });
    }

    /**
     * Display table details in modal
     */
    function displayTableDetails(data, date) {
        const baseUrl =
            document
                .querySelector('meta[name="base-url"]')
                ?.getAttribute("content") || "";
        const tableInfo = data.table_info;
        const bookedSessions = data.booked_sessions;
        const freeSlots = data.free_slots;
        const currentTime =
            data.current_time || new Date().toTimeString().substring(0, 5);

        let html = `
        <div class="card mb-3">
            <div class="card-header bg-primary text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Meja ${tableInfo.table_number}</h5>
                    <span class="badge bg-${getStatusBadgeClass(
                        tableInfo.status
                    )}">
                        ${formatStatus(tableInfo.status)}
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Ruangan:</strong> ${tableInfo.room}</p>
                        <p><strong>Brand:</strong> ${tableInfo.brand}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Total Durasi Terpakai:</strong> ${
                            tableInfo.total_hours_used
                        } jam</p>
                        <p><strong>Waktu Saat Ini:</strong> ${currentTime}</p>
                    </div>
                </div>
            </div>
        </div>
    `;

        // Booked Sessions Section
        html += `<h5 class="border-bottom pb-2 mb-3"><i class="fas fa-calendar-check me-2"></i> Sesi Terpesan</h5>`;

        if (bookedSessions.length === 0) {
            html += `
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                Tidak ada sesi terpesan untuk hari ini.
            </div>
        `;
        } else {
            html += `
            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Customer</th>
                            <th>Waktu</th>
                            <th>Durasi</th>
                            <th>Status</th>
                            <th>Info</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

            bookedSessions.forEach((session) => {
                const isCurrentSession = session.is_current;
                const rowClass = isCurrentSession
                    ? "table-primary"
                    : session.status === "pending"
                    ? "table-warning"
                    : "";

                html += `
                <tr class="${rowClass}">
                    <td><i class="fas fa-user me-1"></i> ${
                        session.customer
                    }</td>
                    <td><i class="fas fa-clock me-1"></i> ${
                        session.start_time
                    } - ${session.end_time}</td>
                    <td>${formatDuration(parseFloat(session.duration))}</td>
                    <td><span class="badge bg-${getSessionBadgeClass(
                        session.status
                    )}">${session.status_text}</span></td>
                    <td>
            `;

                // Current time for comparison
                const now = new Date();
                const [currentHour, currentMinute] = currentTime
                    .split(":")
                    .map(Number);
                const [startHour, startMinute] = session.start_time
                    .split(":")
                    .map(Number);

                // Convert to minutes for easy comparison
                const currentTimeMinutes = currentHour * 60 + currentMinute;
                const startTimeMinutes = startHour * 60 + startMinute;

                // Check if this is a future session (start time is after current time)
                const isFutureSession = startTimeMinutes > currentTimeMinutes;

                if (session.remaining_time && !isFutureSession) {
                    // Show remaining time for active sessions
                    html += `<small class="text-danger"><i class="fas fa-hourglass-half me-1"></i> Sisa: ${session.remaining_time}</small>`;
                } else if (isFutureSession) {
                    // Calculate time until start for future sessions
                    const diffMinutes = startTimeMinutes - currentTimeMinutes;
                    const diffHours = Math.floor(diffMinutes / 60);
                    const remainingMinutes = diffMinutes % 60;

                    html += `<small class="text-info"><i class="fas fa-clock me-1"></i> Mulai dalam: ${diffHours}h ${remainingMinutes}m</small>`;
                }

                html += `
                    </td>
                </tr>
            `;
            });

            html += `
                    </tbody>
                </table>
            </div>
        `;
        }

        // Free Slots Section
        html += `<h5 class="border-bottom pb-2 mb-3 mt-4"><i class="fas fa-calendar-alt me-2"></i> Waktu Senggang</h5>`;

        if (freeSlots.length === 0) {
            html += `
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Tidak ada waktu senggang tersedia untuk hari ini.
            </div>
        `;
        } else {
            html += `
            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead class="table-light">
                        <tr>
                           <th>Waktu</th>
                            <th>Durasi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

            const now = new Date();
            const [currentHours, currentMinutes] = currentTime
                .split(":")
                .map(Number);
            now.setHours(currentHours, currentMinutes, 0);

            freeSlots.forEach((slot) => {
                const [startHours, startMinutes] = slot.start_time
                    .split(":")
                    .map(Number);
                const slotStartDate = new Date();
                slotStartDate.setHours(startHours, startMinutes, 0);

                // Tentukan apakah slot waktu ini sudah lewat
                const isPastSlot = slotStartDate < now;
                const rowClass = isPastSlot ? "text-muted" : "";

                html += `
                <tr class="${rowClass}">
                    <td><i class="fas fa-clock me-1"></i> ${slot.start_time} - ${slot.end_time}</td>
                    <td>${formatDuration(parseFloat(slot.duration))}</td>
                    <td>
            `;

                if (isPastSlot) {
                    html += `<span class="badge bg-secondary"><i class="fas fa-ban me-1"></i> Sudah lewat</span>`;
                } else {
                    html += `
                    <a href="${baseUrl}/admin/walkin/create?table_id=${tableInfo.id}&date=${date}&start_time=${slot.start_time}"
                       class="btn btn-sm btn-primary slot-time-link">
                        <i class="fas fa-check me-1"></i> Gunakan
                    </a>
                `;
                }

                html += `
                    </td>
                </tr>
            `;
            });

            html += `
                    </tbody>
                </table>
            </div>
        `;
        }

        // Actions Section
        html += `
        <div class="mt-4 text-center">
            <a href="${baseUrl}/admin/walkin/create?table_id=${tableInfo.id}&date=${date}"
               class="btn btn-success">
                <i class="fas fa-plus-circle me-1"></i> Buat Transaksi Baru
            </a>
        </div>
    `;

        elements.tableDetailsContent.innerHTML = html;

        // Add event listeners for slot time links in the detail modal
        document
            .querySelectorAll("#tableDetailsContent .slot-time-link")
            .forEach((link) => {
                link.addEventListener("click", function (e) {
                    e.preventDefault();

                    // Get original URL
                    const originalUrl = this.getAttribute("href");

                    // Parse URL and parameters
                    const url = new URL(originalUrl, window.location.origin);
                    const tableId = url.searchParams.get("table_id");
                    const date = url.searchParams.get("date");
                    const startTime = url.searchParams.get("start_time");

                    // Check availability first
                    checkTimeSlotAvailability(tableId, date, startTime, 1)
                        .then((response) => {
                            if (
                                response.success &&
                                response.data.is_available
                            ) {
                                // If available, proceed to create page
                                window.location.href = originalUrl;
                            } else {
                                // If not available, show error message
                                const errorMsg = response.data.conflict_info
                                    ? `Slot waktu ini telah dibooking oleh ${response.data.conflict_info.customer} dari ${response.data.conflict_info.start_time} sampai ${response.data.conflict_info.end_time}`
                                    : "Slot waktu ini tidak tersedia";

                                if (typeof toastr !== "undefined") {
                                    toastr.error(errorMsg);
                                } else {
                                    alert(errorMsg);
                                }
                            }
                        })
                        .catch((error) => {
                            console.error(
                                "Error checking availability:",
                                error
                            );

                            if (typeof toastr !== "undefined") {
                                toastr.error(
                                    "Terjadi kesalahan saat memeriksa ketersediaan. Silakan coba lagi."
                                );
                            } else {
                                alert(
                                    "Terjadi kesalahan saat memeriksa ketersediaan. Silakan coba lagi."
                                );
                            }
                        });
                });
            });
    }

    /**
     * Check availability of a time slot via AJAX
     * @param {string} tableId - ID of the billiard table
     * @param {string} date - Date in Y-m-d format
     * @param {string} startTime - Start time in H:i format
     * @param {number} duration - Duration in hours
     * @returns {Promise} - Promise that resolves to the availability response
     */
    function checkTimeSlotAvailability(tableId, date, startTime, duration) {
        const baseUrl =
            document
                .querySelector('meta[name="base-url"]')
                ?.getAttribute("content") || "";

        // Ensure time format is correct (H:i)
        const formattedStartTime = startTime.includes(":")
            ? startTime
            : `${startTime}:00`;

        // PERUBAHAN: Ganti '/api/check-availability' menjadi '/walkin/check-availability'
        return fetch(`${baseUrl}/walkin/check-availability`, {
            method: "POST", // Anda memiliki route POST untuk endpoint ini
            headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
                "X-CSRF-TOKEN": document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute("content"),
            },
            body: JSON.stringify({
                billard_table_id: tableId,
                date: date,
                start_time: formattedStartTime,
                duration: duration,
            }),
        }).then((response) => {
            if (!response.ok) {
                throw new Error(`Server error: ${response.status}`);
            }
            return response.json();
        });
    }

    /**
     * Helper function to get badge class for table status
     */
    function getStatusBadgeClass(status) {
        const classMap = {
            available: "success",
            in_use: "warning",
            maintenance: "danger",
        };
        return classMap[status] || "secondary";
    }

    /**
     * Helper function to get badge class for session status
     */
    function getSessionBadgeClass(status) {
        const classMap = {
            pending: "secondary",
            confirmed: "info",
            in_progress: "primary",
            completed: "success",
            cancelled: "danger",
        };
        return classMap[status] || "secondary";
    }

    /**
     * Format table status for display
     */
    function formatStatus(status) {
        const statusMap = {
            available: "Tersedia",
            in_use: "Sedang Digunakan",
            maintenance: "Dalam Perawatan",
        };
        return statusMap[status] || status;
    }

    /**
     * Format session status for display
     */
    function formatSessionStatus(status) {
        const statusMap = {
            pending: "Menunggu",
            confirmed: "Terkonfirmasi",
            in_progress: "Berlangsung",
            completed: "Selesai",
            cancelled: "Dibatalkan",
        };
        return statusMap[status] || status;
    }
});

/**
 * Check table status via AJAX
 * This function is called periodically to check for table status changes
 */
function checkTableStatus() {
    try {
        const baseUrl = document.querySelector('meta[name="base-url"]')?.getAttribute("content") || "";

        // Tambahkan parameter timestamp untuk menghindari cache
        const timestamp = new Date().getTime();
        const url = `${baseUrl}/check-table-status?_=${timestamp}`;

        fetch(url)
            .then((response) => {
                if (!response.ok) {
                    throw new Error(`Network response was not ok: ${response.status}`);
                }
                return response.json();
            })
            .then((data) => {
                if (data.updated_tables && data.updated_tables.length > 0) {
                    // Show notification
                    if (typeof toastr !== "undefined") {
                        toastr.info(
                            "Status meja telah diperbarui. Halaman akan dimuat ulang."
                        );
                    }
                    setTimeout(() => location.reload(), 2000);
                }
            })
            .catch((error) => {
                console.error("Error checking table status:", error);
            });
    } catch (error) {
        console.error("Error in checkTableStatus function:", error);
    }
}
