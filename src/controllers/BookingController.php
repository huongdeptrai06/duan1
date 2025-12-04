<?php

require_once BASE_PATH . '/src/helpers/database.php';

class BookingController
{
    // Helper function để lấy danh sách guides
    private function getGuides($pdo)
    {
        $guides = [];
        if (!$pdo) {
            return $guides;
        }

        try {
            // Kiểm tra xem bảng guides có tồn tại không
            $guidesTableExists = $pdo->query("SHOW TABLES LIKE 'guides'")->fetch();
            
            if ($guidesTableExists) {
                // Nếu bảng guides tồn tại, lấy từ đó
                try {
                    $stmt = $pdo->query('SELECT id, full_name FROM guides WHERE status = 1 ORDER BY full_name');
                    $guides = $stmt->fetchAll();
                } catch (PDOException $e) {
                    error_log('Fetch from guides table failed: ' . $e->getMessage());
                    $guides = [];
                }
            }
            
            // Nếu không có guides từ bảng guides hoặc bảng không tồn tại, lấy từ users
            if (empty($guides)) {
                try {
                    $stmt = $pdo->query("
                        SELECT u.id, u.name as full_name 
                        FROM users u 
                        WHERE u.role = 'huong_dan_vien' AND u.status = 1 
                        ORDER BY u.name
                    ");
                    $guides = $stmt->fetchAll();
                } catch (PDOException $e) {
                    error_log('Fetch guides from users failed: ' . $e->getMessage());
                    $guides = [];
                }
            }
        } catch (PDOException $e) {
            error_log('Check guides table failed: ' . $e->getMessage());
            // Fallback: thử lấy từ users
            try {
                $stmt = $pdo->query("
                    SELECT u.id, u.name as full_name 
                    FROM users u 
                    WHERE u.role = 'huong_dan_vien' AND u.status = 1 
                    ORDER BY u.name
                ");
                $guides = $stmt->fetchAll();
            } catch (PDOException $e2) {
                error_log('Fetch guides from users (fallback) failed: ' . $e2->getMessage());
                $guides = [];
            }
        }

        return $guides;
    }

    // Helper function để lấy danh sách statuses có thể chuyển tiếp (chỉ tiến lên, không quay lại)
    private function getNextStatuses($pdo, $currentStatusId)
    {
        $statuses = [];
        if (!$pdo) {
            return $statuses;
        }

        try {
            // Kiểm tra xem bảng có tồn tại không
            $tableExists = $pdo->query("SHOW TABLES LIKE 'tour_statuses'")->fetch();
            if (!$tableExists) {
                error_log('Table tour_statuses does not exist');
                return $statuses;
            }

            // Lấy tất cả trạng thái
            $stmt = $pdo->query('SELECT id, name FROM tour_statuses ORDER BY id');
            $allStatuses = $stmt->fetchAll();
            
            // Lọc chỉ lấy các trạng thái có thể chuyển tiếp
            // Quy tắc: chỉ cho phép chuyển sang trạng thái có id >= currentStatusId
            // Trừ trường hợp hủy (thường là id cao nhất hoặc có tên chứa "hủy")
            foreach ($allStatuses as $status) {
                $statusId = (int)$status['id'];
                $statusName = strtolower($status['name'] ?? '');
                
                // Cho phép chuyển sang trạng thái tiếp theo hoặc bằng
                if ($statusId >= $currentStatusId) {
                    $statuses[] = $status;
                }
                // Cho phép hủy từ bất kỳ trạng thái nào (nếu chưa hủy)
                elseif (stripos($statusName, 'hủy') !== false || stripos($statusName, 'cancel') !== false) {
                    if ($currentStatusId != $statusId) {
                        $statuses[] = $status;
                    }
                }
            }
            
            return $statuses;
        } catch (PDOException $e) {
            error_log('Fetch next statuses failed: ' . $e->getMessage());
            return [];
        }
    }

    // Helper function để lấy danh sách statuses
    private function getStatuses($pdo)
    {
        $statuses = [];
        if (!$pdo) {
            return $statuses;
        }

        try {
            // Kiểm tra xem bảng có tồn tại không
            $tableExists = $pdo->query("SHOW TABLES LIKE 'tour_statuses'")->fetch();
            if (!$tableExists) {
                error_log('Table tour_statuses does not exist');
                return $statuses;
            }

            $stmt = $pdo->query('SELECT id, name FROM tour_statuses ORDER BY id');
            $statuses = $stmt->fetchAll();
            
            // Nếu không có trạng thái nào, tạo một số trạng thái mặc định
            if (empty($statuses)) {
                try {
                    $defaultStatuses = [
                        ['name' => 'Chờ xác nhận'],
                        ['name' => 'Đã xác nhận'],
                        ['name' => 'Đang diễn ra'],
                        ['name' => 'Hoàn thành'],
                        ['name' => 'Đã hủy'],
                    ];
                    
                    foreach ($defaultStatuses as $status) {
                        try {
                            $insertStmt = $pdo->prepare('INSERT INTO tour_statuses (name) VALUES (:name)');
                            $insertStmt->execute(['name' => $status['name']]);
                        } catch (PDOException $insertError) {
                            error_log('Failed to insert default status: ' . $insertError->getMessage());
                            // Tiếp tục với status tiếp theo
                        }
                    }
                    
                    // Lấy lại danh sách
                    $stmt = $pdo->query('SELECT id, name FROM tour_statuses ORDER BY id');
                    $statuses = $stmt->fetchAll();
                } catch (PDOException $createError) {
                    error_log('Failed to create default statuses: ' . $createError->getMessage());
                }
            }
        } catch (PDOException $e) {
            error_log('Fetch statuses failed: ' . $e->getMessage());
            $statuses = [];
        }

        return $statuses;
    }
    // Danh sách bookings
    public function index(): void
    {
        requireGuideOrAdmin();

        $pdo = getDB();
        $errors = [];
        $bookings = [];
        $successMessage = $_GET['success'] ?? null;
        $statusFilter = $_GET['status'] ?? null;
        $currentUser = getCurrentUser();
        $isGuide = isGuide() && !isAdmin();

        if ($pdo === null) {
            $errors[] = 'Không thể kết nối cơ sở dữ liệu.';
        } else {
            try {
                // Kiểm tra xem bảng guides có tồn tại không
                $guidesTableExists = $pdo->query("SHOW TABLES LIKE 'guides'")->fetch();
                
                if ($guidesTableExists) {
                    // Nếu bảng guides tồn tại, JOIN với guides
                    $query = 'SELECT b.*, 
                             t.name as tour_name, 
                             u.name as created_by_name,
                             g.full_name as guide_name,
                             ts.name as status_name
                             FROM bookings b
                             LEFT JOIN tours t ON b.tour_id = t.id
                             LEFT JOIN users u ON b.created_by = u.id
                             LEFT JOIN guides g ON b.assigned_guide_id = g.id
                             LEFT JOIN tour_statuses ts ON b.status = ts.id';
                } else {
                    // Nếu bảng guides không tồn tại, JOIN với users
                    $query = 'SELECT b.*, 
                             t.name as tour_name, 
                             u.name as created_by_name,
                             u_guide.name as guide_name,
                             ts.name as status_name
                             FROM bookings b
                             LEFT JOIN tours t ON b.tour_id = t.id
                             LEFT JOIN users u ON b.created_by = u.id
                             LEFT JOIN users u_guide ON b.assigned_guide_id = u_guide.id AND u_guide.role = "huong_dan_vien"
                             LEFT JOIN tour_statuses ts ON b.status = ts.id';
                }

                $params = [];
                $whereConditions = [];
                
                // Nếu là guide, chỉ hiển thị bookings được gán cho họ
                if ($isGuide && $currentUser) {
                    if ($guidesTableExists) {
                        // Tìm guide_id từ user_id (giả sử có cột user_id trong guides hoặc mapping)
                        // Nếu không có mapping, thử tìm guide theo user_id hoặc email
                        // Hoặc nếu assigned_guide_id trực tiếp trỏ đến users.id
                        // Tạm thời giả sử guides có user_id hoặc có cách map khác
                        // Nếu không có, sẽ dùng cách khác
                        try {
                            // Kiểm tra xem guides có cột user_id không
                            $checkStmt = $pdo->query("SHOW COLUMNS FROM guides LIKE 'user_id'");
                            $hasUserId = $checkStmt->fetch();
                            
                            if ($hasUserId) {
                                // Nếu có user_id trong guides, tìm guide_id
                                $guideStmt = $pdo->prepare('SELECT id FROM guides WHERE user_id = :user_id LIMIT 1');
                                $guideStmt->execute(['user_id' => $currentUser->id]);
                                $guide = $guideStmt->fetch();
                                if ($guide) {
                                    $whereConditions[] = 'b.assigned_guide_id = :guide_id';
                                    $params['guide_id'] = $guide['id'];
                                } else {
                                    // Không tìm thấy guide, không hiển thị booking nào
                                    $whereConditions[] = '1 = 0';
                                }
                            } else {
                                // Không có user_id, giả sử assigned_guide_id trỏ đến users.id
                                // Hoặc cần mapping khác - tạm thời dùng cách này
                                $whereConditions[] = 'b.assigned_guide_id = :user_id';
                                $params['user_id'] = $currentUser->id;
                            }
                        } catch (PDOException $e) {
                            error_log('Check guides structure failed: ' . $e->getMessage());
                            // Fallback: giả sử assigned_guide_id trỏ đến users.id
                            $whereConditions[] = 'b.assigned_guide_id = :user_id';
                            $params['user_id'] = $currentUser->id;
                        }
                    } else {
                        // Không có bảng guides, assigned_guide_id trỏ đến users.id
                        $whereConditions[] = 'b.assigned_guide_id = :user_id';
                        $params['user_id'] = $currentUser->id;
                    }
                }
                
                if ($statusFilter) {
                    $whereConditions[] = 'b.status = :status';
                    $params['status'] = $statusFilter;
                }
                
                if (!empty($whereConditions)) {
                    $query .= ' WHERE ' . implode(' AND ', $whereConditions);
                }

                $query .= ' ORDER BY b.created_at DESC';

                $stmt = $pdo->prepare($query);
                $stmt->execute($params);
                $bookings = $stmt->fetchAll();
            } catch (PDOException $e) {
                error_log('Bookings index failed: ' . $e->getMessage());
                error_log('SQL Error: ' . print_r($e->errorInfo ?? [], true));
                $errors[] = 'Không thể tải danh sách booking. Chi tiết: ' . $e->getMessage();
            }
        }

        // Lấy danh sách trạng thái để filter
        $statuses = [];
        if ($pdo) {
            try {
                $stmt = $pdo->query('SELECT id, name FROM tour_statuses ORDER BY id');
                $statuses = $stmt->fetchAll();
            } catch (PDOException $e) {
                error_log('Fetch statuses failed: ' . $e->getMessage());
            }
        }

        view('admin.bookings.index', [
            'title' => 'Quản lý booking',
            'bookings' => $bookings,
            'errors' => $errors,
            'successMessage' => $successMessage,
            'statuses' => $statuses,
            'statusFilter' => $statusFilter,
        ]);
    }

    // Form tạo booking mới
    public function create(): void
    {
        requireAdmin(); // Chỉ admin mới tạo được booking

        $pdo = getDB();
        $tours = [];
        $guides = [];
        $statuses = [];

        if ($pdo) {
            try {
                // Lấy danh sách tours đang hoạt động
                $stmt = $pdo->query('SELECT id, name FROM tours WHERE status = 1 ORDER BY name');
                $tours = $stmt->fetchAll();
            } catch (PDOException $e) {
                error_log('Fetch tours failed: ' . $e->getMessage());
                $tours = [];
            }
        }

        // Lấy guides và statuses bằng helper functions
        $guides = $this->getGuides($pdo);
        $statuses = $this->getStatuses($pdo);

        view('admin.bookings.create', [
            'title' => 'Tạo booking mới',
            'tours' => $tours,
            'guides' => $guides,
            'statuses' => $statuses,
        ]);
    }

    // Lưu booking mới
    public function store(): void
    {
        requireAdmin(); // Chỉ admin mới tạo được booking

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'admin/bookings');
            exit;
        }

        $pdo = getDB();
        $errors = [];
        $currentUser = getCurrentUser();

        // Kiểm tra user đã đăng nhập
        if (!$currentUser || !$currentUser->id) {
            $errors[] = 'Bạn chưa đăng nhập hoặc phiên đăng nhập đã hết hạn.';
        }

        $tour_id = !empty($_POST['tour_id']) ? (int)$_POST['tour_id'] : null;
        $assigned_guide_id = !empty($_POST['assigned_guide_id']) ? (int)$_POST['assigned_guide_id'] : null;
        $status = !empty($_POST['status']) ? (int)$_POST['status'] : null;
        $start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
        $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
        $schedule_detail_raw = trim($_POST['schedule_detail'] ?? '');
        $service_detail_raw = trim($_POST['service_detail'] ?? '');
        $diary_raw = trim($_POST['diary'] ?? '');
        $notes = trim($_POST['notes'] ?? '');
        
        // Xử lý các trường phải là JSON hợp lệ (theo constraint)
        // Nếu người dùng nhập text thường, chuyển thành JSON string
        $schedule_detail = null;
        if ($schedule_detail_raw !== '') {
            // Thử decode để kiểm tra xem đã là JSON chưa
            $decoded = json_decode($schedule_detail_raw, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                // Đã là JSON hợp lệ, giữ nguyên
                $schedule_detail = $schedule_detail_raw;
            } else {
                // Không phải JSON, chuyển thành JSON string
                $schedule_detail = json_encode($schedule_detail_raw, JSON_UNESCAPED_UNICODE);
            }
        }
        
        $service_detail = null;
        if ($service_detail_raw !== '') {
            $decoded = json_decode($service_detail_raw, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $service_detail = $service_detail_raw;
            } else {
                $service_detail = json_encode($service_detail_raw, JSON_UNESCAPED_UNICODE);
            }
        }
        
        $diary = null;
        if ($diary_raw !== '') {
            $decoded = json_decode($diary_raw, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $diary = $diary_raw;
            } else {
                $diary = json_encode($diary_raw, JSON_UNESCAPED_UNICODE);
            }
        }

        // Validation
        if (!$tour_id) {
            $errors[] = 'Vui lòng chọn tour.';
        }

        if (!$start_date) {
            $errors[] = 'Vui lòng chọn ngày khởi hành.';
        }

        // Kiểm tra ngày khởi hành phải là ngày trong tương lai (từ hôm nay trở đi)
        if ($start_date) {
            $startTimestamp = strtotime($start_date);
            $todayTimestamp = strtotime(date('Y-m-d'));
            
            if ($startTimestamp < $todayTimestamp) {
                $errors[] = 'Ngày khởi hành phải là ngày trong tương lai. Không thể chọn ngày trong quá khứ.';
            }
        }

        // Validation ngày tháng - đảm bảo end_date >= start_date
        if ($start_date && $end_date) {
            $startTimestamp = strtotime($start_date);
            $endTimestamp = strtotime($end_date);
            
            if ($endTimestamp < $startTimestamp) {
                $errors[] = 'Ngày kết thúc phải sau hoặc bằng ngày khởi hành.';
            }
        }

        // Kiểm tra status - có thể constraint yêu cầu status không được NULL
        if (!$status) {
            // Thử lấy status đầu tiên từ danh sách nếu có
            if ($pdo) {
                try {
                    $stmt = $pdo->query('SELECT id FROM tour_statuses ORDER BY id LIMIT 1');
                    $firstStatus = $stmt->fetch();
                    if ($firstStatus) {
                        $status = (int)$firstStatus['id'];
                    } else {
                        $errors[] = 'Vui lòng chọn trạng thái.';
                    }
                } catch (PDOException $e) {
                    error_log('Check status failed: ' . $e->getMessage());
                    $errors[] = 'Vui lòng chọn trạng thái.';
                }
            } else {
                $errors[] = 'Vui lòng chọn trạng thái.';
            }
        }

        if ($pdo === null) {
            $errors[] = 'Không thể kết nối cơ sở dữ liệu.';
        }
        
        // Kiểm tra assigned_guide_id trước khi insert (pre-validation)
        if ($assigned_guide_id && $pdo) {
            try {
                $guidesTableExists = $pdo->query("SHOW TABLES LIKE 'guides'")->fetch() !== false;
                if ($guidesTableExists) {
                    // Nếu bảng guides tồn tại, kiểm tra ID có trong guides không
                    $checkGuide = $pdo->prepare('SELECT id FROM guides WHERE id = :id LIMIT 1');
                    $checkGuide->execute(['id' => $assigned_guide_id]);
                    if (!$checkGuide->fetch()) {
                        // Không tìm thấy trong guides, đặt về null để tránh foreign key violation
                        $assigned_guide_id = null;
                    }
                } else {
                    // Nếu bảng guides không tồn tại, kiểm tra trong users
                    $checkUser = $pdo->prepare("SELECT id FROM users WHERE id = :id AND role = 'huong_dan_vien' AND status = 1 LIMIT 1");
                    $checkUser->execute(['id' => $assigned_guide_id]);
                    if (!$checkUser->fetch()) {
                        // Không tìm thấy trong users, đặt về null
                        $assigned_guide_id = null;
                    }
                }
            } catch (PDOException $e) {
                error_log('Pre-validate guide ID failed: ' . $e->getMessage());
                // Đặt về null để an toàn
                $assigned_guide_id = null;
            }
        }

        if (!empty($errors)) {
            // Reload form với errors
            $tours = [];
            if ($pdo) {
                try {
                    $stmt = $pdo->query('SELECT id, name FROM tours WHERE status = 1 ORDER BY name');
                    $tours = $stmt->fetchAll();
                } catch (PDOException $e) {
                    error_log('Reload tours failed: ' . $e->getMessage());
                }
            }
            $guides = $this->getGuides($pdo);
            $statuses = $this->getStatuses($pdo);

            view('admin.bookings.create', [
                'title' => 'Tạo booking mới',
                'errors' => $errors,
                'tours' => $tours,
                'guides' => $guides,
                'statuses' => $statuses,
                'formData' => $_POST,
            ]);
            return;
        }

        try {
            $pdo->beginTransaction();

            // Kiểm tra constraint của bảng bookings để hiểu yêu cầu
            try {
                // Thử lấy thông tin check constraints
                try {
                    $constraintQuery = $pdo->query("
                        SELECT CONSTRAINT_NAME, CHECK_CLAUSE 
                        FROM information_schema.CHECK_CONSTRAINTS 
                        WHERE CONSTRAINT_SCHEMA = 'duan1' AND TABLE_NAME = 'bookings'
                    ");
                    $constraints = $constraintQuery->fetchAll();
                    foreach ($constraints as $constraint) {
                        error_log('Constraint ' . $constraint['CONSTRAINT_NAME'] . ': ' . $constraint['CHECK_CLAUSE']);
                    }
                } catch (PDOException $e) {
                    // Nếu không query được, thử cách khác
                    error_log('Could not get constraints: ' . $e->getMessage());
                    $constraintQuery = $pdo->query("SHOW CREATE TABLE bookings");
                    $createTable = $constraintQuery->fetch();
                    if ($createTable && isset($createTable['Create Table'])) {
                        error_log('Bookings table structure: ' . substr($createTable['Create Table'], 0, 500));
                    }
                }
            } catch (PDOException $e) {
                error_log('Could not get table structure: ' . $e->getMessage());
            }

            // Kiểm tra tour_id có tồn tại không
            if ($tour_id) {
                $checkTour = $pdo->prepare('SELECT id FROM tours WHERE id = :id LIMIT 1');
                $checkTour->execute(['id' => $tour_id]);
                if (!$checkTour->fetch()) {
                    throw new Exception('Tour không tồn tại.');
                }
            }

            // Kiểm tra assigned_guide_id nếu có
            // Lưu ý: Nếu bảng bookings có foreign key constraint đến bảng guides,
            // thì assigned_guide_id phải là ID từ bảng guides, không phải users
            if ($assigned_guide_id) {
                $validGuideId = null;
                $guidesTableExists = false;
                
                // Kiểm tra xem bảng guides có tồn tại không
                try {
                    $guidesTableExists = $pdo->query("SHOW TABLES LIKE 'guides'")->fetch() !== false;
                } catch (PDOException $e) {
                    error_log('Check guides table existence failed: ' . $e->getMessage());
                }
                
                if ($guidesTableExists) {
                    // Bảng guides tồn tại, chỉ chấp nhận ID từ guides
                    try {
                        $checkGuide = $pdo->prepare('SELECT id FROM guides WHERE id = :id LIMIT 1');
                        $checkGuide->execute(['id' => $assigned_guide_id]);
                        $guideRow = $checkGuide->fetch();
                        if ($guideRow) {
                            $validGuideId = (int)$guideRow['id'];
                        } else {
                            // Không tìm thấy trong guides, đặt về null để tránh foreign key violation
                            error_log('Guide ID ' . $assigned_guide_id . ' not found in guides table, setting to null');
                            $assigned_guide_id = null;
                        }
                    } catch (PDOException $e) {
                        error_log('Check guide in guides table failed: ' . $e->getMessage());
                        $assigned_guide_id = null;
                    }
                } else {
                    // Bảng guides không tồn tại, có thể dùng user_id từ users
                    try {
                        $checkUser = $pdo->prepare("SELECT id FROM users WHERE id = :id AND role = 'huong_dan_vien' AND status = 1 LIMIT 1");
                        $checkUser->execute(['id' => $assigned_guide_id]);
                        $userRow = $checkUser->fetch();
                        if ($userRow) {
                            $validGuideId = (int)$userRow['id'];
                        } else {
                            // Không tìm thấy trong users, đặt về null
                            error_log('User guide ID ' . $assigned_guide_id . ' not found, setting to null');
                            $assigned_guide_id = null;
                        }
                    } catch (PDOException $e) {
                        error_log('Check user guide failed: ' . $e->getMessage());
                        $assigned_guide_id = null;
                    }
                }
                
                if ($validGuideId) {
                    $assigned_guide_id = $validGuideId;
                }
            }

            // Tạo booking
            // Đảm bảo status không null nếu constraint yêu cầu
            $finalStatus = $status ?: null;
            
            // Xử lý ngày tháng để đáp ứng constraint
            // Constraint bookings_chk_1 có thể yêu cầu: end_date >= start_date (nếu cả hai đều không null)
            $finalEndDate = $end_date;
            
            // Nếu có start_date nhưng không có end_date, đặt end_date = start_date
            // (có thể constraint yêu cầu cả hai đều có giá trị hoặc cả hai đều null)
            if ($start_date && !$end_date) {
                $finalEndDate = $start_date;
                error_log('End date is missing, setting end_date = start_date to satisfy constraint');
            }
            
            // Đảm bảo end_date >= start_date
            if ($start_date && $finalEndDate) {
                $startTimestamp = strtotime($start_date);
                $endTimestamp = strtotime($finalEndDate);
                if ($endTimestamp < $startTimestamp) {
                    // Nếu end_date < start_date, đặt end_date = start_date để đáp ứng constraint
                    $finalEndDate = $start_date;
                    error_log('End date (' . $finalEndDate . ') is before start date (' . $start_date . '), setting end_date = start_date');
                }
            }
            
            // Log dữ liệu trước khi insert để debug
            error_log('Creating booking with data: ' . json_encode([
                'tour_id' => $tour_id,
                'created_by' => $currentUser->id,
                'assigned_guide_id' => $assigned_guide_id,
                'status' => $finalStatus,
                'start_date' => $start_date,
                'end_date' => $finalEndDate,
            ], JSON_UNESCAPED_UNICODE));
            
            // Chuẩn bị dữ liệu để insert
            // Constraint bookings_chk_1 thường yêu cầu: end_date >= start_date (khi cả hai đều không null)
            // Hoặc có thể yêu cầu: nếu start_date có giá trị thì end_date cũng phải có giá trị
            
            // Đảm bảo end_date không null nếu start_date có giá trị
            // (constraint có thể yêu cầu cả hai đều có giá trị hoặc cả hai đều null)
            if ($start_date && !$finalEndDate) {
                $finalEndDate = $start_date;
                error_log('End date is missing, setting end_date = start_date to satisfy constraint');
            }
            
            // Đảm bảo end_date >= start_date (đây là yêu cầu phổ biến nhất của check constraint)
            if ($start_date && $finalEndDate) {
                $startTimestamp = strtotime($start_date);
                $endTimestamp = strtotime($finalEndDate);
                if ($endTimestamp < $startTimestamp) {
                    $finalEndDate = $start_date;
                    error_log('End date (' . $finalEndDate . ') is before start date (' . $start_date . '), setting end_date = start_date');
                }
            }
            
            // Đảm bảo cả start_date và end_date đều có giá trị hoặc đều null
            // (một số constraint yêu cầu điều này để tránh trường hợp một có giá trị một không)
            if ($start_date && !$finalEndDate) {
                $finalEndDate = $start_date;
            }
            if (!$start_date && $finalEndDate) {
                // Nếu có end_date nhưng không có start_date, đặt start_date = end_date
                $start_date = $finalEndDate;
                error_log('Start date is missing but end_date exists, setting start_date = end_date');
            }
            
            // Final validation: đảm bảo end_date >= start_date một lần nữa
            if ($start_date && $finalEndDate) {
                $startTimestamp = strtotime($start_date);
                $endTimestamp = strtotime($finalEndDate);
                if ($endTimestamp < $startTimestamp) {
                    $finalEndDate = $start_date;
                }
            }
            
            // Final check: đảm bảo end_date >= start_date trước khi insert
            // Đây là yêu cầu phổ biến nhất của check constraint cho bảng bookings
            if ($start_date && $finalEndDate) {
                $startTimestamp = strtotime($start_date);
                $endTimestamp = strtotime($finalEndDate);
                if ($endTimestamp < $startTimestamp) {
                    $finalEndDate = $start_date;
                    error_log('Final check: End date adjusted to match start date');
                }
            }
            
            $stmt = $pdo->prepare('INSERT INTO bookings 
                (tour_id, created_by, assigned_guide_id, status, start_date, end_date, schedule_detail, service_detail, diary, notes, created_at, updated_at) 
                VALUES (:tour_id, :created_by, :assigned_guide_id, :status, :start_date, :end_date, :schedule_detail, :service_detail, :diary, :notes, NOW(), NOW())');
            
            $executeParams = [
                'tour_id' => $tour_id,
                'created_by' => $currentUser->id,
                'assigned_guide_id' => $assigned_guide_id ?: null,
                'status' => $finalStatus,
                'start_date' => $start_date ?: null,
                'end_date' => $finalEndDate ?: null,
                'schedule_detail' => $schedule_detail ?: null,
                'service_detail' => $service_detail ?: null,
                'diary' => $diary ?: null,
                'notes' => $notes ?: null,
            ];
            
            // Log parameters trước khi execute
            error_log('Final execute params: ' . json_encode($executeParams, JSON_UNESCAPED_UNICODE));
            
            // Thử insert
            try {
                $stmt->execute($executeParams);
            } catch (PDOException $insertError) {
                // Log chi tiết lỗi
                error_log('Insert failed: ' . $insertError->getMessage());
                error_log('Error code: ' . $insertError->getCode());
                error_log('Error info: ' . print_r($insertError->errorInfo ?? [], true));
                
                // Nếu vẫn là check constraint error, thử với end_date = start_date
                if (strpos($insertError->getMessage(), 'Check constraint') !== false || 
                    strpos($insertError->getMessage(), 'chk_') !== false) {
                    if ($start_date && $finalEndDate && $finalEndDate != $start_date) {
                        error_log('Retrying with end_date = start_date');
                        $executeParams['end_date'] = $start_date;
                        try {
                            $stmt->execute($executeParams);
                            error_log('Retry successful with end_date = start_date');
                        } catch (PDOException $retryError) {
                            error_log('Retry also failed: ' . $retryError->getMessage());
                            throw $insertError; // Throw original error
                        }
                    } else {
                        throw $insertError;
                    }
                } else {
                    throw $insertError;
                }
            }

            $bookingId = $pdo->lastInsertId();
            
            if (!$bookingId) {
                throw new Exception('Không thể lấy ID của booking vừa tạo.');
            }

            // Ghi log trạng thái nếu có (không bắt buộc, nếu lỗi thì bỏ qua)
            if ($status && $currentUser && $currentUser->id) {
                try {
                    $logStmt = $pdo->prepare('INSERT INTO booking_status_logs 
                        (booking_id, old_status, new_status, changed_by, changed_at) 
                        VALUES (:booking_id, NULL, :new_status, :changed_by, NOW())');
                    $logStmt->execute([
                        'booking_id' => $bookingId,
                        'new_status' => $status,
                        'changed_by' => $currentUser->id,
                    ]);
                } catch (PDOException $logError) {
                    // Log lỗi nhưng không rollback vì booking đã tạo thành công
                    error_log('Failed to create status log (non-critical): ' . $logError->getMessage());
                }
            }

            $pdo->commit();
        } catch (PDOException $e) {
            if ($pdo && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('Create booking failed: ' . $e->getMessage());
            error_log('SQL Error Code: ' . $e->getCode());
            error_log('SQL Error Info: ' . print_r($e->errorInfo ?? [], true));
            
            // Phân tích lỗi và hiển thị thông báo phù hợp
            $errorCode = $e->getCode();
            $errorMessage = $e->getMessage();
            
            if (strpos($errorMessage, 'foreign key constraint') !== false) {
                if (strpos($errorMessage, 'tour_id') !== false) {
                    $errors[] = 'Tour không tồn tại hoặc không hợp lệ.';
                } elseif (strpos($errorMessage, 'assigned_guide_id') !== false) {
                    $errors[] = 'Hướng dẫn viên không tồn tại hoặc không hợp lệ.';
                } elseif (strpos($errorMessage, 'created_by') !== false) {
                    $errors[] = 'Thông tin người dùng không hợp lệ. Vui lòng đăng nhập lại.';
                } else {
                    $errors[] = 'Dữ liệu không hợp lệ. Vui lòng kiểm tra lại thông tin.';
                }
            } elseif (strpos($errorMessage, 'Cannot add or update a child row') !== false) {
                $errors[] = 'Dữ liệu không hợp lệ. Vui lòng kiểm tra lại thông tin đã nhập.';
            } elseif (strpos($errorMessage, 'Check constraint') !== false || strpos($errorMessage, 'chk_') !== false) {
                // Xử lý lỗi check constraint - có thể là end_date >= start_date
                if (strpos($errorMessage, 'date') !== false || strpos($errorMessage, 'ngày') !== false || 
                    strpos($errorMessage, 'start_date') !== false || strpos($errorMessage, 'end_date') !== false) {
                    $errors[] = 'Ngày kết thúc phải sau hoặc bằng ngày khởi hành. Vui lòng kiểm tra lại.';
                } elseif (strpos($errorMessage, 'status') !== false || !$status) {
                    $errors[] = 'Trạng thái không hợp lệ. Vui lòng chọn trạng thái từ danh sách.';
                } else {
                    // Hiển thị lỗi chi tiết hơn trong development
                    $isLocal = in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1']) || 
                              strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost') !== false ||
                              strpos($_SERVER['HTTP_HOST'] ?? '', '127.0.0.1') !== false;
                    if ($isLocal) {
                        $errors[] = 'Dữ liệu không đáp ứng yêu cầu. Chi tiết: ' . $e->getMessage();
                    } else {
                        $errors[] = 'Dữ liệu không đáp ứng yêu cầu. Vui lòng kiểm tra lại thông tin đã nhập.';
                    }
                }
            } else {
                $errorMessage = 'Không thể tạo booking. Vui lòng thử lại.';
                // Hiển thị lỗi chi tiết trong development hoặc localhost
                $isLocal = in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1']) || 
                          strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost') !== false ||
                          strpos($_SERVER['HTTP_HOST'] ?? '', '127.0.0.1') !== false;
                if ($isLocal || (defined('DEBUG') && DEBUG)) {
                    $errorMessage .= ' Chi tiết: ' . $e->getMessage() . ' (Code: ' . $e->getCode() . ')';
                }
                $errors[] = $errorMessage;
            }
            
            $tours = [];
            if ($pdo) {
                try {
                    $stmt = $pdo->query('SELECT id, name FROM tours WHERE status = 1 ORDER BY name');
                    $tours = $stmt->fetchAll();
                } catch (PDOException $e2) {
                    error_log('Reload tours failed: ' . $e2->getMessage());
                }
            }
            $guides = $this->getGuides($pdo);
            $statuses = $this->getStatuses($pdo);

            view('admin.bookings.create', [
                'title' => 'Tạo booking mới',
                'errors' => $errors,
                'tours' => $tours,
                'guides' => $guides,
                'statuses' => $statuses,
                'formData' => $_POST,
            ]);
            return;
        } catch (Exception $e) {
            if ($pdo && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('Create booking failed (general): ' . $e->getMessage());
            $errors[] = 'Có lỗi xảy ra khi tạo booking. Vui lòng thử lại.';
            
            $tours = [];
            if ($pdo) {
                try {
                    $stmt = $pdo->query('SELECT id, name FROM tours WHERE status = 1 ORDER BY name');
                    $tours = $stmt->fetchAll();
                } catch (PDOException $e2) {
                    error_log('Reload tours failed: ' . $e2->getMessage());
                }
            }
            $guides = $this->getGuides($pdo);
            $statuses = $this->getStatuses($pdo);

            view('admin.bookings.create', [
                'title' => 'Tạo booking mới',
                'errors' => $errors,
                'tours' => $tours,
                'guides' => $guides,
                'statuses' => $statuses,
                'formData' => $_POST,
            ]);
            return;
        }

        header('Location: ' . BASE_URL . 'admin/bookings?success=' . urlencode('Đã tạo booking mới thành công.'));
        exit;
    }

    // Chi tiết booking
    public function show(): void
    {
        requireGuideOrAdmin();

        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            view('not_found', ['title' => 'Booking không tồn tại']);
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
            // Kiểm tra xem bảng guides có tồn tại không
            $guidesTableExists = $pdo->query("SHOW TABLES LIKE 'guides'")->fetch();
            
            if ($guidesTableExists) {
                $stmt = $pdo->prepare('SELECT b.*, 
                             t.name as tour_name, 
                             t.description as tour_description,
                             u.name as created_by_name,
                             u.email as created_by_email,
                             g.full_name as guide_name,
                             g.contact as guide_contact,
                             ts.name as status_name
                             FROM bookings b
                             LEFT JOIN tours t ON b.tour_id = t.id
                             LEFT JOIN users u ON b.created_by = u.id
                             LEFT JOIN guides g ON b.assigned_guide_id = g.id
                             LEFT JOIN tour_statuses ts ON b.status = ts.id
                             WHERE b.id = :id LIMIT 1');
            } else {
                $stmt = $pdo->prepare('SELECT b.*, 
                             t.name as tour_name, 
                             t.description as tour_description,
                             u.name as created_by_name,
                             u.email as created_by_email,
                             u_guide.name as guide_name,
                             NULL as guide_contact,
                             ts.name as status_name
                             FROM bookings b
                             LEFT JOIN tours t ON b.tour_id = t.id
                             LEFT JOIN users u ON b.created_by = u.id
                             LEFT JOIN users u_guide ON b.assigned_guide_id = u_guide.id AND u_guide.role = "huong_dan_vien"
                             LEFT JOIN tour_statuses ts ON b.status = ts.id
                             WHERE b.id = :id LIMIT 1');
            }
            $stmt->execute(['id' => $id]);
            $booking = $stmt->fetch();
            
            // Kiểm tra quyền: nếu là guide, chỉ xem được booking được gán cho họ
            if ($isGuide && $currentUser) {
                $hasAccess = false;
                
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
                            if ($guide && $booking['assigned_guide_id'] == $guide['id']) {
                                $hasAccess = true;
                            }
                        } else {
                            // Không có user_id, giả sử assigned_guide_id trỏ đến users.id
                            if ($booking['assigned_guide_id'] == $currentUser->id) {
                                $hasAccess = true;
                            }
                        }
                    } catch (PDOException $e) {
                        error_log('Check guide access failed: ' . $e->getMessage());
                        // Fallback
                        if ($booking['assigned_guide_id'] == $currentUser->id) {
                            $hasAccess = true;
                        }
                    }
                } else {
                    // Không có bảng guides, assigned_guide_id trỏ đến users.id
                    if ($booking['assigned_guide_id'] == $currentUser->id) {
                        $hasAccess = true;
                    }
                }
                
                if (!$hasAccess) {
                    view('not_found', ['title' => 'Bạn không có quyền xem booking này']);
                    return;
                }
            }

            // Lấy lịch sử thay đổi trạng thái
            $statusLogs = [];
            $logStmt = $pdo->prepare('SELECT bsl.*, 
                                     u.name as changed_by_name,
                                     ts_old.name as old_status_name,
                                     ts_new.name as new_status_name
                                     FROM booking_status_logs bsl
                                     LEFT JOIN users u ON bsl.changed_by = u.id
                                     LEFT JOIN tour_statuses ts_old ON bsl.old_status = ts_old.id
                                     LEFT JOIN tour_statuses ts_new ON bsl.new_status = ts_new.id
                                     WHERE bsl.booking_id = :booking_id
                                     ORDER BY bsl.changed_at DESC');
            $logStmt->execute(['booking_id' => $id]);
            $statusLogs = $logStmt->fetchAll();

            // Lấy danh sách guides và statuses để có thể chỉnh sửa
            $guides = $this->getGuides($pdo);
            $statuses = $this->getStatuses($pdo);
            
            // Lấy danh sách trạng thái có thể chuyển tiếp (chỉ tiến lên, không quay lại)
            $currentStatusId = (int)($booking['status'] ?? 0);
            $nextStatuses = $this->getNextStatuses($pdo, $currentStatusId);

            view('admin.bookings.show', [
                'title' => 'Chi tiết booking',
                'booking' => $booking,
                'statusLogs' => $statusLogs,
                'guides' => $guides,
                'statuses' => $statuses,
                'nextStatuses' => $nextStatuses, // Danh sách trạng thái có thể chuyển tiếp
            ]);
        } catch (PDOException $e) {
            error_log('Show booking failed: ' . $e->getMessage());
            view('not_found', ['title' => 'Lỗi khi tải booking']);
        }
    }

    // Form chỉnh sửa booking
    public function edit(): void
    {
        requireAdmin(); // Chỉ admin mới sửa được booking

        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            header('Location: ' . BASE_URL . 'admin/bookings');
            exit;
        }

        $pdo = getDB();
        if ($pdo === null) {
            header('Location: ' . BASE_URL . 'admin/bookings');
            exit;
        }

        try {
            // Lấy thông tin booking kèm tên trạng thái
            $tableExists = $pdo->query("SHOW TABLES LIKE 'tour_statuses'")->fetch();
            if ($tableExists) {
                $stmt = $pdo->prepare('SELECT b.*, ts.name as status_name 
                                       FROM bookings b 
                                       LEFT JOIN tour_statuses ts ON b.status = ts.id 
                                       WHERE b.id = :id LIMIT 1');
            } else {
                $stmt = $pdo->prepare('SELECT * FROM bookings WHERE id = :id LIMIT 1');
            }
            $stmt->execute(['id' => $id]);
            $booking = $stmt->fetch();

            if (!$booking) {
                header('Location: ' . BASE_URL . 'admin/bookings');
                exit;
            }

            // Lấy danh sách tours, guides, statuses
            $tours = [];
            try {
                $stmt = $pdo->query('SELECT id, name FROM tours WHERE status = 1 ORDER BY name');
                $tours = $stmt->fetchAll();
            } catch (PDOException $e) {
                error_log('Fetch tours failed: ' . $e->getMessage());
            }
            $guides = $this->getGuides($pdo);
            $statuses = $this->getStatuses($pdo);

            view('admin.bookings.edit', [
                'title' => 'Chỉnh sửa booking',
                'booking' => $booking,
                'tours' => $tours,
                'guides' => $guides,
                'statuses' => $statuses,
            ]);
        } catch (PDOException $e) {
            error_log('Edit booking failed: ' . $e->getMessage());
            header('Location: ' . BASE_URL . 'admin/bookings');
            exit;
        }
    }

    // Cập nhật booking
    public function update(): void
    {
        requireAdmin(); // Chỉ admin mới sửa được booking

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'admin/bookings');
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            header('Location: ' . BASE_URL . 'admin/bookings');
            exit;
        }

        $pdo = getDB();
        $errors = [];
        $currentUser = getCurrentUser();

        $tour_id = !empty($_POST['tour_id']) ? (int)$_POST['tour_id'] : null;
        $assigned_guide_id = !empty($_POST['assigned_guide_id']) ? (int)$_POST['assigned_guide_id'] : null;
        // Không cho phép thay đổi status khi chỉnh sửa, chỉ giữ nguyên status hiện tại
        $start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
        $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
        $schedule_detail_raw = trim($_POST['schedule_detail'] ?? '');
        $service_detail_raw = trim($_POST['service_detail'] ?? '');
        $diary_raw = trim($_POST['diary'] ?? '');
        $notes = trim($_POST['notes'] ?? '');
        
        // Xử lý các trường phải là JSON hợp lệ (theo constraint)
        $schedule_detail = null;
        if ($schedule_detail_raw !== '') {
            $decoded = json_decode($schedule_detail_raw, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $schedule_detail = $schedule_detail_raw;
            } else {
                $schedule_detail = json_encode($schedule_detail_raw, JSON_UNESCAPED_UNICODE);
            }
        }
        
        $service_detail = null;
        if ($service_detail_raw !== '') {
            $decoded = json_decode($service_detail_raw, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $service_detail = $service_detail_raw;
            } else {
                $service_detail = json_encode($service_detail_raw, JSON_UNESCAPED_UNICODE);
            }
        }
        
        $diary = null;
        if ($diary_raw !== '') {
            $decoded = json_decode($diary_raw, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $diary = $diary_raw;
            } else {
                $diary = json_encode($diary_raw, JSON_UNESCAPED_UNICODE);
            }
        }

        // Validation
        if (!$tour_id) {
            $errors[] = 'Vui lòng chọn tour.';
        }

        if (!$start_date) {
            $errors[] = 'Vui lòng chọn ngày khởi hành.';
        }

        // Kiểm tra ngày khởi hành phải là ngày trong tương lai (từ hôm nay trở đi)
        if ($start_date) {
            $startTimestamp = strtotime($start_date);
            $todayTimestamp = strtotime(date('Y-m-d'));
            
            if ($startTimestamp < $todayTimestamp) {
                $errors[] = 'Ngày khởi hành phải là ngày trong tương lai. Không thể chọn ngày trong quá khứ.';
            }
        }

        // Validation ngày tháng - đảm bảo end_date >= start_date
        if ($start_date && $end_date) {
            $startTimestamp = strtotime($start_date);
            $endTimestamp = strtotime($end_date);
            
            if ($endTimestamp < $startTimestamp) {
                $errors[] = 'Ngày kết thúc phải sau hoặc bằng ngày khởi hành.';
            }
        }

        if ($pdo === null) {
            $errors[] = 'Không thể kết nối cơ sở dữ liệu.';
        }

        if (!empty($errors)) {
            // Reload form với errors
            $booking = ['id' => $id] + $_POST;
            $tours = [];
            if ($pdo) {
                try {
                    $stmt = $pdo->query('SELECT id, name FROM tours WHERE status = 1 ORDER BY name');
                    $tours = $stmt->fetchAll();
                } catch (PDOException $e) {
                    error_log('Reload tours failed: ' . $e->getMessage());
                }
            }
            $guides = $this->getGuides($pdo);
            $statuses = $this->getStatuses($pdo);

            view('admin.bookings.edit', [
                'title' => 'Chỉnh sửa booking',
                'errors' => $errors,
                'booking' => $booking,
                'tours' => $tours,
                'guides' => $guides,
                'statuses' => $statuses,
            ]);
            return;
        }

        try {
            $pdo->beginTransaction();

            // Lấy trạng thái hiện tại từ database (không cho phép thay đổi khi chỉnh sửa)
            $oldStmt = $pdo->prepare('SELECT status FROM bookings WHERE id = :id LIMIT 1');
            $oldStmt->execute(['id' => $id]);
            $oldBooking = $oldStmt->fetch();
            $currentStatus = $oldBooking ? $oldBooking['status'] : null;

            // Cập nhật booking (không cập nhật status, giữ nguyên status hiện tại)
            $stmt = $pdo->prepare('UPDATE bookings SET 
                tour_id = :tour_id, 
                assigned_guide_id = :assigned_guide_id, 
                start_date = :start_date, 
                end_date = :end_date, 
                schedule_detail = :schedule_detail, 
                service_detail = :service_detail, 
                diary = :diary, 
                notes = :notes, 
                updated_at = NOW() 
                WHERE id = :id');
            
            $stmt->execute([
                'tour_id' => $tour_id,
                'assigned_guide_id' => $assigned_guide_id ?: null,
                'start_date' => $start_date ?: null,
                'end_date' => $end_date ?: null,
                'schedule_detail' => $schedule_detail ?: null,
                'service_detail' => $service_detail ?: null,
                'diary' => $diary ?: null,
                'notes' => $notes ?: null,
                'id' => $id,
            ]);

            // Không ghi log thay đổi trạng thái vì không cho phép thay đổi status khi chỉnh sửa
            // (Trạng thái chỉ có thể thay đổi qua form tạo booking mới hoặc chức năng thay đổi trạng thái riêng)
            if (false) {
                $logStmt = $pdo->prepare('INSERT INTO booking_status_logs 
                    (booking_id, old_status, new_status, changed_by, note, changed_at) 
                    VALUES (:booking_id, :old_status, :new_status, :changed_by, :note, NOW())');
                $logStmt->execute([
                    'booking_id' => $id,
                    'old_status' => $oldStatus,
                    'new_status' => $status,
                    'changed_by' => $currentUser->id,
                    'note' => 'Cập nhật từ form chỉnh sửa',
                ]);
            }

            $pdo->commit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log('Update booking failed: ' . $e->getMessage());
            $errors[] = 'Không thể cập nhật booking. Vui lòng thử lại.';
            
            $booking = ['id' => $id] + $_POST;
            $tours = [];
            if ($pdo) {
                try {
                    $stmt = $pdo->query('SELECT id, name FROM tours WHERE status = 1 ORDER BY name');
                    $tours = $stmt->fetchAll();
                } catch (PDOException $e2) {
                    error_log('Reload tours failed: ' . $e2->getMessage());
                }
            }
            $guides = $this->getGuides($pdo);
            $statuses = $this->getStatuses($pdo);

            view('admin.bookings.edit', [
                'title' => 'Chỉnh sửa booking',
                'errors' => $errors,
                'booking' => $booking,
                'tours' => $tours,
                'guides' => $guides,
                'statuses' => $statuses,
            ]);
            return;
        }

        header('Location: ' . BASE_URL . 'admin/bookings/show&id=' . $id . '&success=' . urlencode('Đã cập nhật booking thành công.'));
        exit;
    }

    // Thay đổi trạng thái booking
    public function changeStatus(): void
    {
        requireGuideOrAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'admin/bookings');
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        $note = trim($_POST['note'] ?? '');

        if ($id <= 0) {
            header('Location: ' . BASE_URL . 'admin/bookings');
            exit;
        }

        $pdo = getDB();
        $currentUser = getCurrentUser();

        if ($pdo === null) {
            header('Location: ' . BASE_URL . 'admin/bookings/show&id=' . $id . '&error=' . urlencode('Không thể kết nối database.'));
            exit;
        }

        try {
            $pdo->beginTransaction();

            // Lấy trạng thái cũ
            $oldStmt = $pdo->prepare('SELECT status FROM bookings WHERE id = :id LIMIT 1');
            $oldStmt->execute(['id' => $id]);
            $oldBooking = $oldStmt->fetch();
            
            if (!$oldBooking) {
                $pdo->rollBack();
                header('Location: ' . BASE_URL . 'admin/bookings');
                exit;
            }

            $oldStatus = (int)$oldBooking['status'];

            // Tự động tìm trạng thái tiếp theo (id > trạng thái hiện tại, id nhỏ nhất)
            $nextStatusStmt = $pdo->prepare('SELECT id, name FROM tour_statuses WHERE id > :current_status ORDER BY id ASC LIMIT 1');
            $nextStatusStmt->execute(['current_status' => $oldStatus]);
            $nextStatusInfo = $nextStatusStmt->fetch();
            
            if (!$nextStatusInfo) {
                $pdo->rollBack();
                header('Location: ' . BASE_URL . 'admin/bookings/show&id=' . $id . '&error=' . urlencode('Không có trạng thái tiếp theo. Booking có thể đã ở trạng thái cuối cùng.'));
                exit;
            }
            
            $newStatus = (int)$nextStatusInfo['id'];

            // Cập nhật trạng thái
            $stmt = $pdo->prepare('UPDATE bookings SET status = :status, updated_at = NOW() WHERE id = :id');
            $stmt->execute([
                'status' => $newStatus,
                'id' => $id,
            ]);

            // Ghi log
            $logStmt = $pdo->prepare('INSERT INTO booking_status_logs 
                (booking_id, old_status, new_status, changed_by, note, changed_at) 
                VALUES (:booking_id, :old_status, :new_status, :changed_by, :note, NOW())');
            $logStmt->execute([
                'booking_id' => $id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'changed_by' => $currentUser->id,
                'note' => $note ?: null,
            ]);

            $pdo->commit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log('Change booking status failed: ' . $e->getMessage());
            header('Location: ' . BASE_URL . 'admin/bookings/show&id=' . $id . '&error=' . urlencode('Không thể thay đổi trạng thái.'));
            exit;
        }

        header('Location: ' . BASE_URL . 'admin/bookings/show&id=' . $id . '&success=' . urlencode('Đã thay đổi trạng thái thành công.'));
        exit;
    }

    // Phân bổ hướng dẫn viên
    public function assignGuide(): void
    {
        requireGuideOrAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'admin/bookings');
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        $guideId = !empty($_POST['guide_id']) ? (int)$_POST['guide_id'] : null;

        if ($id <= 0) {
            header('Location: ' . BASE_URL . 'admin/bookings');
            exit;
        }

        $pdo = getDB();
        if ($pdo === null) {
            header('Location: ' . BASE_URL . 'admin/bookings/show&id=' . $id . '&error=' . urlencode('Không thể kết nối database.'));
            exit;
        }

        try {
            $stmt = $pdo->prepare('UPDATE bookings SET assigned_guide_id = :guide_id, updated_at = NOW() WHERE id = :id');
            $stmt->execute([
                'guide_id' => $guideId,
                'id' => $id,
            ]);
        } catch (PDOException $e) {
            error_log('Assign guide failed: ' . $e->getMessage());
            header('Location: ' . BASE_URL . 'admin/bookings/show&id=' . $id . '&error=' . urlencode('Không thể phân bổ hướng dẫn viên.'));
            exit;
        }

        header('Location: ' . BASE_URL . 'admin/bookings/show&id=' . $id . '&success=' . urlencode('Đã phân bổ hướng dẫn viên thành công.'));
        exit;
    }

    // Lịch khởi hành
    public function schedule(): void
    {
        requireGuideOrAdmin();

        $pdo = getDB();
        $errors = [];
        $bookings = [];

        // Lọc theo tháng/năm nếu có
        $month = $_GET['month'] ?? date('m');
        $year = $_GET['year'] ?? date('Y');

        if ($pdo === null) {
            $errors[] = 'Không thể kết nối cơ sở dữ liệu.';
        } else {
            try {
                $startDate = sprintf('%s-%s-01', $year, $month);
                $endDate = date('Y-m-t', strtotime($startDate));

                // Kiểm tra xem bảng guides có tồn tại không
                $guidesTableExists = $pdo->query("SHOW TABLES LIKE 'guides'")->fetch();
                
                if ($guidesTableExists) {
                    $stmt = $pdo->prepare('SELECT b.*, 
                                 t.name as tour_name, 
                                 g.full_name as guide_name,
                                 ts.name as status_name
                                 FROM bookings b
                                 LEFT JOIN tours t ON b.tour_id = t.id
                                 LEFT JOIN guides g ON b.assigned_guide_id = g.id
                                 LEFT JOIN tour_statuses ts ON b.status = ts.id
                                 WHERE b.start_date BETWEEN :start_date AND :end_date
                                 ORDER BY b.start_date ASC, b.created_at ASC');
                } else {
                    $stmt = $pdo->prepare('SELECT b.*, 
                                 t.name as tour_name, 
                                 u_guide.name as guide_name,
                                 ts.name as status_name
                                 FROM bookings b
                                 LEFT JOIN tours t ON b.tour_id = t.id
                                 LEFT JOIN users u_guide ON b.assigned_guide_id = u_guide.id AND u_guide.role = "huong_dan_vien"
                                 LEFT JOIN tour_statuses ts ON b.status = ts.id
                                 WHERE b.start_date BETWEEN :start_date AND :end_date
                                 ORDER BY b.start_date ASC, b.created_at ASC');
                }
                $stmt->execute([
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ]);
                $bookings = $stmt->fetchAll();
            } catch (PDOException $e) {
                error_log('Schedule failed: ' . $e->getMessage());
                $errors[] = 'Không thể tải lịch khởi hành.';
            }
        }

        view('admin.bookings.schedule', [
            'title' => 'Lịch khởi hành',
            'bookings' => $bookings,
            'errors' => $errors,
            'month' => $month,
            'year' => $year,
        ]);
    }

    // Danh sách khách hàng (từ bookings)
    public function customerList(): void
    {
        requireGuideOrAdmin();

        $pdo = getDB();
        $errors = [];
        $customers = [];

        if ($pdo === null) {
            $errors[] = 'Không thể kết nối cơ sở dữ liệu.';
        } else {
            try {
                // Lấy danh sách users đã tạo booking (khách hàng)
                $stmt = $pdo->query('SELECT DISTINCT u.id, u.name, u.email, 
                         COUNT(b.id) as total_bookings,
                         MAX(b.created_at) as last_booking_date
                         FROM users u
                         INNER JOIN bookings b ON u.id = b.created_by
                         GROUP BY u.id, u.name, u.email
                         ORDER BY total_bookings DESC, last_booking_date DESC');
                $customers = $stmt->fetchAll();
            } catch (PDOException $e) {
                error_log('Customer list failed: ' . $e->getMessage());
                $errors[] = 'Không thể tải danh sách khách hàng.';
            }
        }

        view('admin.bookings.customers', [
            'title' => 'Danh sách khách hàng',
            'customers' => $customers,
            'errors' => $errors,
        ]);
    }

    // Thêm ghi chú
    public function addNote(): void
    {
        requireGuideOrAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'admin/bookings');
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        $note = trim($_POST['note'] ?? '');

        if ($id <= 0) {
            header('Location: ' . BASE_URL . 'admin/bookings');
            exit;
        }

        $pdo = getDB();
        if ($pdo === null) {
            header('Location: ' . BASE_URL . 'admin/bookings/show&id=' . $id . '&error=' . urlencode('Không thể kết nối database.'));
            exit;
        }

        try {
            // Lấy ghi chú cũ và thêm mới
            $stmt = $pdo->prepare('SELECT notes FROM bookings WHERE id = :id LIMIT 1');
            $stmt->execute(['id' => $id]);
            $booking = $stmt->fetch();
            
            $oldNotes = $booking ? ($booking['notes'] ?? '') : '';
            $newNotes = $oldNotes . ($oldNotes ? "\n\n" : '') . date('d/m/Y H:i') . ': ' . $note;

            $updateStmt = $pdo->prepare('UPDATE bookings SET notes = :notes, updated_at = NOW() WHERE id = :id');
            $updateStmt->execute([
                'notes' => $newNotes,
                'id' => $id,
            ]);
        } catch (PDOException $e) {
            error_log('Add note failed: ' . $e->getMessage());
            header('Location: ' . BASE_URL . 'admin/bookings/show&id=' . $id . '&error=' . urlencode('Không thể thêm ghi chú.'));
            exit;
        }

        header('Location: ' . BASE_URL . 'admin/bookings/show&id=' . $id . '&success=' . urlencode('Đã thêm ghi chú thành công.'));
        exit;
    }

    // Thêm phản hồi/đánh giá/sự cố (lưu vào diary)
    public function addFeedback(): void
    {
        requireGuideOrAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'admin/bookings');
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        $feedback = trim($_POST['feedback'] ?? '');
        $feedbackType = $_POST['feedback_type'] ?? 'feedback'; // feedback, review, incident

        if ($id <= 0 || !$feedback) {
            header('Location: ' . BASE_URL . 'admin/bookings/show&id=' . $id . '&error=' . urlencode('Vui lòng nhập nội dung phản hồi.'));
            exit;
        }

        $pdo = getDB();
        if ($pdo === null) {
            header('Location: ' . BASE_URL . 'admin/bookings/show&id=' . $id . '&error=' . urlencode('Không thể kết nối database.'));
            exit;
        }

        try {
            // Lấy diary cũ và thêm mới
            $stmt = $pdo->prepare('SELECT diary FROM bookings WHERE id = :id LIMIT 1');
            $stmt->execute(['id' => $id]);
            $booking = $stmt->fetch();
            
            $oldDiary = $booking ? ($booking['diary'] ?? '') : '';
            $typeLabel = match($feedbackType) {
                'review' => 'Đánh giá',
                'incident' => 'Sự cố',
                default => 'Phản hồi',
            };
            $newDiary = $oldDiary . ($oldDiary ? "\n\n" : '') . '[' . $typeLabel . ' - ' . date('d/m/Y H:i') . ']: ' . $feedback;

            $updateStmt = $pdo->prepare('UPDATE bookings SET diary = :diary, updated_at = NOW() WHERE id = :id');
            $updateStmt->execute([
                'diary' => $newDiary,
                'id' => $id,
            ]);
        } catch (PDOException $e) {
            error_log('Add feedback failed: ' . $e->getMessage());
            header('Location: ' . BASE_URL . 'admin/bookings/show&id=' . $id . '&error=' . urlencode('Không thể thêm phản hồi.'));
            exit;
        }

        header('Location: ' . BASE_URL . 'admin/bookings/show&id=' . $id . '&success=' . urlencode('Đã thêm phản hồi thành công.'));
        exit;
    }

    // Xóa booking
    public function delete(): void
    {
        requireAdmin(); // Chỉ admin mới được xóa

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'admin/bookings');
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);

        $pdo = getDB();
        if ($pdo !== null && $id > 0) {
            try {
                $pdo->beginTransaction();
                
                // Xóa logs trước
                $stmt = $pdo->prepare('DELETE FROM booking_status_logs WHERE booking_id = :id');
                $stmt->execute(['id' => $id]);
                
                // Xóa booking
                $stmt = $pdo->prepare('DELETE FROM bookings WHERE id = :id');
                $stmt->execute(['id' => $id]);
                
                $pdo->commit();
            } catch (PDOException $e) {
                $pdo->rollBack();
                error_log('Delete booking failed: ' . $e->getMessage());
                header('Location: ' . BASE_URL . 'admin/bookings?error=' . urlencode('Không thể xóa booking.'));
                exit;
            }
        }

        header('Location: ' . BASE_URL . 'admin/bookings?success=' . urlencode('Đã xóa booking.'));
        exit;
    }
}

