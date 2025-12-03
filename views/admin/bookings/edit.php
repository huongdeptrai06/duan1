<?php
ob_start();
?>
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-warning text-dark py-3">
                <h3 class="card-title mb-0 d-flex align-items-center">
                    <i class="bi bi-pencil-square me-2 fs-5"></i>Chỉnh sửa booking
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

                <form action="<?= BASE_URL ?>admin/bookings/update" method="post" novalidate>
                    <input type="hidden" name="id" value="<?= $booking['id'] ?>">
                    
                    <!-- Thông tin cơ bản -->
                    <div class="mb-4">
                        <h5 class="text-primary mb-3 d-flex align-items-center">
                            <i class="bi bi-info-circle me-2"></i>Thông tin cơ bản
                        </h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="tour_id" class="form-label fw-semibold">
                                    <i class="bi bi-airplane-engines me-1 text-primary"></i>Tour <span class="text-danger">*</span>
                                </label>
                                <select class="form-select form-select-lg" id="tour_id" name="tour_id" required>
                                    <option value="">-- Chọn tour --</option>
                                    <?php foreach ($tours as $tour): ?>
                                        <option value="<?= $tour['id'] ?>" <?= ($booking['tour_id'] == $tour['id']) ? 'selected' : '' ?>>
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
                                        <option value="<?= $status['id'] ?>" <?= ($booking['status'] == $status['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($status['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Thông tin hướng dẫn viên và ngày tháng -->
                    <div class="mb-4">
                        <h5 class="text-primary mb-3 d-flex align-items-center">
                            <i class="bi bi-calendar-event me-2"></i>Thông tin lịch trình
                        </h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="assigned_guide_id" class="form-label fw-semibold">
                                    <i class="bi bi-person-badge me-1 text-primary"></i>Hướng dẫn viên
                                </label>
                                <select class="form-select form-select-lg" id="assigned_guide_id" name="assigned_guide_id">
                                    <option value="">-- Chọn hướng dẫn viên --</option>
                                    <?php foreach ($guides as $guide): ?>
                                        <option value="<?= $guide['id'] ?>" <?= ($booking['assigned_guide_id'] == $guide['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($guide['full_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label for="start_date" class="form-label fw-semibold">
                                    <i class="bi bi-calendar-event me-1 text-primary"></i>Ngày khởi hành <span class="text-danger">*</span>
                                </label>
                                <input type="date" class="form-control form-control-lg" id="start_date" name="start_date" 
                                       value="<?= htmlspecialchars($booking['start_date'] ?? '') ?>" required>
                            </div>

                            <div class="col-md-3">
                                <label for="end_date" class="form-label fw-semibold">
                                    <i class="bi bi-calendar-check me-1 text-primary"></i>Ngày kết thúc
                                </label>
                                <input type="date" class="form-control form-control-lg" id="end_date" name="end_date" 
                                       value="<?= htmlspecialchars($booking['end_date'] ?? '') ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Chi tiết tour -->
                    <div class="mb-4">
                        <h5 class="text-primary mb-3 d-flex align-items-center">
                            <i class="bi bi-file-text me-2"></i>Chi tiết tour
                        </h5>
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="schedule_detail" class="form-label fw-semibold">
                                    <i class="bi bi-calendar3 me-1 text-primary"></i>Chi tiết lịch trình
                                </label>
                                <textarea class="form-control" id="schedule_detail" name="schedule_detail" rows="6"
                                          placeholder="Nhập chi tiết lịch trình tour..."><?= htmlspecialchars($booking['schedule_detail'] ?? '') ?></textarea>
                                <small class="form-text text-muted">Mỗi ngày trên một dòng, ví dụ: Ngày 1: ...</small>
                            </div>

                            <div class="col-12">
                                <label for="service_detail" class="form-label fw-semibold">
                                    <i class="bi bi-list-check me-1 text-primary"></i>Chi tiết dịch vụ
                                </label>
                                <textarea class="form-control" id="service_detail" name="service_detail" rows="6"
                                          placeholder="Nhập chi tiết dịch vụ..."><?= htmlspecialchars($booking['service_detail'] ?? '') ?></textarea>
                                <small class="form-text text-muted">Liệt kê các dịch vụ được bao gồm trong tour</small>
                            </div>
                        </div>
                    </div>

                    <!-- Ghi chú và nhật ký -->
                    <div class="mb-4">
                        <h5 class="text-primary mb-3 d-flex align-items-center">
                            <i class="bi bi-journal me-2"></i>Ghi chú và nhật ký
                        </h5>
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="diary" class="form-label fw-semibold">
                                    <i class="bi bi-journal-text me-1 text-primary"></i>Nhật ký tour
                                </label>
                                <textarea class="form-control" id="diary" name="diary" rows="6"
                                          placeholder="Nhật ký tour, phản hồi, đánh giá, sự cố..."><?= htmlspecialchars($booking['diary'] ?? '') ?></textarea>
                                <small class="form-text text-muted">Format: [Loại - Ngày/giờ]: Nội dung, ví dụ: [Đánh giá - 03/12/2025 10:40]: ...</small>
                            </div>

                            <div class="col-12">
                                <label for="notes" class="form-label fw-semibold">
                                    <i class="bi bi-sticky me-1 text-primary"></i>Ghi chú
                                </label>
                                <textarea class="form-control" id="notes" name="notes" rows="4"
                                          placeholder="Ghi chú về booking..."><?= htmlspecialchars($booking['notes'] ?? '') ?></textarea>
                                <small class="form-text text-muted">Ghi chú nội bộ về booking này</small>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-3 mt-4 pt-3 border-top">
                        <a href="<?= BASE_URL ?>admin/bookings/show&id=<?= $booking['id'] ?>" class="btn btn-outline-secondary btn-lg">
                            <i class="bi bi-arrow-left me-1"></i>Quay lại
                        </a>
                        <button type="submit" class="btn btn-warning text-white btn-lg px-4">
                            <i class="bi bi-save me-1"></i>Lưu thay đổi
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

