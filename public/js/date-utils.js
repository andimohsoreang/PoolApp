(function () {
    // Private scope

    /**
     * Format date for display (DD MMM YYYY)
     */
    function formatDisplayDate(date) {
        if (!date) return "";

        const d = new Date(date);
        const options = {
            day: "2-digit",
            month: "short",
            year: "numeric",
        };
        return d.toLocaleDateString("id-ID", options);
    }

    /**
     * Change the selected date by offset days
     */
    function changeDate(elements, state, dayOffset) {
        if (!elements.currentDateDisplay || !elements.formDate) return;

        const currentDate = new Date(state.date);
        currentDate.setDate(currentDate.getDate() + dayOffset);

        // Format the date as YYYY-MM-DD
        const newDate = currentDate.toISOString().split("T")[0];
        state.date = newDate;
        elements.formDate.value = newDate;

        // Update date display
        elements.currentDateDisplay.textContent =
            formatDisplayDate(currentDate);

        // Fetch transactions for new date
        window.scheduleManager.loadData(
            elements,
            state,
            state.tableId,
            newDate
        );

        // Reset availability status
        resetAvailabilityStatus();
    }
    /**
     * Reset availability status
     */
    function resetAvailabilityStatus(elements) {
        if (!elements.availabilityStatus || !elements.submitBtn) return;

        elements.availabilityStatus.innerHTML = "";
        elements.submitBtn.disabled = false;
        state.isAvailable = false;
    }

    window.dateUtils = {
        initialize: function () {},
        changeDate: changeDate,
        formatDisplayDate: formatDisplayDate,
    };
})();
