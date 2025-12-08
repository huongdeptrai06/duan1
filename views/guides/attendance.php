<?php
ob_start();
?>
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white">
                <h3 class="card-title mb-0">
                    <i class="bi bi-check2-square me-2"></i>Điểm danh khách hàng
                </h3>
            </div>
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0 ps-3">
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success">
                        <?= htmlspecialchars($_GET['success']) ?>
                    </div>
                <?php endif; ?>

                <?php if (empty($bookings)): ?>
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        Bạn chưa có tour nào sắp diễn ra cần điểm danh.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Tên tour</th>
                                    <th>Ngày khởi hành</th>
                                    <th>Ngày kết thúc</th>
                                    <th>Số khách</th>
                                    <th class="text-end">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bookings as $index => $booking): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($booking['tour_name'] ?? 'N/A') ?></strong>
                                        </td>
                                        <td>
                                            <i class="bi bi-calendar-event me-1"></i>
                                            <?= date('d/m/Y', strtotime($booking['start_date'])) ?>
                                        </td>
                                        <td>
                                            <?php if ($booking['end_date']): ?>
                                                <i class="bi bi-calendar-check me-1"></i>
                                                <?= date('d/m/Y', strtotime($booking['end_date'])) ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">
                                                <i class="bi bi-people me-1"></i>
                                                <?= $booking['total_customers'] ?? 0 ?> người
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <a href="<?= BASE_URL ?>guides/attendance-detail&booking_id=<?= $booking['id'] ?>" 
                                               class="btn btn-sm btn-success">
                                                <i class="bi bi-check2-square me-1"></i>Điểm danh
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
    'title' => $title ?? 'Điểm danh khách hàng',
    'pageTitle' => 'Điểm danh khách hàng',
    'content' => $content,
    'breadcrumb' => [
        ['label' => 'Trang chủ', 'url' => BASE_URL . 'home'],
        ['label' => 'Điểm danh', 'url' => BASE_URL . 'guides/attendance', 'active' => true],
    ],
]);
?>

