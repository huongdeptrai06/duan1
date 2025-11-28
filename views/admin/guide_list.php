<?php
ob_start();
$guides = $guides ?? [];
$guideGroups = $guideGroups ?? [];
?>
<div class="card shadow-sm">
    <div class="card-header bg-white border-0 d-flex flex-column flex-md-row justify-content-between align-items-md-center">
        <div class="mb-3 mb-md-0">
            <h5 class="mb-1">Danh sách tài khoản hướng dẫn viên</h5>
            <p class="text-muted mb-0 small">Quản trị, chỉnh sửa và xóa tài khoản hướng dẫn viên.</p>
        </div>
        <div class="d-flex gap-2">
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

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 60px;">#</th>
                    <th>Họ và tên</th>
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
                    <td colspan="5" class="text-center text-muted py-4">
                        <i class="bi bi-person-dash me-2"></i>Chưa có hướng dẫn viên nào được tạo.
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($guides as $index => $guide): ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td class="fw-semibold">
                            <a href="<?= BASE_URL . 'admin-guide-detail?id=' . urlencode($guide['id']) ?>" class="text-decoration-none">
                                <?= htmlspecialchars($guide['name'] ?? '(Chưa cập nhật)') ?>
                            </a>
                        </td>
                        <td>
                            <?php
                            $groupKey = $guide['guide_group'] ?? null;
                            $groupLabel = $groupKey && isset($guideGroups[$groupKey])
                                ? $guideGroups[$groupKey]
                                : 'Chưa phân nhóm';
                            ?>
                            <span class="badge bg-info-subtle text-info fw-semibold">
                                <?= htmlspecialchars($groupLabel) ?>
                            </span>
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

