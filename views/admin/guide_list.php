<?php
ob_start();
$guides = $guides ?? [];
$guideGroups = $guideGroups ?? [];
?>
<div class="card shadow-sm">
    <div class="card-header bg-white border-0 d-flex flex-column flex-md-row align-items-md-center">
        <div class="mb-3 mb-md-0">
            <h5 class="mb-1">Danh sách tài khoản  hướng dẫn viên</h5>
            <p class="text-muted mb-0 small">Quản trị viên và hướng dẫn viên đang hoạt động trong hệ thống.</p>
        </div>
        <div class="d-flex gap-2 ms-md-auto">
            <a href="<?= BASE_URL ?>admin-guide-list" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-clockwise me-1"></i>Làm mới
            </a>
            <a href="<?= BASE_URL ?>admin-guide-create" class="btn btn-primary">
                <i class="bi bi-person-plus me-2"></i>Cấp tài khoản mới
            </a>
        </div>
    </div>

    <?php if (!empty($errors)): ?>
    <div class="alert alert-danger mx-3">
        <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars(implode(' ', $errors)) ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($successMessage)): ?>
    <div class="alert alert-success mx-3">
        <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($successMessage) ?>
    </div>
    <?php endif; ?>

    <?php if (isset($_GET['updated']) && $_GET['updated'] == '1'): ?>
    <div class="alert alert-success mx-3">
        <i class="bi bi-check-circle-fill me-2"></i>Đã cập nhật thông tin hướng dẫn viên thành công.
    </div>
    <?php endif; ?>

    <?php if (isset($_GET['deleted']) && $_GET['deleted'] == '1'): ?>
    <div class="alert alert-success mx-3">
        <i class="bi bi-check-circle-fill me-2"></i>Đã xóa hướng dẫn viên thành công.
    </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger mx-3">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <?php
        $errorMsg = match($_GET['error']) {
            'db' => 'Không thể kết nối cơ sở dữ liệu.',
            'notfound' => 'Không tìm thấy hướng dẫn viên.',
            'delete' => 'Không thể xóa hướng dẫn viên. Vui lòng thử lại.',
            default => 'Có lỗi xảy ra.',
        };
        echo htmlspecialchars($errorMsg);
        ?>
    </div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 60px;">#</th>
                    <th>Họ và tên</th>
                    <th>Vai trò</th>
                    <th>Nhóm chuyên môn</th>
                    <th>Email</th>
                    <th>Trạng thái</th>
                    <th>Ngày tạo</th>
                    <th style="width: 180px;" class="text-center">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($guides)): ?>
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">
                        <i class="bi bi-person-dash me-2"></i>Chưa có tài khoản phù hợp để hiển thị.
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($guides as $index => $guide): ?>
                    <tr>
                        <?php
                        $role = $guide['role'] ?? 'guide';
                        $isAdminRole = $role === 'admin';
                        $roleLabel = $isAdminRole ? 'Quản trị viên' : 'Hướng dẫn viên';
                        $roleClass = $isAdminRole ? 'bg-primary' : 'bg-info';
                        ?>
                        <td><?= $index + 1 ?></td>
                        <td class="fw-semibold">
                            <?php if ($isAdminRole): ?>
                                <?= htmlspecialchars($guide['name'] ?? '(Chưa cập nhật)') ?>
                            <?php else: ?>
                                <a href="<?= BASE_URL . 'admin-guide-detail?id=' . urlencode($guide['id']) ?>" class="text-decoration-none">
                                    <?= htmlspecialchars($guide['name'] ?? '(Chưa cập nhật)') ?>
                                </a>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge <?= $roleClass ?>"><?= $roleLabel ?></span>
                        </td>
                        <td>
                            <?php
                            $groupKey = $guide['guide_group'] ?? null;
                            $groupLabel = $groupKey && isset($guideGroups[$groupKey])
                                ? $guideGroups[$groupKey]
                                : 'Chưa phân nhóm';
                            ?>
                            <?php if ($isAdminRole): ?>
                                <span class="text-muted small">Không áp dụng</span>
                            <?php else: ?>
                                <span class="badge bg-info-subtle text-info fw-semibold">
                                    <?= htmlspecialchars($groupLabel) ?>
                                </span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($guide['email'] ?? '') ?></td>
                        <td>
                            <?php $isActive = (int)($guide['status'] ?? 0) === 1; ?>
                            <span class="badge <?= $isActive ? 'bg-success' : 'bg-secondary' ?>">
                                <?= $isActive ? 'Đang hoạt động' : 'Bị khóa' ?>
                            </span>
                        </td>
                        <td>
                            <?= htmlspecialchars(
                                $guide['created_at']
                                    ? date('d/m/Y H:i', strtotime($guide['created_at']))
                                    : '-'
                            ) ?>
                        </td>
                        <td class="text-center text-nowrap">
                            <?php if ($isAdminRole): ?>
                                <span class="text-muted small">Không áp dụng cho admin</span>
                            <?php else: ?>
                                <a href="<?= BASE_URL . 'admin-guide-edit?id=' . urlencode($guide['id']) ?>" class="btn btn-outline-primary btn-sm me-1">
                                    <i class="bi bi-pencil-square me-1"></i>Sửa
                                </a>
                                <form action="<?= BASE_URL ?>admin-guide-delete"
                                      method="post"
                                      class="d-inline"
                                      onsubmit="return confirm('Bạn chắc chắn muốn xóa tài khoản này?');">
                                    <input type="hidden" name="id" value="<?= (int)$guide['id'] ?>">
                                    <button type="submit" class="btn btn-outline-danger btn-sm">
                                        <i class="bi bi-trash me-1"></i>Xóa
                                    </button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php
$content = ob_get_clean();

view('layouts.AdminLayout', [
    'title' => $title ?? 'Danh sách hướng dẫn viên',
    'pageTitle' => $pageTitle ?? 'Danh sách hướng dẫn viên',
    'breadcrumb' => $breadcrumb ?? [],
    'content' => $content,
]);
?>
