<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — SiPresensi Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #1a1a2e 0%, #c62828 50%, #e64a19 100%);
            background-attachment: fixed;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', system-ui, sans-serif;
        }

        .login-box {
            width: 100%;
            max-width: 420px;
            background: #fff;
            border-radius: 22px;
            padding: 36px 32px 32px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .login-logo {
            width: 68px;
            height: 68px;
            border-radius: 18px;
            background: linear-gradient(135deg, #f4b846, #e64a19);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            margin: 0 auto 16px;
            box-shadow: 0 4px 20px rgba(244, 184, 70, 0.4);
        }

        .login-title {
            font-size: 22px;
            font-weight: 900;
            color: #c62828;
            text-align: center;
        }

        .login-sub {
            font-size: 13px;
            color: #888;
            text-align: center;
            margin-top: 4px;
            margin-bottom: 28px;
        }

        .form-label {
            font-size: 13px;
            font-weight: 600;
            color: #444;
        }

        .form-control {
            border-radius: 10px;
            border: 2px solid #e8e8e8;
            font-size: 14px;
            padding: 11px 14px;
        }

        .form-control:focus {
            border-color: #c62828;
            box-shadow: 0 0 0 3px rgba(198, 40, 40, 0.12);
        }

        .btn-login {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, #c62828, #e64a19);
            border: none;
            border-radius: 12px;
            color: #fff;
            font-size: 15px;
            font-weight: 800;
            box-shadow: 0 4px 16px rgba(198, 40, 40, 0.35);
            transition: all 0.2s;
        }

        .btn-login:hover {
            transform: translateY(-1px);
            filter: brightness(1.06);
            color: #fff;
        }

        .input-group-text {
            background: #f8f8f8;
            border: 2px solid #e8e8e8;
            border-right: none;
            border-radius: 10px 0 0 10px;
        }

        .input-group .form-control {
            border-left: none;
            border-radius: 0 10px 10px 0;
        }

        .input-group .form-control:focus~.input-group-text,
        .input-group:focus-within .input-group-text {
            border-color: #c62828;
        }
    </style>
</head>

<body>
    <div class="login-box">
        <div class="login-logo">🏆</div>
        <div class="login-title">SiPresensi Admin</div>
        <div class="login-sub">Sistem Manajemen Presensi Perlombaan</div>

        @if ($errors->any())
            <div class="alert alert-danger d-flex align-items-center gap-2 py-2 px-3 mb-3"
                style="border-radius:10px;font-size:13px;">
                <i class="bi bi-exclamation-circle-fill"></i>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('login.submit') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Email</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope" style="color:#c62828;"></i></span>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                        placeholder="admin@email.com" value="{{ old('email') }}" required autofocus>
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock" style="color:#c62828;"></i></span>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required
                        id="pwd-input">
                    <button type="button" class="btn btn-outline-secondary" onclick="togglePwd()"
                        style="border-radius:0 10px 10px 0;border:2px solid #e8e8e8;border-left:none;">
                        <i class="bi bi-eye" id="pwd-eye"></i>
                    </button>
                </div>
            </div>
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember"
                        style="border-color:#c62828;">
                    <label class="form-check-label" for="remember" style="font-size:13px;">Ingat saya</label>
                </div>
            </div>
            <button type="submit" class="btn btn-login">
                <i class="bi bi-box-arrow-in-right me-1"></i> Masuk ke Dashboard
            </button>
        </form>

        <p class="text-center text-muted mt-4" style="font-size:11px;">
            Halaman absensi peserta tersedia via link QR per event.<br>
            Tidak memerlukan login.
        </p>
    </div>

    <script>
        function togglePwd() {
            const inp = document.getElementById('pwd-input');
            const icon = document.getElementById('pwd-eye');
            if (inp.type === 'password') {
                inp.type = 'text';
                icon.className = 'bi bi-eye-slash';
            } else {
                inp.type = 'password';
                icon.className = 'bi bi-eye';
            }
        }
    </script>
</body>

</html>
