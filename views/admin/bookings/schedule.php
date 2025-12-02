<?php
ob_start();
?>
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white border-0 d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-2">
                <div>
                    <h3 class="card-title mb-1">
                        <i class="bi bi-calendar-event me-2"></i>
                        Lịch khởi hành
                    </h3>
                    <small class="text-muted">Xem lịch khởi hành theo tháng/năm.</small>
                </div>
                <div class="d-flex gap-2">
                    <a href="<?= BASE_URL ?>admin/bookings" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Quay lại
                    </a>
                </div>
            </div>
            <div class="card-body">
                <!-- Filter by month/year -->
                <form method="get" action="<?= BASE_URL ?>admin/bookings/schedule" class="mb-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="month" class="form-label">Tháng</label>
                            <select class="form-select" id="month" name="month">
                                <?php for ($m = 1; $m <= 12; $m++): ?>
                                    <option value="<?= sprintf('%02d', $m) ?>" <?= $month == sprintf('%02d', $m) ? 'selected' : '' ?>>
                                        Tháng <?= $m ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="year" class="form-label">Năm</label>
                            <select class="form-select" id="year" name="year">
                                <?php for ($y = date('Y') - 1; $y <= date('Y') + 2; $y++): ?>
                                    <option value="<?= $y ?>" <?= $year == $y ? 'selected' : '' ?>>
                                        <?= $y ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-search me-1"></i> Xem lịch
                            </button>
                        </div>
                    </div>
                </form>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0 ps-3">
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if (empty($bookings)): ?>
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        Không có booking nào trong tháng này.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Tour</th>
                                    <th>Hướng dẫn viên</th>
                                    <th>Ngày khởi hành</th>
                                    <th>Ngày kết thúc</th>
                                    <th>Trạng thái</th>
                                    <th class="text-end">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bookings as $index => $booking): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td>
                                            <a href="<?= BASE_URL ?>admin/bookings/show&id=<?= $booking['id'] ?>" class="fw-semibold text-decoration-none">
                                                <?= htmlspecialchars($booking['tour_name'] ?? 'N/A') ?>
                                            </a>
                                        </td>
                                        <td>
                                            <?php if ($booking['guide_name']): ?>
                                                <?= htmlspecialchars($booking['guide_name']) ?>
                                            <?php else: ?>
                                                <span class="text-muted">Chưa phân bổ</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($booking['start_date']): ?>
                                                <strong><?= date('d/m/Y', strtotime($booking['start_date'])) ?></strong>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($booking['end_date']): ?>
                                                <?= date('d/m/Y', strtotime($booking['end_date'])) ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($booking['status_name']): ?>
                                                <span class="badge bg-info"><?= htmlspecialchars($booking['status_name']) ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Chưa có</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <a href="<?= BASE_URL ?>admin/bookings/show&id=<?= $booking['id'] ?>" class="btn btn-sm btn-outline-info">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();

view('layouts.AdminLayout', [
    'title' => $title ?? 'Lịch khởi hành',
    'pageTitle' => 'Lịch khởi hành',
    'content' => $content,
    'breadcrumb' => [
        ['label' => 'Trang chủ', 'url' => BASE_URL . 'home'],
        ['label' => 'Booking', 'url' => BASE_URL . 'admin/bookings'],
        ['label' => 'Lịch khởi hành', 'url' => BASE_URL . 'admin/bookings/schedule', 'active' => true],
    ],
]);
?>

