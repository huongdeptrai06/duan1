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
                            <button type="button" class="btn btn-success" onclick="showAddCustomerModal()">
                                <i class="bi bi-plus-circle me-1"></i> Thêm khách hàng
                            </button>
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
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-warning" onclick="editCustomer(<?= $customer['id'] ?>, '<?= htmlspecialchars($customer['name'], ENT_QUOTES) ?>', '<?= htmlspecialchars($customer['phone'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($customer['email'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($customer['gender'] ?? '', ENT_QUOTES) ?>')">
                                                    <i class="bi bi-pencil me-1"></i>Sửa
                                                </button>
                                            <button type="button" class="btn btn-sm btn-danger" onclick="deleteCustomer(<?= $customer['id'] ?>, '<?= htmlspecialchars($customer['name'], ENT_QUOTES) ?>')">
                                                <i class="bi bi-trash me-1"></i>Xóa
                                            </button>
                                            </div>
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

<!-- Modal thêm khách hàng -->
<div class="modal fade" id="addCustomerModal" tabindex="-1" aria-labelledby="addCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="addCustomerModalLabel">
                    <i class="bi bi-person-plus me-2"></i>Thêm khách hàng
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addCustomerForm">
                <div class="modal-body">
                    <input type="hidden" name="booking_id" value="<?= $booking['id'] ?? 0 ?>">
                    
                    <div class="mb-3">
                        <label for="add_customer_name" class="form-label fw-semibold">
                            Tên khách hàng <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="add_customer_name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="add_customer_phone" class="form-label fw-semibold">
                            Số điện thoại
                        </label>
                        <input type="tel" class="form-control" id="add_customer_phone" name="phone">
                    </div>
                    
                    <div class="mb-3">
                        <label for="add_customer_email" class="form-label fw-semibold">
                            Email
                        </label>
                        <input type="email" class="form-control" id="add_customer_email" name="email">
                    </div>
                    
                    <div class="mb-3">
                        <label for="add_customer_gender" class="form-label fw-semibold">
                            Giới tính
                        </label>
                        <select class="form-select" id="add_customer_gender" name="gender">
                            <option value="">-- Chọn giới tính --</option>
                            <option value="male">Nam</option>
                            <option value="female">Nữ</option>
                            <option value="other">Khác</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check me-1"></i>Thêm khách hàng
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal sửa thông tin khách hàng -->
<div class="modal fade" id="editCustomerModal" tabindex="-1" aria-labelledby="editCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="editCustomerModalLabel">
                    <i class="bi bi-pencil me-2"></i>Sửa thông tin khách hàng
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editCustomerForm">
                <div class="modal-body">
                    <input type="hidden" id="edit_customer_id" name="customer_id">
                    <input type="hidden" name="booking_id" value="<?= $booking['id'] ?? 0 ?>">
                    
                    <div class="mb-3">
                        <label for="edit_customer_name" class="form-label fw-semibold">
                            Tên khách hàng <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="edit_customer_name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_customer_phone" class="form-label fw-semibold">
                            Số điện thoại
                        </label>
                        <input type="tel" class="form-control" id="edit_customer_phone" name="phone">
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_customer_email" class="form-label fw-semibold">
                            Email
                        </label>
                        <input type="email" class="form-control" id="edit_customer_email" name="email">
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_customer_gender" class="form-label fw-semibold">
                            Giới tính
                        </label>
                        <select class="form-select" id="edit_customer_gender" name="gender">
                            <option value="">-- Chọn giới tính --</option>
                            <option value="male">Nam</option>
                            <option value="female">Nữ</option>
                            <option value="other">Khác</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-save me-1"></i>Lưu thay đổi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const bookingId = <?= $booking['id'] ?? 0 ?>;

function showAddCustomerModal() {
    // Reset form
    const form = document.getElementById('addCustomerForm');
    form.reset();
    // Đảm bảo booking_id luôn được set
    const bookingIdInput = form.querySelector('input[name="booking_id"]');
    if (bookingIdInput) {
        bookingIdInput.value = bookingId;
    }
    
    const modal = new bootstrap.Modal(document.getElementById('addCustomerModal'));
    modal.show();
}

// Xử lý submit form thêm khách hàng
document.getElementById('addCustomerForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('<?= BASE_URL ?>admin/bookings/add-customer', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Thêm khách hàng thành công!');
            const modal = bootstrap.Modal.getInstance(document.getElementById('addCustomerModal'));
            modal.hide();
            location.reload();
        } else {
            alert('Lỗi: ' + (data.message || 'Không thể thêm khách hàng.'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Lỗi khi thêm khách hàng. Vui lòng thử lại.');
    });
});

function editCustomer(customerId, name, phone, email, gender) {
    document.getElementById('edit_customer_id').value = customerId;
    document.getElementById('edit_customer_name').value = name;
    document.getElementById('edit_customer_phone').value = phone || '';
    document.getElementById('edit_customer_email').value = email || '';
    document.getElementById('edit_customer_gender').value = gender || '';
    
    const modal = new bootstrap.Modal(document.getElementById('editCustomerModal'));
    modal.show();
}

// Xử lý submit form sửa khách hàng
document.getElementById('editCustomerForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('<?= BASE_URL ?>admin/bookings/update-customer', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Cập nhật thông tin khách hàng thành công!');
            const modal = bootstrap.Modal.getInstance(document.getElementById('editCustomerModal'));
            modal.hide();
            location.reload();
        } else {
            alert('Lỗi: ' + (data.message || 'Không thể cập nhật thông tin khách hàng.'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Lỗi khi cập nhật thông tin khách hàng. Vui lòng thử lại.');
    });
});

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

