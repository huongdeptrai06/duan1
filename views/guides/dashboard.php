<?php
ob_start();
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

        <!-- Xin nghỉ -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-warning text-dark">
                <h5 class="card-title mb-0">
                    <i class="bi bi-calendar-x me-2"></i>Xin nghỉ
                </h5>
            </div>
            <div class="card-body">
                <form action="<?= BASE_URL ?>guides/request-leave" method="post">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="start_date" class="form-label fw-semibold">
                                <i class="bi bi-calendar-event me-1 text-primary"></i>Ngày bắt đầu <span class="text-danger">*</span>
                            </label>
                            <input type="date" class="form-control form-control-lg" id="start_date" name="start_date" 
                                   min="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label for="end_date" class="form-label fw-semibold">
                                <i class="bi bi-calendar-check me-1 text-primary"></i>Ngày kết thúc <span class="text-danger">*</span>
                            </label>
                            <input type="date" class="form-control form-control-lg" id="end_date" name="end_date" 
                                   min="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label for="reason" class="form-label fw-semibold">
                                <i class="bi bi-file-text me-1 text-primary"></i>Lý do <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control form-control-lg" id="reason" name="reason" rows="2" 
                                      placeholder="Nhập lý do xin nghỉ..." required></textarea>
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-warning btn-lg">
                            <i class="bi bi-send me-1"></i>Gửi đơn xin nghỉ
                        </button>
                    </div>
                </form>

                <!-- Danh sách đơn xin nghỉ -->
                <?php if (!empty($leaveRequests)): ?>
                <hr class="my-4">
                <h6 class="mb-3">Lịch sử đơn xin nghỉ</h6>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Ngày bắt đầu</th>
                                <th>Ngày kết thúc</th>
                                <th>Lý do</th>
                                <th>Trạng thái</th>
                                <th>Ngày gửi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($leaveRequests as $request): ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($request['start_date'])) ?></td>
                                <td><?= date('d/m/Y', strtotime($request['end_date'])) ?></td>
                                <td><?= htmlspecialchars($request['reason']) ?></td>
                                <td>
                                    <?php
                                    $statusClass = match($request['status']) {
                                        'approved' => 'success',
                                        'rejected' => 'danger',
                                        default => 'warning'
                                    };
                                    $statusText = match($request['status']) {
                                        'approved' => 'Đã duyệt',
                                        'rejected' => 'Từ chối',
                                        default => 'Chờ duyệt'
                                    };
                                    ?>
                                    <span class="badge bg-<?= $statusClass ?>"><?= $statusText ?></span>
                                </td>
                                <td><?= date('d/m/Y H:i', strtotime($request['created_at'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Ghi chú -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="card-title mb-0">
                    <i class="bi bi-sticky me-2"></i>Ghi chú
                </h5>
            </div>
            <div class="card-body">
                <form action="<?= BASE_URL ?>guides/add-note" method="post">
                    <div class="mb-3">
                        <label for="note" class="form-label fw-semibold">
                            <i class="bi bi-pencil me-1 text-primary"></i>Thêm ghi chú mới
                        </label>
                        <textarea class="form-control form-control-lg" id="note" name="note" rows="3" 
                                  placeholder="Nhập ghi chú của bạn..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-info text-white btn-lg">
                        <i class="bi bi-plus-circle me-1"></i>Thêm ghi chú
                    </button>
                </form>

                <!-- Danh sách ghi chú -->
                <?php if (!empty($notes)): ?>
                <hr class="my-4">
                <h6 class="mb-3">Ghi chú của tôi</h6>
                <div class="list-group">
                    <?php foreach ($notes as $note): ?>
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <p class="mb-1"><?= nl2br(htmlspecialchars($note['note'])) ?></p>
                                <small class="text-muted">
                                    <i class="bi bi-clock me-1"></i><?= date('d/m/Y H:i', strtotime($note['created_at'])) ?>
                                </small>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="alert alert-info mt-3">
                    <i class="bi bi-info-circle me-2"></i>Chưa có ghi chú nào.
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tour được phân bổ -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">
                    <i class="bi bi-airplane-engines me-2"></i>Tour được phân bổ
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($assignedTours)): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Tour</th>
                                <th>Khách hàng</th>
                                <th>Ngày khởi hành</th>
                                <th>Ngày kết thúc</th>
                                <th>Trạng thái</th>
                                <th>Xác nhận</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            foreach ($assignedTours as $tour): 
                                $isConfirmed = isset($confirmationsMap[$tour['id']]) && $confirmationsMap[$tour['id']]['confirmed'] == 1;
                            ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($tour['tour_name'] ?? 'N/A') ?></strong>
                                    <?php if ($tour['tour_price']): ?>
                                        <br><small class="text-muted"><?= number_format($tour['tour_price'], 0, ',', '.') ?> ₫</small>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($tour['customer_name'] ?? 'N/A') ?></td>
                                <td>
                                    <?php if ($tour['start_date']): ?>
                                        <?= date('d/m/Y', strtotime($tour['start_date'])) ?>
                                    <?php else: ?>
                                        <span class="text-muted">Chưa có</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($tour['end_date']): ?>
                                        <?= date('d/m/Y', strtotime($tour['end_date'])) ?>
                                    <?php else: ?>
                                        <span class="text-muted">Chưa có</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($tour['status_name']): ?>
                                        <span class="badge bg-info"><?= htmlspecialchars($tour['status_name']) ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Chưa có</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($isConfirmed): ?>
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle me-1"></i>Đã xác nhận
                                        </span>
                                        <?php if (isset($confirmationsMap[$tour['id']]['confirmed_at'])): ?>
                                            <br><small class="text-muted"><?= date('d/m/Y H:i', strtotime($confirmationsMap[$tour['id']]['confirmed_at'])) ?></small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge bg-warning">Chưa xác nhận</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form action="<?= BASE_URL ?>guides/confirm-tour" method="post" class="d-inline">
                                        <input type="hidden" name="booking_id" value="<?= $tour['id'] ?>">
                                        <input type="hidden" name="confirmed" value="<?= $isConfirmed ? '0' : '1' ?>">
                                        <button type="submit" class="btn btn-sm <?= $isConfirmed ? 'btn-outline-danger' : 'btn-success' ?>">
                                            <i class="bi <?= $isConfirmed ? 'bi-x-circle' : 'bi-check-circle' ?> me-1"></i>
                                            <?= $isConfirmed ? 'Hủy xác nhận' : 'Xác nhận' ?>
                                        </button>
                                    </form>
                                    <a href="<?= BASE_URL ?>admin/bookings/show&id=<?= $tour['id'] ?>" class="btn btn-sm btn-outline-info">
                                        <i class="bi bi-eye me-1"></i>Chi tiết
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>Chưa có tour nào được phân bổ cho bạn.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();

view('layouts.AdminLayout', [
    'title' => $title ?? 'Dashboard HDV',
    'pageTitle' => 'Dashboard HDV',
    'content' => $content,
    'breadcrumb' => [
        ['label' => 'Trang chủ', 'url' => BASE_URL . 'home'],
        ['label' => 'Dashboard HDV', 'url' => BASE_URL . 'guides/dashboard', 'active' => true],
    ],
]);
?>

