<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error - Pool Open System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .error-container {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 500px;
            width: 90%;
            margin: 2rem;
            position: relative;
            overflow: hidden;
        }

        .error-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #6246ea, #2563eb);
        }

        .error-code {
            font-size: 8rem;
            font-weight: 800;
            background: linear-gradient(135deg, #6246ea 0%, #2563eb 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin: 0;
            line-height: 1;
        }

        .error-icon {
            font-size: 4rem;
            color: #6246ea;
            margin: 1.5rem 0;
            animation: float 3s ease-in-out infinite;
        }

        .error-title {
            font-size: 1.8rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 1rem;
        }

        .error-message {
            color: #64748b;
            font-size: 1.1rem;
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .error-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }

        .btn {
            padding: 0.8rem 1.5rem;
            font-weight: 500;
            border-radius: 10px;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #6246ea 0%, #2563eb 100%);
            border: none;
            color: white;
        }

        .btn-outline {
            background: white;
            border: 2px solid #6246ea;
            color: #6246ea;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(98, 70, 234, 0.2);
        }

        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }

        @media (max-width: 576px) {
            .error-container {
                padding: 2rem;
            }

            .error-code {
                font-size: 6rem;
            }

            .error-icon {
                font-size: 3rem;
            }

            .error-title {
                font-size: 1.5rem;
            }

            .error-actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="error-container">
        @if($exception->getStatusCode() == 404)
            <h1 class="error-code">404</h1>
            <div class="error-icon">
                <i class="fas fa-search"></i>
            </div>
            <h2 class="error-title">Halaman Tidak Ditemukan</h2>
            <p class="error-message">Maaf, halaman yang Anda cari tidak dapat ditemukan atau telah dipindahkan.</p>
        @elseif($exception->getStatusCode() == 403)
            <h1 class="error-code">403</h1>
            <div class="error-icon">
                <i class="fas fa-lock"></i>
            </div>
            <h2 class="error-title">Akses Ditolak</h2>
            <p class="error-message">Maaf, Anda tidak memiliki izin untuk mengakses halaman ini.</p>
        @else
            <h1 class="error-code">500</h1>
            <div class="error-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h2 class="error-title">Kesalahan Server</h2>
            <p class="error-message">Maaf, terjadi kesalahan pada server. Tim kami sedang menangani masalah ini.</p>
        @endif

        <div class="error-actions">
            <a href="{{ url()->previous() }}" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i>
                Kembali
            </a>
            <a href="{{ route('customer.reservation.index') }}" class="btn btn-primary">
                <i class="fas fa-home"></i>
                Beranda
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>