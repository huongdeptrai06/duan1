<?php

require_once BASE_PATH . '/src/helpers/database.php';

class GuideController
{
    // List guides, optional filter by group (noidia / quocte)
    public function index(): void
    {
        requireAdmin();

        $group = $_GET['group'] ?? null;
        $db = getDB();

        if ($group && in_array($group, ['noidia', 'quocte'])) {
            $stmt = $db->prepare('SELECT * FROM guides WHERE `group` = :group ORDER BY id DESC');
            $stmt->execute([':group' => $group]);
        } else {
            $stmt = $db->query('SELECT * FROM guides ORDER BY id DESC');
        }

        $guides = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Lấy số đơn xin nghỉ chờ duyệt cho mỗi HDV
        $pendingLeaveCounts = [];
        $pendingLeaveRequests = [];
        try {
            // Tạo bảng nếu chưa tồn tại
            $db->exec("
                CREATE TABLE IF NOT EXISTS guide_leave_requests (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    guide_id INT NOT NULL,
                    start_date DATE NOT NULL,
                    end_date DATE NOT NULL,
                    reason TEXT NOT NULL,
                    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_guide_id (guide_id),
                    INDEX idx_status (status)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            $leaveStmt = $db->query('
                SELECT guide_id, COUNT(*) as count 
                FROM guide_leave_requests 
                WHERE status = "pending" 
                GROUP BY guide_id
            ');
            $counts = $leaveStmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($counts as $count) {
                $pendingLeaveCounts[$count['guide_id']] = (int)$count['count'];
            }

            // Lấy danh sách đơn xin nghỉ chờ duyệt
            // Thử join với cả guides và users để lấy tên
            $guidesTableExists = $db->query("SHOW TABLES LIKE 'guides'")->fetch();
            if ($guidesTableExists) {
                // Kiểm tra xem guides có cột user_id không
                $checkStmt = $db->query("SHOW COLUMNS FROM guides LIKE 'user_id'");
                $hasUserId = $checkStmt->fetch();
                
                if ($hasUserId) {
                    // Nếu có user_id, join qua guides rồi đến users
                    $requestsStmt = $db->query('
                        SELECT glr.*, 
                               COALESCE(g.full_name, u.name) as guide_name
                        FROM guide_leave_requests glr
                        LEFT JOIN guides g ON glr.guide_id = g.id
                        LEFT JOIN users u ON (g.user_id = u.id OR glr.guide_id = u.id) AND u.role = "huong_dan_vien"
                        WHERE glr.status = "pending"
                        ORDER BY glr.created_at DESC
                    ');
                } else {
                    // Nếu không có user_id, thử join trực tiếp với guides và users
                    $requestsStmt = $db->query('
                        SELECT glr.*, 
                               COALESCE(g.full_name, u.name) as guide_name
                        FROM guide_leave_requests glr
                        LEFT JOIN guides g ON glr.guide_id = g.id
                        LEFT JOIN users u ON glr.guide_id = u.id AND u.role = "huong_dan_vien"
                        WHERE glr.status = "pending"
                        ORDER BY glr.created_at DESC
                    ');
                }
            } else {
                // Nếu không có bảng guides, lấy từ users
                $requestsStmt = $db->query('
                    SELECT glr.*, u.name as guide_name
                    FROM guide_leave_requests glr
                    LEFT JOIN users u ON glr.guide_id = u.id AND u.role = "huong_dan_vien"
                    WHERE glr.status = "pending"
                    ORDER BY glr.created_at DESC
                ');
            }
            $pendingLeaveRequests = $requestsStmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Get pending leave requests failed: ' . $e->getMessage());
            // Bảng có thể chưa tồn tại hoặc có lỗi
        }

        view('admin.guides.index', [
            'title' => 'Danh sách hướng dẫn viên',
            'pageTitle' => 'Danh sách hướng dẫn viên',
            'guides' => $guides,
            'pendingLeaveCounts' => $pendingLeaveCounts,
            'pendingLeaveRequests' => $pendingLeaveRequests,
            'successMessage' => $_GET['success'] ?? null,
            'errorMessage' => $_GET['error'] ?? null,
        ]);
    }

    // Show create form
    public function create(): void
    {
        requireAdmin();

        view('admin.guides.create', [
            'title' => 'Thêm hướng dẫn viên',
            'pageTitle' => 'Thêm hướng dẫn viên',
        ]);
    }

    // Store new guide
    public function store(): void
    {
        requireAdmin();

        $db = getDB();

        $data = [
            'full_name' => $_POST['full_name'] ?? '',
            'dob' => $_POST['dob'] ?? null,
            'contact' => $_POST['contact'] ?? '',
            'certificates' => $_POST['certificates'] ?? '',
            'languages' => $_POST['languages'] ?? '',
            'experience' => $_POST['experience'] ?? '',
            'tour_history' => $_POST['tour_history'] ?? '',
            'rating' => $_POST['rating'] ?? null,
            'health_status' => $_POST['health_status'] ?? '',
            'group' => in_array($_POST['group'] ?? 'noidia', ['noidia','quocte']) ? $_POST['group'] : 'noidia',
            'status' => isset($_POST['status']) ? (int)$_POST['status'] : 1,
        ];

        // Handle photo upload
        $photoPath = null;
        if (!empty($_FILES['photo']['name'])) {
            $uploadsDir = BASE_PATH . '/public/uploads/guides';
            if (!is_dir($uploadsDir)) {
                mkdir($uploadsDir, 0755, true);
            }
            $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            $fileName = uniqid('guide_') . '.' . $ext;
            $target = $uploadsDir . DIRECTORY_SEPARATOR . $fileName;
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $target)) {
                $photoPath = 'uploads/guides/' . $fileName;
            }
        }

        $stmt = $db->prepare('INSERT INTO guides (full_name,dob,photo,contact,certificates,languages,experience,tour_history,rating,health_status,`group`,status,created_at,updated_at) VALUES (:full_name,:dob,:photo,:contact,:certificates,:languages,:experience,:tour_history,:rating,:health_status,:group,:status, NOW(), NOW())');
        $stmt->execute([
            ':full_name' => $data['full_name'],
            ':dob' => $data['dob'],
            ':photo' => $photoPath,
            ':contact' => $data['contact'],
            ':certificates' => $data['certificates'],
            ':languages' => $data['languages'],
            ':experience' => $data['experience'],
            ':tour_history' => $data['tour_history'],
            ':rating' => $data['rating'],
            ':health_status' => $data['health_status'],
            ':group' => $data['group'],
            ':status' => $data['status'],
        ]);

        header('Location: ' . BASE_URL . 'admin/guides');
        exit;
    }

    // Show edit form
    public function edit(): void
    {
        requireAdmin();
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: ' . BASE_URL . 'admin/guides');
            exit;
        }

        $db = getDB();
        $stmt = $db->prepare('SELECT * FROM guides WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $guide = $stmt->fetch(PDO::FETCH_ASSOC);

        view('admin.guides.edit', [
            'title' => 'Chỉnh sửa hướng dẫn viên',
            'pageTitle' => 'Chỉnh sửa hướng dẫn viên',
            'guide' => $guide,
        ]);
    }

    // Update guide and write audit log
    public function update(): void
    {
        requireAdmin();
        $id = $_POST['id'] ?? null;
        if (!$id) {
            header('Location: ' . BASE_URL . 'admin/guides');
            exit;
        }

        $db = getDB();
        $stmt = $db->prepare('SELECT * FROM guides WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $old = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        $data = [
            'full_name' => $_POST['full_name'] ?? '',
            'dob' => $_POST['dob'] ?? null,
            'contact' => $_POST['contact'] ?? '',
            'certificates' => $_POST['certificates'] ?? '',
            'languages' => $_POST['languages'] ?? '',
            'experience' => $_POST['experience'] ?? '',
            'tour_history' => $_POST['tour_history'] ?? '',
            'rating' => $_POST['rating'] ?? null,
            'health_status' => $_POST['health_status'] ?? '',
            'group' => in_array($_POST['group'] ?? 'noidia', ['noidia','quocte']) ? $_POST['group'] : 'noidia',
            'status' => isset($_POST['status']) ? (int)$_POST['status'] : 1,
        ];

        // Handle photo upload
        $photoPath = $old['photo'] ?? null;
        if (!empty($_FILES['photo']['name'])) {
            $uploadsDir = BASE_PATH . '/public/uploads/guides';
            if (!is_dir($uploadsDir)) {
                mkdir($uploadsDir, 0755, true);
            }
            $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            $fileName = uniqid('guide_') . '.' . $ext;
            $target = $uploadsDir . DIRECTORY_SEPARATOR . $fileName;
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $target)) {
                $photoPath = 'uploads/guides/' . $fileName;
            }
        }

        $stmt = $db->prepare('UPDATE guides SET full_name = :full_name, dob = :dob, photo = :photo, contact = :contact, certificates = :certificates, languages = :languages, experience = :experience, tour_history = :tour_history, rating = :rating, health_status = :health_status, `group` = :group, status = :status, updated_at = NOW() WHERE id = :id');
        $stmt->execute([
            ':full_name' => $data['full_name'],
            ':dob' => $data['dob'],
            ':photo' => $photoPath,
            ':contact' => $data['contact'],
            ':certificates' => $data['certificates'],
            ':languages' => $data['languages'],
            ':experience' => $data['experience'],
            ':tour_history' => $data['tour_history'],
            ':rating' => $data['rating'],
            ':health_status' => $data['health_status'],
            ':group' => $data['group'],
            ':status' => $data['status'],
            ':id' => $id,
        ]);

        // Write audit log: record changed fields
        $changed = [];
        $fields = ['full_name','dob','photo','contact','certificates','languages','experience','tour_history','rating','health_status','group','status'];
        foreach ($fields as $f) {
            $oldVal = $old[$f] ?? null;
            $newVal = ($f === 'photo') ? $photoPath : ($data[$f] ?? $oldVal);
            if ($oldVal != $newVal) {
                $changed[$f] = ['old' => $oldVal, 'new' => $newVal];
            }
        }

        if (!empty($changed)) {
            $user = getCurrentUser();
            $logStmt = $db->prepare('INSERT INTO guide_logs (guide_id, changed_by, change_data, created_at) VALUES (:guide_id, :changed_by, :change_data, NOW())');
            $logStmt->execute([
                ':guide_id' => $id,
                ':changed_by' => $user ? $user->id : null,
                ':change_data' => json_encode($changed, JSON_UNESCAPED_UNICODE),
            ]);
        }

        header('Location: ' . BASE_URL . 'admin/guides');
        exit;
    }

    // Show guide detail (including change log)
    public function show(): void
    {
        requireAdmin();
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: ' . BASE_URL . 'admin/guides');
            exit;
        }

        $db = getDB();
        $stmt = $db->prepare('SELECT * FROM guides WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $guide = $stmt->fetch(PDO::FETCH_ASSOC);

        $logStmt = $db->prepare('SELECT * FROM guide_logs WHERE guide_id = :id ORDER BY id DESC');
        $logStmt->execute([':id' => $id]);
        $logs = $logStmt->fetchAll(PDO::FETCH_ASSOC);

        view('admin.guides.show', [
            'title' => 'Chi tiết hướng dẫn viên',
            'pageTitle' => 'Chi tiết hướng dẫn viên',
            'guide' => $guide,
            'logs' => $logs,
        ]);
    }

    // Delete guide
    public function delete(): void
    {
        requireAdmin();
        $id = $_POST['id'] ?? null;
        if (!$id) {
            header('Location: ' . BASE_URL . 'admin/guides');
            exit;
        }

        $db = getDB();
        $stmt = $db->prepare('DELETE FROM guides WHERE id = :id');
        $stmt->execute([':id' => $id]);

        header('Location: ' . BASE_URL . 'admin/guides');
        exit;
    }

    // Dashboard cho hướng dẫn viên
    public function dashboard(): void
    {
        requireGuideOrAdmin();
        
        $currentUser = getCurrentUser();
        if (!$currentUser) {
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        $pdo = getDB();
        $errors = [];
        $successMessage = $_GET['success'] ?? null;
        $errorMessage = $_GET['error'] ?? null;

        // Lấy guide_id từ user_id
        $guideId = null;
        $guidesTableExists = $pdo->query("SHOW TABLES LIKE 'guides'")->fetch();
        
        if ($guidesTableExists) {
            try {
                $checkStmt = $pdo->query("SHOW COLUMNS FROM guides LIKE 'user_id'");
                $hasUserId = $checkStmt->fetch();
                
                if ($hasUserId) {
                    $guideStmt = $pdo->prepare('SELECT id FROM guides WHERE user_id = :user_id LIMIT 1');
                    $guideStmt->execute(['user_id' => $currentUser->id]);
                    $guide = $guideStmt->fetch();
                    if ($guide) {
                        $guideId = $guide['id'];
                    }
                } else {
                    // Nếu không có user_id, giả sử assigned_guide_id trỏ đến users.id
                    $guideId = $currentUser->id;
                }
            } catch (PDOException $e) {
                error_log('Get guide id failed: ' . $e->getMessage());
                $guideId = $currentUser->id;
            }
        } else {
            // Không có bảng guides, assigned_guide_id trỏ đến users.id
            $guideId = $currentUser->id;
        }

        // Lấy danh sách tour được phân bổ
        $assignedTours = [];
        if ($guideId) {
            try {
                $toursStmt = $pdo->prepare('
                    SELECT b.*, 
                           t.name as tour_name,
                           t.price as tour_price,
                           ts.name as status_name,
                           u.name as customer_name
                    FROM bookings b
                    LEFT JOIN tours t ON b.tour_id = t.id
                    LEFT JOIN tour_statuses ts ON b.status = ts.id
                    LEFT JOIN users u ON b.created_by = u.id
                    WHERE b.assigned_guide_id = :guide_id
                    ORDER BY b.start_date DESC, b.created_at DESC
                ');
                $toursStmt->execute(['guide_id' => $guideId]);
                $assignedTours = $toursStmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                error_log('Get assigned tours failed: ' . $e->getMessage());
                $errors[] = 'Không thể tải danh sách tour được phân bổ.';
            }
        }

        // Lấy danh sách xin nghỉ
        $leaveRequests = [];
        try {
            $leaveStmt = $pdo->prepare('
                SELECT * FROM guide_leave_requests 
                WHERE guide_id = :guide_id 
                ORDER BY created_at DESC
            ');
            $leaveStmt->execute(['guide_id' => $guideId ?? 0]);
            $leaveRequests = $leaveStmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Bảng có thể chưa tồn tại, bỏ qua
        }

        // Lấy ghi chú
        $notes = [];
        try {
            $notesStmt = $pdo->prepare('
                SELECT * FROM guide_notes 
                WHERE guide_id = :guide_id 
                ORDER BY created_at DESC
            ');
            $notesStmt->execute(['guide_id' => $guideId ?? 0]);
            $notes = $notesStmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Bảng có thể chưa tồn tại, bỏ qua
        }

        // Lấy danh sách xác nhận tour
        $confirmationsMap = [];
        if ($guideId) {
            try {
                $confStmt = $pdo->prepare('
                    SELECT booking_id, confirmed, confirmed_at 
                    FROM guide_tour_confirmations 
                    WHERE guide_id = :guide_id
                ');
                $confStmt->execute(['guide_id' => $guideId]);
                $confirmations = $confStmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($confirmations as $conf) {
                    $confirmationsMap[$conf['booking_id']] = $conf;
                }
            } catch (PDOException $e) {
                // Bảng có thể chưa tồn tại, bỏ qua
            }
        }

        view('guides.dashboard', [
            'title' => 'Dashboard HDV',
            'pageTitle' => 'Dashboard HDV',
            'assignedTours' => $assignedTours,
            'leaveRequests' => $leaveRequests,
            'notes' => $notes,
            'confirmationsMap' => $confirmationsMap,
            'errors' => $errors,
            'successMessage' => $successMessage,
            'errorMessage' => $errorMessage,
            'guideId' => $guideId,
        ]);
    }

    // Xử lý xin nghỉ
    public function requestLeave(): void
    {
        requireGuideOrAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'guides/dashboard');
            exit;
        }

        $currentUser = getCurrentUser();
        $pdo = getDB();

        // Lấy guide_id
        $guideId = null;
        $guidesTableExists = $pdo->query("SHOW TABLES LIKE 'guides'")->fetch();
        
        if ($guidesTableExists) {
            try {
                $checkStmt = $pdo->query("SHOW COLUMNS FROM guides LIKE 'user_id'");
                $hasUserId = $checkStmt->fetch();
                
                if ($hasUserId) {
                    $guideStmt = $pdo->prepare('SELECT id FROM guides WHERE user_id = :user_id LIMIT 1');
                    $guideStmt->execute(['user_id' => $currentUser->id]);
                    $guide = $guideStmt->fetch();
                    if ($guide) {
                        $guideId = $guide['id'];
                    }
                } else {
                    $guideId = $currentUser->id;
                }
            } catch (PDOException $e) {
                $guideId = $currentUser->id;
            }
        } else {
            $guideId = $currentUser->id;
        }

        $startDate = $_POST['start_date'] ?? null;
        $endDate = $_POST['end_date'] ?? null;
        $reason = trim($_POST['reason'] ?? '');

        if (!$startDate || !$endDate || !$reason) {
            header('Location: ' . BASE_URL . 'guides/dashboard&error=' . urlencode('Vui lòng điền đầy đủ thông tin.'));
            exit;
        }

        try {
            // Tạo bảng nếu chưa tồn tại
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS guide_leave_requests (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    guide_id INT NOT NULL,
                    start_date DATE NOT NULL,
                    end_date DATE NOT NULL,
                    reason TEXT NOT NULL,
                    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_guide_id (guide_id),
                    INDEX idx_status (status)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            $stmt = $pdo->prepare('
                INSERT INTO guide_leave_requests (guide_id, start_date, end_date, reason, status, created_at)
                VALUES (:guide_id, :start_date, :end_date, :reason, "pending", NOW())
            ');
            $stmt->execute([
                'guide_id' => $guideId,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'reason' => $reason,
            ]);

            header('Location: ' . BASE_URL . 'guides/dashboard&success=' . urlencode('Đã gửi đơn xin nghỉ thành công.'));
        } catch (PDOException $e) {
            error_log('Request leave failed: ' . $e->getMessage());
            header('Location: ' . BASE_URL . 'guides/dashboard&error=' . urlencode('Không thể gửi đơn xin nghỉ.'));
        }
        exit;
    }

    // Xử lý thêm ghi chú
    public function addNote(): void
    {
        requireGuideOrAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'guides/dashboard');
            exit;
        }

        $currentUser = getCurrentUser();
        $pdo = getDB();

        // Lấy guide_id
        $guideId = null;
        $guidesTableExists = $pdo->query("SHOW TABLES LIKE 'guides'")->fetch();
        
        if ($guidesTableExists) {
            try {
                $checkStmt = $pdo->query("SHOW COLUMNS FROM guides LIKE 'user_id'");
                $hasUserId = $checkStmt->fetch();
                
                if ($hasUserId) {
                    $guideStmt = $pdo->prepare('SELECT id FROM guides WHERE user_id = :user_id LIMIT 1');
                    $guideStmt->execute(['user_id' => $currentUser->id]);
                    $guide = $guideStmt->fetch();
                    if ($guide) {
                        $guideId = $guide['id'];
                    }
                } else {
                    $guideId = $currentUser->id;
                }
            } catch (PDOException $e) {
                $guideId = $currentUser->id;
            }
        } else {
            $guideId = $currentUser->id;
        }

        $note = trim($_POST['note'] ?? '');

        if (!$note) {
            header('Location: ' . BASE_URL . 'guides/dashboard&error=' . urlencode('Vui lòng nhập ghi chú.'));
            exit;
        }

        try {
            // Tạo bảng nếu chưa tồn tại với trạng thái pending
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS guide_notes (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    guide_id INT NOT NULL,
                    note TEXT NOT NULL,
                    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_guide_id (guide_id),
                    INDEX idx_status (status)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            $stmt = $pdo->prepare('
                INSERT INTO guide_notes (guide_id, note, status, created_at)
                VALUES (:guide_id, :note, "pending", NOW())
            ');
            $stmt->execute([
                'guide_id' => $guideId,
                'note' => $note,
            ]);

            header('Location: ' . BASE_URL . 'admin/tours&success=' . urlencode('Đã gửi yêu cầu thêm ghi chú. Vui lòng chờ admin duyệt.'));
        } catch (PDOException $e) {
            error_log('Add note failed: ' . $e->getMessage());
            header('Location: ' . BASE_URL . 'guides/dashboard&error=' . urlencode('Không thể thêm ghi chú.'));
        }
        exit;
    }

    // Xác nhận tour
    public function confirmTour(): void
    {
        requireGuideOrAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'guides/dashboard');
            exit;
        }

        $bookingId = (int)($_POST['booking_id'] ?? 0);
        $confirmed = isset($_POST['confirmed']) && $_POST['confirmed'] == '1';

        if ($bookingId <= 0) {
            header('Location: ' . BASE_URL . 'guides/dashboard&error=' . urlencode('Booking không hợp lệ.'));
            exit;
        }

        $currentUser = getCurrentUser();
        $pdo = getDB();

        // Lấy guide_id
        $guideId = null;
        $guidesTableExists = $pdo->query("SHOW TABLES LIKE 'guides'")->fetch();
        
        if ($guidesTableExists) {
            try {
                $checkStmt = $pdo->query("SHOW COLUMNS FROM guides LIKE 'user_id'");
                $hasUserId = $checkStmt->fetch();
                
                if ($hasUserId) {
                    $guideStmt = $pdo->prepare('SELECT id FROM guides WHERE user_id = :user_id LIMIT 1');
                    $guideStmt->execute(['user_id' => $currentUser->id]);
                    $guide = $guideStmt->fetch();
                    if ($guide) {
                        $guideId = $guide['id'];
                    }
                } else {
                    $guideId = $currentUser->id;
                }
            } catch (PDOException $e) {
                $guideId = $currentUser->id;
            }
        } else {
            $guideId = $currentUser->id;
        }

        // Kiểm tra booking có thuộc về guide này không
        try {
            $checkStmt = $pdo->prepare('SELECT id FROM bookings WHERE id = :id AND assigned_guide_id = :guide_id LIMIT 1');
            $checkStmt->execute(['id' => $bookingId, 'guide_id' => $guideId]);
            $booking = $checkStmt->fetch();

            if (!$booking) {
                header('Location: ' . BASE_URL . 'guides/dashboard&error=' . urlencode('Bạn không có quyền xác nhận tour này.'));
                exit;
            }

            // Tạo bảng nếu chưa tồn tại với trạng thái pending
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS guide_tour_confirmations (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    booking_id INT NOT NULL,
                    guide_id INT NOT NULL,
                    confirmed TINYINT(1) DEFAULT 0,
                    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
                    confirmed_at DATETIME NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    UNIQUE KEY unique_booking_guide (booking_id, guide_id),
                    INDEX idx_guide_id (guide_id),
                    INDEX idx_booking_id (booking_id),
                    INDEX idx_status (status)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            // Thêm cột status nếu chưa có
            try {
                $pdo->exec("ALTER TABLE guide_tour_confirmations ADD COLUMN status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending' AFTER confirmed");
            } catch (PDOException $e) {
                // Cột đã tồn tại, bỏ qua
            }

            if ($confirmed) {
                $stmt = $pdo->prepare('
                    INSERT INTO guide_tour_confirmations (booking_id, guide_id, confirmed, status, created_at)
                    VALUES (:booking_id, :guide_id, 1, "pending", NOW())
                    ON DUPLICATE KEY UPDATE confirmed = 1, status = "pending", updated_at = NOW()
                ');
            } else {
                $stmt = $pdo->prepare('
                    INSERT INTO guide_tour_confirmations (booking_id, guide_id, confirmed, status, confirmed_at, created_at)
                    VALUES (:booking_id, :guide_id, 0, "pending", NULL, NOW())
                    ON DUPLICATE KEY UPDATE confirmed = 0, status = "pending", confirmed_at = NULL, updated_at = NOW()
                ');
            }
            
            $stmt->execute([
                'booking_id' => $bookingId,
                'guide_id' => $guideId,
            ]);

            header('Location: ' . BASE_URL . 'admin/tours&success=' . urlencode('Đã gửi yêu cầu xác nhận tour. Vui lòng chờ admin duyệt.'));
        } catch (PDOException $e) {
            error_log('Confirm tour failed: ' . $e->getMessage());
            header('Location: ' . BASE_URL . 'guides/dashboard&error=' . urlencode('Không thể xác nhận tour.'));
        }
        exit;
    }

    // Từ chối tour - gửi yêu cầu cho admin duyệt
    public function rejectTour(): void
    {
        requireGuideOrAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'admin/tours');
            exit;
        }

        $bookingId = (int)($_POST['booking_id'] ?? 0);
        $reason = trim($_POST['reason'] ?? '');

        if ($bookingId <= 0) {
            header('Location: ' . BASE_URL . 'admin/tours&error=' . urlencode('Booking không hợp lệ.'));
            exit;
        }

        if (empty($reason)) {
            header('Location: ' . BASE_URL . 'admin/tours&error=' . urlencode('Vui lòng nhập lý do từ chối.'));
            exit;
        }

        $currentUser = getCurrentUser();
        $pdo = getDB();

        // Lấy guide_id
        $guideId = null;
        $guidesTableExists = $pdo->query("SHOW TABLES LIKE 'guides'")->fetch();
        
        if ($guidesTableExists) {
            try {
                $checkStmt = $pdo->query("SHOW COLUMNS FROM guides LIKE 'user_id'");
                $hasUserId = $checkStmt->fetch();
                
                if ($hasUserId) {
                    $guideStmt = $pdo->prepare('SELECT id FROM guides WHERE user_id = :user_id LIMIT 1');
                    $guideStmt->execute(['user_id' => $currentUser->id]);
                    $guide = $guideStmt->fetch();
                    if ($guide) {
                        $guideId = $guide['id'];
                    }
                } else {
                    $guideId = $currentUser->id;
                }
            } catch (PDOException $e) {
                $guideId = $currentUser->id;
            }
        } else {
            $guideId = $currentUser->id;
        }

        // Kiểm tra booking có thuộc về guide này không
        try {
            $checkStmt = $pdo->prepare('SELECT id FROM bookings WHERE id = :id AND assigned_guide_id = :guide_id LIMIT 1');
            $checkStmt->execute(['id' => $bookingId, 'guide_id' => $guideId]);
            $booking = $checkStmt->fetch();

            if (!$booking) {
                header('Location: ' . BASE_URL . 'admin/tours&error=' . urlencode('Bạn không có quyền từ chối tour này.'));
                exit;
            }

            // Tạo bảng nếu chưa tồn tại
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS guide_tour_rejections (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    booking_id INT NOT NULL,
                    guide_id INT NOT NULL,
                    reason TEXT NOT NULL,
                    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
                    admin_note TEXT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_guide_id (guide_id),
                    INDEX idx_booking_id (booking_id),
                    INDEX idx_status (status)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            // Kiểm tra xem đã có yêu cầu từ chối chưa
            $checkRejectionStmt = $pdo->prepare('SELECT id FROM guide_tour_rejections WHERE booking_id = :booking_id AND guide_id = :guide_id AND status = "pending" LIMIT 1');
            $checkRejectionStmt->execute(['booking_id' => $bookingId, 'guide_id' => $guideId]);
            $existingRejection = $checkRejectionStmt->fetch();

            if ($existingRejection) {
                header('Location: ' . BASE_URL . 'admin/tours&error=' . urlencode('Bạn đã gửi yêu cầu từ chối tour này. Vui lòng chờ admin duyệt.'));
                exit;
            }

            // Thêm yêu cầu từ chối
            $stmt = $pdo->prepare('
                INSERT INTO guide_tour_rejections (booking_id, guide_id, reason, status, created_at)
                VALUES (:booking_id, :guide_id, :reason, "pending", NOW())
            ');
            
            $stmt->execute([
                'booking_id' => $bookingId,
                'guide_id' => $guideId,
                'reason' => $reason,
            ]);

            header('Location: ' . BASE_URL . 'admin/tours&success=' . urlencode('Đã gửi yêu cầu từ chối tour. Vui lòng chờ admin duyệt.'));
        } catch (PDOException $e) {
            error_log('Reject tour failed: ' . $e->getMessage());
            header('Location: ' . BASE_URL . 'admin/tours&error=' . urlencode('Không thể gửi yêu cầu từ chối tour.'));
        }
        exit;
    }

    // Xử lý xác nhận/từ chối đơn xin nghỉ
    public function processLeaveRequest(): void
    {
        requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'admin/guides');
            exit;
        }

        $requestId = (int)($_POST['request_id'] ?? 0);
        $action = $_POST['action'] ?? ''; // 'approve' hoặc 'reject'
        $note = trim($_POST['note'] ?? '');

        if ($requestId <= 0 || !in_array($action, ['approve', 'reject'])) {
            header('Location: ' . BASE_URL . 'admin/guides&error=' . urlencode('Thông tin không hợp lệ.'));
            exit;
        }

        $pdo = getDB();
        if ($pdo === null) {
            header('Location: ' . BASE_URL . 'admin/guides&error=' . urlencode('Không thể kết nối database.'));
            exit;
        }

        try {
            $status = $action === 'approve' ? 'approved' : 'rejected';
            $stmt = $pdo->prepare('
                UPDATE guide_leave_requests 
                SET status = :status, updated_at = NOW() 
                WHERE id = :id
            ');
            $stmt->execute([
                'status' => $status,
                'id' => $requestId,
            ]);

            // Ghi log nếu có ghi chú
            if ($note) {
                try {
                    $pdo->exec("
                        CREATE TABLE IF NOT EXISTS guide_leave_request_notes (
                            id INT AUTO_INCREMENT PRIMARY KEY,
                            request_id INT NOT NULL,
                            note TEXT,
                            created_by INT,
                            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                            INDEX idx_request_id (request_id)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                    ");

                    $currentUser = getCurrentUser();
                    $noteStmt = $pdo->prepare('
                        INSERT INTO guide_leave_request_notes (request_id, note, created_by, created_at)
                        VALUES (:request_id, :note, :created_by, NOW())
                    ');
                    $noteStmt->execute([
                        'request_id' => $requestId,
                        'note' => $note,
                        'created_by' => $currentUser ? $currentUser->id : null,
                    ]);
                } catch (PDOException $e) {
                    error_log('Add leave request note failed: ' . $e->getMessage());
                }
            }

            $message = $action === 'approve' ? 'Đã duyệt đơn xin nghỉ thành công.' : 'Đã từ chối đơn xin nghỉ.';
            header('Location: ' . BASE_URL . 'admin/guides/requests&success=' . urlencode($message));
        } catch (PDOException $e) {
            error_log('Process leave request failed: ' . $e->getMessage());
            header('Location: ' . BASE_URL . 'admin/guides/requests&error=' . urlencode('Không thể xử lý đơn xin nghỉ.'));
        }
        exit;
    }

    // Trang quản lý tất cả yêu cầu từ HDV
    public function requests(): void
    {
        requireAdmin();

        $pdo = getDB();
        $errors = [];
        $pendingLeaveRequests = [];
        $pendingNotes = [];
        $pendingConfirmations = [];
        $pendingRejections = [];

        try {
            // Tạo các bảng nếu chưa tồn tại
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS guide_leave_requests (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    guide_id INT NOT NULL,
                    start_date DATE NOT NULL,
                    end_date DATE NOT NULL,
                    reason TEXT NOT NULL,
                    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_guide_id (guide_id),
                    INDEX idx_status (status)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            $pdo->exec("
                CREATE TABLE IF NOT EXISTS guide_notes (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    guide_id INT NOT NULL,
                    note TEXT NOT NULL,
                    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_guide_id (guide_id),
                    INDEX idx_status (status)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            // Lấy đơn xin nghỉ chờ duyệt
            $leaveStmt = $pdo->query('
                SELECT glr.*, u.name as guide_name
                FROM guide_leave_requests glr
                LEFT JOIN users u ON glr.guide_id = u.id AND u.role = "huong_dan_vien"
                WHERE glr.status = "pending"
                ORDER BY glr.created_at DESC
            ');
            $pendingLeaveRequests = $leaveStmt->fetchAll(PDO::FETCH_ASSOC);

            // Lấy ghi chú chờ duyệt
            $notesStmt = $pdo->query('
                SELECT gn.*, u.name as guide_name
                FROM guide_notes gn
                LEFT JOIN users u ON gn.guide_id = u.id AND u.role = "huong_dan_vien"
                WHERE gn.status = "pending"
                ORDER BY gn.created_at DESC
            ');
            $pendingNotes = $notesStmt->fetchAll(PDO::FETCH_ASSOC);

            // Lấy xác nhận tour chờ duyệt
            try {
                $confStmt = $pdo->query('
                    SELECT gtc.*, 
                           u.name as guide_name,
                           t.name as tour_name,
                           b.start_date as booking_start_date
                    FROM guide_tour_confirmations gtc
                    LEFT JOIN users u ON gtc.guide_id = u.id AND u.role = "huong_dan_vien"
                    LEFT JOIN bookings b ON gtc.booking_id = b.id
                    LEFT JOIN tours t ON b.tour_id = t.id
                    WHERE gtc.status = "pending"
                    ORDER BY gtc.created_at DESC
                ');
                $pendingConfirmations = $confStmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                // Bảng có thể chưa có cột status
                error_log('Get pending confirmations failed: ' . $e->getMessage());
            }

            // Tạo bảng guide_tour_rejections nếu chưa tồn tại
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS guide_tour_rejections (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    booking_id INT NOT NULL,
                    guide_id INT NOT NULL,
                    reason TEXT NOT NULL,
                    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
                    admin_note TEXT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_guide_id (guide_id),
                    INDEX idx_booking_id (booking_id),
                    INDEX idx_status (status)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            // Lấy yêu cầu từ chối tour chờ duyệt
            try {
                // Kiểm tra xem bảng guides có tồn tại và có cột user_id không
                $guidesTableExists = $pdo->query("SHOW TABLES LIKE 'guides'")->fetch();
                $hasUserId = false;
                
                if ($guidesTableExists) {
                    try {
                        $checkStmt = $pdo->query("SHOW COLUMNS FROM guides LIKE 'user_id'");
                        $hasUserId = (bool)$checkStmt->fetch();
                    } catch (PDOException $e) {
                        // Bỏ qua
                    }
                }

                if ($guidesTableExists && $hasUserId) {
                    // Nếu có bảng guides và có user_id
                    $rejectStmt = $pdo->query('
                        SELECT gtr.*, 
                               COALESCE(g.full_name, u.name) as guide_name,
                               t.name as tour_name,
                               b.start_date as booking_start_date,
                               b.end_date as booking_end_date,
                               b.id as booking_id
                        FROM guide_tour_rejections gtr
                        LEFT JOIN bookings b ON gtr.booking_id = b.id
                        LEFT JOIN tours t ON b.tour_id = t.id
                        LEFT JOIN guides g ON gtr.guide_id = g.id
                        LEFT JOIN users u ON g.user_id = u.id AND u.role = "huong_dan_vien"
                        WHERE gtr.status = "pending"
                        ORDER BY gtr.created_at DESC
                    ');
                } else {
                    // Nếu không có bảng guides hoặc không có user_id, join trực tiếp với users
                    $rejectStmt = $pdo->query('
                        SELECT gtr.*, 
                               u.name as guide_name,
                               t.name as tour_name,
                               b.start_date as booking_start_date,
                               b.end_date as booking_end_date,
                               b.id as booking_id
                        FROM guide_tour_rejections gtr
                        LEFT JOIN bookings b ON gtr.booking_id = b.id
                        LEFT JOIN tours t ON b.tour_id = t.id
                        LEFT JOIN users u ON gtr.guide_id = u.id AND u.role = "huong_dan_vien"
                        WHERE gtr.status = "pending"
                        ORDER BY gtr.created_at DESC
                    ');
                }
                
                $pendingRejections = $rejectStmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                // Bảng có thể chưa tồn tại
                error_log('Get pending rejections failed: ' . $e->getMessage());
                $pendingRejections = [];
            }

        } catch (PDOException $e) {
            error_log('Get pending requests failed: ' . $e->getMessage());
            $errors[] = 'Không thể tải danh sách yêu cầu.';
        }

        view('admin.guides.requests', [
            'title' => 'Quản lý yêu cầu HDV',
            'pageTitle' => 'Quản lý yêu cầu HDV',
            'pendingLeaveRequests' => $pendingLeaveRequests,
            'pendingNotes' => $pendingNotes,
            'pendingConfirmations' => $pendingConfirmations,
            'pendingRejections' => $pendingRejections,
            'errors' => $errors,
            'successMessage' => $_GET['success'] ?? null,
            'errorMessage' => $_GET['error'] ?? null,
        ]);
    }

    // Xử lý duyệt/từ chối ghi chú
    public function processNote(): void
    {
        requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'admin/guides/requests');
            exit;
        }

        $noteId = (int)($_POST['note_id'] ?? 0);
        $action = $_POST['action'] ?? '';

        if ($noteId <= 0 || !in_array($action, ['approve', 'reject'])) {
            header('Location: ' . BASE_URL . 'admin/guides/requests&error=' . urlencode('Thông tin không hợp lệ.'));
            exit;
        }

        $pdo = getDB();
        if ($pdo === null) {
            header('Location: ' . BASE_URL . 'admin/guides/requests&error=' . urlencode('Không thể kết nối database.'));
            exit;
        }

        try {
            $status = $action === 'approve' ? 'approved' : 'rejected';
            $stmt = $pdo->prepare('
                UPDATE guide_notes 
                SET status = :status, updated_at = NOW() 
                WHERE id = :id
            ');
            $stmt->execute([
                'status' => $status,
                'id' => $noteId,
            ]);

            $message = $action === 'approve' ? 'Đã duyệt ghi chú thành công.' : 'Đã từ chối ghi chú.';
            header('Location: ' . BASE_URL . 'admin/guides/requests&success=' . urlencode($message));
        } catch (PDOException $e) {
            error_log('Process note failed: ' . $e->getMessage());
            header('Location: ' . BASE_URL . 'admin/guides/requests&error=' . urlencode('Không thể xử lý ghi chú.'));
        }
        exit;
    }

    // Xử lý duyệt/từ chối xác nhận tour
    public function processConfirmation(): void
    {
        requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'admin/guides/requests');
            exit;
        }

        $confirmationId = (int)($_POST['confirmation_id'] ?? 0);
        $action = $_POST['action'] ?? '';

        if ($confirmationId <= 0 || !in_array($action, ['approve', 'reject'])) {
            header('Location: ' . BASE_URL . 'admin/guides/requests&error=' . urlencode('Thông tin không hợp lệ.'));
            exit;
        }

        $pdo = getDB();
        if ($pdo === null) {
            header('Location: ' . BASE_URL . 'admin/guides/requests&error=' . urlencode('Không thể kết nối database.'));
            exit;
        }

        try {
            $status = $action === 'approve' ? 'approved' : 'rejected';
            $confirmed = $action === 'approve' ? 1 : 0;
            
            $stmt = $pdo->prepare('
                UPDATE guide_tour_confirmations 
                SET status = :status, 
                    confirmed = :confirmed,
                    confirmed_at = CASE WHEN :confirmed = 1 THEN NOW() ELSE NULL END,
                    updated_at = NOW() 
                WHERE id = :id
            ');
            $stmt->execute([
                'status' => $status,
                'confirmed' => $confirmed,
                'id' => $confirmationId,
            ]);

            $message = $action === 'approve' ? 'Đã duyệt xác nhận tour thành công.' : 'Đã từ chối xác nhận tour.';
            header('Location: ' . BASE_URL . 'admin/guides/requests&success=' . urlencode($message));
        } catch (PDOException $e) {
            error_log('Process confirmation failed: ' . $e->getMessage());
            header('Location: ' . BASE_URL . 'admin/guides/requests&error=' . urlencode('Không thể xử lý xác nhận tour.'));
        }
        exit;
    }

    // Xử lý duyệt/từ chối yêu cầu từ chối tour
    public function processRejection(): void
    {
        requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'admin/guides/requests');
            exit;
        }

        $rejectionId = (int)($_POST['rejection_id'] ?? 0);
        $action = $_POST['action'] ?? '';
        $adminNote = trim($_POST['admin_note'] ?? '');

        if ($rejectionId <= 0 || !in_array($action, ['approve', 'reject'])) {
            header('Location: ' . BASE_URL . 'admin/guides/requests&error=' . urlencode('Thông tin không hợp lệ.'));
            exit;
        }

        $pdo = getDB();
        if ($pdo === null) {
            header('Location: ' . BASE_URL . 'admin/guides/requests&error=' . urlencode('Không thể kết nối database.'));
            exit;
        }

        try {
            $status = $action === 'approve' ? 'approved' : 'rejected';
            
            // Lấy thông tin rejection để cập nhật booking
            $rejectionStmt = $pdo->prepare('SELECT booking_id, guide_id FROM guide_tour_rejections WHERE id = :id LIMIT 1');
            $rejectionStmt->execute(['id' => $rejectionId]);
            $rejection = $rejectionStmt->fetch();
            
            if (!$rejection) {
                header('Location: ' . BASE_URL . 'admin/guides/requests&error=' . urlencode('Không tìm thấy yêu cầu từ chối.'));
                exit;
            }

            // Cập nhật status của rejection
            $stmt = $pdo->prepare('
                UPDATE guide_tour_rejections 
                SET status = :status, 
                    admin_note = :admin_note,
                    updated_at = NOW() 
                WHERE id = :id
            ');
            $stmt->execute([
                'status' => $status,
                'admin_note' => $adminNote ?: null,
                'id' => $rejectionId,
            ]);

            // Nếu duyệt yêu cầu từ chối, xóa assigned_guide_id khỏi booking
            if ($action === 'approve') {
                $updateBookingStmt = $pdo->prepare('
                    UPDATE bookings 
                    SET assigned_guide_id = NULL, updated_at = NOW() 
                    WHERE id = :booking_id
                ');
                $updateBookingStmt->execute(['booking_id' => $rejection['booking_id']]);
            }

            $message = $action === 'approve' ? 'Đã duyệt yêu cầu từ chối tour. Tour đã được gỡ khỏi hướng dẫn viên.' : 'Đã từ chối yêu cầu từ chối tour.';
            header('Location: ' . BASE_URL . 'admin/guides/requests&success=' . urlencode($message));
        } catch (PDOException $e) {
            error_log('Process rejection failed: ' . $e->getMessage());
            header('Location: ' . BASE_URL . 'admin/guides/requests&error=' . urlencode('Không thể xử lý yêu cầu từ chối tour.'));
        }
        exit;
    }
}
