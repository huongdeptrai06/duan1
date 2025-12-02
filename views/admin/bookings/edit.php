<?php
ob_start();
?>
<div class="row justify-content-center">
    <div class="col-12 col-lg-10">
        <div class="card shadow-sm">
            <div class="card-header bg-warning text-dark">
                <h3 class="card-title mb-0">
                    <i class="bi bi-pencil-square me-2"></i>Chỉnh sửa booking
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

                <form action="<?= BASE_URL ?>admin/bookings/update" method="post" novalidate>
                    <input type="hidden" name="id" value="<?= $booking['id'] ?>">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="tour_id" class="form-label fw-semibold">Tour <span class="text-danger">*</span></label>
                            <select class="form-select" id="tour_id" name="tour_id" required>
                                <option value="">-- Chọn tour --</option>
                                <?php foreach ($tours as $tour): ?>
                                    <option value="<?= $tour['id'] ?>" <?= ($booking['tour_id'] == $tour['id']) ? 'selected' : '' ?>>
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
                                    <option value="<?= $status['id'] ?>" <?= ($booking['status'] == $status['id']) ? 'selected' : '' ?>>
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
                                    <option value="<?= $guide['id'] ?>" <?= ($booking['assigned_guide_id'] == $guide['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($guide['full_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="start_date" class="form-label fw-semibold">Ngày khởi hành <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="start_date" name="start_date" 
                                   value="<?= htmlspecialchars($booking['start_date'] ?? '') ?>" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="end_date" class="form-label fw-semibold">Ngày kết thúc</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" 
                                   value="<?= htmlspecialchars($booking['end_date'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="schedule_detail" class="form-label fw-semibold">Chi tiết lịch trình</label>
                        <textarea class="form-control" id="schedule_detail" name="schedule_detail" rows="5"
                                  placeholder="Nhập chi tiết lịch trình tour..."><?= htmlspecialchars($booking['schedule_detail'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="service_detail" class="form-label fw-semibold">Chi tiết dịch vụ</label>
                        <textarea class="form-control" id="service_detail" name="service_detail" rows="5"
                                  placeholder="Nhập chi tiết dịch vụ..."><?= htmlspecialchars($booking['service_detail'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="diary" class="form-label fw-semibold">Nhật ký tour</label>
                        <textarea class="form-control" id="diary" name="diary" rows="5"
                                  placeholder="Nhật ký tour, phản hồi, đánh giá, sự cố..."><?= htmlspecialchars($booking['diary'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-4">
                        <label for="notes" class="form-label fw-semibold">Ghi chú</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"
                                  placeholder="Ghi chú về booking..."><?= htmlspecialchars($booking['notes'] ?? '') ?></textarea>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="<?= BASE_URL ?>admin/bookings/show&id=<?= $booking['id'] ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i>Quay lại
                        </a>
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-save me-1"></i>Cập nhật booking
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
    'title' => $title ?? 'Chỉnh sửa booking',
    'pageTitle' => 'Chỉnh sửa booking',
    'content' => $content,
    'breadcrumb' => [
        ['label' => 'Trang chủ', 'url' => BASE_URL . 'home'],
        ['label' => 'Booking', 'url' => BASE_URL . 'admin/bookings'],
        ['label' => 'Chỉnh sửa', 'url' => BASE_URL . 'admin/bookings/edit&id=' . $booking['id'], 'active' => true],
    ],
]);
?>

