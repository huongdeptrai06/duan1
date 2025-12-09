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
                        <button type="button" class="btn btn-success" onclick="showAddCustomerModal()">
                            <i class="bi bi-plus-circle me-1"></i> Thêm khách hàng
                        </button>
                        <button type="button" class="btn btn-info" onclick="showImportExcelModal()">
                            <i class="bi bi-file-earmark-excel me-1"></i> Import từ Excel
                        </button>
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
                                            <div class="d-flex gap-2 justify-content-end">
                                            <a href="<?= BASE_URL ?>admin/bookings/customer-detail&booking_id=<?= $customer['booking_id'] ?>" class="btn-action btn-action-view" title="Chi tiết">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                                <button type="button" 
                                                        class="btn-action btn-action-edit" 
                                                        onclick="editCustomer(<?= $customer['id'] ?>)"
                                                        title="Sửa">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button type="button" 
                                                        class="btn-action btn-action-delete" 
                                                        onclick="deleteCustomerFromList(<?= $customer['id'] ?>, <?= $customer['booking_id'] ?>, '<?= htmlspecialchars($customer['name'] ?? '', ENT_QUOTES) ?>')"
                                                        title="Xóa">
                                                    <i class="bi bi-trash"></i>
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

<!-- Modal thêm khách hàng -->
<div class="modal fade" id="addCustomerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-person-plus me-2"></i>Thêm khách hàng
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addCustomerForm" onsubmit="submitAddCustomer(event)">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Chọn Booking <span class="text-danger">*</span></label>
                        <select class="form-select" name="booking_id" id="bookingSelect" required>
                            <option value="">-- Chọn booking --</option>
                            <?php if (!empty($bookings)): ?>
                                <?php foreach ($bookings as $booking): ?>
                                    <option value="<?= $booking['id'] ?>">
                                        #<?= $booking['id'] ?> - <?= htmlspecialchars($booking['tour_name'] ?? 'N/A') ?>
                                        <?php if (!empty($booking['start_date'])): ?>
                                            (<?= date('d/m/Y', strtotime($booking['start_date'])) ?>)
                                        <?php endif; ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tên khách hàng <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Số điện thoại</label>
                        <input type="tel" class="form-control" name="phone">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Giới tính</label>
                        <select class="form-select" name="gender">
                            <option value="">-- Chọn --</option>
                            <option value="male">Nam</option>
                            <option value="female">Nữ</option>
                            <option value="other">Khác</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check me-1"></i>Thêm khách hàng
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal sửa khách hàng -->
<div class="modal fade" id="editCustomerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-pencil me-2"></i>Sửa khách hàng
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editCustomerForm" onsubmit="submitEditCustomer(event)">
                <div class="modal-body">
                    <input type="hidden" name="customer_id" id="edit_customer_id">
                    <div class="mb-3">
                        <label class="form-label">Tên khách hàng <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" id="edit_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Số điện thoại</label>
                        <input type="tel" class="form-control" name="phone" id="edit_phone">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Giới tính</label>
                        <select class="form-select" name="gender" id="edit_gender">
                            <option value="">-- Chọn --</option>
                            <option value="male">Nam</option>
                            <option value="female">Nữ</option>
                            <option value="other">Khác</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" id="edit_email">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-check me-1"></i>Cập nhật
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Import Excel -->
<div class="modal fade" id="importExcelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-file-earmark-excel me-2"></i>Import khách hàng từ Excel
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="importExcelForm" onsubmit="submitImportExcel(event)">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Chọn Booking <span class="text-danger">*</span></label>
                        <select class="form-select" name="booking_id" required>
                            <option value="">-- Chọn booking --</option>
                            <?php if (!empty($bookings)): ?>
                                <?php foreach ($bookings as $booking): ?>
                                    <option value="<?= $booking['id'] ?>">
                                        #<?= $booking['id'] ?> - <?= htmlspecialchars($booking['tour_name'] ?? 'N/A') ?>
                                        <?php if (!empty($booking['start_date'])): ?>
                                            (<?= date('d/m/Y', strtotime($booking['start_date'])) ?>)
                                        <?php endif; ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Chọn file Excel <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" name="excel_file" accept=".xlsx,.xls,.csv" required>
                        <div class="form-text">
                            Định dạng: .xlsx, .xls hoặc .csv<br>
                            Cột: Tên | Số điện thoại | Giới tính | Email
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-info">
                        <i class="bi bi-upload me-1"></i>Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.btn-action {
    width: 36px;
    height: 36px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 2px solid;
    border-radius: 8px;
    background: transparent;
    transition: all 0.3s ease;
    cursor: pointer;
    text-decoration: none;
}

