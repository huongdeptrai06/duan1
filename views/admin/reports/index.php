<?php
ob_start();
$stats = $stats ?? [];
?>
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-gradient-primary text-white py-3">
                <h3 class="card-title mb-0 d-flex align-items-center">
                    <i class="bi bi-graph-up-arrow me-2 fs-5"></i>Báo cáo thống kê
                </h3>
            </div>
            <div class="card-body p-4">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Lưu ý:</strong> <?= htmlspecialchars(implode(', ', $errors)) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Thống kê tổng quan -->
                <div class="row g-3 mb-4">
                    <div class="col-md-3 col-sm-6">
                        <div class="card border-0 shadow-sm bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-white-50 mb-1">Tổng số Tour</h6>
                                        <h2 class="mb-0"><?= number_format($stats['total_tours'] ?? 0) ?></h2>
                                    </div>
                                    <div class="fs-1 opacity-50">
                                        <i class="bi bi-airplane-engines"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="card border-0 shadow-sm bg-info text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-white-50 mb-1">Tổng số Booking</h6>
                                        <h2 class="mb-0"><?= number_format($stats['total_bookings'] ?? 0) ?></h2>
                                    </div>
                                    <div class="fs-1 opacity-50">
                                        <i class="bi bi-calendar-check"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="card border-0 shadow-sm bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-white-50 mb-1">Tổng số Khách hàng</h6>
                                        <h2 class="mb-0"><?= number_format($stats['total_customers'] ?? 0) ?></h2>
                                    </div>
                                    <div class="fs-1 opacity-50">
                                        <i class="bi bi-people"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="card border-0 shadow-sm bg-warning text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-white-50 mb-1">Tổng số HDV</h6>
                                        <h2 class="mb-0"><?= number_format($stats['total_guides'] ?? 0) ?></h2>
                                    </div>
                                    <div class="fs-1 opacity-50">
                                        <i class="bi bi-person-badge"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Doanh thu -->
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm bg-gradient-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-white-50 mb-1">Tổng doanh thu</h6>
                                        <h3 class="mb-0"><?= number_format($stats['total_revenue'] ?? 0, 0, ',', '.') ?> ₫</h3>
                                    </div>
                                    <div class="fs-1 opacity-50">
                                        <i class="bi bi-currency-dollar"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm bg-gradient-info text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-white-50 mb-1">Doanh thu đã xác nhận</h6>
                                        <h3 class="mb-0"><?= number_format($stats['confirmed_revenue'] ?? 0, 0, ',', '.') ?> ₫</h3>
                                    </div>
                                    <div class="fs-1 opacity-50">
                                        <i class="bi bi-check-circle"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm bg-gradient-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-white-50 mb-1">Doanh thu hoàn thành</h6>
                                        <h3 class="mb-0"><?= number_format($stats['completed_revenue'] ?? 0, 0, ',', '.') ?> ₫</h3>
                                    </div>
                                    <div class="fs-1 opacity-50">
                                        <i class="bi bi-trophy"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Doanh thu theo trạng thái -->
                <?php if (isset($stats['pending_revenue']) && $stats['pending_revenue'] > 0): ?>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Doanh thu chờ xác nhận</h6>
                                        <h4 class="text-warning mb-0"><?= number_format($stats['pending_revenue'] ?? 0, 0, ',', '.') ?> ₫</h4>
                                    </div>
                                    <div class="fs-2 text-warning opacity-50">
                                        <i class="bi bi-clock-history"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Thống kê tỷ lệ -->
                <?php if (isset($stats['avg_revenue_per_booking']) && $stats['avg_revenue_per_booking'] > 0): ?>
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center">
                                <h6 class="text-muted mb-2">Doanh thu trung bình/Booking</h6>
                                <h4 class="text-primary mb-0"><?= number_format($stats['avg_revenue_per_booking'] ?? 0, 0, ',', '.') ?> ₫</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center">
                                <h6 class="text-muted mb-2">Booking/Tour</h6>
                                <h4 class="text-info mb-0"><?= number_format($stats['booking_per_tour'] ?? 0, 2) ?></h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center">
                                <h6 class="text-muted mb-2">Booking/Khách hàng</h6>
                                <h4 class="text-success mb-0"><?= number_format($stats['booking_per_customer'] ?? 0, 2) ?></h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center">
                                <h6 class="text-muted mb-2">Booking/HDV</h6>
                                <h4 class="text-warning mb-0"><?= number_format($stats['booking_per_guide'] ?? 0, 2) ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <div class="row g-3">
                    <!-- Thống kê booking theo trạng thái -->
                    <?php if (!empty($stats['booking_by_status'])): ?>
                    <div class="col-md-6">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-header bg-white border-bottom">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-pie-chart me-2 text-primary"></i>Booking theo trạng thái
                                </h5>
                            </div>
                            <div class="card-body p-4">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th class="pb-3">Trạng thái</th>
                                                <th class="text-end pb-3">Số lượng</th>
                                                <th class="text-end pb-3">Doanh thu</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($stats['booking_by_status'] as $status): ?>
                                            <tr>
                                                <td class="py-3">
                                                    <span class="badge bg-info px-3 py-2"><?= htmlspecialchars($status['name'] ?? 'N/A') ?></span>
                                                </td>
                                                <td class="text-end fw-semibold py-3"><?= number_format($status['count'] ?? 0) ?></td>
                                                <td class="text-end fw-bold text-success py-3"><?= number_format($status['revenue'] ?? 0, 0, ',', '.') ?> ₫</td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Tổng quan doanh thu -->
                    <div class="col-md-6">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-header bg-white border-bottom">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-graph-up me-2 text-success"></i>Tổng quan doanh thu
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="d-flex flex-column gap-3">
                                    <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded">
                                        <div>
                                            <small class="text-muted d-block mb-1">Tổng doanh thu</small>
                                            <h4 class="mb-0 text-success fw-bold"><?= number_format($stats['total_revenue'] ?? 0, 0, ',', '.') ?> ₫</h4>
                                        </div>
                                        <div class="fs-2 text-success opacity-50">
                                            <i class="bi bi-currency-dollar"></i>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded">
                                        <div>
                                            <small class="text-muted d-block mb-1">Doanh thu đã xác nhận</small>
                                            <h5 class="mb-0 text-info fw-bold"><?= number_format($stats['confirmed_revenue'] ?? 0, 0, ',', '.') ?> ₫</h5>
                                        </div>
                                        <div class="fs-3 text-info opacity-50">
                                            <i class="bi bi-check-circle"></i>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded">
                                        <div>
                                            <small class="text-muted d-block mb-1">Doanh thu hoàn thành</small>
                                            <h5 class="mb-0 text-primary fw-bold"><?= number_format($stats['completed_revenue'] ?? 0, 0, ',', '.') ?> ₫</h5>
                                        </div>
                                        <div class="fs-3 text-primary opacity-50">
                                            <i class="bi bi-trophy"></i>
                                        </div>
                                    </div>
                                    <?php if (isset($stats['avg_revenue_per_booking']) && $stats['avg_revenue_per_booking'] > 0): ?>
                                    <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded">
                                        <div>
                                            <small class="text-muted d-block mb-1">Doanh thu trung bình/Booking</small>
                                            <h5 class="mb-0 text-warning fw-bold"><?= number_format($stats['avg_revenue_per_booking'] ?? 0, 0, ',', '.') ?> ₫</h5>
                                        </div>
                                        <div class="fs-3 text-warning opacity-50">
                                            <i class="bi bi-calculator"></i>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    <?php 
                                    $totalBookings = $stats['total_bookings'] ?? 0;
                                    $completedBookings = 0;
                                    foreach ($stats['booking_by_status'] ?? [] as $status) {
                                        if (stripos($status['name'] ?? '', 'Hoàn thành') !== false || stripos($status['name'] ?? '', 'Hoàn tất') !== false) {
                                            $completedBookings = $status['count'] ?? 0;
                                            break;
                                        }
                                    }
                                    $completionRate = $totalBookings > 0 ? round(($completedBookings / $totalBookings) * 100, 1) : 0;
                                    ?>
                                    <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded">
                                        <div>
                                            <small class="text-muted d-block mb-1">Tỷ lệ hoàn thành</small>
                                            <h5 class="mb-0 text-success fw-bold"><?= $completionRate ?>%</h5>
                                        </div>
                                        <div class="fs-3 text-success opacity-50">
                                            <i class="bi bi-percent"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Top 5 tour phổ biến -->
                    <?php if (!empty($stats['top_tours'])): ?>
                    <div class="col-md-6">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-header bg-white border-bottom">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-trophy me-2 text-warning"></i>Top 5 Tour phổ biến
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>Tour</th>
                                                <th class="text-end">Giá</th>
                                                <th class="text-end">Số booking</th>
                                                <th class="text-end">Doanh thu</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach (array_slice($stats['top_tours'], 0, 5) as $index => $tour): ?>
                                            <tr>
                                                <td>
                                                    <span class="badge bg-primary me-2">#<?= $index + 1 ?></span>
                                                    <?= htmlspecialchars($tour['name'] ?? 'N/A') ?>
                                                </td>
                                                <td class="text-end"><?= number_format($tour['price'] ?? 0, 0, ',', '.') ?> ₫</td>
                                                <td class="text-end fw-semibold"><?= number_format($tour['booking_count'] ?? 0) ?></td>
                                                <td class="text-end fw-bold text-success"><?= number_format($tour['total_revenue'] ?? 0, 0, ',', '.') ?> ₫</td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Thống kê theo danh mục -->
                    <?php if (!empty($stats['category_stats'])): ?>
                    <div class="col-md-6">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-header bg-white border-bottom">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-tags me-2 text-success"></i>Thống kê theo danh mục
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>Danh mục</th>
                                                <th class="text-end">Tour</th>
                                                <th class="text-end">Booking</th>
                                                <th class="text-end">Doanh thu</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($stats['category_stats'] as $cat): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($cat['category_name'] ?? 'N/A') ?></td>
                                                <td class="text-end"><?= number_format($cat['tour_count'] ?? 0) ?></td>
                                                <td class="text-end fw-semibold"><?= number_format($cat['booking_count'] ?? 0) ?></td>
                                                <td class="text-end fw-bold text-success"><?= number_format($cat['total_revenue'] ?? 0, 0, ',', '.') ?> ₫</td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Thống kê theo hướng dẫn viên -->
                    <?php if (!empty($stats['guide_stats'])): ?>
                    <div class="col-md-6">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-header bg-white border-bottom">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-person-badge me-2 text-info"></i>Top HDV có nhiều booking
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>Hướng dẫn viên</th>
                                                <th class="text-end">Số booking</th>
                                                <th class="text-end">Doanh thu</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($stats['guide_stats'] as $guide): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($guide['guide_name'] ?? 'N/A') ?></td>
                                                <td class="text-end fw-semibold"><?= number_format($guide['booking_count'] ?? 0) ?></td>
                                                <td class="text-end fw-bold text-success"><?= number_format($guide['total_revenue'] ?? 0, 0, ',', '.') ?> ₫</td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Biểu đồ booking theo tháng -->
                    <?php if (!empty($stats['monthly_bookings'])): ?>
                    <div class="col-12">
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-white border-bottom">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-bar-chart me-2 text-primary"></i>Booking theo tháng (12 tháng gần nhất)
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>Tháng</th>
                                                <th class="text-end">Số lượng booking</th>
                                                <th class="text-end">Doanh thu</th>
                                                <th class="w-50">Biểu đồ</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $maxCount = 0;
                                            foreach ($stats['monthly_bookings'] as $month) {
                                                if ($month['count'] > $maxCount) {
                                                    $maxCount = $month['count'];
                                                }
                                            }
                                            foreach ($stats['monthly_bookings'] as $month): 
                                                $percentage = $maxCount > 0 ? ($month['count'] / $maxCount) * 100 : 0;
                                                $monthName = date('m/Y', strtotime($month['month'] . '-01'));
                                            ?>
                                            <tr>
                                                <td class="fw-semibold"><?= htmlspecialchars($monthName) ?></td>
                                                <td class="text-end fw-semibold"><?= number_format($month['count'] ?? 0) ?></td>
                                                <td class="text-end fw-bold text-success"><?= number_format($month['revenue'] ?? 0, 0, ',', '.') ?> ₫</td>
                                                <td>
                                                    <div class="progress" style="height: 25px;">
                                                        <div class="progress-bar bg-primary" role="progressbar" 
                                                             style="width: <?= $percentage ?>%" 
                                                             aria-valuenow="<?= $month['count'] ?>" 
                                                             aria-valuemin="0" 
                                                             aria-valuemax="<?= $maxCount ?>">
                                                            <?= $month['count'] ?>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Booking mới nhất -->
                    <?php if (!empty($stats['recent_bookings'])): ?>
                    <div class="col-md-6">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-header bg-white border-bottom">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-clock-history me-2 text-info"></i>Booking mới nhất
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>Khách hàng</th>
                                                <th>Tour</th>
                                                <th class="text-end">Ngày tạo</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach (array_slice($stats['recent_bookings'], 0, 5) as $booking): ?>
                                            <tr>
                                                <td>
                                                    <small class="text-muted"><?= htmlspecialchars($booking['customer_name'] ?? 'N/A') ?></small>
                                                </td>
                                                <td>
                                                    <small><?= htmlspecialchars($booking['tour_name'] ?? 'N/A') ?></small>
                                                </td>
                                                <td class="text-end">
                                                    <small class="text-muted"><?= date('d/m/Y', strtotime($booking['created_at'])) ?></small>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Tour mới nhất -->
                    <?php if (!empty($stats['recent_tours'])): ?>
                    <div class="col-md-6">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-header bg-white border-bottom">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-airplane-engines me-2 text-primary"></i>Tour mới nhất
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>Tour</th>
                                                <th>Danh mục</th>
                                                <th class="text-end">Giá</th>
                                                <th class="text-end">Booking</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach (array_slice($stats['recent_tours'], 0, 5) as $tour): ?>
                                            <tr>
                                                <td>
                                                    <small><?= htmlspecialchars($tour['name'] ?? 'N/A') ?></small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary"><?= htmlspecialchars($tour['category_name'] ?? 'N/A') ?></span>
                                                </td>
                                                <td class="text-end">
                                                    <small class="fw-semibold"><?= number_format($tour['price'] ?? 0, 0, ',', '.') ?> ₫</small>
                                                </td>
                                                <td class="text-end">
                                                    <span class="badge bg-info"><?= number_format($tour['booking_count'] ?? 0) ?></span>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Thống kê booking theo tuần -->
                    <?php if (!empty($stats['weekly_bookings'])): ?>
                    <div class="col-12">
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-white border-bottom">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-calendar-week me-2 text-success"></i>Booking theo tuần (8 tuần gần nhất)
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>Tuần</th>
                                                <th>Số lượng booking</th>
                                                <th class="w-50">Biểu đồ</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $maxWeekCount = 0;
                                            foreach ($stats['weekly_bookings'] as $week) {
                                                if ($week['count'] > $maxWeekCount) {
                                                    $maxWeekCount = $week['count'];
                                                }
                                            }
                                            foreach ($stats['weekly_bookings'] as $week): 
                                                $percentage = $maxWeekCount > 0 ? ($week['count'] / $maxWeekCount) * 100 : 0;
                                            ?>
                                            <tr>
                                                <td class="fw-semibold"><?= htmlspecialchars($week['week_start'] ?? 'N/A') ?></td>
                                                <td class="fw-semibold"><?= number_format($week['count'] ?? 0) ?></td>
                                                <td>
                                                    <div class="progress" style="height: 20px;">
                                                        <div class="progress-bar bg-success" role="progressbar" 
                                                             style="width: <?= $percentage ?>%" 
                                                             aria-valuenow="<?= $week['count'] ?>" 
                                                             aria-valuemin="0" 
                                                             aria-valuemax="<?= $maxWeekCount ?>">
                                                            <?= $week['count'] ?>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();

view('layouts.AdminLayout', [
    'title' => $title ?? 'Báo cáo thống kê',
    'pageTitle' => 'Báo cáo thống kê',
    'content' => $content,
    'breadcrumb' => [
        ['label' => 'Trang chủ', 'url' => BASE_URL . 'home'],
        ['label' => 'Báo cáo thống kê', 'url' => BASE_URL . 'admin/reports', 'active' => true],
    ],
]);
?>



