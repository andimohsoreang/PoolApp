(function () {
    // Private scope

    /**
     * Update the duration text display
     */
    function updateDurationText() {
        if (!elements.duration || !elements.durationText) return;

        const duration = parseInt(elements.duration.value) || 1;
        elements.durationText.textContent = `${duration} jam`;
    }

    /**
     * Update the end time display based on start time and duration
     */
    function updateEndTimeDisplay() {
        if (
            !elements.startTime ||
            !elements.endTimeDisplay ||
            !elements.timeRange
        )
            return;

        const startTime = elements.startTime.value;
        if (!startTime) {
            elements.endTimeDisplay.textContent = "-";
            elements.timeRange.textContent = "-";
            return;
        }

        const duration = parseInt(elements.duration?.value) || 1;
        const [hours, minutes] = startTime.split(":").map(Number);

        // Calculate end time
        const startDate = new Date();
        startDate.setHours(hours, minutes, 0);

        const endDate = new Date(startDate);
        endDate.setHours(endDate.getHours() + duration);

        // Format for display
        const endTimeFormatted = `${String(endDate.getHours()).padStart(
            2,
            "0"
        )}:${String(endDate.getMinutes()).padStart(2, "0")}`;
        elements.endTimeDisplay.textContent = endTimeFormatted;

        // Update time range display
        elements.timeRange.textContent = `${startTime} - ${endTimeFormatted}`;

        // Update visual indicator in the time info text
        const timeInfoText = document.getElementById("time-info-text");
        if (timeInfoText) {
            timeInfoText.textContent = `Jam Mulai: ${startTime}`;
        }
    }

    /**
     * Calculate and update total price
     */
    function updateTotalPrice() {
        if (
            !elements.duration ||
            !elements.pricePerHour ||
            !elements.totalPrice
        )
            return;

        const duration = parseInt(elements.duration.value) || 1;

        // Ensure we have a valid price
        if (isNaN(state.price) || state.price <= 0) {
            state.price = 50000; // Default price
            elements.pricePerHour.textContent = `Rp ${formatNumber(
                state.price
            )}`;
        }

        // Basic calculation (more accurate calculation will come from AJAX)
        const total = Math.round(state.price * duration);
        elements.totalPrice.textContent = `Rp ${formatNumber(total)}`;
    }

    /**
     * Reset availability status
     */
    function resetAvailabilityStatus() {
        if (!elements.availabilityStatus || !elements.submitBtn) return;

        elements.availabilityStatus.innerHTML = "";
        elements.submitBtn.disabled = false;
        state.isAvailable = false;
    }

    /**
     * Show availability status with specific type (success, warning, danger)
     */
    function showAvailabilityStatus(type, message, details = null) {
        if (!elements.availabilityStatus) return;

        let icon = "fas fa-info-circle";
        if (type === "success") icon = "fas fa-check-circle";
        if (type === "warning") icon = "fas fa-exclamation-triangle";
        if (type === "danger") icon = "fas fa-times-circle";

        let html = `
    <div class="alert alert-${type} py-2 mb-0">
        <i class="${icon} me-1"></i> ${message}
    </div>
`;

        if (details) {
            html = `
        <div class="alert alert-${type} py-2 mb-0">
            <i class="${icon} me-1"></i> ${message}<br>
            <small>${details}</small>
        </div>
    `;
        }

        elements.availabilityStatus.innerHTML = html;
    }

    function renderHourlyPrices(hourlyPrices) {
        const priceBreakdownElement =
            document.getElementById("price-breakdown");
        if (priceBreakdownElement) {
            priceBreakdownElement.innerHTML = hourlyPrices
                .map(
                    (hourPrice) => `
    <div class="price-item">
        <span>Jam ${hourPrice.hour}</span>
        <span>Rp ${formatNumber(hourPrice.price)}</span>
        <span class="badge">${hourPrice.day_type}</span>
    </div>
`
                )
                .join("");
        }
    }

    /**
     * Fetch price for the selected time from the server
     */
    function fetchPriceForTime(tableId, date, startTime, duration) {
        fetch(
            `/walkin/calculate-slot-price?billard_table_id=${tableId}&date=${date}&start_time=${startTime}&duration=${duration}`
        )
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    // Tampilkan rincian harga per jam
                    renderHourlyPrices(data.data.hourly_prices);

                    // Update total harga
                    elements.totalPrice.textContent = data.data.formatted_total;

                    // Update rentang waktu
                    elements.timeRange.textContent = `${data.data.start_time} - ${data.data.end_time}`;
                }
            });
    }

    /**
     * Check availability for the selected time and duration
     */
    function checkAvailability(submitAfterCheck = false) {
        if (
            !elements.availabilityStatus ||
            !elements.startTime ||
            !elements.duration
        )
            return;

        const tableId = state.tableId;
        const date = state.date;
        const startTime = elements.startTime.value;
        const duration = elements.duration.value;

        // Validasi input
        if (!startTime) {
            showAvailabilityStatus("warning", "Silakan pilih waktu mulai");
            return;
        }

        if (!duration) {
            showAvailabilityStatus("warning", "Silakan pilih durasi");
            return;
        }

        // Show loading state
        elements.availabilityStatus.innerHTML =
            '<div class="d-flex justify-content-center"><div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div> Memeriksa ketersediaan...</div>';

        // Get base URL from meta tag
        const baseUrl =
            document
                .querySelector('meta[name="base-url"]')
                ?.getAttribute("content") || "";

        fetch(
            `${baseUrl}/walkin/check-availability?billard_table_id=${tableId}&date=${date}&start_time=${startTime}&duration=${duration}`,
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
                    if (response.status === 404) {
                        throw new Error(
                            "Endpoint tidak ditemukan. Pastikan route /walkin/check-availability terdaftar."
                        );
                    } else {
                        return response.text().then((text) => {
                            console.error("Response error text:", text);
                            throw new Error(`Server error: ${response.status}`);
                        });
                    }
                }
                return response.json();
            })
            .then((data) => {
                if (data.success) {
                    state.isAvailable = data.data.is_available;

                    if (state.isAvailable) {
                        // Available - show success and update price
                        showAvailabilityStatus("success", "Tersedia!");

                        // If price estimate is provided, update the display
                        if (data.data.price_estimate && elements.totalPrice) {
                            elements.totalPrice.textContent =
                                data.data.price_estimate;
                        }

                        // Update time range display with the exact times from server
                        if (
                            data.data.start_time &&
                            data.data.end_time &&
                            elements.timeRange &&
                            elements.endTimeDisplay
                        ) {
                            elements.timeRange.textContent = `${data.data.start_time} - ${data.data.end_time}`;
                            elements.endTimeDisplay.textContent =
                                data.data.end_time;
                        }

                        // Enable submit button
                        if (elements.submitBtn)
                            elements.submitBtn.disabled = false;

                        // Submit form if requested
                        if (submitAfterCheck && elements.walkInForm) {
                            // Add a slight delay to allow user to see the success message
                            setTimeout(() => {
                                elements.walkInForm.submit();
                            }, 500);
                        }
                    } else {
                        // Not available - show conflict info
                        const conflict = data.data.conflict_info;
                        if (conflict) {
                            let statusText = formatStatus(conflict.status);
                            const customerName =
                                conflict.customer_name ||
                                conflict.customer ||
                                "Customer";

                            showAvailabilityStatus(
                                "danger",
                                "Tidak tersedia!",
                                `Bentrok dengan reservasi ${customerName} (${conflict.start_time} - ${conflict.end_time})<br>Status: ${statusText}`
                            );
                        } else {
                            showAvailabilityStatus("danger", "Tidak tersedia!");
                        }

                        // Disable submit button
                        if (elements.submitBtn)
                            elements.submitBtn.disabled = true;
                    }
                } else {
                    // Error in validation
                    let errorMessage = "Gagal memeriksa ketersediaan";
                    if (data.message) {
                        errorMessage += ": " + data.message;
                    }

                    showAvailabilityStatus("warning", errorMessage);

                    // Disable submit button
                    if (elements.submitBtn) elements.submitBtn.disabled = true;
                }
            })
            .catch((error) => {
                console.error("Fetch error:", error);

                // Show more detailed error message
                showAvailabilityStatus(
                    "danger",
                    "Terjadi kesalahan saat memeriksa ketersediaan. Silakan coba lagi.",
                    error.message || "Detail error tidak tersedia"
                );

                // Disable submit button
                if (elements.submitBtn) elements.submitBtn.disabled = true;
            });
    }

    // Format number with thousand separators
    function formatNumber(number) {
        if (!number) return "0";
        return new Intl.NumberFormat("id-ID").format(number);
    }

    // Fungsi-fungsi yang ingin diakses dari luar
    window.formHandler = {
        initialize: function (elements, state) {
            // Update initial values
            if (elements.duration) updateDurationText(elements);
            if (elements.startTime) updateEndTimeDisplay(elements);
            if (elements.pricePerHour) updateTotalPrice(elements, state);

            // Add event listeners for form inputs
            if (elements.startTime) {
                elements.startTime.addEventListener('change', function () {
                    updateEndTimeDisplay(elements);
                    resetAvailabilityStatus(elements);

                    // Auto-check availability
                    if (this.value && elements.duration?.value) {
                        checkAvailability(elements, state);
                    }
                });
            }

            if (elements.duration) {
                elements.duration.addEventListener('change', function () {
                    updateDurationText(elements);
                    updateEndTimeDisplay(elements);
                    updateTotalPrice(elements, state);
                    resetAvailabilityStatus(elements);

                    // Auto-check availability
                    if (elements.startTime?.value) {
                        checkAvailability(elements, state);
                    }

                    // Fetch new price
                    if (
                        state.tableId &&
                        state.date &&
                        elements.startTime?.value
                    ) {
                        fetchPriceForTime(elements, state,
                            state.tableId,
                            state.date,
                            elements.startTime.value,
                            this.value
                        );
                    }
                });
            }

            // Add event listener for check availability button
            if (elements.checkAvailabilityBtn) {
                elements.checkAvailabilityBtn.addEventListener(
                    "click",
                    function () {
                        checkAvailability(elements, state);
                    }
                );
            }

            // Add event listener for form submission
            if (elements.submitBtn && elements.walkInForm) {
                elements.submitBtn.addEventListener('click', function (e) {
                    e.preventDefault();

                    // Validasi form
                    if (!elements.userId?.value) {
                        showAvailabilityStatus(elements,
                            'warning',
                            'Silakan pilih customer');
                        return;
                    }

                    if (!elements.startTime?.value) {
                        showAvailabilityStatus(elements,
                            'warning',
                            'Silakan pilih waktu mulai');
                        return;
                    }

                    if (!elements.duration?.value) {
                        showAvailabilityStatus(elements,
                            'warning',
                            'Silakan pilih durasi');
                        return;
                    }

                    // Cek ketersediaan sebelum submit
                    checkAvailability(elements, state, (availability) => {
                        if (availability) {
                            // submitWalkInForm();
                        }
                    });
                });
            }

            // Check availability on initial load if time is set
            if (elements.startTime?.value && elements.checkAvailabilityBtn) {
                // Use setTimeout to ensure DOM is fully loaded
                setTimeout(function () {
                    checkAvailability(elements, state);
                }, 500);
            }
        },
    };
})();
