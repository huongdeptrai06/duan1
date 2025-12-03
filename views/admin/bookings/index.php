<?php
ob_start();
?>
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white border-0 d-flex flex-column flex-md-row align-items-start align-items-md-center gap-2">
                <div>
                    <h3 class="card-title mb-1">
                        <i class="bi bi-calendar-check me-2"></i>
                        Quản lý booking
                    </h3>
                    <small class="text-muted">Quản lý đặt tour, lịch khởi hành và phân bổ hướng dẫn viên.</small>
                </div>
                <?php if (isAdmin()): ?>
                <div class="d-flex gap-2 ms-md-auto">
                    <a href="<?= BASE_URL ?>admin/bookings/schedule" class="btn btn-outline-info">
                        <i class="bi bi-calendar-event me-1"></i> Lịch khởi hành
                    </a>
                    <a href="<?= BASE_URL ?>admin/bookings/customers" class="btn btn-outline-secondary">
                        <i class="bi bi-people me-1"></i> Khách hàng
                    </a>
                    <a href="<?= BASE_URL ?>admin/bookings/create" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-1"></i> Tạo booking mới
                    </a>
                </div>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if (!empty($successMessage)): ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <?= htmlspecialchars($successMessage) ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0 ps-3">
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <!-- Filter by status -->
                <?php if (!empty($statuses)): ?>
                    <div class="mb-3 d-flex gap-2 flex-wrap">
                        <a href="<?= BASE_URL ?>admin/bookings" class="btn btn-sm <?= !$statusFilter ? 'btn-primary' : 'btn-outline-secondary' ?>">
                            Tất cả
                        </a>
                        <?php foreach ($statuses as $status): ?>
                            <a href="<?= BASE_URL ?>admin/bookings?status=<?= $status['id'] ?>" 
                               class="btn btn-sm <?= $statusFilter == $status['id'] ? 'btn-primary' : 'btn-outline-secondary' ?>">
                                <?= htmlspecialchars($status['name']) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if (empty($bookings)): ?>
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        Chưa có booking nào. Hãy tạo booking đầu tiên.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Tour</th>
                                    <th>Khách hàng</th>
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
                                        <td><?= htmlspecialchars($booking['created_by_name'] ?? 'N/A') ?></td>
                                        <td>
                                            <?php if ($booking['guide_name']): ?>
                                                <?= htmlspecialchars($booking['guide_name']) ?>
                                            <?php else: ?>
                                                <span class="text-muted">Chưa phân bổ</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($booking['start_date']): ?>
                                                <?= date('d/m/Y', strtotime($booking['start_date'])) ?>
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
                                            <a href="<?= BASE_URL ?>admin/bookings/show&id=<?= $booking['id'] ?>" class="btn btn-sm btn-outline-info" title="Xem chi tiết">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <?php if (isAdmin()): ?>
                                            <a href="<?= BASE_URL ?>admin/bookings/edit&id=<?= $booking['id'] ?>" class="btn btn-sm btn-outline-primary" title="Chỉnh sửa">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <form action="<?= BASE_URL ?>admin/bookings/delete" method="post" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa booking này?');">
                                                <input type="hidden" name="id" value="<?= $booking['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Xóa">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                            <?php endif; ?>
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
    'title' => $title ?? 'Quản lý booking',
    'pageTitle' => 'Quản lý booking',
    'content' => $content,
    'breadcrumb' => [
        ['label' => 'Trang chủ', 'url' => BASE_URL . 'home'],
        ['label' => 'Booking', 'url' => BASE_URL . 'admin/bookings', 'active' => true],
    ],
]);
?>

