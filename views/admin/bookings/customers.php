<?php
ob_start();
?>
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white border-0">
                <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-2">
                <div>
                    <h3 class="card-title mb-0">
                        <i class="bi bi-people me-2"></i>
                        Danh sách khách hàng
                    </h3>
                </div>
                    <div class="d-flex gap-2">
                        <a href="<?= BASE_URL ?>admin/bookings" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Quay lại
                    </a>
                    </div>
                </div>
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

                <?php if (empty($customers)): ?>
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        Chưa có khách hàng nào.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Tên khách hàng</th>
                                    <th>Giới tính</th>
                                    <th>Số điện thoại</th>
                                    <th>Email</th>
                                    <th>Tour</th>
                                    <th>Ngày booking</th>
                                    <th class="text-end">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($customers as $index => $customer): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($customer['name'] ?? 'N/A') ?></strong>
                                        </td>
                                        <td>
                                            <?php 
                                            $gender = $customer['gender'] ?? '';
                                            $genderText = '';
                                            if ($gender === 'male') {
                                                $genderText = 'Nam';
                                            } elseif ($gender === 'female') {
                                                $genderText = 'Nữ';
                                            } elseif ($gender === 'other') {
                                                $genderText = 'Khác';
                                            } else {
                                                $genderText = '-';
                                            }
                                            ?>
                                            <span class="badge bg-info"><?= $genderText ?></span>
                                        </td>
                                        <td><?= htmlspecialchars($customer['phone'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($customer['email'] ?? '-') ?></td>
                                        <td>
                                            <small><?= htmlspecialchars($customer['tour_name'] ?? 'N/A') ?></small>
                                        </td>
                                        <td>
                                            <?php if ($customer['booking_date']): ?>
                                                <?= date('d/m/Y H:i', strtotime($customer['booking_date'])) ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <a href="<?= BASE_URL ?>admin/bookings/customer-detail&booking_id=<?= $customer['booking_id'] ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye me-1"></i> Chi tiết
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
    'title' => $title ?? 'Danh sách khách hàng',
    'pageTitle' => 'Danh sách khách hàng',
    'content' => $content,
    'breadcrumb' => [
        ['label' => 'Trang chủ', 'url' => BASE_URL . 'home'],
        ['label' => 'Booking', 'url' => BASE_URL . 'admin/bookings'],
        ['label' => 'Khách hàng', 'url' => BASE_URL . 'admin/bookings/customers', 'active' => true],
    ],
]);
?>

