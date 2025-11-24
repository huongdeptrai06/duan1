<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập Tài Khoản</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #ff8c00;
            background-image: radial-gradient(circle at 10% 20%, #ff8c00 0%, #ffa500 50%, #ff6347 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
        }
        .login-card {
            background-color: white;
            padding: 40px 60px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 480px;
        }
        .form-control { border-radius: 8px; padding: 10px; }
        .btn-custom {
            background-color: #ff6347;
            border-color: #ff6347;
            color: white;
            padding: 10px;
            font-size: 1.1rem;
            border-radius: 8px;
            width: 100%;
        }
        .btn-custom:hover { background-color: #e5533c; border-color: #e5533c; }
        .form-check-input:checked { background-color: #ff6347; border-color: #ff6347; }
    </style>
</head>
<body>

<div class="login-card">
    <h3 class="text-center mb-1 fw-bold">Đăng Nhập Tài Khoản</h3>
    <p class="text-center text-muted mb-4" style="font-size: 0.9rem;">Vui lòng sử dụng tài khoản đã được cấp để đăng nhập</p>

    <form method="POST" action="process_login.php">
        <div class="mb-3">
            <label for="emailInput" class="form-label small text-muted">Email</label>
            <input type="email" class="form-control" id="emailInput" name="email" placeholder="anhnd120@fpt.edu.vn" value="anhnd120@fpt.edu.vn">
        </div>

        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center">
                <label for="passwordInput" class="form-label small text-muted mb-0">Mật khẩu</label>
                <a href="#" class="small text-decoration-none" style="color: #ff6347;">Quên mật khẩu?</a>
            </div>
            <input type="password" class="form-control" id="passwordInput" name="password" placeholder="••••••••" value="12345678">
        </div>

        <div class="mb-4 form-check">
            <input type="checkbox" class="form-check-input" id="rememberMe" name="remember_me" checked>
            <label class="form-check-label small text-muted" for="rememberMe">Ghi nhớ đăng nhập</label>
        </div>

        <button type="submit" class="btn btn-custom mb-4">Sign In</button>

        <p class="text-center small">
            Bạn không có tài khoản? <a href="#" class="text-decoration-none" style="color: #ff6347; font-weight: bold;">Liên hệ với quản trị viên</a>
        </p>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>