.btn-action i {
    font-size: 16px;
    line-height: 1;
}

.btn-action-view {
    color: #0dcaf0;
    border-color: #0dcaf0;
}

.btn-action-view:hover {
    background-color: #0dcaf0;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(13, 202, 240, 0.3);
}

.btn-action-edit {
    color: #0d6efd;
    border-color: #0d6efd;
    background-color: rgba(13, 110, 253, 0.1);
}

.btn-action-edit:hover {
    background-color: #0d6efd;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(13, 110, 253, 0.3);
}

.btn-action-delete {
    color: #dc3545;
    border-color: #dc3545;
}

.btn-action-delete:hover {
    background-color: #dc3545;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3);
}

.btn-action:active {
    transform: translateY(0);
}
</style>

<script>
function showAddCustomerModal() {
    const modal = new bootstrap.Modal(document.getElementById('addCustomerModal'));
    modal.show();
}

function showImportExcelModal() {
    const modal = new bootstrap.Modal(document.getElementById('importExcelModal'));
    modal.show();
}

function submitAddCustomer(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    
    fetch('<?= BASE_URL ?>admin/bookings/add-customer', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Lỗi: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Có lỗi xảy ra khi thêm khách hàng');
    });
}

function submitImportExcel(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    
    const submitBtn = form.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Đang import...';
    
    fetch('<?= BASE_URL ?>admin/bookings/import-customers-to-booking', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Lỗi: ' + data.message);
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-upload me-1"></i>Import';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Có lỗi xảy ra khi import');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="bi bi-upload me-1"></i>Import';
    });
}

function editCustomer(customerId) {
    // Lấy thông tin khách hàng
    fetch('<?= BASE_URL ?>admin/bookings/edit-customer&customer_id=' + customerId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const customer = data.customer;
                document.getElementById('edit_customer_id').value = customer.id;
                document.getElementById('edit_name').value = customer.name || '';
                document.getElementById('edit_phone').value = customer.phone || '';
                document.getElementById('edit_gender').value = customer.gender || '';
                document.getElementById('edit_email').value = customer.email || '';
                
                const modal = new bootstrap.Modal(document.getElementById('editCustomerModal'));
                modal.show();
            } else {
                alert('Lỗi: ' + (data.message || 'Không thể lấy thông tin khách hàng.'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Có lỗi xảy ra khi lấy thông tin khách hàng.');
        });
}

function submitEditCustomer(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    
    fetch('<?= BASE_URL ?>admin/bookings/update-customer', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Lỗi: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Có lỗi xảy ra khi cập nhật khách hàng');
    });
}

function deleteCustomerFromList(customerId, bookingId, customerName) {
    if (!confirm('Bạn có chắc chắn muốn xóa cả đoàn (tất cả khách hàng) trong booking này?\n\nLưu ý: Hành động này sẽ xóa tất cả khách hàng trong booking, không chỉ "' + customerName + '".')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('customer_id', customerId);
    formData.append('booking_id', bookingId);
    formData.append('delete_all', '1'); // Xóa cả đoàn
    
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
        alert('Có lỗi xảy ra khi xóa khách hàng. Vui lòng thử lại.');
    });
}
</script>

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

