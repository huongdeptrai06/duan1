<?php

// Controller xử lý các chức năng liên quan đến xác thực (đăng nhập, đăng xuất)
class AuthController
{
    
    // Hiển thị form đăng nhập
    public function login()
    {
        // Nếu đã đăng nhập rồi thì chuyển về trang home
        if (isLoggedIn()) {
            header('Location: ' . BASE_URL . 'home');
            exit;   
        }

        // Lấy URL redirect nếu có (để quay lại trang đang xem sau khi đăng nhập)
        // Mặc định redirect về trang home
        $redirect = $_GET['redirect'] ?? BASE_URL . 'home';

        // Hiển thị view login
        view('auth.login', [
            'title' => 'Đăng nhập',
            'redirect' => $redirect,
        ]);
    }

    // Xử lý đăng nhập (nhận dữ liệu từ form POST)
    public function checkLogin()
    {
        // Chỉ xử lý khi là POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        // Lấy dữ liệu từ form
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        // Mặc định redirect về trang home sau khi đăng nhập
        $redirect = $_POST['redirect'] ?? BASE_URL . 'home';

        // Validate dữ liệu đầu vào
        $errors = [];

        if (empty($email)) {
            $errors[] = 'Vui lòng nhập email';
        }

        if (empty($password)) {
            $errors[] = 'Vui lòng nhập mật khẩu';
        }

        // Nếu có lỗi validation thì quay lại form login
        if (!empty($errors)) {
            view('auth.login', [
                'title' => 'Đăng nhập',
                'errors' => $errors,
                'email' => $email,
                'redirect' => $redirect,
            ]);
            return;
        }

        // Lấy kết nối database
        $pdo = getDB();

        if ($pdo === null) {
            $errors[] = 'Không thể kết nối tới cơ sở dữ liệu. Vui lòng thử lại sau.';
            view('auth.login', [
                'title' => 'Đăng nhập',
                'errors' => $errors,
                'email' => $email,
                'redirect' => $redirect,
            ]);
            return;
        }

        try {
            $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
            $stmt->execute(['email' => $email]);
            $userData = $stmt->fetch();
        } catch (PDOException $e) {
            error_log('Login query failed: ' . $e->getMessage());
            $errors[] = 'Có lỗi xảy ra khi xử lý đăng nhập. Vui lòng thử lại.';
            view('auth.login', [
                'title' => 'Đăng nhập',
                'errors' => $errors,
                'email' => $email,
                'redirect' => $redirect,
            ]);
            return;
        }

        if (!$userData) {
            $errors[] = 'Email chưa được đăng ký.';
            view('auth.login', [
                'title' => 'Đăng nhập',
                'errors' => $errors,
                'email' => $email,
                'redirect' => $redirect,
            ]);
            return;
        }

        $passwordColumn = array_key_exists('password_hash', $userData) ? 'password_hash' : 'password';
        $storedPassword = $userData[$passwordColumn] ?? '';
        $isPasswordHashed = !empty($storedPassword) && password_get_info($storedPassword)['algo'] !== 0;
        $isValidPassword = $isPasswordHashed
            ? password_verify($password, $storedPassword)
            : hash_equals($storedPassword, $password);

        if (!$isValidPassword) {
            $errors[] = 'Mật khẩu không đúng.';
            view('auth.login', [
                'title' => 'Đăng nhập',
                'errors' => $errors,
                'email' => $email,
                'redirect' => $redirect,
            ]);
            return;
        }

        if (isset($userData['status']) && (int)$userData['status'] === 0) {
            $errors[] = 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên.';
            view('auth.login', [
                'title' => 'Đăng nhập',
                'errors' => $errors,
                'email' => $email,
                'redirect' => $redirect,
            ]);
            return;
        }

        $user = new User([
            'id' => $userData['id'] ?? null,
            'name' => $userData['name'] ?? '',
            'email' => $userData['email'] ?? $email,
            'role' => $userData['role'] ?? 'huong_dan_vien',
            'status' => $userData['status'] ?? 1,
        ]);

        // Đăng nhập thành công: lưu vào session
        loginUser($user);

        // Chuyển hướng về trang được yêu cầu hoặc trang chủ
        header('Location: ' . $redirect);
        exit;
    }

