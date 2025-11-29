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
                // Thử query với LEFT JOIN guide_profiles trước
                // Nếu bảng guide_profiles chưa tồn tại, sẽ thử query không JOIN
                try {
                    $stmt = $pdo->prepare(
                        'SELECT u.id,
                                u.name,
                                u.email,
                                u.role,
                                u.status,
                                u.created_at,
                                gp.guide_group
                         FROM users u
                         LEFT JOIN guide_profiles gp ON gp.user_id = u.id
                         WHERE u.role IN (:guideRole, :legacyRole)
                         ORDER BY u.created_at DESC'
                    );
                    $stmt->execute([
                        'guideRole' => 'guide',
                        'legacyRole' => 'huong_dan_vien',
                    ]);
                    $guides = $stmt->fetchAll();
                } catch (PDOException $joinError) {
                    // Nếu LEFT JOIN thất bại (bảng guide_profiles chưa tồn tại), 
                    // thử query chỉ từ bảng users
                    error_log('Guide list JOIN failed, trying without JOIN: ' . $joinError->getMessage());
                    
                    $stmt = $pdo->prepare(
                        'SELECT u.id,
                                u.name,
                                u.email,
                                u.role,
                                u.status,
                                u.created_at,
                                NULL as guide_group
                         FROM users u
                         WHERE u.role IN (:guideRole, :legacyRole)
                         ORDER BY u.created_at DESC'
                    );
                    $stmt->execute([
                        'guideRole' => 'guide',
                        'legacyRole' => 'huong_dan_vien',
                    ]);
                    $guides = $stmt->fetchAll();
                }
            } catch (PDOException $e) {
                error_log('Guide list query failed: ' . $e->getMessage());
                $errors[] = 'Không thể tải danh sách hướng dẫn viên: ' . htmlspecialchars($e->getMessage());
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
            'guideGroups' => $this->getGuideGroups(),
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
            'guideGroups' => $this->getGuideGroups(),
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

        $profile = $this->findGuideProfile($guideId);

        view('admin.edit_guide', [
            'title' => 'Chỉnh sửa hướng dẫn viên',
            'pageTitle' => 'Chỉnh sửa hướng dẫn viên',
            'breadcrumb' => [
                ['label' => 'Người dùng', 'url' => BASE_URL . 'home'],
                ['label' => 'Danh sách hướng dẫn viên', 'url' => BASE_URL . 'admin-guide-list'],
                ['label' => 'Chỉnh sửa', 'active' => true],
            ],
            'guide' => $guide,
            'profileData' => $profile ?? [],
            'guideGroups' => $this->getGuideGroups(),
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

        $existingProfile = $this->findGuideProfile($guideId) ?? [];
        $profileFallback = [
            'full_name' => $existingProfile['full_name'] ?? ($guide['name'] ?? ''),
            'email' => $existingProfile['contact_email'] ?? ($guide['email'] ?? ''),
            'guide_group' => $existingProfile['guide_group'] ?? '',
        ];
        $profileInput = $this->collectGuideProfileInput($_POST, $profileFallback);
        $profileErrors = $this->validateGuideProfileInput($profileInput);

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

        $errors = array_merge($errors, $profileErrors);

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
                'profileData' => $profileInput,
                'guideGroups' => $this->getGuideGroups(),
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
                'profileData' => $profileInput,
                'guideGroups' => $this->getGuideGroups(),
                'errors' => $errors,
            ]);
            return;
        }

        try {
            $this->saveGuideProfile($guideId, $profileInput);
        } catch (RuntimeException $e) {
            error_log('Guide profile update skipped: ' . $e->getMessage());
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

        $profileInput = $this->collectGuideProfileInput($_POST, [
            'full_name' => $name,
            'email' => $email,
        ]);
        $profileErrors = $this->validateGuideProfileInput($profileInput);

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

        $errors = array_merge($errors, $profileErrors);

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
                'profileData' => $profileInput,
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
            $newGuideId = (int)$pdo->lastInsertId();
            if ($newGuideId > 0) {
                try {
                    $this->saveGuideProfile($newGuideId, $profileInput);
                } catch (RuntimeException $e) {
                    error_log('Guide profile create skipped: ' . $e->getMessage());
                    // Lỗi lưu profile không chặn việc tạo user, chỉ log
                } catch (Exception $e) {
                    error_log('Guide profile create error: ' . $e->getMessage());
                    // Lỗi lưu profile không chặn việc tạo user, chỉ log
                }
            }
        } catch (PDOException $e) {
            error_log('Guide create insert failed: ' . $e->getMessage());
            $errors[] = 'Không thể tạo tài khoản. Vui lòng thử lại sau.';
            $this->renderGuideCreationPage([
                'errors' => $errors,
                'formData' => $formData,
                'profileData' => $profileInput,
            ]);
            return;
        }

        // Redirect to guide list page after successful creation
        header('Location: ' . BASE_URL . 'admin-guide-list?status=created');
        exit;
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
            'profileData' => [],
            'errors' => [],
            'guideGroups' => $this->getGuideGroups(),
        ];

        view('admin.create_guide', array_merge($defaultData, $data));
    }

    private function collectGuideProfileInput(array $source, array $fallback = []): array
    {
        $guideGroups = $this->getGuideGroups();
        $defaultGroup = array_key_first($guideGroups);

        return [
            'full_name' => trim($source['profile_full_name'] ?? ($fallback['full_name'] ?? '')),
            'dob' => trim($source['profile_dob'] ?? ''),
            'gender' => trim($source['profile_gender'] ?? ''),
            'avatar_url' => trim($source['profile_avatar_url'] ?? ''),
            'id_number' => trim($source['profile_id_number'] ?? ''),
            'address' => trim($source['profile_address'] ?? ''),
            'phone' => trim($source['profile_phone'] ?? ''),
            'email' => trim($source['profile_email'] ?? ($fallback['email'] ?? '')),
            'license' => trim($source['profile_license'] ?? ''),
            'guide_type' => trim($source['profile_guide_type'] ?? ''),
            'guide_group' => $source['profile_guide_group'] ?? ($fallback['guide_group'] ?? $defaultGroup),
            'languages' => trim($source['profile_languages'] ?? ''),
            'experience_years' => trim($source['profile_experience_years'] ?? ''),
            'experience_detail' => trim($source['profile_experience_detail'] ?? ''),
            'notable_tours' => trim($source['profile_notable_tours'] ?? ''),
            'tour_history' => trim($source['profile_tour_history'] ?? ''),
            'strengths' => trim($source['profile_strengths'] ?? ''),
            'rating' => trim($source['profile_rating'] ?? ''),
            'health_status' => trim($source['profile_health_status'] ?? ''),
        ];
    }

    private function validateGuideProfileInput(array $profile): array
    {
        $errors = [];

        if ($profile['full_name'] === '') {
            $errors[] = 'Vui lòng nhập họ tên đầy đủ cho hồ sơ hướng dẫn viên.';
        }

        $guideGroups = $this->getGuideGroups();
        if ($profile['guide_group'] !== '' && !array_key_exists($profile['guide_group'], $guideGroups)) {
            $errors[] = 'Nhóm hướng dẫn viên không hợp lệ.';
        }

        if ($profile['experience_years'] !== '' && !ctype_digit((string)$profile['experience_years'])) {
            $errors[] = 'Số năm kinh nghiệm phải là số nguyên không âm.';
        }

        if ($profile['rating'] !== '') {
            if (!is_numeric($profile['rating'])) {
                $errors[] = 'Đánh giá năng lực phải là số.';
            } else {
                $rating = (float)$profile['rating'];
                if ($rating < 0 || $rating > 5) {
                    $errors[] = 'Đánh giá năng lực nên nằm trong khoảng 0 - 5.';
                }
            }
        }

        return $errors;
    }

    private function saveGuideProfile(int $userId, array $profileInput): void
    {
        $pdo = getDB();
        if ($pdo === null) {
            throw new RuntimeException('Không thể kết nối cơ sở dữ liệu.');
        }

        $normalized = [
            'full_name' => $profileInput['full_name'] ?: null,
            'dob' => $profileInput['dob'] ?: null,
            'gender' => $profileInput['gender'] ?: null,
            'avatar_url' => $profileInput['avatar_url'] ?: null,
            'id_number' => $profileInput['id_number'] ?: null,
            'address' => $profileInput['address'] ?: null,
            'phone' => $profileInput['phone'] ?: null,
            'contact_email' => $profileInput['email'] ?: null,
            'license' => $profileInput['license'] ?: null,
            'guide_type' => $profileInput['guide_type'] ?: null,
            'guide_group' => $profileInput['guide_group'] ?: null,
            'languages' => $profileInput['languages'] ?: null,
            'experience_years' => $profileInput['experience_years'] !== '' ? (int)$profileInput['experience_years'] : null,
            'experience_detail' => $profileInput['experience_detail'] ?: null,
            'notable_tours' => $profileInput['notable_tours'] ?: null,
            'tour_history' => $profileInput['tour_history'] ?: null,
            'strengths' => $profileInput['strengths'] ?: null,
            'rating' => $profileInput['rating'] !== '' ? (float)$profileInput['rating'] : null,
            'health_status' => $profileInput['health_status'] ?: null,
        ];

        try {
            $columns = array_keys($normalized);
            $insertColumns = implode(', ', $columns);
            $insertParams = implode(', ', array_map(fn($col) => ':' . $col, $columns));
            $updateAssignments = implode(', ', array_map(fn($col) => "{$col} = VALUES({$col})", $columns));

            $stmt = $pdo->prepare(
                "INSERT INTO guide_profiles (user_id, {$insertColumns})
                 VALUES (:user_id, {$insertParams})
                 ON DUPLICATE KEY UPDATE {$updateAssignments}"
            );

            $stmt->execute(array_merge(['user_id' => $userId], $normalized));
            
            // Log để debug
            error_log('Guide profile saved successfully for user_id: ' . $userId);
        } catch (PDOException $e) {
            // Nếu bảng chưa tồn tại hoặc lỗi khác, ghi log để admin xử lý
            error_log('Save guide profile failed for user_id ' . $userId . ': ' . $e->getMessage());
            error_log('SQL Error Info: ' . print_r($e->errorInfo, true));
            // Ném lại exception để caller biết có lỗi
            throw new RuntimeException('Không thể lưu thông tin hồ sơ: ' . $e->getMessage(), 0, $e);
        }
    }

    private function getGuideGroups(): array
    {
        return [
            'noi_dia' => 'Nội địa',
            'quoc_te' => 'Quốc tế',
            'chuyen_tuyen' => 'Chuyên tuyến',
            'chuyen_khach_doan' => 'Chuyên khách đoàn',
            'du_lich_sinh_thai' => 'Du lịch sinh thái',
            'du_lich_mao_hiem' => 'Du lịch mạo hiểm',
        ];
    }

    private function mapGroupTypeToCode(?string $groupType): ?string
    {
        if (empty($groupType)) {
            return null;
        }

        $groupType = mb_strtolower(trim($groupType));
        
        if (strpos($groupType, 'quốc tế') !== false || strpos($groupType, 'quoc te') !== false) {
            return 'quoc_te';
        }
        if (strpos($groupType, 'nội địa') !== false || strpos($groupType, 'noi dia') !== false) {
            return 'noi_dia';
        }
        if (strpos($groupType, 'tuyến') !== false || strpos($groupType, 'tuyen') !== false) {
            return 'chuyen_tuyen';
        }
        if (strpos($groupType, 'đoàn') !== false || strpos($groupType, 'doan') !== false) {
            return 'chuyen_khach_doan';
        }
        if (strpos($groupType, 'sinh thái') !== false || strpos($groupType, 'sinh thai') !== false) {
            return 'du_lich_sinh_thai';
        }
        if (strpos($groupType, 'mạo hiểm') !== false || strpos($groupType, 'mao hiem') !== false) {
            return 'du_lich_mao_hiem';
        }
        
        return null;
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
            // Query tất cả các cột có thể có
            $stmt = $pdo->prepare('SELECT * FROM guide_profiles WHERE user_id = :user_id LIMIT 1');
            $stmt->execute(['user_id' => $userId]);
            $profile = $stmt->fetch();
            
            if (!$profile) {
                error_log("Guide profile not found for user_id: {$userId}");
                return null;
            }
            
            // Log để debug
            error_log("Guide profile found for user_id: {$userId}, columns: " . implode(', ', array_keys($profile)));
            
            // Map dữ liệu từ cột cũ sang format mới nếu cần
            $mapped = [
                'user_id' => $profile['user_id'] ?? null,
                'full_name' => $profile['full_name'] ?? null,
                'dob' => $profile['dob'] ?? $profile['birthdate'] ?? null,
                'gender' => $profile['gender'] ?? null,
                'avatar_url' => $profile['avatar_url'] ?? (!empty($profile['avatar']) ? '/uploads/guides/' . $profile['avatar'] : null),
                'id_number' => $profile['id_number'] ?? null,
                'address' => $profile['address'] ?? null,
                'phone' => $profile['phone'] ?? null,
                'contact_email' => $profile['contact_email'] ?? null,
                'license' => $profile['license'] ?? $profile['certificate'] ?? null,
                'guide_type' => $profile['guide_type'] ?? $profile['speciality'] ?? null,
                'guide_group' => $profile['guide_group'] ?? $this->mapGroupTypeToCode($profile['group_type'] ?? null),
                'languages' => $profile['languages'] ?? null,
                'experience_years' => $profile['experience_years'] ?? null,
                'experience_detail' => $profile['experience_detail'] ?? $profile['experience'] ?? null,
                'notable_tours' => $profile['notable_tours'] ?? null,
                'tour_history' => $profile['tour_history'] ?? $profile['history'] ?? null,
                'strengths' => $profile['strengths'] ?? null,
                'rating' => $profile['rating'] ?? null,
                'health_status' => $profile['health_status'] ?? null,
            ];
            
            // Log để debug - kiểm tra xem dữ liệu có được map đúng không
            error_log("Mapped profile data for user_id {$userId}: " . json_encode(array_filter($mapped, fn($v) => $v !== null)));
            
            return $mapped;
        } catch (PDOException $e) {
            // Có thể bảng chưa tồn tại - ghi log và tiếp tục
            error_log('Find guide profile failed: ' . $e->getMessage());
            return null;
        }
    }

    private function buildGuideProfileData(array $guide, ?array $profile): array
    {
        $guideGroups = $this->getGuideGroups();
        
        // Nếu không có profile, trả về dữ liệu từ guide
        if (!$profile || empty($profile)) {
            error_log("No profile data found for guide ID: " . ($guide['id'] ?? 'unknown'));
            return [
                'full_name' => $guide['name'] ?? 'Chưa cập nhật',
                'dob' => null,
                'gender' => null,
                'avatar_url' => null,
                'id_number' => null,
                'address' => null,
                'phone' => null,
                'email' => $guide['email'] ?? null,
                'license' => null,
                'guide_type' => null,
                'guide_group' => null,
                'guide_group_label' => null,
                'languages' => null,
                'experience_years' => null,
                'experience_detail' => null,
                'notable_tours' => null,
                'tour_history' => null,
                'strengths' => null,
                'rating' => null,
                'health_status' => null,
            ];
        }
        
        $groupKey = $profile['guide_group'] ?? null;

        return [
            'full_name' => !empty($profile['full_name']) ? $profile['full_name'] : ($guide['name'] ?? 'Chưa cập nhật'),
            'dob' => $profile['dob'] ?? null,
            'gender' => $profile['gender'] ?? null,
            'avatar_url' => $profile['avatar_url'] ?? null,
            'id_number' => $profile['id_number'] ?? null,
            'address' => $profile['address'] ?? null,
            'phone' => $profile['phone'] ?? null,
            'email' => $profile['contact_email'] ?? $guide['email'] ?? null,
            'license' => $profile['license'] ?? null,
            'guide_type' => $profile['guide_type'] ?? null,
            'guide_group' => $groupKey,
            'guide_group_label' => $groupKey && isset($guideGroups[$groupKey]) ? $guideGroups[$groupKey] : null,
            'languages' => $profile['languages'] ?? null,
            'experience_years' => $profile['experience_years'] ?? null,
            'experience_detail' => $profile['experience_detail'] ?? null,
            'notable_tours' => $profile['notable_tours'] ?? null,
            'tour_history' => $profile['tour_history'] ?? null,
            'strengths' => $profile['strengths'] ?? null,
            'rating' => $profile['rating'] ?? null,
            'health_status' => $profile['health_status'] ?? null,
        ];
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
            'created' => ['type' => 'success', 'text' => 'Đã tạo tài khoản hướng dẫn viên thành công.'],
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

