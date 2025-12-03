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
                    <div class="alert alert-danger">
                        <ul class="mb-0 ps-3">
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
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
                <?php if (isset($stats['total_revenue']) && $stats['total_revenue'] > 0): ?>
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm bg-gradient-success text-white">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="text-white-50 mb-2">Tổng doanh thu</h5>
                                        <h1 class="mb-0"><?= number_format($stats['total_revenue'], 0, ',', '.') ?> ₫</h1>
                                    </div>
                                    <div class="fs-1 opacity-50">
                                        <i class="bi bi-currency-dollar"></i>
                                    </div>
                                </div>
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
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>Trạng thái</th>
                                                <th class="text-end">Số lượng</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($stats['booking_by_status'] as $status): ?>
                                            <tr>
                                                <td>
                                                    <span class="badge bg-info"><?= htmlspecialchars($status['name'] ?? 'N/A') ?></span>
                                                </td>
                                                <td class="text-end fw-semibold"><?= number_format($status['count'] ?? 0) ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

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
                                                <th class="text-end">Số booking</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($stats['top_tours'] as $index => $tour): ?>
                                            <tr>
                                                <td>
                                                    <span class="badge bg-primary me-2">#<?= $index + 1 ?></span>
                                                    <?= htmlspecialchars($tour['name'] ?? 'N/A') ?>
                                                </td>
                                                <td class="text-end fw-semibold"><?= number_format($tour['booking_count'] ?? 0) ?></td>
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
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($stats['category_stats'] as $cat): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($cat['category_name'] ?? 'N/A') ?></td>
                                                <td class="text-end"><?= number_format($cat['tour_count'] ?? 0) ?></td>
                                                <td class="text-end fw-semibold"><?= number_format($cat['booking_count'] ?? 0) ?></td>
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
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($stats['guide_stats'] as $guide): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($guide['guide_name'] ?? 'N/A') ?></td>
                                                <td class="text-end fw-semibold"><?= number_format($guide['booking_count'] ?? 0) ?></td>
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
                                                <th>Số lượng booking</th>
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
                                                <td class="fw-semibold"><?= number_format($month['count'] ?? 0) ?></td>
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