    // Hiển thị form đăng ký tài khoản
    public function register()
    {
        if (isLoggedIn()) {
            header('Location: ' . BASE_URL . 'home');
            exit;
        }

        $redirect = $_GET['redirect'] ?? BASE_URL . 'home';

        view('admin.register', [
            'title' => 'Đăng ký tài khoản',
            'redirect' => $redirect,
            'roles' => $this->getAssignableRoles(),
        ]);
    }

    // Xử lý submit đăng ký
    public function handleRegister()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'register');
            exit;
        }

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $passwordConfirmation = $_POST['password_confirmation'] ?? '';
        $role = $_POST['role'] ?? 'admin';
        $redirect = $_POST['redirect'] ?? BASE_URL . 'home';
        $status = 1;

        $errors = [];

        if ($name === '') {
            $errors[] = 'Vui lòng nhập họ tên.';
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email không hợp lệ.';
        }

        if (strlen($password) < 6) {
            $errors[] = 'Mật khẩu phải có ít nhất 6 ký tự.';
        }

        if ($password !== $passwordConfirmation) {
            $errors[] = 'Xác nhận mật khẩu không khớp.';
        }

        $roles = $this->getAssignableRoles();
        if (!array_key_exists($role, $roles)) {
            $errors[] = 'Vai trò không hợp lệ.';
        }

        $formData = [
            'name' => $name,
            'email' => $email,
            'role' => $role,
        ];

        $pdo = getDB();

        if ($pdo === null) {
            $errors[] = 'Không thể kết nối cơ sở dữ liệu. Vui lòng thử lại.';
        }

        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
                $stmt->execute(['email' => $email]);
                if ($stmt->fetch()) {
                    $errors[] = 'Email này đã được sử dụng.';
                }
            } catch (PDOException $e) {
                error_log('Register check failed: ' . $e->getMessage());
                $errors[] = 'Có lỗi xảy ra khi kiểm tra tài khoản.';
            }
        }

        if (!empty($errors)) {
            view('admin.register', [
                'title' => 'Đăng ký tài khoản',
                'errors' => $errors,
                'formData' => $formData,
                'roles' => $roles,
                'redirect' => $redirect,
            ]);
            return;
        }

        try {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $now = date('Y-m-d H:i:s');

            $insert = $pdo->prepare(
                'INSERT INTO users (name, email, password, role, status, created_at, updated_at)
                 VALUES (:name, :email, :password, :role, :status, :created_at, :updated_at)'
            );

            $insert->execute([
                'name' => $name,
                'email' => $email,
                'password' => $hashedPassword,
                'role' => $role,
                'status' => $status,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $userId = (int)$pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log('Register insert failed: ' . $e->getMessage());
            $errors[] = 'Không thể tạo tài khoản. Vui lòng thử lại sau.';
            view('admin.register', [
                'title' => 'Đăng ký tài khoản',
                'errors' => $errors,
                'formData' => $formData,
                'roles' => $roles,
                'redirect' => $redirect,
            ]);
            return;
        }

        $user = new User([
            'id' => $userId,
            'name' => $name,
            'email' => $email,
            'role' => $role,
            'status' => $status,
        ]);

        loginUser($user);

        header('Location: ' . $redirect);
        exit;
    }

    private function getAssignableRoles(): array
    {
        return [
            'admin' => 'Quản trị viên',
            'guide' => 'Hướng dẫn viên',
        ];
    }

    // Xử lý đăng xuất
    public function logout()
    {
        // Xóa session và đăng xuất
        logoutUser();

        // Chuyển hướng về trang welcome
        header('Location: ' . BASE_URL . 'welcome');
        exit;
    }
}

