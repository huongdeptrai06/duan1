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

    // Hiển thị form để admin tạo tài khoản hướng dẫn viên
    public function showGuideCreationForm(): void
    {
        requireAdmin();
        $this->renderGuideCreationPage();
    }

    // Hiển thị danh sách tài khoản hướng dẫn viên cho admin
    public function showGuideList(): void
    {
        requireAdmin();

        $guides = [];
        $errors = [];
        $statusCode = $_GET['status'] ?? null;
        $pdo = getDB();

        if ($pdo === null) {
            $errors[] = 'Không thể kết nối cơ sở dữ liệu. Vui lòng thử lại.';
        } else {
            try {
                $stmt = $pdo->prepare(
                    'SELECT id, name, email, role, status, created_at
                     FROM users
                     WHERE role IN (:guideRole, :legacyRole)
                     ORDER BY created_at DESC'
                );
                $stmt->execute([
                    'guideRole' => 'guide',
                    'legacyRole' => 'huong_dan_vien',
                ]);
                $guides = $stmt->fetchAll();
            } catch (PDOException $e) {
                error_log('Guide list query failed: ' . $e->getMessage());
                $errors[] = 'Không thể tải danh sách hướng dẫn viên.';
            }
        }

        $statusMessage = $this->resolveGuideStatusMessage($statusCode);
        if ($statusMessage && $statusMessage['type'] === 'error') {
            $errors[] = $statusMessage['text'];
        }

        view('admin.guide_list', [
            'title' => 'Danh sách hướng dẫn viên',
            'pageTitle' => 'Danh sách hướng dẫn viên',
            'breadcrumb' => [
                ['label' => 'Người dùng', 'url' => BASE_URL . 'home'],
                ['label' => 'Danh sách hướng dẫn viên', 'active' => true],
            ],
            'guides' => $guides,
            'errors' => $errors,
            'successMessage' => $statusMessage && $statusMessage['type'] === 'success'
                ? $statusMessage['text']
                : null,
        ]);
    }

    // Hiển thị chi tiết một hướng dẫn viên
    public function showGuideDetail(): void
    {
        requireAdmin();

        $guideId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($guideId <= 0) {
            $this->redirectToGuideList('not-found');
        }

        $guide = $this->findGuideById($guideId);
        if (!$guide) {
            $this->redirectToGuideList('not-found');
        }

        $profile = $this->findGuideProfile($guideId);
        $profileData = $this->buildGuideProfileData($guide, $profile);

        view('admin.guide_detail', [
            'title' => 'Thông tin hướng dẫn viên',
            'pageTitle' => 'Thông tin hướng dẫn viên',
            'breadcrumb' => [
                ['label' => 'Người dùng', 'url' => BASE_URL . 'home'],
                ['label' => 'Danh sách hướng dẫn viên', 'url' => BASE_URL . 'admin-guide-list'],
                ['label' => htmlspecialchars($guide['name'] ?? 'Chi tiết'), 'active' => true],
            ],
            'guide' => $guide,
            'profile' => $profileData,
        ]);
    }

    // Hiển thị form chỉnh sửa tài khoản hướng dẫn viên
    public function showGuideEditForm(): void
    {
        requireAdmin();
        $guideId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($guideId <= 0) {
            $this->redirectToGuideList('not-found');
        }

        $guide = $this->findGuideById($guideId);
        if (!$guide) {
            $this->redirectToGuideList('not-found');
        }

        view('admin.edit_guide', [
            'title' => 'Chỉnh sửa hướng dẫn viên',
            'pageTitle' => 'Chỉnh sửa hướng dẫn viên',
            'breadcrumb' => [
                ['label' => 'Người dùng', 'url' => BASE_URL . 'home'],
                ['label' => 'Danh sách hướng dẫn viên', 'url' => BASE_URL . 'admin-guide-list'],
                ['label' => 'Chỉnh sửa', 'active' => true],
            ],
            'guide' => $guide,
        ]);
    }

    // Xử lý cập nhật thông tin hướng dẫn viên
    public function handleGuideUpdate(): void
    {
        requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectToGuideList();
        }

        $guideId = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($guideId <= 0) {
            $this->redirectToGuideList('not-found');
        }

        $guide = $this->findGuideById($guideId);
        if (!$guide) {
            $this->redirectToGuideList('not-found');
        }

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $status = (int)($_POST['status'] ?? 1) === 1 ? 1 : 0;
        $password = $_POST['password'] ?? '';
        $passwordConfirmation = $_POST['password_confirmation'] ?? '';

        $errors = [];

        if ($name === '') {
            $errors[] = 'Vui lòng nhập họ tên.';
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email không hợp lệ.';
        }

        $updatePassword = false;
        if ($password !== '') {
            if (strlen($password) < 6) {
                $errors[] = 'Mật khẩu mới phải có ít nhất 6 ký tự.';
            }

            if ($password !== $passwordConfirmation) {
                $errors[] = 'Xác nhận mật khẩu không khớp.';
            }

            $updatePassword = true;
        }

        $pdo = getDB();
        if ($pdo === null) {
            $errors[] = 'Không thể kết nối cơ sở dữ liệu. Vui lòng thử lại.';
        }

        if (empty($errors) && $pdo !== null) {
            try {
                $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email AND id != :id LIMIT 1');
                $stmt->execute([
                    'email' => $email,
                    'id' => $guideId,
                ]);
                if ($stmt->fetch()) {
                    $errors[] = 'Email này đã được sử dụng bởi tài khoản khác.';
                }
            } catch (PDOException $e) {
                error_log('Guide update uniqueness failed: ' . $e->getMessage());
                $errors[] = 'Có lỗi xảy ra khi kiểm tra email.';
            }
        }

        if (!empty($errors)) {
            view('admin.edit_guide', [
                'title' => 'Chỉnh sửa hướng dẫn viên',
                'pageTitle' => 'Chỉnh sửa hướng dẫn viên',
                'breadcrumb' => [
                    ['label' => 'Người dùng', 'url' => BASE_URL . 'home'],
                    ['label' => 'Danh sách hướng dẫn viên', 'url' => BASE_URL . 'admin-guide-list'],
                    ['label' => 'Chỉnh sửa', 'active' => true],
                ],
                'guide' => [
                    'id' => $guideId,
                    'name' => $name,
                    'email' => $email,
                    'status' => $status,
                ],
                'errors' => $errors,
            ]);
            return;
        }

        try {
            $updateFields = 'name = :name, email = :email, status = :status, updated_at = :updated_at';
            $params = [
                'name' => $name,
                'email' => $email,
                'status' => $status,
                'updated_at' => date('Y-m-d H:i:s'),
                'id' => $guideId,
                'guideRole' => 'guide',
                'legacyRole' => 'huong_dan_vien',
            ];

            if ($updatePassword) {
                $updateFields .= ', password = :password';
                $params['password'] = password_hash($password, PASSWORD_BCRYPT);
            }

            $stmt = $pdo->prepare(
                "UPDATE users SET {$updateFields} WHERE id = :id AND role IN (:guideRole, :legacyRole)"
            );
            $stmt->execute($params);
        } catch (PDOException $e) {
            error_log('Guide update failed: ' . $e->getMessage());
            $errors[] = 'Không thể cập nhật tài khoản. Vui lòng thử lại.';

            view('admin.edit_guide', [
                'title' => 'Chỉnh sửa hướng dẫn viên',
                'pageTitle' => 'Chỉnh sửa hướng dẫn viên',
                'breadcrumb' => [
                    ['label' => 'Người dùng', 'url' => BASE_URL . 'home'],
                    ['label' => 'Danh sách hướng dẫn viên', 'url' => BASE_URL . 'admin-guide-list'],
                    ['label' => 'Chỉnh sửa', 'active' => true],
                ],
                'guide' => [
                    'id' => $guideId,
                    'name' => $name,
                    'email' => $email,
                    'status' => $status,
                ],
                'errors' => $errors,
            ]);
            return;
        }

        $this->redirectToGuideList('updated');
    }

    // Xử lý xóa hướng dẫn viên
    public function handleGuideDeletion(): void
    {
        requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectToGuideList();
        }

        $guideId = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($guideId <= 0) {
            $this->redirectToGuideList('not-found');
        }

        $pdo = getDB();
        if ($pdo === null) {
            $this->redirectToGuideList('db-error');
        }

        try {
            $stmt = $pdo->prepare(
                'DELETE FROM users WHERE id = :id AND role IN (:guideRole, :legacyRole)'
            );
            $stmt->execute([
                'id' => $guideId,
                'guideRole' => 'guide',
                'legacyRole' => 'huong_dan_vien',
            ]);

            if ($stmt->rowCount() === 0) {
                $this->redirectToGuideList('not-found');
            }
        } catch (PDOException $e) {
            error_log('Guide deletion failed: ' . $e->getMessage());
            $this->redirectToGuideList('db-error');
        }

        $this->redirectToGuideList('deleted');
    }

    // Xử lý việc admin tạo mới tài khoản hướng dẫn viên
    public function handleGuideCreation(): void
    {
        requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'admin-guide-create');
            exit;
        }

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $passwordConfirmation = $_POST['password_confirmation'] ?? '';

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

        $formData = [
            'name' => $name,
            'email' => $email,
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
                    $errors[] = 'Email này đã tồn tại trong hệ thống.';
                }
            } catch (PDOException $e) {
                error_log('Guide create check failed: ' . $e->getMessage());
                $errors[] = 'Có lỗi xảy ra khi kiểm tra tài khoản.';
            }
        }

        if (!empty($errors)) {
            $this->renderGuideCreationPage([
                'errors' => $errors,
                'formData' => $formData,
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
                'role' => 'guide',
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        } catch (PDOException $e) {
            error_log('Guide create insert failed: ' . $e->getMessage());
            $errors[] = 'Không thể tạo tài khoản. Vui lòng thử lại sau.';
            $this->renderGuideCreationPage([
                'errors' => $errors,
                'formData' => $formData,
            ]);
            return;
        }

        $this->renderGuideCreationPage([
            'successMessage' => 'Đã tạo tài khoản hướng dẫn viên thành công.',
            'formData' => [],
        ]);
    }

    private function renderGuideCreationPage(array $data = []): void
    {
        $defaultData = [
            'title' => 'Cấp tài khoản hướng dẫn viên',
            'pageTitle' => 'Cấp tài khoản hướng dẫn viên',
            'breadcrumb' => [
                ['label' => 'Người dùng', 'url' => BASE_URL . 'home'],
                ['label' => 'Cấp tài khoản hướng dẫn viên', 'active' => true],
            ],
            'formData' => [],
            'errors' => [],
        ];

        view('admin.create_guide', array_merge($defaultData, $data));
    }

    private function findGuideById(int $id): ?array
    {
        $pdo = getDB();
        if ($pdo === null) {
            return null;
        }

        try {
            $stmt = $pdo->prepare(
                'SELECT id, name, email, status, role, created_at
                 FROM users
                 WHERE id = :id AND role IN (:guideRole, :legacyRole)
                 LIMIT 1'
            );
            $stmt->execute([
                'id' => $id,
                'guideRole' => 'guide',
                'legacyRole' => 'huong_dan_vien',
            ]);
            $guide = $stmt->fetch();
            return $guide ?: null;
        } catch (PDOException $e) {
            error_log('Find guide failed: ' . $e->getMessage());
            return null;
        }
    }

    private function findGuideProfile(int $userId): ?array
    {
        $pdo = getDB();
        if ($pdo === null) {
            return null;
        }

        try {
            $stmt = $pdo->prepare(
                'SELECT user_id, full_name, dob, gender, id_number, address, phone, license,
                        guide_type, languages, experience_years, notable_tours, strengths
                 FROM guide_profiles
                 WHERE user_id = :user_id
                 LIMIT 1'
            );
            $stmt->execute(['user_id' => $userId]);
            $profile = $stmt->fetch();
            return $profile ?: null;
        } catch (PDOException $e) {
            // Có thể bảng chưa tồn tại - ghi log và tiếp tục
            error_log('Find guide profile failed: ' . $e->getMessage());
            return null;
        }
    }

    private function buildGuideProfileData(array $guide, ?array $profile): array
    {
        $data = [
            'full_name' => $profile['full_name'] ?? $guide['name'] ?? 'Chưa cập nhật',
            'dob' => $profile['dob'] ?? null,
            'gender' => $profile['gender'] ?? null,
            'id_number' => $profile['id_number'] ?? null,
            'address' => $profile['address'] ?? null,
            'phone' => $profile['phone'] ?? null,
            'email' => $guide['email'] ?? null,
            'license' => $profile['license'] ?? null,
            'guide_type' => $profile['guide_type'] ?? null,
            'languages' => $profile['languages'] ?? null,
            'experience_years' => $profile['experience_years'] ?? null,
            'notable_tours' => $profile['notable_tours'] ?? null,
            'strengths' => $profile['strengths'] ?? null,
        ];

        return $data;
    }

    private function redirectToGuideList(?string $status = null): void
    {
        $url = BASE_URL . 'admin-guide-list';
        if ($status !== null) {
            $url .= '?status=' . urlencode($status);
        }

        header('Location: ' . $url);
        exit;
    }

    private function resolveGuideStatusMessage(?string $code): ?array
    {
        if ($code === null) {
            return null;
        }

        return match ($code) {
            'updated' => ['type' => 'success', 'text' => 'Đã cập nhật tài khoản hướng dẫn viên.'],
            'deleted' => ['type' => 'success', 'text' => 'Đã xóa tài khoản hướng dẫn viên.'],
            'not-found' => ['type' => 'error', 'text' => 'Không tìm thấy tài khoản hướng dẫn viên.'],
            'db-error' => ['type' => 'error', 'text' => 'Có lỗi hệ thống. Vui lòng thử lại sau.'],
            default => null,
        };
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

