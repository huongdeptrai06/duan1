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
}

