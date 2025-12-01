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
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
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

    // Hiển thị form để admin cấp tài khoản cho hướng dẫn viên
    public function showGuideCreationForm(): void
    {
        requireAdmin();

        $successMessage = null;
        $successEmail = null;
        if (isset($_GET['created']) && $_GET['created'] === '1') {
            $successEmail = $_GET['newEmail'] ?? '';
            $successMessage = 'Đã tạo tài khoản hướng dẫn viên thành công.';
        }

        view('admin.guide_create', [
            'title' => 'Cấp tài khoản hướng dẫn viên',
            'successMessage' => $successMessage,
            'successEmail' => $successEmail,
        ]);
    }

    // Xử lý submit cấp tài khoản hướng dẫn viên
    public function handleGuideCreation(): void
    {
        requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'admin/guide/create');
            exit;
        }

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $passwordConfirmation = $_POST['password_confirmation'] ?? '';
        $role = 'huong_dan_vien';
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
                    $errors[] = 'Email này đã được sử dụng.';
                }
            } catch (PDOException $e) {
                error_log('Guide create check failed: ' . $e->getMessage());
                $errors[] = 'Có lỗi xảy ra khi kiểm tra tài khoản.';
            }
        }

        if (!empty($errors)) {
            view('admin.guide_create', [
                'title' => 'Cấp tài khoản hướng dẫn viên',
                'errors' => $errors,
                'formData' => $formData,
            ]);
            return;
        }

        try {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $now = date('Y-m-d H:i:s');

            // Bắt đầu transaction
            $pdo->beginTransaction();

            // 1. Tạo record trong bảng users
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

            // Lấy ID của user vừa tạo
            $newUserId = $pdo->lastInsertId();

            // 2. Tạo record trống trong bảng guide_profiles để đảm bảo thông tin hiển thị đồng nhất
            try {
                $tableExists = $pdo->query("SHOW TABLES LIKE 'guide_profiles'")->fetch();
                if ($tableExists && $newUserId) {
                    $insertProfile = $pdo->prepare(
                        "INSERT INTO guide_profiles (user_id, created_at, updated_at)
                         VALUES (:user_id, :created_at, :updated_at)"
                    );
                    $insertProfile->execute([
                        'user_id' => $newUserId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            } catch (PDOException $e) {
                // Log lỗi nhưng không rollback vì bảng guide_profiles có thể chưa tồn tại
                error_log('Guide profile insert failed (non-critical): ' . $e->getMessage());
            }

            // Commit transaction
            $pdo->commit();
        } catch (PDOException $e) {
            // Rollback nếu có lỗi
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('Guide create insert failed: ' . $e->getMessage());
            $errors[] = 'Không thể tạo tài khoản. Vui lòng thử lại sau.';
            view('admin.guide_create', [
                'title' => 'Cấp tài khoản hướng dẫn viên',
                'errors' => $errors,
                'formData' => $formData,
            ]);
            return;
        }

        header('Location: ' . BASE_URL . 'admin/guide/create?created=1&newEmail=' . urlencode($email));
        exit;
    }

    // Danh sách hướng dẫn viên cho admin
    public function showGuideList(): void
    {
        requireAdmin();

        $pdo = getDB();
        $errors = [];
        $guides = [];
        $guideGroups = $this->getGuideGroups();

        if ($pdo === null) {
            $errors[] = 'Không thể kết nối cơ sở dữ liệu. Vui lòng thử lại sau.';
        } else {
            try {
                // Kiểm tra xem cột guide_group có tồn tại không
                $checkColumn = $pdo->query("SHOW COLUMNS FROM users LIKE 'guide_group'")->fetch();
                $hasGuideGroup = $checkColumn !== false;
                
                if ($hasGuideGroup) {
                    $stmt = $pdo->query(
                        "SELECT id, name, email, role, status, created_at, guide_group
                         FROM users
                         WHERE role = 'huong_dan_vien'
                         ORDER BY created_at DESC"
                    );
                } else {
                    $stmt = $pdo->query(
                        "SELECT id, name, email, role, status, created_at, NULL as guide_group
                         FROM users
                         WHERE role = 'huong_dan_vien'
                         ORDER BY created_at DESC"
                    );
                }
                $guides = $stmt->fetchAll();
            } catch (PDOException $e) {
                error_log('Fetch guide list failed: ' . $e->getMessage());
                $errors[] = 'Không thể tải danh sách hướng dẫn viên.';
            }
        }

        view('admin.guide_list', [
            'title' => 'Danh sách hướng dẫn viên',
            'pageTitle' => 'Danh sách hướng dẫn viên',
            'guides' => $guides,
            'errors' => $errors,
            'guideGroups' => $guideGroups,
        ]);
    }

    // Danh sách tài khoản người dùng cho admin
    public function listUsers(): void
    {
        requireAdmin();

        $pdo = getDB();
        $errors = [];
        $users = [];

        if ($pdo === null) {
            $errors[] = 'Không thể kết nối cơ sở dữ liệu. Vui lòng thử lại sau.';
        } else {
            try {
                $stmt = $pdo->query('SELECT id, name, email, role, status, created_at, updated_at FROM users ORDER BY created_at DESC');
                $users = $stmt->fetchAll();
            } catch (PDOException $e) {
                error_log('Fetch users failed: ' . $e->getMessage());
                $errors[] = 'Không thể tải danh sách tài khoản.';
            }
        }

        view('admin.users.index', [
            'title' => 'Danh sách tài khoản',
            'users' => $users,
            'errors' => $errors,
        ]);
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

    // Các nhóm chuyên môn của HDV để hiển thị nhãn trên giao diện
    private function getGuideGroups(): array
    {
        return [
            'noidia' => 'Tour trong nước',
            'quocte' => 'Tour quốc tế',
        ];
    }

    // Hiển thị chi tiết hướng dẫn viên
    public function showGuideDetail(): void
    {
        requireAdmin();

        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: ' . BASE_URL . 'admin-guide-list');
            exit;
        }

        $pdo = getDB();
        $errors = [];
        $userData = null;
        $guideData = null;
        $guideGroups = $this->getGuideGroups();

        if ($pdo === null) {
            $errors[] = 'Không thể kết nối cơ sở dữ liệu.';
        } else {
            try {
                // Lấy thông tin từ bảng users
                $checkColumn = $pdo->query("SHOW COLUMNS FROM users LIKE 'guide_group'")->fetch();
                $hasGuideGroup = $checkColumn !== false;
                
                if ($hasGuideGroup) {
                    $stmt = $pdo->prepare(
                        "SELECT id, name, email, role, status, created_at, updated_at, guide_group
                         FROM users
                         WHERE id = :id AND role = 'huong_dan_vien'
                         LIMIT 1"
                    );
                } else {
                    $stmt = $pdo->prepare(
                        "SELECT id, name, email, role, status, created_at, updated_at, NULL as guide_group
                         FROM users
                         WHERE id = :id AND role = 'huong_dan_vien'
                         LIMIT 1"
                    );
                }
                $stmt->execute(['id' => $id]);
                $userData = $stmt->fetch();
                
                if (!$userData) {
                    $errors[] = 'Không tìm thấy hướng dẫn viên.';
                } else {
                    // Lấy thông tin từ bảng guide_profiles
                    try {
                        $guideStmt = $pdo->prepare(
                            "SELECT * FROM guide_profiles WHERE user_id = :user_id LIMIT 1"
                        );
                        $guideStmt->execute(['user_id' => $id]);
                        $guideData = $guideStmt->fetch();
                        
                        // Merge dữ liệu và map tên cột
                        $mergedData = $userData;
                        if ($guideData) {
                            // Map tên cột từ guide_profiles sang format chuẩn
                            // Schema: không có full_name, dùng name từ users
                            $mappedGuideData = [
                                'full_name' => $userData['name'], // Không có full_name trong guide_profiles, dùng name từ users
                                'dob' => isset($guideData['birthdate']) && $guideData['birthdate'] !== null ? $guideData['birthdate'] : null,
                                'birthdate' => isset($guideData['birthdate']) && $guideData['birthdate'] !== null ? $guideData['birthdate'] : null,
                                'photo' => isset($guideData['avatar']) && $guideData['avatar'] !== null && $guideData['avatar'] !== '' ? $guideData['avatar'] : null,
                                'avatar' => isset($guideData['avatar']) && $guideData['avatar'] !== null && $guideData['avatar'] !== '' ? $guideData['avatar'] : null,
                                'contact' => isset($guideData['phone']) && $guideData['phone'] !== null && $guideData['phone'] !== '' ? $guideData['phone'] : null,
                                'phone' => isset($guideData['phone']) && $guideData['phone'] !== null && $guideData['phone'] !== '' ? $guideData['phone'] : null,
                                'certificates' => isset($guideData['certificate']) && $guideData['certificate'] !== null && $guideData['certificate'] !== '' ? $guideData['certificate'] : null,
                                'certificate' => isset($guideData['certificate']) && $guideData['certificate'] !== null && $guideData['certificate'] !== '' ? $guideData['certificate'] : null,
                                'languages' => isset($guideData['languages']) && $guideData['languages'] !== null && $guideData['languages'] !== '' ? $guideData['languages'] : null,
                                'experience' => isset($guideData['experience']) && $guideData['experience'] !== null && $guideData['experience'] !== '' ? $guideData['experience'] : null,
                                'tour_history' => isset($guideData['history']) && $guideData['history'] !== null && $guideData['history'] !== '' ? $guideData['history'] : null,
                                'history' => isset($guideData['history']) && $guideData['history'] !== null && $guideData['history'] !== '' ? $guideData['history'] : null,
                                'rating' => isset($guideData['rating']) && $guideData['rating'] !== null ? $guideData['rating'] : null,
                                'health_status' => isset($guideData['health_status']) && $guideData['health_status'] !== null && $guideData['health_status'] !== '' ? $guideData['health_status'] : null,
                                'group' => isset($guideData['group_type']) && $guideData['group_type'] !== null && $guideData['group_type'] !== '' ? $guideData['group_type'] : null,
                                'group_type' => isset($guideData['group_type']) && $guideData['group_type'] !== null && $guideData['group_type'] !== '' ? $guideData['group_type'] : null,
                                'speciality' => isset($guideData['speciality']) && $guideData['speciality'] !== null && $guideData['speciality'] !== '' ? $guideData['speciality'] : null,
                            ];
                            $mergedData = array_merge($userData, $mappedGuideData);
                            // Map group_type sang guide_group nếu chưa có
                            if (isset($guideData['group_type']) && !empty($guideData['group_type']) && empty($mergedData['guide_group'])) {
                                $mergedData['guide_group'] = $guideData['group_type'];
                            }
                        }
                    } catch (PDOException $e) {
                        error_log('Fetch guide_profiles failed: ' . $e->getMessage());
                        // Không báo lỗi nếu không có bảng guide_profiles, chỉ dùng userData
                        $mergedData = $userData;
                    }
                }
            } catch (PDOException $e) {
                error_log('Fetch guide detail failed: ' . $e->getMessage());
                $errors[] = 'Không thể tải thông tin hướng dẫn viên.';
            }
        }

        view('admin.guide_detail_user', [
            'title' => 'Chi tiết hướng dẫn viên',
            'pageTitle' => 'Chi tiết hướng dẫn viên',
            'guide' => $mergedData ?? $userData,
            'userData' => $userData,
            'guideData' => $guideData,
            'errors' => $errors,
            'guideGroups' => $guideGroups,
        ]);
    }

    // Hiển thị form sửa hướng dẫn viên
    public function showGuideEditForm(): void
    {
        requireAdmin();

        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: ' . BASE_URL . 'admin-guide-list');
            exit;
        }

        $pdo = getDB();
        $errors = [];
        $userData = null;
        $guideData = null;
        $guideGroups = $this->getGuideGroups();

        if ($pdo === null) {
            $errors[] = 'Không thể kết nối cơ sở dữ liệu.';
        } else {
            try {
                // Lấy thông tin từ bảng users
                $checkColumn = $pdo->query("SHOW COLUMNS FROM users LIKE 'guide_group'")->fetch();
                $hasGuideGroup = $checkColumn !== false;
                
                if ($hasGuideGroup) {
                    $stmt = $pdo->prepare(
                        "SELECT id, name, email, role, status, created_at, updated_at, guide_group
                         FROM users
                         WHERE id = :id AND role = 'huong_dan_vien'
                         LIMIT 1"
                    );
                } else {
                    $stmt = $pdo->prepare(
                        "SELECT id, name, email, role, status, created_at, updated_at, NULL as guide_group
                         FROM users
                         WHERE id = :id AND role = 'huong_dan_vien'
                         LIMIT 1"
                    );
                }
                $stmt->execute(['id' => $id]);
                $userData = $stmt->fetch();
                
                if (!$userData) {
                    $errors[] = 'Không tìm thấy hướng dẫn viên.';
                } else {
                    // Lấy thông tin từ bảng guide_profiles
                    try {
                        // Kiểm tra xem bảng guide_profiles có tồn tại không
                        $tableExists = $pdo->query("SHOW TABLES LIKE 'guide_profiles'")->fetch();
                        $guideData = null;
                        if ($tableExists) {
                            $guideStmt = $pdo->prepare(
                                "SELECT * FROM guide_profiles WHERE user_id = :user_id LIMIT 1"
                            );
                            $guideStmt->execute(['user_id' => $id]);
                            $guideData = $guideStmt->fetch();
                        } else {
                            error_log('Table guide_profiles does not exist. Please run migration 002_create_guide_profiles_table.sql');
                        }
                        
                        // Merge dữ liệu và map tên cột
                        $mergedData = $userData;
                        if ($guideData) {
                            // Map tên cột từ guide_profiles
                            $mappedGuideData = [
                                'full_name' => $guideData['full_name'] ?? $userData['name'],
                                'dob' => $guideData['birthdate'] ?? null,
                                'photo' => $guideData['avatar'] ?? null,
                                'contact' => $guideData['phone'] ?? null,
                                'certificates' => $guideData['certificate'] ?? null,
                                'languages' => $guideData['languages'] ?? null,
                                'experience' => $guideData['experience'] ?? null,
                                'tour_history' => $guideData['history'] ?? null,
                                'rating' => $guideData['rating'] ?? null,
                                'health_status' => $guideData['health_status'] ?? null,
                                'group' => $guideData['group_type'] ?? null,
                                'speciality' => $guideData['speciality'] ?? null,
                            ];
                            $mergedData = array_merge($userData, $mappedGuideData);
                            // Map group_type sang guide_group nếu chưa có
                            if (isset($guideData['group_type']) && empty($mergedData['guide_group'])) {
                                $mergedData['guide_group'] = $guideData['group_type'];
                            }
                        }
                    } catch (PDOException $e) {
                        error_log('Fetch guide_profiles for edit failed: ' . $e->getMessage());
                        // Không báo lỗi, chỉ dùng userData
                        $mergedData = $userData;
                    }
                }
            } catch (PDOException $e) {
                error_log('Fetch guide for edit failed: ' . $e->getMessage());
                $errors[] = 'Không thể tải thông tin hướng dẫn viên.';
            }
        }

        view('admin.edit_guide_user', [
            'title' => 'Sửa thông tin hướng dẫn viên',
            'pageTitle' => 'Sửa thông tin hướng dẫn viên',
            'guide' => $userData,
            'guideData' => $guideData,
            'errors' => $errors,
            'guideGroups' => $guideGroups,
            'formData' => $mergedData ?? $userData ?? [],
        ]);
    }

    // Xử lý cập nhật thông tin hướng dẫn viên
    public function handleGuideUpdate(): void
    {
        requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'admin-guide-list');
            exit;
        }

        $id = $_POST['id'] ?? null;
        if (!$id) {
            header('Location: ' . BASE_URL . 'admin-guide-list');
            exit;
        }

        // Lấy dữ liệu từ form
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $status = isset($_POST['status']) ? (int)$_POST['status'] : 1;
        $password = $_POST['password'] ?? '';
        $passwordConfirmation = $_POST['password_confirmation'] ?? '';
        
        // Dữ liệu từ bảng guide_profiles
        $dob = !empty($_POST['dob']) ? $_POST['dob'] : null;
        $contact = trim($_POST['contact'] ?? '');
        $certificates = trim($_POST['certificates'] ?? '');
        $languages = trim($_POST['languages'] ?? '');
        $experience = trim($_POST['experience'] ?? '');
        $tourHistory = trim($_POST['tour_history'] ?? '');
        $rating = !empty($_POST['rating']) ? (float)$_POST['rating'] : null;
        $healthStatus = trim($_POST['health_status'] ?? '');
        $speciality = trim($_POST['speciality'] ?? '');
        $guideGroup = $_POST['guide_group'] ?? 'noidia';
        if (!in_array($guideGroup, ['noidia', 'quocte'])) {
            $guideGroup = 'noidia';
        }

        $errors = [];
        $guideGroups = $this->getGuideGroups();

        // Validate
        if ($name === '') {
            $errors[] = 'Vui lòng nhập họ tên.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email không hợp lệ.';
        }
        if (!empty($password)) {
            if (strlen($password) < 6) {
                $errors[] = 'Mật khẩu phải có ít nhất 6 ký tự.';
            }
            if ($password !== $passwordConfirmation) {
                $errors[] = 'Xác nhận mật khẩu không khớp.';
            }
        }
        if ($rating !== null && ($rating < 0 || $rating > 5)) {
            $errors[] = 'Đánh giá phải từ 0 đến 5.';
        }

        $pdo = getDB();
        if ($pdo === null) {
            $errors[] = 'Không thể kết nối cơ sở dữ liệu.';
        }

        // Kiểm tra email trùng
        if (empty($errors)) {
            $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email AND id != :id LIMIT 1');
            $stmt->execute(['email' => $email, 'id' => $id]);
            if ($stmt->fetch()) {
                $errors[] = 'Email này đã được sử dụng bởi tài khoản khác.';
            }
        }

        // Nếu có lỗi validation, quay lại form
        if (!empty($errors)) {
            $formData = [
                'id' => $id,
                'name' => $name,
                'email' => $email,
                'status' => $status,
                'guide_group' => $guideGroup,
                'dob' => $dob,
                'contact' => $contact,
                'certificates' => $certificates,
                'languages' => $languages,
                'experience' => $experience,
                'tour_history' => $tourHistory,
                'rating' => $rating,
                'health_status' => $healthStatus,
                'speciality' => $speciality,
            ];
            view('admin.edit_guide_user', [
                'title' => 'Sửa thông tin hướng dẫn viên',
                'pageTitle' => 'Sửa thông tin hướng dẫn viên',
                'errors' => $errors,
                'guideGroups' => $guideGroups,
                'formData' => $formData,
            ]);
            return;
        }

        $now = date('Y-m-d H:i:s');

        try {
            // 1. Cập nhật bảng users
            if (!empty($password)) {
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare(
                    "UPDATE users SET name = :name, email = :email, password = :password, 
                     status = :status, updated_at = :updated_at 
                     WHERE id = :id AND role = 'huong_dan_vien'"
                );
                $stmt->execute([
                    'name' => $name,
                    'email' => $email,
                    'password' => $hashedPassword,
                    'status' => $status,
                    'updated_at' => $now,
                    'id' => $id,
                ]);
            } else {
                $stmt = $pdo->prepare(
                    "UPDATE users SET name = :name, email = :email, status = :status, updated_at = :updated_at 
                     WHERE id = :id AND role = 'huong_dan_vien'"
                );
                $stmt->execute([
                    'name' => $name,
                    'email' => $email,
                    'status' => $status,
                    'updated_at' => $now,
                    'id' => $id,
                ]);
            }

            // 2. Xử lý upload ảnh
            $photoPath = null;
            if (!empty($_FILES['photo']['name']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $uploadsDir = BASE_PATH . '/public/uploads/guides';
                if (!is_dir($uploadsDir)) {
                    mkdir($uploadsDir, 0755, true);
                }
                $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
                $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                if (in_array($ext, $allowedExts)) {
                    $fileName = uniqid('guide_') . '.' . $ext;
                    $target = $uploadsDir . DIRECTORY_SEPARATOR . $fileName;
                    if (move_uploaded_file($_FILES['photo']['tmp_name'], $target)) {
                        $photoPath = 'uploads/guides/' . $fileName;
                    }
                }
            }

            // 3. Cập nhật hoặc tạo mới guide_profiles
            $checkStmt = $pdo->prepare("SELECT id, avatar FROM guide_profiles WHERE user_id = :user_id LIMIT 1");
            $checkStmt->execute(['user_id' => $id]);
            $existingGuide = $checkStmt->fetch();

            // Nếu không upload ảnh mới, giữ ảnh cũ
            if ($photoPath === null && $existingGuide) {
                $photoPath = $existingGuide['avatar'] ?? null;
            }

            // Chuẩn hóa dữ liệu - chuyển empty string thành NULL
            $birthdate = !empty($dob) ? $dob : null;
            $avatar = !empty($photoPath) ? $photoPath : null;
            $phone = !empty($contact) ? $contact : null;
            $certificate = !empty($certificates) ? $certificates : null;
            $lang = !empty($languages) ? $languages : null;
            $exp = !empty($experience) ? $experience : null;
            $hist = !empty($tourHistory) ? $tourHistory : null;
            $rat = ($rating !== null && $rating !== '') ? (float)$rating : null;
            $health = !empty($healthStatus) ? $healthStatus : null;
            $spec = !empty($speciality) ? $speciality : null;
            // group_type phải là 'noidia' hoặc 'quocte', không được NULL
            $groupType = in_array($guideGroup, ['noidia', 'quocte']) ? $guideGroup : 'noidia';

            if ($existingGuide) {
                // UPDATE
                $updateStmt = $pdo->prepare(
                    "UPDATE guide_profiles SET 
                     birthdate = :birthdate, avatar = :avatar, phone = :phone, 
                     certificate = :certificate, languages = :languages, experience = :experience, 
                     history = :history, rating = :rating, health_status = :health_status, 
                     group_type = :group_type, speciality = :speciality, updated_at = :updated_at
                     WHERE user_id = :user_id"
                );
                $updateStmt->execute([
                    'birthdate' => $birthdate,
                    'avatar' => $avatar,
                    'phone' => $phone,
                    'certificate' => $certificate,
                    'languages' => $lang,
                    'experience' => $exp,
                    'history' => $hist,
                    'rating' => $rat,
                    'health_status' => $health,
                    'group_type' => $groupType,
                    'speciality' => $spec,
                    'updated_at' => $now,
                    'user_id' => $id,
                ]);
            } else {
                // INSERT
                $insertStmt = $pdo->prepare(
                    "INSERT INTO guide_profiles 
                     (user_id, birthdate, avatar, phone, certificate, languages, experience, 
                      history, rating, health_status, group_type, speciality, created_at, updated_at)
                     VALUES 
                     (:user_id, :birthdate, :avatar, :phone, :certificate, :languages, :experience, 
                      :history, :rating, :health_status, :group_type, :speciality, :created_at, :updated_at)"
                );
                $insertStmt->execute([
                    'user_id' => $id,
                    'birthdate' => $birthdate,
                    'avatar' => $avatar,
                    'phone' => $phone,
                    'certificate' => $certificate,
                    'languages' => $lang,
                    'experience' => $exp,
                    'history' => $hist,
                    'rating' => $rat,
                    'health_status' => $health,
                    'group_type' => $groupType,
                    'speciality' => $spec,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            // Thành công - redirect
            header('Location: ' . BASE_URL . 'admin-guide-detail?id=' . $id . '&updated=1');
            exit;

        } catch (PDOException $e) {
            error_log('Update guide failed: ' . $e->getMessage());
            $errors[] = 'Lỗi database: ' . $e->getMessage();
            $formData = [
                'id' => $id,
                'name' => $name,
                'email' => $email,
                'status' => $status,
                'guide_group' => $guideGroup,
                'dob' => $dob,
                'contact' => $contact,
                'certificates' => $certificates,
                'languages' => $languages,
                'experience' => $experience,
                'tour_history' => $tourHistory,
                'rating' => $rating,
                'health_status' => $healthStatus,
                'speciality' => $speciality,
            ];
            view('admin.edit_guide_user', [
                'title' => 'Sửa thông tin hướng dẫn viên',
                'pageTitle' => 'Sửa thông tin hướng dẫn viên',
                'errors' => $errors,
                'guideGroups' => $guideGroups,
                'formData' => $formData,
            ]);
            return;
        }
    }

    // Xử lý xóa hướng dẫn viên
    public function handleGuideDelete(): void
    {
        requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'admin-guide-list');
            exit;
        }

        $id = $_POST['id'] ?? null;
        if (!$id) {
            header('Location: ' . BASE_URL . 'admin-guide-list');
            exit;
        }

        $pdo = getDB();
        if ($pdo === null) {
            header('Location: ' . BASE_URL . 'admin-guide-list?error=db');
            exit;
        }

        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id AND role = 'huong_dan_vien'");
            $stmt->execute(['id' => $id]);
            
            if ($stmt->rowCount() === 0) {
                header('Location: ' . BASE_URL . 'admin-guide-list?error=notfound');
                exit;
            }
        } catch (PDOException $e) {
            error_log('Delete guide failed: ' . $e->getMessage());
            header('Location: ' . BASE_URL . 'admin-guide-list?error=delete');
            exit;
        }

        header('Location: ' . BASE_URL . 'admin-guide-list?deleted=1');
        exit;
    }

}

