(function () {
    // Private scope
    let snapToken; // Declare snapToken in a wider scope

    /**
     * Submit walk-in form
     */
    function submitWalkInForm() {
        // Log lebih detail
        console.log(
            "Payment Method:",
            document.getElementById("payment_method").value
        );
        console.log("Full Response Data:", data);
        console.log("Midtrans Snap Token:", snapToken);

        // Tambahkan pengecekan tipe dan panjang token
        console.log("snapToken type:", typeof snapToken);
        console.log("snapToken length:", snapToken ? snapToken.length : "N/A");

        // Pastikan token tidak kosong dan memiliki panjang yang valid
        if (typeof snapToken === "string" && snapToken.trim() !== "") {
            console.log("Token valid, melanjutkan pembayaran");
            // Lanjutkan proses pembayaran
        } else {
            console.error("Token pembayaran invalid");
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "Token pembayaran tidak valid",
            });
        }

        const form = document.getElementById("walkInForm");
        const formData = new FormData(form);
        const paymentMethod = document.getElementById("payment_method").value;

        // Tampilkan loading
        Swal.fire({
            title: "Memproses Pembayaran...",
            html: '<div class="spinner-border text-primary" role="status"></div>',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            },
        });

        fetch("{{ route('walkin.store') }}", {
            method: "POST",
            body: formData,
            headers: {
                Accept: "application/json",
                "X-CSRF-TOKEN": document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute("content"),
            },
        })
            .then((response) => {
                if (!response.ok) {
                    return response.json().then((err) => {
                        throw new Error(
                            err.message ||
                                "Terjadi kesalahan saat memproses permintaan."
                        );
                    });
                }
                return response.json();
            })
            .then((data) => {
                console.log("Response Data:", data);

                if (data.status === "success") {
                    snapToken =
                        data.midtrans_snap_token ||
                        data.payment?.midtrans_snap_token;

                    // =======================================================
                    //  **CRUCIAL: VERIFY snapToken VALUE HERE**
                    // =======================================================
                    console.log("snapToken:", snapToken);

                    // =======================================================

                    if (paymentMethod === "cash") {
                        Swal.fire({
                            icon: "success",
                            title: "Berhasil!",
                            text: "Transaksi berhasil dibuat dengan metode pembayaran cash.",
                        }).then(() => {
                            window.location.href =
                                "{{ route('walkin.index') }}";
                        });
                    } else if (snapToken) {
                        console.log(
                            "Attempting to open Snap with token:",
                            snapToken
                        );
                        console.log(
                            "Snap object exists:",
                            typeof snap !== "undefined"
                        );

                        snap.pay(snapToken, {
                            onSuccess: (result) => {
                                console.log("onSuccess", result);
                                Swal.fire({
                                    icon: "success",
                                    title: "Pembayaran Berhasil!",
                                    text: "Anda akan dialihkan...",
                                    timer: 2000,
                                    showConfirmButton: false,
                                    allowOutsideClick: false,
                                }).then(() => {
                                    window.location.href =
                                        "{{ route('walkin.index') }}";
                                });
                            },
                            onPending: (result) => {
                                console.log("onPending", result);
                                Swal.fire({
                                    icon: "info",
                                    title: "Menunggu Pembayaran",
                                    text: "Silakan selesaikan pembayaran Anda.",
                                }).then(() => {
                                    window.location.href =
                                        "{{ route('walkin.index') }}";
                                });
                            },
                            onError: (result) => {
                                console.error("onError", result);
                                // Tambahkan logging detail error
                                console.error("Snap Payment Error:", result);
                                Swal.fire({
                                    icon: "error",
                                    title: "Pembayaran Gagal",
                                    text: "Terjadi kesalahan saat melakukan pembayaran.",
                                });
                            },
                            onClose: () => {
                                Swal.fire({
                                    icon: "warning",
                                    title: "Pembayaran Dibatalkan",
                                    text: "Anda menutup jendela pembayaran sebelum menyelesaikan.",
                                });
                            },
                        });
                    } else {
                        console.error("Token pembayaran tidak tersedia.");
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: "Token pembayaran tidak tersedia.",
                        });
                    }
                } else {
                    throw new Error(data.message || "Gagal membuat transaksi.");
                }
            })
            .catch((error) => {
                console.error("Error:", error);
                Swal.fire({
                    icon: "error",
                    title: "Oops...",
                    text:
                        error.message ||
                        "Terjadi kesalahan sistem. Silakan coba lagi.",
                });
            });
    }

    /**
     * Load Midtrans Snap.js
     */
    function loadMidtransScript() {
        return new Promise((resolve, reject) => {
            const script = document.createElement("script");
            script.src = "https://app.sandbox.midtrans.com/snap/snap.js";
            script.setAttribute(
                "data-client-key",
                "{{ config('services.midtrans.client_key') }}"
            );
            script.async = true;

            script.onload = () => {
                console.log("Midtrans Snap.js loaded successfully");
                console.log("Snap object:", typeof snap, snap);

                if (typeof snap !== "undefined") {
                    resolve(snap);
                } else {
                    reject(new Error("Snap object not found"));
                }
            };

            script.onerror = (error) => {
                console.error("Failed to load Midtrans Snap.js", error);
                reject(error);
            };

            document.head.appendChild(script);
        });
    }

    // Functions that will be accessible from outside
    window.paymentProcessor = {
        initialize: function () {
            // Panggil fungsi load script saat halaman dimuat
            document.addEventListener("DOMContentLoaded", () => {
                loadMidtransScript()
                    .then((snapObj) => {
                        console.log("Snap.js is ready", snapObj);
                    })
                    .catch((error) => {
                        console.error("Snap.js load error", error);
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: "Gagal memuat sistem pembayaran. Silakan muat ulang halaman.",
                        });
                    });
            });
        },
    };
})();
