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
                                    $query = 'SELECT DISTINCT t.*, c.name as category_name
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
                                $query = 'SELECT DISTINCT t.*, c.name as category_name
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
                            $query = 'SELECT DISTINCT t.*, c.name as category_name
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
                    // Admin xem tất cả tours
                    $query = 'SELECT t.*, c.name as category_name
                             FROM tours t
                             LEFT JOIN categories c ON t.category_id = c.id
                             WHERE t.status = 1
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

        view('admin.tours.index', [
            'title' => 'Danh sách tour',
            'tours' => $tours,
            'errors' => $errors,
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

            view('admin.tours.show', [
                'title' => 'Chi tiết tour',
                'tour' => $tour,
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
        $price = isset($_POST['price']) && $_POST['price'] !== '' ? (float)$_POST['price'] : null;
        $status = isset($_POST['status']) ? 1 : 0;

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

            header('Location: ' . BASE_URL . 'admin/tours?success=1');
            exit;
        } catch (PDOException $e) {
            error_log('Create tour failed: ' . $e->getMessage());
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
        $price = isset($_POST['price']) && $_POST['price'] !== '' ? (float)$_POST['price'] : null;
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

