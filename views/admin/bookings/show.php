<?php
ob_start();
$successMessage = $_GET['success'] ?? null;
$errorMessage = $_GET['error'] ?? null;
?>
<div class="row">
    <div class="col-12">
        <?php if ($successMessage): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle-fill me-2"></i>
                <?= htmlspecialchars($successMessage) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($errorMessage): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-circle-fill me-2"></i>
                <?= htmlspecialchars($errorMessage) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">
                    <i class="bi bi-calendar-check me-2"></i>Chi tiết booking #<?= $booking['id'] ?>
                </h3>
                <div class="d-flex gap-2">
                    <a href="<?= BASE_URL ?>admin/bookings/edit&id=<?= $booking['id'] ?>" class="btn btn-light btn-sm">
                        <i class="bi bi-pencil-square me-1"></i>Chỉnh sửa
                    </a>
                    <a href="<?= BASE_URL ?>admin/bookings" class="btn btn-light btn-sm">
                        <i class="bi bi-arrow-left me-1"></i>Quay lại
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <dl class="row mb-0">
                            <dt class="col-sm-4">Tour</dt>
                            <dd class="col-sm-8">
                                <strong><?= htmlspecialchars($booking['tour_name'] ?? 'N/A') ?></strong>
                            </dd>

                            <dt class="col-sm-4">Khách hàng</dt>
                            <dd class="col-sm-8">
                                <?= htmlspecialchars($booking['created_by_name'] ?? 'N/A') ?>
                                <?php if ($booking['created_by_email']): ?>
                                    <br><small class="text-muted"><?= htmlspecialchars($booking['created_by_email']) ?></small>
                                <?php endif; ?>
                            </dd>

                            <dt class="col-sm-4">Hướng dẫn viên</dt>
                            <dd class="col-sm-8">
                                <?php if ($booking['guide_name']): ?>
                                    <?= htmlspecialchars($booking['guide_name']) ?>
                                    <?php if ($booking['guide_contact']): ?>
                                        <br><small class="text-muted"><?= htmlspecialchars($booking['guide_contact']) ?></small>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">Chưa phân bổ</span>
                                <?php endif; ?>
                            </dd>

                            <dt class="col-sm-4">Trạng thái</dt>
                            <dd class="col-sm-8">
                                <?php if ($booking['status_name']): ?>
                                    <span class="badge bg-info fs-6"><?= htmlspecialchars($booking['status_name']) ?></span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Chưa có</span>
                                <?php endif; ?>
                            </dd>
                        </dl>
                    </div>
                    <div class="col-md-6">
                        <dl class="row mb-0">
                            <dt class="col-sm-4">Ngày khởi hành</dt>
                            <dd class="col-sm-8">
                                <?php if ($booking['start_date']): ?>
                                    <strong><?= date('d/m/Y', strtotime($booking['start_date'])) ?></strong>
                                <?php else: ?>
                                    <span class="text-muted">Chưa có</span>
                                <?php endif; ?>
                            </dd>

                            <dt class="col-sm-4">Ngày kết thúc</dt>
                            <dd class="col-sm-8">
                                <?php if ($booking['end_date']): ?>
                                    <strong><?= date('d/m/Y', strtotime($booking['end_date'])) ?></strong>
                                <?php else: ?>
                                    <span class="text-muted">Chưa có</span>
                                <?php endif; ?>
                            </dd>

                            <dt class="col-sm-4">Ngày tạo</dt>
                            <dd class="col-sm-8"><?= date('d/m/Y H:i', strtotime($booking['created_at'] ?? 'now')) ?></dd>

                            <dt class="col-sm-4">Cập nhật lần cuối</dt>
                            <dd class="col-sm-8"><?= date('d/m/Y H:i', strtotime($booking['updated_at'] ?? 'now')) ?></dd>
                        </dl>
                    </div>
                </div>

                <?php if ($booking['schedule_detail']): ?>
                    <hr>
                    <h5 class="mb-3"><i class="bi bi-calendar3 me-2"></i>Chi tiết lịch trình</h5>
                    <div class="bg-light p-3 rounded">
                        <?= nl2br(htmlspecialchars($booking['schedule_detail'])) ?>
                    </div>
                <?php endif; ?>

                <?php if ($booking['service_detail']): ?>
                    <hr>
                    <h5 class="mb-3"><i class="bi bi-list-check me-2"></i>Chi tiết dịch vụ</h5>
                    <div class="bg-light p-3 rounded">
                        <?= nl2br(htmlspecialchars($booking['service_detail'])) ?>
                    </div>
                <?php endif; ?>

                <?php if ($booking['diary']): ?>
                    <hr>
                    <h5 class="mb-3"><i class="bi bi-journal-text me-2"></i>Nhật ký tour</h5>
                    <div class="bg-light p-3 rounded">
                        <?= nl2br(htmlspecialchars($booking['diary'])) ?>
                    </div>
                <?php endif; ?>

                <?php if ($booking['notes']): ?>
                    <hr>
                    <h5 class="mb-3"><i class="bi bi-sticky me-2"></i>Ghi chú</h5>
                    <div class="bg-light p-3 rounded">
                        <?= nl2br(htmlspecialchars($booking['notes'])) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Thay đổi trạng thái -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-warning text-dark">
                <h5 class="card-title mb-0">
                    <i class="bi bi-arrow-repeat me-2"></i>Thay đổi trạng thái
                </h5>
            </div>
            <div class="card-body">
                <form action="<?= BASE_URL ?>admin/bookings/change-status" method="post">
                    <input type="hidden" name="id" value="<?= $booking['id'] ?>">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Trạng thái mới</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="">-- Chọn trạng thái --</option>
                                <?php foreach ($statuses as $status): ?>
                                    <option value="<?= $status['id'] ?>" <?= ($booking['status'] == $status['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($status['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="status_note" class="form-label">Ghi chú</label>
                            <input type="text" class="form-control" id="status_note" name="note" 
                                   placeholder="Ghi chú về việc thay đổi trạng thái">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-check-circle me-1"></i>Cập nhật trạng thái
                    </button>
                </form>
            </div>
        </div>

        <!-- Phân bổ hướng dẫn viên -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="card-title mb-0">
                    <i class="bi bi-person-check me-2"></i>Phân bổ hướng dẫn viên
                </h5>
            </div>
            <div class="card-body">
                <form action="<?= BASE_URL ?>admin/bookings/assign-guide" method="post">
                    <input type="hidden" name="id" value="<?= $booking['id'] ?>">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="guide_id" class="form-label">Hướng dẫn viên</label>
                            <select class="form-select" id="guide_id" name="guide_id">
                                <option value="">-- Bỏ phân bổ --</option>
                                <?php foreach ($guides as $guide): ?>
                                    <option value="<?= $guide['id'] ?>" <?= ($booking['assigned_guide_id'] == $guide['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($guide['full_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end mb-3">
                            <button type="submit" class="btn btn-success w-100">
                                <i class="bi bi-check-circle me-1"></i>Cập nhật
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Thêm ghi chú -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">
                    <i class="bi bi-sticky-fill me-2"></i>Thêm ghi chú
                </h5>
            </div>
            <div class="card-body">
                <form action="<?= BASE_URL ?>admin/bookings/add-note" method="post">
                    <input type="hidden" name="id" value="<?= $booking['id'] ?>">
                    <div class="mb-3">
                        <textarea class="form-control" name="note" rows="3" placeholder="Nhập ghi chú mới..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-1"></i>Thêm ghi chú
                    </button>
                </form>
            </div>
        </div>

        <!-- Phản hồi, đánh giá, sự cố -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-danger text-white">
                <h5 class="card-title mb-0">
                    <i class="bi bi-chat-dots me-2"></i>Phản hồi / Đánh giá / Sự cố
                </h5>
            </div>
            <div class="card-body">
                <form action="<?= BASE_URL ?>admin/bookings/add-feedback" method="post">
                    <input type="hidden" name="id" value="<?= $booking['id'] ?>">
                    <div class="mb-3">
                        <label for="feedback_type" class="form-label">Loại</label>
                        <select class="form-select" id="feedback_type" name="feedback_type">
                            <option value="feedback">Phản hồi</option>
                            <option value="review">Đánh giá</option>
                            <option value="incident">Sự cố</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <textarea class="form-control" name="feedback" rows="4" placeholder="Nhập nội dung phản hồi, đánh giá hoặc mô tả sự cố..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-send me-1"></i>Gửi phản hồi
                    </button>
                </form>
            </div>
        </div>

        <!-- Lịch sử thay đổi trạng thái -->
        <?php if (!empty($statusLogs)): ?>
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-clock-history me-2"></i>Lịch sử thay đổi trạng thái
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Thời gian</th>
                                    <th>Trạng thái cũ</th>
                                    <th>Trạng thái mới</th>
                                    <th>Người thay đổi</th>
                                    <th>Ghi chú</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($statusLogs as $log): ?>
                                    <tr>
                                        <td><?= date('d/m/Y H:i', strtotime($log['changed_at'] ?? 'now')) ?></td>
                                        <td>
                                            <?php if ($log['old_status_name']): ?>
                                                <span class="badge bg-secondary"><?= htmlspecialchars($log['old_status_name']) ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($log['new_status_name']): ?>
                                                <span class="badge bg-info"><?= htmlspecialchars($log['new_status_name']) ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($log['changed_by_name'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($log['note'] ?? '-') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php
$content = ob_get_clean();

view('layouts.AdminLayout', [
    'title' => $title ?? 'Chi tiết booking',
    'pageTitle' => 'Chi tiết booking #' . $booking['id'],
    'content' => $content,
    'breadcrumb' => [
        ['label' => 'Trang chủ', 'url' => BASE_URL . 'home'],
        ['label' => 'Booking', 'url' => BASE_URL . 'admin/bookings'],
        ['label' => 'Chi tiết', 'url' => BASE_URL . 'admin/bookings/show&id=' . $booking['id'], 'active' => true],
    ],
]);
?>

