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
                $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                
                $totalToursResult = $pdo->query('SELECT COUNT(*) as count FROM tours WHERE status = 1')->fetch();
                $stats['total_tours'] = $totalToursResult['count'] ?? 0;
                
                $totalBookingsResult = $pdo->query('SELECT COUNT(*) as count FROM bookings')->fetch();
                $stats['total_bookings'] = $totalBookingsResult['count'] ?? 0;
                
                $totalCustomersResult = $pdo->query('SELECT COUNT(DISTINCT created_by) as count FROM bookings')->fetch();
                $stats['total_customers'] = $totalCustomersResult['count'] ?? 0;
                
                $totalGuidesResult = $pdo->query('SELECT COUNT(*) as count FROM users WHERE role = "huong_dan_vien" AND status = 1')->fetch();
                $stats['total_guides'] = $totalGuidesResult['count'] ?? 0;
                
                $totalCategoriesResult = $pdo->query('SELECT COUNT(*) as count FROM categories WHERE status = 1')->fetch();
                $stats['total_categories'] = $totalCategoriesResult['count'] ?? 0;

                // Thống kê doanh thu
                try {
                    $revenueQuery = $pdo->query('
                        SELECT 
                            COALESCE(SUM(t.price), 0) as total_revenue,
                            COALESCE(SUM(CASE WHEN b.status = 1 OR b.status = 2 THEN t.price ELSE 0 END), 0) as confirmed_revenue,
                            COALESCE(SUM(CASE WHEN b.status = 3 THEN t.price ELSE 0 END), 0) as completed_revenue,
                            COALESCE(SUM(CASE WHEN b.status = 0 THEN t.price ELSE 0 END), 0) as pending_revenue
                        FROM bookings b
                        INNER JOIN tours t ON b.tour_id = t.id
                        WHERE t.price IS NOT NULL
                    ');
                    $revenueResult = $revenueQuery->fetch(PDO::FETCH_ASSOC);
                    $stats['total_revenue'] = isset($revenueResult['total_revenue']) ? (float)$revenueResult['total_revenue'] : 0;
                    $stats['confirmed_revenue'] = isset($revenueResult['confirmed_revenue']) ? (float)$revenueResult['confirmed_revenue'] : 0;
                    $stats['completed_revenue'] = isset($revenueResult['completed_revenue']) ? (float)$revenueResult['completed_revenue'] : 0;
                    $stats['pending_revenue'] = isset($revenueResult['pending_revenue']) ? (float)$revenueResult['pending_revenue'] : 0;
                } catch (PDOException $e) {
                    error_log('Get revenue stats failed: ' . $e->getMessage());
                    $stats['total_revenue'] = 0;
                    $stats['confirmed_revenue'] = 0;
                    $stats['completed_revenue'] = 0;
                    $stats['pending_revenue'] = 0;
                }

                // Thống kê booking theo trạng thái
                try {
                    $tableExists = $pdo->query("SHOW TABLES LIKE 'tour_statuses'")->fetch();
                    if ($tableExists) {
                        $statusStats = $pdo->query('
                            SELECT 
                                s.id, 
                                s.name, 
                                COUNT(b.id) as count,
                                COALESCE(SUM(t.price), 0) as revenue
                            FROM tour_statuses s 
                            LEFT JOIN bookings b ON s.id = b.status 
                            LEFT JOIN tours t ON b.tour_id = t.id
                            GROUP BY s.id, s.name
                            ORDER BY s.id
                        ')->fetchAll(PDO::FETCH_ASSOC);
                    } else {
                        // Nếu không có bảng statuses, đếm trực tiếp từ bookings
                        $statusStats = $pdo->query('
                            SELECT 
                                COALESCE(b.status, 0) as status_id,
                                CASE 
                                    WHEN b.status = 0 THEN "Chờ xác nhận"
                                    WHEN b.status = 1 THEN "Đã xác nhận"
                                    WHEN b.status = 2 THEN "Đã cọc"
                                    WHEN b.status = 3 THEN "Hoàn thành"
                                    WHEN b.status = 4 THEN "Đã hủy"
                                    ELSE "Khác"
                                END as name,
                                COUNT(b.id) as count,
                                COALESCE(SUM(t.price), 0) as revenue
                            FROM bookings b
                            LEFT JOIN tours t ON b.tour_id = t.id
                            GROUP BY b.status
                            ORDER BY b.status
                        ')->fetchAll(PDO::FETCH_ASSOC);
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
                        DATE_FORMAT(created_at, "%m/%Y") as month_display,
                        COUNT(*) as count,
                        COALESCE(SUM(t.price), 0) as revenue
                    FROM bookings b
                    LEFT JOIN tours t ON b.tour_id = t.id
                    WHERE b.created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                    GROUP BY DATE_FORMAT(created_at, "%Y-%m"), DATE_FORMAT(created_at, "%m/%Y")
                    ORDER BY month ASC
                ')->fetchAll(PDO::FETCH_ASSOC);
                $stats['monthly_bookings'] = $monthlyBookings;

                // Thống kê booking theo tuần (8 tuần gần nhất)
                $weeklyBookings = $pdo->query('
                    SELECT 
                        YEARWEEK(created_at, 1) as week,
                        DATE_FORMAT(MIN(created_at), "%d/%m/%Y") as week_start,
                        COUNT(*) as count
                    FROM bookings
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 8 WEEK)
                    GROUP BY YEARWEEK(created_at, 1)
                    ORDER BY week ASC
                ')->fetchAll(PDO::FETCH_ASSOC);
                $stats['weekly_bookings'] = $weeklyBookings;

                // Top 10 tour phổ biến nhất
                $topTours = $pdo->query('
                    SELECT 
                        t.id,
                        t.name,
                        t.price,
                        COUNT(b.id) as booking_count,
                        COALESCE(SUM(t.price), 0) as total_revenue
                    FROM tours t
                    LEFT JOIN bookings b ON t.id = b.tour_id
                    WHERE t.status = 1
                    GROUP BY t.id, t.name, t.price
                    ORDER BY booking_count DESC, total_revenue DESC
                    LIMIT 10
                ')->fetchAll(PDO::FETCH_ASSOC);
                $stats['top_tours'] = $topTours;

                // Thống kê theo danh mục tour
                $categoryStats = $pdo->query('
                    SELECT 
                        c.id,
                        c.name as category_name,
                        COUNT(DISTINCT t.id) as tour_count,
                        COUNT(b.id) as booking_count,
                        COALESCE(SUM(t.price), 0) as total_revenue
                    FROM categories c
                    LEFT JOIN tours t ON c.id = t.category_id AND t.status = 1
                    LEFT JOIN bookings b ON t.id = b.tour_id
                    WHERE c.status = 1
                    GROUP BY c.id, c.name
                    ORDER BY booking_count DESC, total_revenue DESC
                ')->fetchAll(PDO::FETCH_ASSOC);
                $stats['category_stats'] = $categoryStats;

                // Thống kê theo hướng dẫn viên
                try {
                    $guidesTableExists = $pdo->query("SHOW TABLES LIKE 'guides'")->fetch();
                    if ($guidesTableExists) {
                        $guideStats = $pdo->query('
                            SELECT 
                                g.id,
                                g.full_name as guide_name,
                                COUNT(b.id) as booking_count,
                                COALESCE(SUM(t.price), 0) as total_revenue
                            FROM guides g
                            LEFT JOIN bookings b ON g.id = b.assigned_guide_id
                            LEFT JOIN tours t ON b.tour_id = t.id
                            WHERE g.status = 1
                            GROUP BY g.id, g.full_name
                            HAVING booking_count > 0
                            ORDER BY booking_count DESC, total_revenue DESC
                            LIMIT 10
                        ')->fetchAll(PDO::FETCH_ASSOC);
                    } else {
                        // Nếu không có bảng guides, lấy từ users
                        $guideStats = $pdo->query('
                            SELECT 
                                u.id,
                                u.name as guide_name,
                                COUNT(b.id) as booking_count,
                                COALESCE(SUM(t.price), 0) as total_revenue
                            FROM users u
                            LEFT JOIN bookings b ON u.id = b.assigned_guide_id
                            LEFT JOIN tours t ON b.tour_id = t.id
                            WHERE u.role = "huong_dan_vien" AND u.status = 1
                            GROUP BY u.id, u.name
                            HAVING booking_count > 0
                            ORDER BY booking_count DESC, total_revenue DESC
                            LIMIT 10
                        ')->fetchAll(PDO::FETCH_ASSOC);
                    }
                    $stats['guide_stats'] = $guideStats;
                } catch (PDOException $e) {
                    error_log('Get guide stats failed: ' . $e->getMessage());
                    $stats['guide_stats'] = [];
                }

                // Thống kê booking theo ngày trong tháng hiện tại
                $dailyBookings = $pdo->query('
                    SELECT 
                        DATE(created_at) as date,
                        DATE_FORMAT(created_at, "%d/%m") as date_display,
                        DAY(created_at) as day,
                        COUNT(*) as count
                    FROM bookings
                    WHERE MONTH(created_at) = MONTH(NOW()) 
                      AND YEAR(created_at) = YEAR(NOW())
                    GROUP BY DATE(created_at), DATE_FORMAT(created_at, "%d/%m"), DAY(created_at)
                    ORDER BY date ASC
                ')->fetchAll(PDO::FETCH_ASSOC);
                $stats['daily_bookings'] = $dailyBookings;

                // Thống kê tour mới nhất (10 tour)
                $recentTours = $pdo->query('
                    SELECT 
                        t.id,
                        t.name,
                        t.price,
                        c.name as category_name,
                        COUNT(b.id) as booking_count,
                        t.created_at
                    FROM tours t
                    LEFT JOIN categories c ON t.category_id = c.id
                    LEFT JOIN bookings b ON t.id = b.tour_id
                    WHERE t.status = 1
                    GROUP BY t.id, t.name, t.price, c.name, t.created_at
                    ORDER BY t.created_at DESC
                    LIMIT 10
                ')->fetchAll(PDO::FETCH_ASSOC);
                $stats['recent_tours'] = $recentTours;

                // Thống kê booking mới nhất (10 booking)
                $recentBookings = $pdo->query('
                    SELECT 
                        b.id,
                        b.created_at,
                        t.name as tour_name,
                        u.name as customer_name,
                        b.status
                    FROM bookings b
                    LEFT JOIN tours t ON b.tour_id = t.id
                    LEFT JOIN users u ON b.created_by = u.id
                    ORDER BY b.created_at DESC
                    LIMIT 10
                ')->fetchAll(PDO::FETCH_ASSOC);
                $stats['recent_bookings'] = $recentBookings;

                // Tính toán tỷ lệ
                $stats['booking_per_tour'] = $stats['total_tours'] > 0 
                    ? round($stats['total_bookings'] / $stats['total_tours'], 2) 
                    : 0;
                $stats['booking_per_customer'] = $stats['total_customers'] > 0 
                    ? round($stats['total_bookings'] / $stats['total_customers'], 2) 
                    : 0;
                $stats['booking_per_guide'] = $stats['total_guides'] > 0 
                    ? round($stats['total_bookings'] / $stats['total_guides'], 2) 
                    : 0;
                $stats['avg_revenue_per_booking'] = $stats['total_bookings'] > 0 
                    ? round($stats['total_revenue'] / $stats['total_bookings'], 0) 
                    : 0;

            } catch (PDOException $e) {
                error_log('Reports index failed: ' . $e->getMessage());
                $errors[] = 'Không thể tải dữ liệu thống kê.';
            }
        }

        view('admin.reports.index', [
            'title' => 'Báo cáo thống kê',
            'pageTitle' => 'Báo cáo thống kê',
            'stats' => $stats,
            'errors' => $errors,
        ]);
    }
}
