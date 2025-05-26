(function () {
    // Private scope
    function generateTimeRuler(elements, state) {
        if (!elements.timeRuler) return;

        elements.timeRuler.innerHTML = "";

        // Generate markers for each hour from operating start to operating end
        for (
            let hour = state.operatingHours.start;
            hour <= state.operatingHours.end;
            hour++
        ) {
            const marker = document.createElement("div");
            marker.className = "time-marker";
            marker.textContent = `${hour}:00`;
            elements.timeRuler.appendChild(marker);
        }
    }

    function updateCurrentTimeIndicator(elements, state) {
        if (!elements.currentTimeIndicator || !elements.tableSchedule) return;

        const now = new Date();
        const hours = now.getHours();
        const minutes = now.getMinutes();

        // Only show for current date and during operating hours
        const isToday = state.date === now.toISOString().split("T")[0];
        const isOperatingHours =
            hours >= state.operatingHours.start &&
            hours < state.operatingHours.end;

        if (isToday && isOperatingHours) {
            // Calculate position as percentage of the operating day
            const totalMinutesSinceOpening =
                (hours - state.operatingHours.start) * 60 + minutes;
            const totalOperatingMinutes =
                (state.operatingHours.end - state.operatingHours.start) * 60;
            const positionPercent =
                (totalMinutesSinceOpening / totalOperatingMinutes) * 100;

            elements.currentTimeIndicator.style.left = `${positionPercent}%`;
            elements.currentTimeIndicator.style.display = "block";

            // Add time label to the indicator
            const timeLabel = document.createElement("div");
            timeLabel.className = "time-label";
            timeLabel.textContent = `${String(hours).padStart(2, "0")}:${String(
                minutes
            ).padStart(2, "0")}`;

            // Remove existing label if any
            const existingLabel =
                elements.currentTimeIndicator.querySelector(".time-label");
            if (existingLabel) {
                existingLabel.remove();
            }

            elements.currentTimeIndicator.appendChild(timeLabel);
        } else {
            elements.currentTimeIndicator.style.display = "none";
        }
    }

    function fetchScheduleData(elements, state, tableId, date) {
        if (!elements.tableSchedule || !tableId) return;

        const baseUrl =
            document
                .querySelector('meta[name="base-url"]')
                ?.getAttribute("content") || "";

        // Show loading indicator
        elements.tableSchedule.innerHTML = `
        <div class="text-center py-3">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;

        fetch(
            `${baseUrl}/walkin/get-table-schedule?billard_table_id=${tableId}&date=${date}`,
            {
                method: "GET",
                headers: {
                    Accept: "application/json",
                    "X-CSRF-TOKEN":
                        document
                            .querySelector('meta[name="csrf-token"]')
                            ?.getAttribute("content") || "",
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
                if (data.success && data.data) {
                    // Log semua transaksi yang diterima
                    console.log("Received transactions:", data.data);

                    // Simpan transaksi dalam state
                    state.transactions = data.data;

                    // Render jadwal
                    renderSchedule(elements, state, data.data);
                } else {
                    elements.tableSchedule.innerHTML = `
            <div class="alert alert-info m-3">
                Tidak ada jadwal untuk tanggal ini.
            </div>
        `;
                }
            })
            .catch((error) => {
                console.error("Error fetching schedule data:", error);
                elements.tableSchedule.innerHTML = `
        <div class="alert alert-danger m-3">
            Gagal memuat jadwal: ${error.message}
        </div>
    `;
            });
    }

    function renderSchedule(elements, state, transactions) {
        if (!elements.tableSchedule) return;

        // Clear existing blocks (except current time indicator)
        const existingBlocks =
            elements.tableSchedule.querySelectorAll(".reservation-block");
        existingBlocks.forEach((block) => block.remove());

        // If no transactions, show empty message
        if (!transactions || transactions.length === 0) {
            const emptyNotice = document.createElement("div");
            emptyNotice.className = "text-center text-muted p-3";
            emptyNotice.textContent = "Tidak ada jadwal untuk hari ini";
            elements.tableSchedule.appendChild(emptyNotice);
            return;
        }

        // Current time for determining if transaction is active
        const now = new Date();
        const currentDate = now.toISOString().split("T")[0];
        const currentHour = now.getHours();
        const currentMinute = now.getMinutes();

        // Add blocks for each transaction
        transactions.forEach((transaction) => {
            addReservationBlock(
                elements,
                state,
                transaction,
                now,
                currentDate,
                currentHour,
                currentMinute
            );
        });
    }

    function addReservationBlock(
        elements,
        state,
        transaction,
        now,
        currentDate,
        currentHour,
        currentMinute
    ) {
        if (!elements.tableSchedule) return;

        // Parse times
        const startTime = parseTimeString(transaction.start_time);
        const endTime = transaction.actual_end_time
            ? parseTimeString(transaction.actual_end_time)
            : parseTimeString(transaction.end_time);

        // Skip if invalid times
        if (!startTime || !endTime) return;

        // Create block element
        const block = document.createElement("div");

        // Determine if the transaction is currently active (only mark as active if it's the current date and time is between start and end)
        const isCurrentDate = state.date === currentDate;
        const currentTimeInMinutes = currentHour * 60 + currentMinute;
        const startTimeInMinutes = startTime.hours * 60 + startTime.minutes;
        const endTimeInMinutes = endTime.hours * 60 + endTime.minutes;

        // Transaction is active if it's current date and current time is between start and end
        const isActive =
            isCurrentDate &&
            currentTimeInMinutes >= startTimeInMinutes &&
            currentTimeInMinutes <= endTimeInMinutes;

        // Set class based on status and active state
        if (isActive && transaction.status === "in_progress") {
            block.className = "reservation-block in-progress";
        } else {
            block.className = `reservation-block ${transaction.status}`;
        }

        // Calculate position and width
        const startHour = startTime.hours + startTime.minutes / 60;
        const endHour = endTime.hours + endTime.minutes / 60;
        const startPercent =
            ((startHour - state.operatingHours.start) /
                (state.operatingHours.end - state.operatingHours.start)) *
            100;
        const widthPercent =
            ((endHour - startHour) /
                (state.operatingHours.end - state.operatingHours.start)) *
            100;

        // Set position and size
        block.style.left = `${startPercent}%`;
        block.style.width = `${widthPercent}%`;

        // Add content
        const timeStr = `${formatTimeDisplay(startTime)} - ${formatTimeDisplay(
            endTime
        )}`;
        const customerName =
            transaction.user?.name || transaction.customer_name || "Customer";

        block.innerHTML = `
            <div class="reservation-info">
                <div class="booking-time">${timeStr}</div>
                <div class="booking-title">${customerName}</div>
            </div>
        `;

        // Add tooltip with more details
        block.title = `${customerName}\nWaktu: ${timeStr}\nStatus: ${formatStatus(
            transaction.status
        )}`;

        // Add click event to show details
        block.addEventListener("click", () =>
            showTransactionDetails(transaction)
        );

        // Add to schedule
        elements.tableSchedule.appendChild(block);
    }

    function showTransactionDetails(transaction) {
        if (!transaction) return;

        const startTime = parseTimeString(transaction.start_time);
        const endTime = transaction.actual_end_time
            ? parseTimeString(transaction.actual_end_time)
            : parseTimeString(transaction.end_time);

        const timeStr = `${formatTimeDisplay(startTime)} - ${formatTimeDisplay(
            endTime
        )}`;
        const statusStr = formatStatus(transaction.status);
        const customerName =
            transaction.user?.name || transaction.customer_name || "Customer";

        alert(`
            Detail Transaksi:
            Customer: ${customerName}
            Waktu: ${timeStr}
            Status: ${statusStr}
            Durasi: ${transaction.total_hours} jam
            Total: Rp ${formatNumber(transaction.total_price)}
        `);
    }

    // Fungsi-fungsi utilitas (dipindahkan ke date-utils.js)
    function formatStatus(status) {
        switch (status) {
            case "pending":
                return "Menunggu";
            case "confirmed":
                return "Terkonfirmasi";
            case "in_progress":
                return "Sedang Berlangsung";
            case "completed":
                return "Selesai";
            case "cancelled":
                return "Dibatalkan";
            default:
                return status || "";
        }
    }

    function parseTimeString(timeStr) {
        if (!timeStr) return null;

        const parts = timeStr.toString().split(":");
        if (parts.length < 2) return null;

        return {
            hours: parseInt(parts[0], 10) || 0,
            minutes: parseInt(parts[1], 10) || 0,
        };
    }

    function formatTimeDisplay(time) {
        if (!time) return "--:--";
        return `${String(time.hours).padStart(2, "0")}:${String(
            time.minutes
        ).padStart(2, "0")}`;
    }

    function formatNumber(number) {
        if (!number) return "0";
        return new Intl.NumberFormat("id-ID").format(number);
    }

    // Fungsi-fungsi yang ingin diakses dari luar
    window.scheduleManager = {
        initialize: function (elements, state) {
            generateTimeRuler(elements, state); // Meneruskan elements dan state
            updateCurrentTimeIndicator(elements, state);
            fetchScheduleData(elements, state, state.tableId, state.date);
            renderSchedule(elements, state, state.transactions);
        },
        updateTime: function (elements, state) {
            updateCurrentTimeIndicator(elements, state);
        },
        loadData: function (elements, state, tableId, date) {
            fetchScheduleData(elements, state, tableId, date);
        },
    };
})();
