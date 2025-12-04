<?php
ob_start();
?>
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title mb-0">
                    <i class="bi bi-clipboard-check me-2"></i>
                    Quản lý yêu cầu HDV
                </h3>
            </div>
            <div class="card-body">
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

                <!-- Đơn xin nghỉ -->
                <div class="mb-4">
                    <h5 class="mb-3">
                        <i class="bi bi-calendar-x me-2 text-warning"></i>
                        Đơn xin nghỉ chờ duyệt (<?= count($pendingLeaveRequests) ?>)
                    </h5>
                    <?php if (empty($pendingLeaveRequests)): ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>Không có đơn xin nghỉ nào chờ duyệt.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>HDV</th>
                                        <th>Ngày bắt đầu</th>
                                        <th>Ngày kết thúc</th>
                                        <th>Lý do</th>
                                        <th>Ngày gửi</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pendingLeaveRequests as $request): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($request['guide_name'] ?? 'N/A') ?></strong></td>
                                        <td><?= date('d/m/Y', strtotime($request['start_date'])) ?></td>
                                        <td><?= date('d/m/Y', strtotime($request['end_date'])) ?></td>
                                        <td><?= htmlspecialchars($request['reason']) ?></td>
                                        <td><?= date('d/m/Y H:i', strtotime($request['created_at'])) ?></td>
                                        <td>
                                            <form action="<?= BASE_URL ?>admin/guides/process-leave" method="post" class="d-inline">
                                                <input type="hidden" name="request_id" value="<?= $request['id'] ?>">
                                                <input type="hidden" name="action" value="approve">
                                                <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Bạn có chắc chắn muốn duyệt đơn xin nghỉ này?');">
                                                    <i class="bi bi-check-circle me-1"></i>Duyệt
                                                </button>
                                            </form>
                                            <form action="<?= BASE_URL ?>admin/guides/process-leave" method="post" class="d-inline">
                                                <input type="hidden" name="request_id" value="<?= $request['id'] ?>">
                                                <input type="hidden" name="action" value="reject">
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Bạn có chắc chắn muốn từ chối đơn xin nghỉ này?');">
                                                    <i class="bi bi-x-circle me-1"></i>Từ chối
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Ghi chú -->
                <div class="mb-4">
                    <h5 class="mb-3">
                        <i class="bi bi-sticky me-2 text-info"></i>
                        Ghi chú chờ duyệt (<?= count($pendingNotes) ?>)
                    </h5>
                    <?php if (empty($pendingNotes)): ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>Không có ghi chú nào chờ duyệt.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>HDV</th>
                                        <th>Ghi chú</th>
                                        <th>Ngày gửi</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pendingNotes as $note): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($note['guide_name'] ?? 'N/A') ?></strong></td>
                                        <td><?= nl2br(htmlspecialchars($note['note'])) ?></td>
                                        <td><?= date('d/m/Y H:i', strtotime($note['created_at'])) ?></td>
                                        <td>
                                            <form action="<?= BASE_URL ?>admin/guides/process-note" method="post" class="d-inline">
                                                <input type="hidden" name="note_id" value="<?= $note['id'] ?>">
                                                <input type="hidden" name="action" value="approve">
                                                <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Bạn có chắc chắn muốn duyệt ghi chú này?');">
                                                    <i class="bi bi-check-circle me-1"></i>Duyệt
                                                </button>
                                            </form>
                                            <form action="<?= BASE_URL ?>admin/guides/process-note" method="post" class="d-inline">
                                                <input type="hidden" name="note_id" value="<?= $note['id'] ?>">
                                                <input type="hidden" name="action" value="reject">
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Bạn có chắc chắn muốn từ chối ghi chú này?');">
                                                    <i class="bi bi-x-circle me-1"></i>Từ chối
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Xác nhận tour -->
                <div class="mb-4">
                    <h5 class="mb-3">
                        <i class="bi bi-calendar-check me-2 text-success"></i>
                        Xác nhận tour chờ duyệt (<?= count($pendingConfirmations) ?>)
                    </h5>
                    <?php if (empty($pendingConfirmations)): ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>Không có xác nhận tour nào chờ duyệt.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>HDV</th>
                                        <th>Tour</th>
                                        <th>Ngày khởi hành</th>
                                        <th>Trạng thái</th>
                                        <th>Ngày gửi</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pendingConfirmations as $conf): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($conf['guide_name'] ?? 'N/A') ?></strong></td>
                                        <td><?= htmlspecialchars($conf['tour_name'] ?? 'N/A') ?></td>
                                        <td>
                                            <?php if ($conf['booking_start_date']): ?>
                                                <?= date('d/m/Y', strtotime($conf['booking_start_date'])) ?>
                                            <?php else: ?>
                                                <span class="text-muted">Chưa có</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($conf['confirmed']): ?>
                                                <span class="badge bg-success">Xác nhận</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning">Hủy xác nhận</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= date('d/m/Y H:i', strtotime($conf['created_at'])) ?></td>
                                        <td>
                                            <form action="<?= BASE_URL ?>admin/guides/process-confirmation" method="post" class="d-inline">
                                                <input type="hidden" name="confirmation_id" value="<?= $conf['id'] ?>">
                                                <input type="hidden" name="action" value="approve">
                                                <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Bạn có chắc chắn muốn duyệt xác nhận tour này?');">
                                                    <i class="bi bi-check-circle me-1"></i>Duyệt
                                                </button>
                                            </form>
                                            <form action="<?= BASE_URL ?>admin/guides/process-confirmation" method="post" class="d-inline">
                                                <input type="hidden" name="confirmation_id" value="<?= $conf['id'] ?>">
                                                <input type="hidden" name="action" value="reject">
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Bạn có chắc chắn muốn từ chối xác nhận tour này?');">
                                                    <i class="bi bi-x-circle me-1"></i>Từ chối
                                                </button>
                                            </form>
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
</div>
<?php
$content = ob_get_clean();

view('layouts.AdminLayout', [
    'title' => $title ?? 'Quản lý yêu cầu HDV',
    'pageTitle' => 'Quản lý yêu cầu HDV',
    'content' => $content,
    'breadcrumb' => [
        ['label' => 'Trang chủ', 'url' => BASE_URL . 'home'],
        ['label' => 'Quản lý yêu cầu HDV', 'url' => BASE_URL . 'admin/guides/requests', 'active' => true],
    ],
]);
?>

