<?php
// Sử dụng layout và truyền nội dung vào
ob_start();
$stats = $stats ?? [];
?>

<!--begin::Row-->
<div class="row">
  <div class="col-12">
    <!-- Default box -->
    <div class="card mb-4">
      <div class="card-header">
        <h3 class="card-title">Chào mừng đến với hệ thống quản lý tour</h3>
        <div class="card-tools">
          <button
            type="button"
            class="btn btn-tool"
            data-lte-toggle="card-collapse"
            title="Collapse"
          >
            <i data-lte-icon="expand" class="bi bi-plus-lg"></i>
            <i data-lte-icon="collapse" class="bi bi-dash-lg"></i>
          </button>
        </div>
      </div>
      <div class="card-body">
        <?php if (isLoggedIn()): ?>
          <div class="alert alert-success" role="alert">
            <h4 class="alert-heading">
              <i class="bi bi-check-circle-fill me-2"></i>
              Đăng nhập thành công!
            </h4>
            <p class="mb-0">
              Xin chào, <strong><?= htmlspecialchars($user->name) ?></strong>! 
              Bạn đã đăng nhập với quyền <strong><?= $user->isAdmin() ? 'Admin' : 'Hướng dẫn viên' ?></strong>.
            </p>
          </div>

          <div class="mt-4">
            <h3 class="mb-3">
              <i class="bi bi-info-circle-fill me-2 text-primary"></i>
              Thông tin tài khoản
            </h3>
            <div class="list-group">
              <div class="list-group-item">
                <div class="d-flex w-100 justify-content-between">
                  <h5 class="mb-1">
                    <i class="bi bi-envelope me-2"></i>
                    Email
                  </h5>
                </div>
                <p class="mb-1"><?= htmlspecialchars($user->email) ?></p>
              </div>
              <div class="list-group-item">
                <div class="d-flex w-100 justify-content-between">
                  <h5 class="mb-1">
                    <i class="bi bi-person-badge me-2"></i>
                    Vai trò
                  </h5>
                </div>
                <p class="mb-1">
                  <?= $user->isAdmin() ? 'Quản trị viên' : 'Hướng dẫn viên' ?>
                </p>
              </div>
            </div>
          </div>
        <?php else: ?>
          <div class="alert alert-warning" role="alert">
            <h4 class="alert-heading">
              <i class="bi bi-exclamation-triangle-fill me-2"></i>
              Chưa đăng nhập
            </h4>
            <p class="mb-0">
              Vui lòng <a href="<?= BASE_URL ?>?act=login" class="alert-link">đăng nhập</a> để sử dụng đầy đủ chức năng.
            </p>
          </div>
        <?php endif; ?>
      </div>
      <!-- /.card-body -->
    </div>
    <!-- /.card -->
  </div>
</div>
<!--end::Row-->

<?php if (isLoggedIn() && $user->isAdmin() && !empty($stats)): ?>
<!-- Báo cáo thống kê -->
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
                                <div class="table-responsive" style="max-height: 180px; overflow-y: auto;">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light sticky-top">
                                            <tr>
                                                <th class="pb-3">Trạng thái</th>
                                                <th class="text-end pb-3">Số lượng</th>
                                                <th class="text-end pb-3">Doanh thu</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $displayed = 0;
                                            foreach ($stats['booking_by_status'] as $status): 
                                                if ($displayed >= 3) break;
                                                $displayed++;
                                            ?>
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
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php
$content = ob_get_clean();

// Hiển thị layout với nội dung
view('layouts.AdminLayout', [
    'title' => $title ?? 'Trang chủ - Website Quản Lý Tour',
    'pageTitle' => 'Trang chủ',
    'content' => $content,
    'breadcrumb' => [
        ['label' => 'Trang chủ', 'url' => BASE_URL . 'home', 'active' => true],
    ],
]);
?>
