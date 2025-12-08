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
                            <i class="bi bi-people-fill me-2"></i>
                            Chi tiết khách hàng - Booking #<?= $booking['id'] ?? '' ?>
                        </h3>
                        <?php if ($booking): ?>
                        <p class="text-muted mb-0 mt-1">
                            <i class="bi bi-airplane-engines me-1"></i>
                            <?= htmlspecialchars($booking['tour_name'] ?? 'N/A') ?>
                            <?php if (!empty($booking['start_date'])): ?>
                                - Khởi hành: <?= date('d/m/Y', strtotime($booking['start_date'])) ?>
                            <?php elseif (!empty($booking['created_at'])): ?>
                                - Ngày tạo: <?= date('d/m/Y', strtotime($booking['created_at'])) ?>
                            <?php endif; ?>
                        </p>
                        <?php endif; ?>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="<?= BASE_URL ?>admin/bookings/customers" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Quay lại
                        </a>
                        <?php if ($booking): ?>
                            <a href="<?= BASE_URL ?>admin/bookings/show&id=<?= $booking['id'] ?>" class="btn btn-outline-info">
                                <i class="bi bi-calendar-check me-1"></i> Xem booking
                            </a>
                        <?php endif; ?>
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

                <?php if ($booking): ?>
                    <div class="mb-3">
                        <h5 class="text-primary mb-0">
                            <i class="bi bi-list-ul me-2"></i>
                            Danh sách thành viên (<?= count($customers) ?> người)
                        </h5>
                    </div>

                <?php endif; ?>

                <?php if (empty($customers)): ?>
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        Booking này chưa có khách hàng nào.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead class="table-primary">
                                <tr>
                                    <th>#</th>
                                    <th>Tên khách hàng</th>
                                    <th>Giới tính</th>
                                    <th>Số điện thoại</th>
                                    <th>Email</th>
                                    <th class="text-end">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($customers as $index => $customer): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($customer['name'] ?? 'N/A') ?></strong>
                                            <?php if ($index === 0): ?>
                                                <span class="badge bg-success ms-2">Người đại diện</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php 
                                            $gender = $customer['gender'] ?? '';
                                            $genderText = '';
                                            $genderBadge = 'bg-secondary';
                                            if ($gender === 'male') {
                                                $genderText = 'Nam';
                                                $genderBadge = 'bg-primary';
                                            } elseif ($gender === 'female') {
                                                $genderText = 'Nữ';
                                                $genderBadge = 'bg-danger';
                                            } elseif ($gender === 'other') {
                                                $genderText = 'Khác';
                                                $genderBadge = 'bg-info';
                                            } else {
                                                $genderText = '-';
                                            }
                                            ?>
                                            <span class="badge <?= $genderBadge ?>"><?= $genderText ?></span>
                                        </td>
                                        <td>
                                            <?php if ($customer['phone']): ?>
                                                <i class="bi bi-telephone me-1"></i>
                                                <?= htmlspecialchars($customer['phone']) ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($customer['email']): ?>
                                                <i class="bi bi-envelope me-1"></i>
                                                <?= htmlspecialchars($customer['email']) ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-danger" onclick="deleteCustomer(<?= $customer['id'] ?>, '<?= htmlspecialchars($customer['name'], ENT_QUOTES) ?>')">
                                                <i class="bi bi-trash me-1"></i>Xóa
                                            </button>
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
    'title' => $title ?? 'Chi tiết khách hàng',
    'pageTitle' => 'Chi tiết khách hàng',
    'content' => $content,
    'breadcrumb' => [
        ['label' => 'Trang chủ', 'url' => BASE_URL . 'home'],
        ['label' => 'Booking', 'url' => BASE_URL . 'admin/bookings'],
        ['label' => 'Khách hàng', 'url' => BASE_URL . 'admin/bookings/customers'],
        ['label' => 'Chi tiết', 'url' => BASE_URL . 'admin/bookings/customer-detail&booking_id=' . ($booking['id'] ?? ''), 'active' => true],
    ],
]);
?>

<script>
const bookingId = <?= $booking['id'] ?? 0 ?>;

function deleteCustomer(customerId, customerName) {
    if (!confirm('Bạn có chắc chắn muốn xóa khách hàng "' + customerName + '"?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('customer_id', customerId);
    formData.append('booking_id', bookingId);
    
    fetch('<?= BASE_URL ?>admin/bookings/delete-customer', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Xóa khách hàng thành công!');
            location.reload();
        } else {
            alert('Lỗi: ' + (data.message || 'Không thể xóa khách hàng.'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Lỗi khi xóa khách hàng. Vui lòng thử lại.');
    });
}
</script>

