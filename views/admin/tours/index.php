<?php
ob_start();
?>
<div class="row">
    <div class="col-12">
        <!-- Danh sách tour -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white border-0">
                <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-2">
                <div>
                    <h3 class="card-title mb-0">
                        <i class="bi bi-airplane-engines me-2"></i>
                        <?= $isGuide ? 'Danh sách tour của tôi' : 'Danh sách tour' ?>
                    </h3>
                </div>
                <?php if (isAdmin()): ?>
                <div class="d-flex gap-2">
                    <a href="<?= BASE_URL ?>admin/tours/create" class="btn btn-primary">
                            <i class="bi bi-plus-lg me-1"></i> Thêm tour mới
                    </a>
                </div>
                <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <?php if (!empty($_GET['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <?php
                        $successMsg = match($_GET['success']) {
                            '1' => 'Tour đã được thêm thành công!',
                            'updated' => 'Tour đã được cập nhật thành công!',
                            'deleted' => 'Tour đã được xóa thành công!',
                            default => 'Thao tác thành công!',
                        };
                        echo htmlspecialchars($successMsg);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (!empty($_GET['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="bi bi-exclamation-circle-fill me-2"></i>
                        <?php
                        $errorMsg = match($_GET['error']) {
                            'db' => 'Không thể kết nối cơ sở dữ liệu.',
                            'notfound' => 'Tour không tồn tại.',
                            'delete' => 'Không thể xóa tour. Vui lòng thử lại.',
                            default => 'Có lỗi xảy ra.',
                        };
                        echo htmlspecialchars($errorMsg);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0 ps-3">
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if (empty($tours)): ?>
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        Chưa có tour nào.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Tên tour</th>
                                    <th>Danh mục</th>
                                    <th>Giá</th>
                                    <th>Trạng thái</th>
                                    <th class="text-end">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tours as $index => $tour): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td>
                                            <a href="<?= BASE_URL ?>admin/tours/show&id=<?= $tour['id'] ?>" class="fw-semibold text-decoration-none">
                                                <?= htmlspecialchars($tour['name'] ?? 'N/A') ?>
                                            </a>
                                        </td>
                                        <td><?= htmlspecialchars($tour['category_name'] ?? 'N/A') ?></td>
                                        <td>
                                            <?php if ($tour['price']): ?>
                                                <?= number_format($tour['price'], 0, ',', '.') ?> đ
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ((int)($tour['status'] ?? 1) === 1): ?>
                                                <span class="badge bg-success">Hoạt động</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Ẩn</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <a href="<?= BASE_URL ?>admin/tours/show&id=<?= $tour['id'] ?>" class="btn btn-sm btn-outline-info" title="Xem chi tiết">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <?php if (isAdmin()): ?>
                                            <a href="<?= BASE_URL ?>admin/tours/edit&id=<?= $tour['id'] ?>" class="btn btn-sm btn-outline-primary" title="Chỉnh sửa">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <form action="<?= BASE_URL ?>admin/tours/delete" method="post" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa tour này?');">
                                                <input type="hidden" name="id" value="<?= $tour['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Xóa">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($isGuide): ?>
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

                <?php if (!empty($leaveRequests)): ?>
                <hr class="my-4">
                <h6 class="mb-3">Lịch sử đơn xin nghỉ</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
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
                            <?php foreach (array_slice($leaveRequests, 0, 5) as $request): ?>
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

                <?php if (!empty($notes)): ?>
                <hr class="my-4">
                <h6 class="mb-3">
                    <i class="bi bi-clock-history me-2"></i>Lịch sử ghi chú
                </h6>
                <div class="list-group">
                    <?php foreach ($notes as $note): 
                        $statusClass = match($note['status'] ?? 'pending') {
                            'approved' => 'success',
                            'rejected' => 'danger',
                            default => 'warning'
                        };
                        $statusText = match($note['status'] ?? 'pending') {
                            'approved' => 'Đã duyệt',
                            'rejected' => 'Đã từ chối',
                            default => 'Chờ duyệt'
                        };
                    ?>
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <p class="mb-2"><?= nl2br(htmlspecialchars($note['note'])) ?></p>
                                <div class="d-flex align-items-center gap-3">
                                    <small class="text-muted">
                                        <i class="bi bi-clock me-1"></i><?= date('d/m/Y H:i', strtotime($note['created_at'])) ?>
                                    </small>
                                    <span class="badge bg-<?= $statusClass ?>"><?= $statusText ?></span>
                                </div>
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
                    <i class="bi bi-calendar-check me-2"></i>Tour được phân bổ
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($assignedBookings)): ?>
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
                            <?php foreach ($assignedBookings as $booking): 
                                $isConfirmed = isset($confirmationsMap[$booking['id']]) && $confirmationsMap[$booking['id']]['confirmed'] == 1;
                                $rejection = $rejectionsMap[$booking['id']] ?? null;
                                $hasPendingRejection = $rejection && $rejection['status'] === 'pending';
                            ?>
                            <tr>
                                <td>
                                    <a href="<?= BASE_URL ?>admin/bookings/show&id=<?= $booking['id'] ?>" 
                                       class="text-decoration-none tour-name-link" 
                                       style="color: inherit;">
                                        <strong><?= htmlspecialchars($booking['tour_name'] ?? 'N/A') ?></strong>
                                    </a>
                                    <?php if ($booking['tour_price']): ?>
                                        <br><small class="text-muted"><?= number_format($booking['tour_price'], 0, ',', '.') ?> ₫</small>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($booking['customer_name'] ?? 'N/A') ?></td>
                                <td>
                                    <?php if ($booking['start_date']): ?>
                                        <?= date('d/m/Y', strtotime($booking['start_date'])) ?>
                                    <?php else: ?>
                                        <span class="text-muted">Chưa có</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($booking['end_date']): ?>
                                        <?= date('d/m/Y', strtotime($booking['end_date'])) ?>
                                    <?php else: ?>
                                        <span class="text-muted">Chưa có</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($booking['status_name']): ?>
                                        <span class="badge bg-info"><?= htmlspecialchars($booking['status_name']) ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Chưa có</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($isConfirmed): ?>
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle me-1"></i>Đã xác nhận
                                        </span>
                                        <?php if (isset($confirmationsMap[$booking['id']]['confirmed_at'])): ?>
                                            <br><small class="text-muted"><?= date('d/m/Y H:i', strtotime($confirmationsMap[$booking['id']]['confirmed_at'])) ?></small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge bg-warning">Chưa xác nhận</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <?php if (!$hasPendingRejection): ?>
                                        <form action="<?= BASE_URL ?>guides/confirm-tour" method="post" class="d-inline">
                                            <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                                            <input type="hidden" name="confirmed" value="<?= $isConfirmed ? '0' : '1' ?>">
                                            <button type="submit" class="btn btn-sm <?= $isConfirmed ? 'btn-outline-danger' : 'btn-success' ?>">
                                                <i class="bi <?= $isConfirmed ? 'bi-x-circle' : 'bi-check-circle' ?> me-1"></i>
                                                <?= $isConfirmed ? 'Hủy xác nhận' : 'Xác nhận' ?>
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal<?= $booking['id'] ?>">
                                            <i class="bi bi-x-circle me-1"></i>Từ chối
                                        </button>
                                        <?php else: ?>
                                        <span class="badge bg-warning">
                                            <i class="bi bi-clock me-1"></i>Đã gửi yêu cầu từ chối
                                        </span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Modal từ chối tour -->
                                    <div class="modal fade" id="rejectModal<?= $booking['id'] ?>" tabindex="-1" aria-labelledby="rejectModalLabel<?= $booking['id'] ?>" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="rejectModalLabel<?= $booking['id'] ?>">
                                                        <i class="bi bi-x-circle me-2 text-danger"></i>Từ chối tour
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form action="<?= BASE_URL ?>guides/reject-tour" method="post">
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold">Tour:</label>
                                                            <p class="mb-0"><?= htmlspecialchars($booking['tour_name'] ?? 'N/A') ?></p>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label for="reason<?= $booking['id'] ?>" class="form-label fw-semibold">
                                                                Lý do từ chối <span class="text-danger">*</span>
                                                            </label>
                                                            <textarea class="form-control" id="reason<?= $booking['id'] ?>" name="reason" rows="4" 
                                                                      placeholder="Nhập lý do từ chối tour này..." required></textarea>
                                                            <small class="text-muted">Lý do này sẽ được gửi cho admin để xem xét.</small>
                                                        </div>
                                                        <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                                                        <button type="submit" class="btn btn-danger">
                                                            <i class="bi bi-send me-1"></i>Gửi yêu cầu từ chối
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
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
        <?php endif; ?>
    </div>
</div>
<?php
$content = ob_get_clean();

view('layouts.AdminLayout', [
    'title' => $title ?? ($isGuide ? 'Danh sách tour của tôi' : 'Danh sách tour'),
    'pageTitle' => $isGuide ? 'Danh sách tour của tôi' : 'Danh sách tour',
    'content' => $content,
    'breadcrumb' => [
        ['label' => 'Trang chủ', 'url' => BASE_URL . 'home'],
        ['label' => $isGuide ? 'Danh sách tour của tôi' : 'Tour', 'url' => BASE_URL . 'admin/tours', 'active' => true],
    ],
]);
?>
<style>
.tour-name-link {
    transition: opacity 0.3s ease;
}

.tour-name-link:hover {
    opacity: 0.6;
}

.tour-name-link strong {
    cursor: pointer;
}
</style>

