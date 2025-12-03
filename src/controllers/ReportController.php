<?php

require_once BASE_PATH . '/src/helpers/database.php';

class ReportController
{
    // Trang báo cáo thống kê chính
    public function index(): void
    {
        requireAdmin();

        $pdo = getDB();
        $errors = [];
        $stats = [];

        if ($pdo === null) {
            $errors[] = 'Không thể kết nối cơ sở dữ liệu.';
        } else {
            try {
                // Thống kê tổng quan
                $stats['total_tours'] = $pdo->query('SELECT COUNT(*) as count FROM tours WHERE status = 1')->fetch()['count'] ?? 0;
                $stats['total_bookings'] = $pdo->query('SELECT COUNT(*) as count FROM bookings')->fetch()['count'] ?? 0;
                $stats['total_customers'] = $pdo->query('SELECT COUNT(DISTINCT created_by) as count FROM bookings')->fetch()['count'] ?? 0;
                $stats['total_guides'] = $pdo->query('SELECT COUNT(*) as count FROM users WHERE role = "huong_dan_vien"')->fetch()['count'] ?? 0;

                // Thống kê booking theo trạng thái
                try {
                    $tableExists = $pdo->query("SHOW TABLES LIKE 'tour_statuses'")->fetch();
                    if ($tableExists) {
                        $statusStats = $pdo->query('
                            SELECT s.name, COUNT(b.id) as count 
                            FROM tour_statuses s 
                            LEFT JOIN bookings b ON s.id = b.status 
                            GROUP BY s.id, s.name
                            ORDER BY s.id
                        ')->fetchAll();
                    } else {
                        // Nếu không có bảng statuses, đếm trực tiếp từ bookings
                        $statusStats = $pdo->query('
                            SELECT 
                                COALESCE(status, 0) as status_id,
                                COUNT(*) as count
                            FROM bookings
                            GROUP BY status
                        ')->fetchAll();
                    }
                    $stats['booking_by_status'] = $statusStats;
                } catch (PDOException $e) {
                    error_log('Get status stats failed: ' . $e->getMessage());
                    $stats['booking_by_status'] = [];
                }

                // Thống kê booking theo tháng (12 tháng gần nhất)
                $monthlyBookings = $pdo->query('
                    SELECT 
                        DATE_FORMAT(created_at, "%Y-%m") as month,
                        COUNT(*) as count
                    FROM bookings
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                    GROUP BY DATE_FORMAT(created_at, "%Y-%m")
                    ORDER BY month ASC
                ')->fetchAll();
                $stats['monthly_bookings'] = $monthlyBookings;

                // Top 5 tour phổ biến nhất
                $topTours = $pdo->query('
                    SELECT 
                        t.id,
                        t.name,
                        COUNT(b.id) as booking_count
                    FROM tours t
                    LEFT JOIN bookings b ON t.id = b.tour_id
                    WHERE t.status = 1
                    GROUP BY t.id, t.name
                    ORDER BY booking_count DESC
                    LIMIT 5
                ')->fetchAll();
                $stats['top_tours'] = $topTours;

                // Thống kê doanh thu (nếu có giá tour)
                $revenueQuery = $pdo->query('
                    SELECT 
                        COALESCE(SUM(t.price), 0) as total_revenue
                    FROM bookings b
                    INNER JOIN tours t ON b.tour_id = t.id
                    WHERE t.price IS NOT NULL
                ');
                $revenueResult = $revenueQuery->fetch();
                $stats['total_revenue'] = $revenueResult['total_revenue'] ?? 0;

                // Thống kê booking theo hướng dẫn viên
                try {
                    $guidesTableExists = $pdo->query("SHOW TABLES LIKE 'guides'")->fetch();
                    if ($guidesTableExists) {
                        $guideStats = $pdo->query('
                            SELECT 
                                g.full_name as guide_name,
                                COUNT(b.id) as booking_count
                            FROM guides g
                            LEFT JOIN bookings b ON g.id = b.assigned_guide_id
                            GROUP BY g.id, g.full_name
                            HAVING booking_count > 0
                            ORDER BY booking_count DESC
                            LIMIT 10
                        ')->fetchAll();
                    } else {
                        // Nếu không có bảng guides, lấy từ users
                        $guideStats = $pdo->query('
                            SELECT 
                                u.name as guide_name,
                                COUNT(b.id) as booking_count
                            FROM users u
                            LEFT JOIN bookings b ON u.id = b.assigned_guide_id
                            WHERE u.role = "huong_dan_vien"
                            GROUP BY u.id, u.name
                            HAVING booking_count > 0
                            ORDER BY booking_count DESC
                            LIMIT 10
                        ')->fetchAll();
                    }
                    $stats['guide_stats'] = $guideStats;
                } catch (PDOException $e) {
                    error_log('Get guide stats failed: ' . $e->getMessage());
                    $stats['guide_stats'] = [];
                }

                // Thống kê theo danh mục tour
                $categoryStats = $pdo->query('
                    SELECT 
                        c.name as category_name,
                        COUNT(DISTINCT t.id) as tour_count,
                        COUNT(b.id) as booking_count
                    FROM categories c
                    LEFT JOIN tours t ON c.id = t.category_id AND t.status = 1
                    LEFT JOIN bookings b ON t.id = b.tour_id
                    WHERE c.status = 1
                    GROUP BY c.id, c.name
                    ORDER BY booking_count DESC
                ')->fetchAll();
                $stats['category_stats'] = $categoryStats;

            } catch (PDOException $e) {
                error_log('Reports index failed: ' . $e->getMessage());
                $errors[] = 'Không thể tải dữ liệu thống kê.';
            }
        }

        view('admin.reports.index', [
            'title' => 'Báo cáo thống kê',
            'stats' => $stats,
            'errors' => $errors,
        ]);
    }
}

