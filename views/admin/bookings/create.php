<?php
ob_start();
$formData = $formData ?? [];
?>
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white py-3">
                <h3 class="card-title mb-0 d-flex align-items-center">
                    <i class="bi bi-plus-circle me-2 fs-5"></i>Tạo booking mới
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

                <form action="<?= BASE_URL ?>admin/bookings/store" method="post" novalidate>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="tour_id" class="form-label fw-semibold">
                                <i class="bi bi-airplane-engines me-1 text-primary"></i>Tour <span class="text-danger">*</span>
                            </label>
                            <select class="form-select form-select-lg" id="tour_id" name="tour_id" required>
                                <option value="">-- Chọn tour --</option>
                                <?php foreach ($tours as $tour): ?>
                                    <option value="<?= $tour['id'] ?>" <?= (isset($formData['tour_id']) && $formData['tour_id'] == $tour['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($tour['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="status" class="form-label fw-semibold">
                                <i class="bi bi-info-circle me-1 text-primary"></i>Trạng thái
                            </label>
                            <select class="form-select form-select-lg" id="status" name="status">
                                <option value="">-- Chọn trạng thái --</option>
                                <?php foreach ($statuses as $status): ?>
                                    <option value="<?= $status['id'] ?>" <?= (isset($formData['status']) && $formData['status'] == $status['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($status['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                    </div>

                        <div class="col-md-6">
                            <label for="representative_customer_id" class="form-label fw-semibold">
                                <i class="bi bi-person-check me-1 text-primary"></i>Người đại diện <span class="text-danger">*</span>
                            </label>
                            <select class="form-select form-select-lg" id="representative_customer_id" name="representative_customer_id" required>
                                <option value="">-- Chọn người đại diện --</option>
                                <?php if (!empty($customers)): ?>
                                    <?php foreach ($customers as $customer): ?>
                                        <option value="<?= $customer['id'] ?>" <?= (isset($formData['representative_customer_id']) && $formData['representative_customer_id'] == $customer['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($customer['name']) ?> 
                                            <?php if (!empty($customer['phone'])): ?>
                                                - <?= htmlspecialchars($customer['phone']) ?>
                                            <?php endif; ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <small class="form-text text-muted">
                                <i class="bi bi-info-circle me-1"></i>Chọn từ danh sách khách hàng có sẵn
                            </small>
                        </div>

                        <div class="col-md-6">
                            <label for="assigned_guide_id" class="form-label fw-semibold">
                                <i class="bi bi-person-badge me-1 text-primary"></i>Hướng dẫn viên
                            </label>
                            <select class="form-select form-select-lg" id="assigned_guide_id" name="assigned_guide_id">
                                <option value="">-- Chọn hướng dẫn viên --</option>
                                <?php foreach ($guides as $guide): ?>
                                    <option value="<?= $guide['id'] ?>" <?= (isset($formData['assigned_guide_id']) && $formData['assigned_guide_id'] == $guide['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($guide['full_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="start_date" class="form-label fw-semibold">
                                <i class="bi bi-calendar-event me-1 text-primary"></i>Ngày khởi hành <span class="text-danger">*</span>
                            </label>
                            <input type="date" class="form-control form-control-lg" id="start_date" name="start_date" 
                                   value="<?= htmlspecialchars($formData['start_date'] ?? '') ?>" 
                                   min="<?= date('Y-m-d') ?>" required>
                            <small class="form-text text-muted">
                                <i class="bi bi-info-circle me-1"></i>Chỉ có thể chọn ngày trong tương lai
                            </small>
                    </div>

                        <div class="col-md-6">
                            <label for="end_date" class="form-label fw-semibold">
                                <i class="bi bi-calendar-check me-1 text-primary"></i>Ngày kết thúc
                            </label>
                            <input type="date" class="form-control form-control-lg" id="end_date" name="end_date" 
                                   value="<?= htmlspecialchars($formData['end_date'] ?? '') ?>"
                                   min="<?= date('Y-m-d') ?>">
                            <small class="form-text text-muted">
                                <i class="bi bi-info-circle me-1"></i>Chỉ có thể chọn ngày trong tương lai và không được trước ngày khởi hành
                            </small>
                    </div>

                        <div class="col-12">
                            <label for="schedule_detail" class="form-label fw-semibold">
                                <i class="bi bi-calendar3 me-1 text-primary"></i>Chi tiết lịch trình
                            </label>
                            <textarea class="form-control" id="schedule_detail" name="schedule_detail" rows="6"
                                  placeholder="Nhập chi tiết lịch trình tour..."><?= htmlspecialchars($formData['schedule_detail'] ?? '') ?></textarea>
                    </div>

                        <div class="col-12">
                            <label for="service_detail" class="form-label fw-semibold">
                                <i class="bi bi-list-check me-1 text-primary"></i>Chi tiết dịch vụ
                            </label>
                            <textarea class="form-control" id="service_detail" name="service_detail" rows="6"
                                  placeholder="Nhập chi tiết dịch vụ..."><?= htmlspecialchars($formData['service_detail'] ?? '') ?></textarea>
                    </div>

                        <div class="col-12">
                            <label for="notes" class="form-label fw-semibold">
                                <i class="bi bi-sticky me-1 text-primary"></i>Ghi chú
                            </label>
                            <textarea class="form-control" id="notes" name="notes" rows="4"
                                  placeholder="Ghi chú về booking..."><?= htmlspecialchars($formData['notes'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-3 mt-4 pt-3 border-top">
                        <a href="<?= BASE_URL ?>admin/bookings" class="btn btn-outline-secondary btn-lg">
                            <i class="bi bi-arrow-left me-1"></i>Quay lại
                        </a>
                        <button type="submit" class="btn btn-primary btn-lg px-4">
                            <i class="bi bi-save me-1"></i>Tạo booking
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

view('layouts.AdminLayout', [
    'title' => $title ?? 'Tạo booking mới',
    'pageTitle' => 'Tạo booking mới',
    'content' => $content,
    'breadcrumb' => [
        ['label' => 'Trang chủ', 'url' => BASE_URL . 'home'],
        ['label' => 'Booking', 'url' => BASE_URL . 'admin/bookings'],
        ['label' => 'Tạo mới', 'url' => BASE_URL . 'admin/bookings/create', 'active' => true],
    ],
]);
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    
    if (startDateInput && endDateInput) {
        // Hàm cập nhật min của end_date
        function updateEndDateMin() {
            const startDate = startDateInput.value;
            const today = new Date().toISOString().split('T')[0];
            
            // Nếu đã chọn ngày khởi hành, min của end_date là ngày khởi hành
            // Nếu chưa chọn, min của end_date là hôm nay
            if (startDate) {
                endDateInput.min = startDate;
            } else {
                endDateInput.min = today;
            }
            
            // Nếu end_date hiện tại nhỏ hơn min mới, xóa giá trị
            if (endDateInput.value && endDateInput.value < endDateInput.min) {
                endDateInput.value = '';
            }
        }
        
        // Cập nhật khi start_date thay đổi
        startDateInput.addEventListener('change', updateEndDateMin);
        
        // Cập nhật khi trang load (nếu đã có giá trị start_date)
        updateEndDateMin();
        
        // Kiểm tra khi end_date thay đổi
        endDateInput.addEventListener('change', function() {
            const startDate = startDateInput.value;
            const endDate = endDateInput.value;
            
            if (startDate && endDate && endDate < startDate) {
                alert('Ngày kết thúc không được trước ngày khởi hành!');
                endDateInput.value = startDate;
            }
        });
    }
});
</script>

