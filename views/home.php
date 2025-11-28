<?php
// Sử dụng layout và truyền nội dung vào
ob_start();
?>

<!--begin::Row-->
<div class="row">
  <div class="col-12">
    <!-- Default box -->
    <div class="card">
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
        <?php if ($user->isAdmin()): ?>
          <div class="alert alert-success" role="alert">
            <h4 class="alert-heading">
              <i class="bi bi-check-circle-fill me-2"></i>
              Xin chào, Admin <?= htmlspecialchars($user->name) ?>!
            </h4>
            <p class="mb-0">
              Bạn đang xem bảng điều khiển với đầy đủ chức năng quản trị hệ thống tour.
            </p>
          </div>

          <div class="row g-3 mt-2">
            <div class="col-md-4">
              <div class="info-box bg-primary text-white shadow-sm">
                <span class="info-box-icon"><i class="bi bi-airplane-engines"></i></span>
                <div class="info-box-content">
                  <span class="info-box-text">Quản lý tour</span>
                  <span class="info-box-number small">Theo dõi và cập nhật tour</span>
                </div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="info-box bg-success text-white shadow-sm">
                <span class="info-box-icon"><i class="bi bi-people-fill"></i></span>
                <div class="info-box-content">
                  <span class="info-box-text">Khách hàng</span>
                  <span class="info-box-number small">Thông tin & đặt tour</span>
                </div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="info-box bg-warning text-white shadow-sm">
                <span class="info-box-icon"><i class="bi bi-person-gear"></i></span>
                <div class="info-box-content">
                  <span class="info-box-text">Hướng dẫn viên</span>
                  <span class="info-box-number small">Cấp & quản lý tài khoản</span>
                </div>
              </div>
            </div>
          </div>
        <?php else: ?>
          <div class="alert alert-info" role="alert">
            <h4 class="alert-heading">
              <i class="bi bi-person-walking me-2"></i>
              Xin chào, <?= htmlspecialchars($user->name) ?>!
            </h4>
            <p class="mb-0">
              Đây là trang dành riêng cho hướng dẫn viên. Bạn chỉ xem được lộ trình được phân công.
              Mọi cài đặt hệ thống vẫn nằm ở phía Admin.
            </p>
          </div>

          <div class="mt-4">
            <h3 class="mb-3">
              <i class="bi bi-geo-alt-fill me-2 text-primary"></i>
              Lịch tour được giao
            </h3>
            <div class="table-responsive">
              <table class="table table-striped">
                <thead>
                  <tr>
                    <th>Tour</th>
                    <th>Ngày khởi hành</th>
                    <th>Trạng thái</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($assignedTours)): ?>
                    <tr>
                      <td colspan="3" class="text-center text-muted">
                        Chưa có tour nào được phân công. Vui lòng liên hệ Admin để được cập nhật.
                      </td>
                    </tr>
                  <?php else: ?>
                    <?php foreach ($assignedTours as $tour): ?>
                      <tr>
                        <td><?= htmlspecialchars($tour['name']) ?></td>
                        <td><?= htmlspecialchars($tour['start_date']) ?></td>
                        <td>
                          <span class="badge bg-primary"><?= htmlspecialchars($tour['status']) ?></span>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        <?php endif; ?>
      </div>
      <!-- /.card-body -->
    </div>
    <!-- /.card -->
  </div>
</div>
<!--end::Row-->

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
