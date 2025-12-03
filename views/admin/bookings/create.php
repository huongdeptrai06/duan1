<?php
ob_start();
$formData = $formData ?? [];
?>
<div class="row">
    <div class="col-12 col-lg-11 col-xl-11 mx-auto">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title mb-0">
                    <i class="bi bi-plus-circle me-2"></i>Tạo booking mới
                </h3>
            </div>
            <div class="card-body p-4 p-lg-5">
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
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="tour_id" class="form-label fw-semibold">Tour <span class="text-danger">*</span></label>
                            <select class="form-select" id="tour_id" name="tour_id" required>
                                <option value="">-- Chọn tour --</option>
                                <?php foreach ($tours as $tour): ?>
                                    <option value="<?= $tour['id'] ?>" <?= (isset($formData['tour_id']) && $formData['tour_id'] == $tour['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($tour['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label fw-semibold">Trạng thái</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">-- Chọn trạng thái --</option>
                                <?php foreach ($statuses as $status): ?>
                                    <option value="<?= $status['id'] ?>" <?= (isset($formData['status']) && $formData['status'] == $status['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($status['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="assigned_guide_id" class="form-label fw-semibold">Hướng dẫn viên</label>
                            <select class="form-select" id="assigned_guide_id" name="assigned_guide_id">
                                <option value="">-- Chọn hướng dẫn viên --</option>
                                <?php foreach ($guides as $guide): ?>
                                    <option value="<?= $guide['id'] ?>" <?= (isset($formData['assigned_guide_id']) && $formData['assigned_guide_id'] == $guide['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($guide['full_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="start_date" class="form-label fw-semibold">Ngày khởi hành <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="start_date" name="start_date" 
                                   value="<?= htmlspecialchars($formData['start_date'] ?? '') ?>" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="end_date" class="form-label fw-semibold">Ngày kết thúc</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" 
                                   value="<?= htmlspecialchars($formData['end_date'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="schedule_detail" class="form-label fw-semibold">Chi tiết lịch trình</label>
                        <textarea class="form-control" id="schedule_detail" name="schedule_detail" rows="5"
                                  placeholder="Nhập chi tiết lịch trình tour..."><?= htmlspecialchars($formData['schedule_detail'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="service_detail" class="form-label fw-semibold">Chi tiết dịch vụ</label>
                        <textarea class="form-control" id="service_detail" name="service_detail" rows="5"
                                  placeholder="Nhập chi tiết dịch vụ..."><?= htmlspecialchars($formData['service_detail'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-4">
                        <label for="notes" class="form-label fw-semibold">Ghi chú</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"
                                  placeholder="Ghi chú về booking..."><?= htmlspecialchars($formData['notes'] ?? '') ?></textarea>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="<?= BASE_URL ?>admin/bookings" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i>Quay lại
                        </a>
                        <button type="submit" class="btn btn-primary">
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

