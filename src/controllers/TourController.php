<?php

require_once BASE_PATH . '/src/helpers/database.php';

class TourController
{
    // Danh sách tours - guide chỉ xem tours được phân bổ qua bookings
    public function index(): void
    {
        requireGuideOrAdmin();

        $pdo = getDB();
        $errors = [];
        $tours = [];
        $currentUser = getCurrentUser();
        $isGuide = isGuide() && !isAdmin();

        if ($pdo === null) {
            $errors[] = 'Không thể kết nối cơ sở dữ liệu.';
        } else {
            try {
                // Kiểm tra xem bảng guides có tồn tại không
                $guidesTableExists = $pdo->query("SHOW TABLES LIKE 'guides'")->fetch();
                
                if ($isGuide && $currentUser) {
                    // Guide chỉ xem tours từ bookings được gán cho họ
                    if ($guidesTableExists) {
                        // Kiểm tra xem guides có cột user_id không
                        try {
                            $checkStmt = $pdo->query("SHOW COLUMNS FROM guides LIKE 'user_id'");
                            $hasUserId = $checkStmt->fetch();
                            
                            if ($hasUserId) {
                                // Tìm guide_id từ user_id
                                $guideStmt = $pdo->prepare('SELECT id FROM guides WHERE user_id = :user_id LIMIT 1');
                                $guideStmt->execute(['user_id' => $currentUser->id]);
                                $guide = $guideStmt->fetch();
                                if ($guide) {
                                    $query = 'SELECT DISTINCT t.*, c.name as category_name,
                                             (SELECT b.id FROM bookings b 
                                              WHERE b.tour_id = t.id AND b.assigned_guide_id = :guide_id 
                                              ORDER BY b.start_date DESC LIMIT 1) as booking_id,
                                             (SELECT b.end_date FROM bookings b 
                                              WHERE b.tour_id = t.id AND b.assigned_guide_id = :guide_id 
                                              ORDER BY b.start_date DESC LIMIT 1) as booking_end_date,
                                             (SELECT ts.id FROM bookings b 
                                              LEFT JOIN tour_statuses ts ON b.status = ts.id
                                              WHERE b.tour_id = t.id AND b.assigned_guide_id = :guide_id 
                                              ORDER BY b.start_date DESC LIMIT 1) as booking_status_id,
                                             (SELECT ts.name FROM bookings b 
                                              LEFT JOIN tour_statuses ts ON b.status = ts.id
                                              WHERE b.tour_id = t.id AND b.assigned_guide_id = :guide_id 
                                              ORDER BY b.start_date DESC LIMIT 1) as booking_status_name
                                             FROM tours t
                                             LEFT JOIN categories c ON t.category_id = c.id
                                             INNER JOIN bookings b ON t.id = b.tour_id
                                             WHERE b.assigned_guide_id = :guide_id AND t.status = 1
                                             ORDER BY t.created_at DESC';
                                    $params = ['guide_id' => $guide['id']];
                                } else {
                                    // Không tìm thấy guide, không hiển thị tour nào
                                    $tours = [];
                                }
                            } else {
                                // Không có user_id, giả sử assigned_guide_id trỏ đến users.id
                                $query = 'SELECT DISTINCT t.*, c.name as category_name,
                                         (SELECT b.id FROM bookings b 
                                          WHERE b.tour_id = t.id AND b.assigned_guide_id = :user_id 
                                          ORDER BY b.start_date DESC LIMIT 1) as booking_id,
                                         (SELECT b.end_date FROM bookings b 
                                          WHERE b.tour_id = t.id AND b.assigned_guide_id = :user_id 
                                          ORDER BY b.start_date DESC LIMIT 1) as booking_end_date,
                                         (SELECT ts.id FROM bookings b 
                                          LEFT JOIN tour_statuses ts ON b.status = ts.id
                                          WHERE b.tour_id = t.id AND b.assigned_guide_id = :user_id 
                                          ORDER BY b.start_date DESC LIMIT 1) as booking_status_id,
                                         (SELECT ts.name FROM bookings b 
                                          LEFT JOIN tour_statuses ts ON b.status = ts.id
                                          WHERE b.tour_id = t.id AND b.assigned_guide_id = :user_id 
                                          ORDER BY b.start_date DESC LIMIT 1) as booking_status_name
                                         FROM tours t
                                         LEFT JOIN categories c ON t.category_id = c.id
                                         INNER JOIN bookings b ON t.id = b.tour_id
                                         WHERE b.assigned_guide_id = :user_id AND t.status = 1
                                         ORDER BY t.created_at DESC';
                                $params = ['user_id' => $currentUser->id];
                            }
                        } catch (PDOException $e) {
                            error_log('Check guides structure failed: ' . $e->getMessage());
                            // Fallback: giả sử assigned_guide_id trỏ đến users.id
                            $query = 'SELECT DISTINCT t.*, c.name as category_name,
                                     (SELECT b.id FROM bookings b 
                                      WHERE b.tour_id = t.id AND b.assigned_guide_id = :user_id 
                                      ORDER BY b.start_date DESC LIMIT 1) as booking_id,
                                     (SELECT b.end_date FROM bookings b 
                                      WHERE b.tour_id = t.id AND b.assigned_guide_id = :user_id 
                                      ORDER BY b.start_date DESC LIMIT 1) as booking_end_date,
                                     (SELECT ts.id FROM bookings b 
                                      LEFT JOIN tour_statuses ts ON b.status = ts.id
                                      WHERE b.tour_id = t.id AND b.assigned_guide_id = :user_id 
                                      ORDER BY b.start_date DESC LIMIT 1) as booking_status_id,
                                     (SELECT ts.name FROM bookings b 
                                      LEFT JOIN tour_statuses ts ON b.status = ts.id
                                      WHERE b.tour_id = t.id AND b.assigned_guide_id = :user_id 
                                      ORDER BY b.start_date DESC LIMIT 1) as booking_status_name
                                     FROM tours t
                                     LEFT JOIN categories c ON t.category_id = c.id
                                     INNER JOIN bookings b ON t.id = b.tour_id
                                     WHERE b.assigned_guide_id = :user_id AND t.status = 1
                                     ORDER BY t.created_at DESC';
                            $params = ['user_id' => $currentUser->id];
                        }
                    } else {
                        // Không có bảng guides, assigned_guide_id trỏ đến users.id
                        $query = 'SELECT DISTINCT t.*, c.name as category_name
                                 FROM tours t
                                 LEFT JOIN categories c ON t.category_id = c.id
                                 INNER JOIN bookings b ON t.id = b.tour_id
                                 WHERE b.assigned_guide_id = :user_id AND t.status = 1
                                 ORDER BY t.created_at DESC';
                        $params = ['user_id' => $currentUser->id];
                    }
                    
                    if (isset($query)) {
                        $stmt = $pdo->prepare($query);
                        $stmt->execute($params);
                        $tours = $stmt->fetchAll();
                    }
                } else {
                    // Admin xem tất cả tours (cả status = 0 và 1)
                    $query = 'SELECT t.*, c.name as category_name
                             FROM tours t
                             LEFT JOIN categories c ON t.category_id = c.id
                             ORDER BY t.created_at DESC';
                    $stmt = $pdo->prepare($query);
                    $stmt->execute();
                    $tours = $stmt->fetchAll();
                }
            } catch (PDOException $e) {
                error_log('Tours index failed: ' . $e->getMessage());
                $errors[] = 'Không thể tải danh sách tour.';
            }
        }

        // Nếu là HDV, lấy thêm dữ liệu cho dashboard
        $assignedBookings = [];
        $completedBookings = [];
        $leaveRequests = [];
        $notes = [];
        $confirmationsMap = [];
        $rejectionsMap = [];
        $guideId = null;

        if ($isGuide && $currentUser) {
            // Lấy guide_id
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

            // Lấy danh sách booking được phân bổ
            if ($guideId) {
                try {
                    $bookingsStmt = $pdo->prepare('
                        SELECT b.*, 
                               t.name as tour_name,
                               t.price as tour_price,
                               ts.name as status_name,
                               ts.id as status_id,
                               u.name as customer_name
                        FROM bookings b
                        LEFT JOIN tours t ON b.tour_id = t.id
                        LEFT JOIN tour_statuses ts ON b.status = ts.id
                        LEFT JOIN users u ON b.created_by = u.id
                        WHERE b.assigned_guide_id = :guide_id
                        ORDER BY b.start_date DESC, b.created_at DESC
                    ');
                    $bookingsStmt->execute(['guide_id' => $guideId]);
                    $allBookings = $bookingsStmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    // Tách tour đã hoàn thành và tour chưa hoàn thành
                    foreach ($allBookings as $booking) {
                        if (stripos($booking['status_name'] ?? '', 'Hoàn thành') !== false || (int)($booking['status_id'] ?? 0) === 4) {
                            $completedBookings[] = $booking;
                        } else {
                            $assignedBookings[] = $booking;
                        }
                    }
                } catch (PDOException $e) {
                    error_log('Get assigned bookings failed: ' . $e->getMessage());
                }
            }

            // Lấy danh sách xin nghỉ
            try {
                $leaveStmt = $pdo->prepare('
                    SELECT * FROM guide_leave_requests 
                    WHERE guide_id = :guide_id 
                    ORDER BY created_at DESC
                ');
                $leaveStmt->execute(['guide_id' => $guideId ?? 0]);
                $leaveRequests = $leaveStmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                // Bảng có thể chưa tồn tại
            }

            // Lấy ghi chú - lấy tất cả các ghi chú (lịch sử)
            try {
                $notesStmt = $pdo->prepare('
                    SELECT * FROM guide_notes 
                    WHERE guide_id = :guide_id
                    ORDER BY created_at DESC
                ');
                $notesStmt->execute(['guide_id' => $guideId ?? 0]);
                $notes = $notesStmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                // Bảng có thể chưa tồn tại
            }

            // Lấy danh sách xác nhận tour
            if ($guideId) {
                try {
                    $confStmt = $pdo->prepare('
                        SELECT booking_id, confirmed, confirmed_at, status
                        FROM guide_tour_confirmations 
                        WHERE guide_id = :guide_id AND confirmed = 1
                    ');
                    $confStmt->execute(['guide_id' => $guideId]);
                    $confirmations = $confStmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($confirmations as $conf) {
                        $confirmationsMap[$conf['booking_id']] = $conf;
                    }
                } catch (PDOException $e) {
                    // Bảng có thể chưa tồn tại
                }
            }

            // Lấy danh sách yêu cầu từ chối tour
            if ($guideId) {
                try {
                    $rejectStmt = $pdo->prepare('
                        SELECT booking_id, status, reason, created_at 
                        FROM guide_tour_rejections 
                        WHERE guide_id = :guide_id
                    ');
                    $rejectStmt->execute(['guide_id' => $guideId]);
                    $rejections = $rejectStmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($rejections as $rej) {
                        $rejectionsMap[$rej['booking_id']] = $rej;
                    }
                } catch (PDOException $e) {
                    // Bảng có thể chưa tồn tại
                }
            }
        }

        view('admin.tours.index', [
            'title' => 'Danh sách tour',
            'tours' => $tours,
            'errors' => $errors,
            'isGuide' => $isGuide,
            'assignedBookings' => $assignedBookings,
            'completedBookings' => $completedBookings,
            'leaveRequests' => $leaveRequests,
            'notes' => $notes,
            'confirmationsMap' => $confirmationsMap,
            'rejectionsMap' => $rejectionsMap,
            'guideId' => $guideId,
            'successMessage' => $_GET['success'] ?? null,
            'errorMessage' => $_GET['error'] ?? null,
        ]);
    }

    // Chi tiết tour - guide chỉ xem tour được phân bổ qua bookings
    public function show(): void
    {
        requireGuideOrAdmin();

        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            view('not_found', ['title' => 'Tour không tồn tại']);
            return;
        }

        $pdo = getDB();
        if ($pdo === null) {
            view('not_found', ['title' => 'Lỗi kết nối database']);
            return;
        }

        $currentUser = getCurrentUser();
        $isGuide = isGuide() && !isAdmin();

        try {
            $stmt = $pdo->prepare('SELECT t.*, c.name as category_name
                                 FROM tours t
                                 LEFT JOIN categories c ON t.category_id = c.id
                                 WHERE t.id = :id LIMIT 1');
            $stmt->execute(['id' => $id]);
            $tour = $stmt->fetch();

            if (!$tour) {
                view('not_found', ['title' => 'Tour không tồn tại']);
                return;
            }
            
            // Kiểm tra quyền: nếu là guide, chỉ xem được tour từ booking được gán cho họ
            if ($isGuide && $currentUser) {
                $hasAccess = false;
                $guidesTableExists = $pdo->query("SHOW TABLES LIKE 'guides'")->fetch();
                
                if ($guidesTableExists) {
                    try {
                        $checkStmt = $pdo->query("SHOW COLUMNS FROM guides LIKE 'user_id'");
                        $hasUserId = $checkStmt->fetch();
                        
                        if ($hasUserId) {
                            // Tìm guide_id từ user_id
                            $guideStmt = $pdo->prepare('SELECT id FROM guides WHERE user_id = :user_id LIMIT 1');
                            $guideStmt->execute(['user_id' => $currentUser->id]);
                            $guide = $guideStmt->fetch();
                            if ($guide) {
                                // Kiểm tra xem có booking nào gán tour này cho guide không
                                $bookingStmt = $pdo->prepare('SELECT COUNT(*) as count FROM bookings WHERE tour_id = :tour_id AND assigned_guide_id = :guide_id LIMIT 1');
                                $bookingStmt->execute(['tour_id' => $id, 'guide_id' => $guide['id']]);
                                $booking = $bookingStmt->fetch();
                                if ($booking && $booking['count'] > 0) {
                                    $hasAccess = true;
                                }
                            }
                        } else {
                            // Không có user_id, giả sử assigned_guide_id trỏ đến users.id
                            $bookingStmt = $pdo->prepare('SELECT COUNT(*) as count FROM bookings WHERE tour_id = :tour_id AND assigned_guide_id = :user_id LIMIT 1');
                            $bookingStmt->execute(['tour_id' => $id, 'user_id' => $currentUser->id]);
                            $booking = $bookingStmt->fetch();
                            if ($booking && $booking['count'] > 0) {
                                $hasAccess = true;
                            }
                        }
                    } catch (PDOException $e) {
                        error_log('Check guide access failed: ' . $e->getMessage());
                        // Fallback
                        $bookingStmt = $pdo->prepare('SELECT COUNT(*) as count FROM bookings WHERE tour_id = :tour_id AND assigned_guide_id = :user_id LIMIT 1');
                        $bookingStmt->execute(['tour_id' => $id, 'user_id' => $currentUser->id]);
                        $booking = $bookingStmt->fetch();
                        if ($booking && $booking['count'] > 0) {
                            $hasAccess = true;
                        }
                    }
                } else {
                    // Không có bảng guides, assigned_guide_id trỏ đến users.id
                    $bookingStmt = $pdo->prepare('SELECT COUNT(*) as count FROM bookings WHERE tour_id = :tour_id AND assigned_guide_id = :user_id LIMIT 1');
                    $bookingStmt->execute(['tour_id' => $id, 'user_id' => $currentUser->id]);
                    $booking = $bookingStmt->fetch();
                    if ($booking && $booking['count'] > 0) {
                        $hasAccess = true;
                    }
                }
                
                if (!$hasAccess) {
                    view('not_found', ['title' => 'Bạn không có quyền xem tour này']);
                    return;
                }
            }

            // Lấy danh sách ảnh của tour
            $tourImages = [];
            try {
                // Kiểm tra xem bảng tour_images có tồn tại không
                $tableExists = $pdo->query("SHOW TABLES LIKE 'tour_images'")->fetch();
                if ($tableExists) {
                    $imagesStmt = $pdo->prepare('SELECT id, image_path, display_order FROM tour_images WHERE tour_id = :tour_id ORDER BY display_order ASC, id ASC');
                    $imagesStmt->execute(['tour_id' => $id]);
                    $tourImages = $imagesStmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    // Debug: Log số lượng ảnh tìm được
                    error_log('Tour #' . $id . ' has ' . count($tourImages) . ' images');
                } else {
                    error_log('Table tour_images does not exist');
                }
            } catch (PDOException $e) {
                // Bảng có thể chưa tồn tại
                error_log('Get tour images failed: ' . $e->getMessage());
                $tourImages = [];
            }

            view('admin.tours.show', [
                'title' => 'Chi tiết tour',
                'tour' => $tour,
                'tourImages' => $tourImages,
            ]);
        } catch (PDOException $e) {
            error_log('Show tour failed: ' . $e->getMessage());
            view('not_found', ['title' => 'Lỗi khi tải tour']);
        }
    }

    // Hiển thị form thêm tour mới
    public function create(): void
    {
        requireAdmin();

        $pdo = getDB();
        $categories = [];
        $errors = [];

        if ($pdo === null) {
            $errors[] = 'Không thể kết nối cơ sở dữ liệu.';
        } else {
            try {
                $stmt = $pdo->query('SELECT id, name FROM categories WHERE status = 1 ORDER BY name ASC');
                $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if (!is_array($categories)) {
                    $categories = [];
                }
            } catch (PDOException $e) {
                error_log('Get categories failed: ' . $e->getMessage());
                $errors[] = 'Không thể tải danh sách danh mục.';
                $categories = [];
            }
        }

        try {
        view('admin.tours.create', [
            'title' => 'Thêm tour',
            'categories' => $categories,
            'errors' => $errors,
        ]);
        } catch (Exception $e) {
            error_log('Tour create view failed: ' . $e->getMessage());
            echo 'Lỗi: ' . htmlspecialchars($e->getMessage());
        }
    }

    // Lưu tour mới
    public function store(): void
    {
        requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'admin/tours');
            exit;
        }

        $name = trim($_POST['name'] ?? '');
        $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : null;
        $description = trim($_POST['description'] ?? '');
        $schedule = trim($_POST['schedule'] ?? '');
        $policies = trim($_POST['policies'] ?? '');
        $suppliers = trim($_POST['suppliers'] ?? '');
        // Xử lý giá: loại bỏ dấu phẩy, chấm và khoảng trắng
        $price = null;
        if (isset($_POST['price']) && $_POST['price'] !== '') {
            $priceStr = str_replace([',', '.', ' ', '₫'], '', trim($_POST['price']));
            $price = is_numeric($priceStr) ? (float)$priceStr : null;
            if ($price !== null) {
                $price = round($price); // Làm tròn về số nguyên
            }
        }
        // Mặc định status = 1 (hoạt động) khi thêm tour mới để tour hiển thị ngay trong danh sách
        $status = isset($_POST['status']) && $_POST['status'] == '1' ? 1 : 1;

        $errors = [];
        $formData = [
            'name' => $name,
            'category_id' => $category_id,
            'description' => $description,
            'schedule' => $schedule,
            'policies' => $policies,
            'suppliers' => $suppliers,
            'price' => $price,
            'status' => $status,
        ];

        // Validation
        if ($name === '') {
            $errors[] = 'Tên tour không được để trống.';
        }

        if (strlen($name) > 255) {
            $errors[] = 'Tên tour không được vượt quá 255 ký tự.';
        }

        if ($category_id === null || $category_id <= 0) {
            $errors[] = 'Vui lòng chọn danh mục.';
        }

        $pdo = getDB();
        $categories = [];

        if ($pdo === null) {
            $errors[] = 'Không thể kết nối cơ sở dữ liệu.';
        } else {
            try {
                $stmt = $pdo->query('SELECT id, name FROM categories WHERE status = 1 ORDER BY name ASC');
                $categories = $stmt->fetchAll();
            } catch (PDOException $e) {
                error_log('Get categories failed: ' . $e->getMessage());
            }

            // Kiểm tra category_id có tồn tại không
            if ($category_id > 0) {
                $checkStmt = $pdo->prepare('SELECT id FROM categories WHERE id = :id AND status = 1 LIMIT 1');
                $checkStmt->execute(['id' => $category_id]);
                if (!$checkStmt->fetch()) {
                    $errors[] = 'Danh mục không tồn tại hoặc đã bị vô hiệu hóa.';
                }
            }
        }

        if (!empty($errors)) {
            view('admin.tours.create', [
                'title' => 'Thêm tour',
                'errors' => $errors,
                'formData' => $formData,
                'categories' => $categories,
            ]);
            return;
        }

        try {
            $now = date('Y-m-d H:i:s');
            $stmt = $pdo->prepare('INSERT INTO tours (name, category_id, description, schedule, policies, suppliers, price, status, created_at, updated_at) VALUES (:name, :category_id, :description, :schedule, :policies, :suppliers, :price, :status, :created_at, :updated_at)');
            $stmt->execute([
                'name' => $name,
                'category_id' => $category_id,
                'description' => $description ?: null,
                'schedule' => $schedule ?: null,
                'policies' => $policies ?: null,
                'suppliers' => $suppliers ?: null,
                'price' => $price,
                'status' => $status,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $tourId = $pdo->lastInsertId();

            // Xử lý upload nhiều ảnh (nếu có)
            if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
                $uploadsDir = BASE_PATH . '/public/uploads/tours';
                if (!is_dir($uploadsDir)) {
                    if (!mkdir($uploadsDir, 0755, true)) {
                        error_log('Cannot create uploads directory: ' . $uploadsDir);
                    }
                }

                // Tạo bảng tour_images nếu chưa tồn tại (không dùng foreign key để tránh lỗi)
                try {
                    $pdo->exec("
                        CREATE TABLE IF NOT EXISTS tour_images (
                            id INT AUTO_INCREMENT PRIMARY KEY,
                            tour_id INT NOT NULL,
                            image_path VARCHAR(255) NOT NULL,
                            display_order INT DEFAULT 0,
                            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                            INDEX idx_tour_id (tour_id)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                    ");
                } catch (PDOException $e) {
                    // Bảng có thể đã tồn tại hoặc có lỗi khác - không chặn việc tạo tour
                    error_log('Create tour_images table: ' . $e->getMessage());
                }

                // Chỉ xử lý upload nếu bảng đã tồn tại hoặc đã tạo thành công
                try {
                    $checkTable = $pdo->query("SHOW TABLES LIKE 'tour_images'");
                    if ($checkTable->fetch()) {
                        $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                        $imageStmt = $pdo->prepare('INSERT INTO tour_images (tour_id, image_path, display_order, created_at) VALUES (:tour_id, :image_path, :display_order, :created_at)');
                        
                        $displayOrder = 0;
                        foreach ($_FILES['images']['name'] as $key => $fileName) {
                            if (isset($_FILES['images']['error'][$key]) && $_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                                $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                                
                                if (in_array($ext, $allowedExts)) {
                                    $newFileName = uniqid('tour_' . $tourId . '_') . '.' . $ext;
                                    $target = $uploadsDir . DIRECTORY_SEPARATOR . $newFileName;
                                    
                                    if (move_uploaded_file($_FILES['images']['tmp_name'][$key], $target)) {
                                        $imagePath = 'uploads/tours/' . $newFileName;
                                        try {
                                            $imageStmt->execute([
                                                'tour_id' => $tourId,
                                                'image_path' => $imagePath,
                                                'display_order' => $displayOrder++,
                                                'created_at' => $now,
                                            ]);
                                        } catch (PDOException $e) {
                                            error_log('Insert tour image failed: ' . $e->getMessage());
                                            // Xóa file nếu không lưu được vào DB
                                            if (file_exists($target)) {
                                                @unlink($target);
                                            }
                                        }
                                    } else {
                                        error_log('Move uploaded file failed: ' . $target);
                                    }
                                }
                            }
                        }
                    }
                } catch (PDOException $e) {
                    error_log('Process tour images failed: ' . $e->getMessage());
                    // Không chặn việc tạo tour nếu upload ảnh lỗi
                }
            }

            header('Location: ' . BASE_URL . 'admin/tours?success=1');
            exit;
        } catch (PDOException $e) {
            error_log('Create tour failed: ' . $e->getMessage());
            error_log('SQL Error Code: ' . $e->getCode());
            error_log('SQL Error Info: ' . print_r($e->errorInfo ?? [], true));
            
            // Hiển thị lỗi chi tiết hơn trong môi trường development
            $errorMessage = $e->getMessage();
            $isLocal = in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1']) || 
                      strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost') !== false ||
                      strpos($_SERVER['HTTP_HOST'] ?? '', '127.0.0.1') !== false;
            
            if (strpos($errorMessage, 'foreign key constraint') !== false) {
                $errors[] = 'Dữ liệu không hợp lệ. Vui lòng kiểm tra lại danh mục đã chọn.';
            } elseif (strpos($errorMessage, 'Duplicate entry') !== false) {
                $errors[] = 'Tour này đã tồn tại. Vui lòng kiểm tra lại tên tour.';
            } elseif ($isLocal) {
                $errors[] = 'Không thể tạo tour. Lỗi: ' . htmlspecialchars($errorMessage);
            } else {
                $errors[] = 'Không thể tạo tour. Vui lòng thử lại.';
            }
            
            view('admin.tours.create', [
                'title' => 'Thêm tour',
                'errors' => $errors,
                'formData' => $formData,
                'categories' => $categories,
            ]);
        } catch (Exception $e) {
            error_log('Create tour failed (general): ' . $e->getMessage());
            $errors[] = 'Không thể tạo tour. Vui lòng thử lại.';
            view('admin.tours.create', [
                'title' => 'Thêm tour',
                'errors' => $errors,
                'formData' => $formData,
                'categories' => $categories,
            ]);
        }
    }

    // Hiển thị form chỉnh sửa tour
    public function edit(): void
    {
        requireAdmin();

        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            header('Location: ' . BASE_URL . 'admin/tours');
            exit;
        }

        $pdo = getDB();
        $categories = [];
        $errors = [];
        $tour = null;

        if ($pdo === null) {
            $errors[] = 'Không thể kết nối cơ sở dữ liệu.';
        } else {
            try {
                // Lấy thông tin tour
                $stmt = $pdo->prepare('SELECT * FROM tours WHERE id = :id LIMIT 1');
                $stmt->execute(['id' => $id]);
                $tour = $stmt->fetch();

                if (!$tour) {
                    header('Location: ' . BASE_URL . 'admin/tours');
                    exit;
                }

                // Lấy danh sách categories
                $catStmt = $pdo->query('SELECT id, name FROM categories WHERE status = 1 ORDER BY name ASC');
                $categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);
                if (!is_array($categories)) {
                    $categories = [];
                }

                // Lấy danh sách ảnh của tour
                $tourImages = [];
                try {
                    $imagesStmt = $pdo->prepare('SELECT id, image_path, display_order FROM tour_images WHERE tour_id = :tour_id ORDER BY display_order ASC, id ASC');
                    $imagesStmt->execute(['tour_id' => $id]);
                    $tourImages = $imagesStmt->fetchAll(PDO::FETCH_ASSOC);
                } catch (PDOException $e) {
                    // Bảng có thể chưa tồn tại
                    error_log('Get tour images failed: ' . $e->getMessage());
                    $tourImages = [];
                }
            } catch (PDOException $e) {
                error_log('Get tour for edit failed: ' . $e->getMessage());
                $errors[] = 'Không thể tải thông tin tour.';
            }
        }

        view('admin.tours.edit', [
            'title' => 'Chỉnh sửa tour',
            'tour' => $tour,
            'categories' => $categories,
            'errors' => $errors,
            'tourImages' => $tourImages ?? [],
        ]);
    }

    // Cập nhật tour
    public function update(): void
    {
        requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'admin/tours');
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            header('Location: ' . BASE_URL . 'admin/tours');
            exit;
        }

        $name = trim($_POST['name'] ?? '');
        $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : null;
        $description = trim($_POST['description'] ?? '');
        $schedule = trim($_POST['schedule'] ?? '');
        $policies = trim($_POST['policies'] ?? '');
        $suppliers = trim($_POST['suppliers'] ?? '');
        // Xử lý giá: loại bỏ dấu phẩy, chấm và khoảng trắng
        $price = null;
        if (isset($_POST['price']) && $_POST['price'] !== '') {
            $priceStr = str_replace([',', '.', ' ', '₫'], '', trim($_POST['price']));
            $price = is_numeric($priceStr) ? (float)$priceStr : null;
            if ($price !== null) {
                $price = round($price); // Làm tròn về số nguyên
            }
        }
        $status = isset($_POST['status']) ? 1 : 0;

        $errors = [];
        $formData = [
            'id' => $id,
            'name' => $name,
            'category_id' => $category_id,
            'description' => $description,
            'schedule' => $schedule,
            'policies' => $policies,
            'suppliers' => $suppliers,
            'price' => $price,
            'status' => $status,
        ];

        // Validation
        if ($name === '') {
            $errors[] = 'Tên tour không được để trống.';
        }

        if (strlen($name) > 255) {
            $errors[] = 'Tên tour không được vượt quá 255 ký tự.';
        }

        if ($category_id === null || $category_id <= 0) {
            $errors[] = 'Vui lòng chọn danh mục.';
        }

        $pdo = getDB();
        $categories = [];
        $tour = null;

        if ($pdo === null) {
            $errors[] = 'Không thể kết nối cơ sở dữ liệu.';
        } else {
            try {
                // Lấy thông tin tour hiện tại
                $stmt = $pdo->prepare('SELECT * FROM tours WHERE id = :id LIMIT 1');
                $stmt->execute(['id' => $id]);
                $tour = $stmt->fetch();

                if (!$tour) {
                    header('Location: ' . BASE_URL . 'admin/tours');
                    exit;
                }

                // Lấy danh sách categories
                $catStmt = $pdo->query('SELECT id, name FROM categories WHERE status = 1 ORDER BY name ASC');
                $categories = $catStmt->fetchAll();

                // Kiểm tra category_id có tồn tại không
                if ($category_id > 0) {
                    $checkStmt = $pdo->prepare('SELECT id FROM categories WHERE id = :id AND status = 1 LIMIT 1');
                    $checkStmt->execute(['id' => $category_id]);
                    if (!$checkStmt->fetch()) {
                        $errors[] = 'Danh mục không tồn tại hoặc đã bị vô hiệu hóa.';
                    }
                }
            } catch (PDOException $e) {
                error_log('Get tour/categories failed: ' . $e->getMessage());
            }
        }

        if (!empty($errors)) {
            view('admin.tours.edit', [
                'title' => 'Chỉnh sửa tour',
                'tour' => $tour ?: $formData,
                'categories' => $categories,
                'errors' => $errors,
            ]);
            return;
        }

        try {
            $now = date('Y-m-d H:i:s');
            $stmt = $pdo->prepare('UPDATE tours SET name = :name, category_id = :category_id, description = :description, schedule = :schedule, policies = :policies, suppliers = :suppliers, price = :price, status = :status, updated_at = :updated_at WHERE id = :id');
            $stmt->execute([
                'id' => $id,
                'name' => $name,
                'category_id' => $category_id,
                'description' => $description ?: null,
                'schedule' => $schedule ?: null,
                'policies' => $policies ?: null,
                'suppliers' => $suppliers ?: null,
                'price' => $price,
                'status' => $status,
                'updated_at' => $now,
            ]);

            // Xử lý xóa ảnh
            if (!empty($_POST['delete_images']) && is_array($_POST['delete_images'])) {
                try {
                    $deleteStmt = $pdo->prepare('SELECT image_path FROM tour_images WHERE id = :id AND tour_id = :tour_id');
                    $deleteImageStmt = $pdo->prepare('DELETE FROM tour_images WHERE id = :id AND tour_id = :tour_id');
                    
                    foreach ($_POST['delete_images'] as $imageId) {
                        $imageId = (int)$imageId;
                        if ($imageId > 0) {
                            // Lấy đường dẫn ảnh để xóa file
                            $deleteStmt->execute(['id' => $imageId, 'tour_id' => $id]);
                            $imageData = $deleteStmt->fetch();
                            
                            if ($imageData && !empty($imageData['image_path'])) {
                                $filePath = BASE_PATH . '/public/' . $imageData['image_path'];
                                if (file_exists($filePath)) {
                                    @unlink($filePath);
                                }
                            }
                            
                            // Xóa khỏi database
                            $deleteImageStmt->execute(['id' => $imageId, 'tour_id' => $id]);
                        }
                    }
                } catch (PDOException $e) {
                    error_log('Delete tour images failed: ' . $e->getMessage());
                }
            }

            // Xử lý thêm ảnh mới
            if (!empty($_FILES['images']['name'][0])) {
                $uploadsDir = BASE_PATH . '/public/uploads/tours';
                if (!is_dir($uploadsDir)) {
                    mkdir($uploadsDir, 0755, true);
                }

                // Đảm bảo bảng tour_images tồn tại (không dùng foreign key để tránh lỗi)
                try {
                    $pdo->exec("
                        CREATE TABLE IF NOT EXISTS tour_images (
                            id INT AUTO_INCREMENT PRIMARY KEY,
                            tour_id INT NOT NULL,
                            image_path VARCHAR(255) NOT NULL,
                            display_order INT DEFAULT 0,
                            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                            INDEX idx_tour_id (tour_id)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                    ");
                } catch (PDOException $e) {
                    error_log('Create tour_images table: ' . $e->getMessage());
                }

                // Lấy display_order cao nhất hiện tại
                $maxOrderStmt = $pdo->prepare('SELECT MAX(display_order) as max_order FROM tour_images WHERE tour_id = :tour_id');
                $maxOrderStmt->execute(['tour_id' => $id]);
                $maxOrderResult = $maxOrderStmt->fetch();
                $displayOrder = ($maxOrderResult && $maxOrderResult['max_order'] !== null) ? (int)$maxOrderResult['max_order'] + 1 : 0;

                $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                $imageStmt = $pdo->prepare('INSERT INTO tour_images (tour_id, image_path, display_order, created_at) VALUES (:tour_id, :image_path, :display_order, :created_at)');
                
                foreach ($_FILES['images']['name'] as $key => $fileName) {
                    if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                        
                        if (in_array($ext, $allowedExts)) {
                            $newFileName = uniqid('tour_' . $id . '_') . '.' . $ext;
                            $target = $uploadsDir . DIRECTORY_SEPARATOR . $newFileName;
                            
                            if (move_uploaded_file($_FILES['images']['tmp_name'][$key], $target)) {
                                $imagePath = 'uploads/tours/' . $newFileName;
                                $imageStmt->execute([
                                    'tour_id' => $id,
                                    'image_path' => $imagePath,
                                    'display_order' => $displayOrder++,
                                    'created_at' => $now,
                                ]);
                            }
                        }
                    }
                }
            }

            header('Location: ' . BASE_URL . 'admin/tours?success=updated');
            exit;
        } catch (PDOException $e) {
            error_log('Update tour failed: ' . $e->getMessage());
            $errors[] = 'Không thể cập nhật tour. Vui lòng thử lại.';
            view('admin.tours.edit', [
                'title' => 'Chỉnh sửa tour',
                'tour' => $tour ?: $formData,
                'categories' => $categories,
                'errors' => $errors,
            ]);
        }
    }

    // Xóa tour
    public function delete(): void
    {
        requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'admin/tours');
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            header('Location: ' . BASE_URL . 'admin/tours');
            exit;
        }

        $pdo = getDB();
        if ($pdo === null) {
            header('Location: ' . BASE_URL . 'admin/tours?error=db');
            exit;
        }

        try {
            // Kiểm tra xem tour có tồn tại không
            $stmt = $pdo->prepare('SELECT id FROM tours WHERE id = :id LIMIT 1');
            $stmt->execute(['id' => $id]);
            $tour = $stmt->fetch();

            if (!$tour) {
                header('Location: ' . BASE_URL . 'admin/tours?error=notfound');
                exit;
            }

            // Xóa tour
            $deleteStmt = $pdo->prepare('DELETE FROM tours WHERE id = :id');
            $deleteStmt->execute(['id' => $id]);

            header('Location: ' . BASE_URL . 'admin/tours?success=deleted');
            exit;
        } catch (PDOException $e) {
            error_log('Delete tour failed: ' . $e->getMessage());
            header('Location: ' . BASE_URL . 'admin/tours?error=delete');
            exit;
        }
    }
}

