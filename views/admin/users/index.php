<?php
ob_start();
?>
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white border-0 d-flex align-items-center">
                <div>
                    <h3 class="card-title mb-0"><i class="bi bi-people-fill me-2"></i>Danh sách tài khoản</h3>
                    <small class="text-muted">Quản trị viên có thể xem nhanh quyền và trạng thái của từng tài khoản.</small>
                </div>
                <a href="<?= BASE_URL ?>admin/guide/create" class="btn btn-primary ms-auto">
                    <i class="bi bi-person-plus me-1"></i> Cấp tài khoản HDV
                </a>
            </div>
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                            <strong>Có lỗi xảy ra</strong>
                        </div>
                        <ul class="mb-0 ps-3">
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if (empty($users)): ?>
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle-fill me-2"></i>Chưa có tài khoản nào trong hệ thống.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Họ tên</th>
                                    <th>Email</th>
                                    <th>Vai trò</th>
                                    <th>Trạng thái</th>
                                    <th>Ngày tạo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $index => $user): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td><?= htmlspecialchars($user['name'] ?? '---') ?></td>
                                        <td><?= htmlspecialchars($user['email'] ?? '') ?></td>
                                        <td>
                                            <?php if (($user['role'] ?? '') === 'admin'): ?>
                                                <span class="badge bg-danger-subtle text-danger fw-semibold">Admin</span>
                                            <?php else: ?>
                                                <span class="badge bg-primary-subtle text-primary fw-semibold">Hướng dẫn viên</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ((int)($user['status'] ?? 0) === 1): ?>
                                                <span class="badge bg-success-subtle text-success">Hoạt động</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Đã khóa</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($user['created_at'] ?? 'now'))) ?></td>
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
<?php
$content = ob_get_clean();

view('layouts.AdminLayout', [
    'title' => $title ?? 'Danh sách tài khoản',
    'pageTitle' => 'Quản lý tài khoản',
    'content' => $content,
    'breadcrumb' => [
        ['label' => 'Trang chủ', 'url' => BASE_URL . 'home'],
        ['label' => 'Danh sách tài khoản', 'url' => BASE_URL . 'admin/users', 'active' => true],
    ],
]);
?>








