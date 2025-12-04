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
                <div class="d-flex gap-2 justify-content-end">
                    <?php if (isAdmin()): ?>
                    <a href="<?= BASE_URL ?>admin/bookings/edit&id=<?= $booking['id'] ?>" class="btn btn-light btn-sm">
                        <i class="bi bi-pencil-square me-1"></i>Chỉnh sửa
                    </a>
                    <?php endif; ?>
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
                    <hr class="my-3">
                    <div class="card border-0 shadow-sm mb-2">
                        <div class="card-header bg-white border-bottom py-2">
                            <h6 class="mb-0 d-flex align-items-center">
                                <i class="bi bi-calendar3 me-2 text-primary"></i>Chi tiết lịch trình
                            </h6>
                        </div>
                        <div class="card-body p-3">
                            <div class="text-content" style="white-space: pre-wrap; line-height: 1.6; font-size: 0.95rem;">
                                <?php
                                $schedule = trim($booking['schedule_detail']);
                                // Normalize line breaks
                                $schedule = str_replace(["\r\n", "\r"], "\n", $schedule);
                                // Split by double newlines to create paragraphs
                                $lines = explode("\n\n", $schedule);
                                foreach ($lines as $line) {
                                    $line = trim($line);
                                    if (empty($line)) continue;
                                    // Check if line starts with "Ngày" or number pattern
                                    if (preg_match('/^(Ngày\s+\d+|Ngày\s+\d+:|Day\s+\d+)/i', $line)) {
                                        echo '<div class="mb-2"><strong class="text-primary d-block mb-1">' . htmlspecialchars($line) . '</strong></div>';
                                    } else {
                                        echo '<div class="mb-1 ps-3">' . nl2br(htmlspecialchars($line)) . '</div>';
                                    }
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($booking['service_detail']): ?>
                    <hr class="my-3">
                    <div class="card border-0 shadow-sm mb-2">
                        <div class="card-header bg-white border-bottom py-2">
                            <h6 class="mb-0 d-flex align-items-center">
                                <i class="bi bi-list-check me-2 text-success"></i>Chi tiết dịch vụ
                            </h6>
                        </div>
                        <div class="card-body p-3">
                            <div class="text-content" style="line-height: 1.6; font-size: 0.95rem;">
                                <?php
                                $service = trim($booking['service_detail']);
                                // Normalize line breaks
                                $service = str_replace(["\r\n", "\r"], "\n", $service);
                                // Split by newlines
                                $lines = preg_split('/\n+/', $service);
                                echo '<ul class="list-unstyled mb-0">';
                                foreach ($lines as $line) {
                                    $line = trim($line);
                                    if (empty($line)) continue;
                                    echo '<li class="mb-1 d-flex align-items-start">';
                                    echo '<i class="bi bi-check-circle-fill text-success me-2 mt-1 flex-shrink-0" style="font-size: 0.9rem;"></i>';
                                    echo '<span>' . htmlspecialchars($line) . '</span>';
                                    echo '</li>';
                                }
                                echo '</ul>';
                                ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($booking['diary']): ?>
                    <hr class="my-3">
                    <div class="card border-0 shadow-sm mb-2">
                        <div class="card-header bg-white border-bottom py-2">
                            <h6 class="mb-0 d-flex align-items-center">
                                <i class="bi bi-journal-text me-2 text-warning"></i>Nhật ký tour
                            </h6>
                        </div>
                        <div class="card-body p-3">
                            <div class="text-content" style="line-height: 1.6; font-size: 0.95rem;">
                                <?php
                                $diary = trim($booking['diary']);
                                // Normalize line breaks
                                $diary = str_replace(["\r\n", "\r"], "\n", $diary);
                                // Split by double newlines or pattern like [Đánh giá - date]
                                $entries = preg_split('/\n(?=\[)/', $diary);
                                if (count($entries) === 1) {
                                    // If no pattern found, split by double newlines
                                    $entries = preg_split('/\n\n+/', $diary);
                                }
                                
                                foreach ($entries as $entry) {
                                    $entry = trim($entry);
                                    if (empty($entry)) continue;
                                    
                                    // Check if entry matches pattern [Type - Date]: content
                                    if (preg_match('/^\[([^\]]+)\s*-\s*([^\]]+)\]:\s*(.+)$/s', $entry, $matches)) {
                                        $type = trim($matches[1]);
                                        $date = trim($matches[2]);
                                        $content = trim($matches[3]);
                                        
                                        // Determine badge color based on type
                                        $badgeClass = 'bg-info';
                                        if (stripos($type, 'đánh giá') !== false || stripos($type, 'review') !== false) {
                                            $badgeClass = 'bg-warning text-dark';
                                        } elseif (stripos($type, 'phản hồi') !== false || stripos($type, 'feedback') !== false) {
                                            $badgeClass = 'bg-primary';
                                        } elseif (stripos($type, 'sự cố') !== false || stripos($type, 'incident') !== false) {
                                            $badgeClass = 'bg-danger';
                                        }
                                        
                                        echo '<div class="mb-2 p-2 bg-light rounded border-start border-3 border-primary">';
                                        echo '<div class="d-flex justify-content-between align-items-center mb-1 flex-wrap gap-2">';
                                        echo '<span class="badge ' . $badgeClass . '">' . htmlspecialchars($type) . '</span>';
                                        echo '<small class="text-muted"><i class="bi bi-clock me-1"></i>' . htmlspecialchars($date) . '</small>';
                                        echo '</div>';
                                        echo '<div class="mt-1">' . nl2br(htmlspecialchars($content)) . '</div>';
                                        echo '</div>';
                                    } else {
                                        // Regular entry without pattern
                                        echo '<div class="mb-2 p-2 bg-light rounded">';
                                        echo nl2br(htmlspecialchars($entry));
                                        echo '</div>';
                                    }
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($booking['notes']): ?>
                    <hr class="my-3">
                    <div class="card border-0 shadow-sm mb-2">
                        <div class="card-header bg-white border-bottom py-2">
                            <h6 class="mb-0 d-flex align-items-center">
                                <i class="bi bi-sticky me-2 text-info"></i>Ghi chú
                            </h6>
                        </div>
                        <div class="card-body p-3">
                            <div class="text-content" style="white-space: pre-wrap; line-height: 1.6; font-size: 0.95rem; color: #495057;">
                                <?php
                                $notes = trim($booking['notes']);
                                // Normalize line breaks
                                $notes = str_replace(["\r\n", "\r"], "\n", $notes);
                                // Split by newlines and format
                                $lines = preg_split('/\n+/', $notes);
                                foreach ($lines as $line) {
                                    $line = trim($line);
                                    if (empty($line)) {
                                        echo '<br>';
                                        continue;
                                    }
                                    // Check if line looks like a bullet point or numbered item
                                    if (preg_match('/^[-•*]\s*(.+)$/', $line, $matches)) {
                                        echo '<div class="mb-1 d-flex align-items-start">';
                                        echo '<i class="bi bi-dot me-2 mt-1 text-info"></i>';
                                        echo '<span>' . htmlspecialchars($matches[1]) . '</span>';
                                        echo '</div>';
                                    } elseif (preg_match('/^(\d+[.)])\s*(.+)$/', $line, $matches)) {
                                        echo '<div class="mb-1 d-flex align-items-start">';
                                        echo '<span class="text-info me-2 fw-bold">' . htmlspecialchars($matches[1]) . '</span>';
                                        echo '<span>' . htmlspecialchars($matches[2]) . '</span>';
                                        echo '</div>';
                                    } else {
                                        echo '<div class="mb-1">' . htmlspecialchars($line) . '</div>';
                                    }
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
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

