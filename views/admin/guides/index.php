<?php
ob_start();
?>
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white border-0 d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-2">
                <div>
                    <h3 class="card-title mb-1">
                        <i class="bi bi-person-bounding-box me-2"></i>
                        Danh sách hướng dẫn viên
                    </h3>
                    <small class="text-muted">Quản lý thông tin HDV, phân loại nội địa / quốc tế và theo dõi lịch sử chỉnh sửa.</small>
                </div>
                <div class="d-flex gap-2">
                    <a href="<?= BASE_URL ?>admin/guides/create" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-1"></i> Thêm HDV
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="mb-3 d-flex gap-2">
                    <a href="<?= BASE_URL ?>admin/guides" class="btn btn-sm btn-outline-secondary">Tất cả</a>
                    <a href="<?= BASE_URL ?>admin/guides?group=noidia" class="btn btn-sm btn-outline-secondary">Nội địa</a>
                    <a href="<?= BASE_URL ?>admin/guides?group=quocte" class="btn btn-sm btn-outline-secondary">Quốc tế</a>
                </div>

                <?php if (empty($guides)): ?>
                    <div class="alert alert-info">Chưa có HDV nào. Hãy thêm mới.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Họ tên</th>
                                    <th>Nhóm</th>
                                    <th>Ngôn ngữ</th>
                                    <th>Kinh nghiệm</th>
                                    <th>Trạng thái</th>
                                    <th class="text-end">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($guides as $i => $g): ?>
                                    <tr>
                                        <td><?= $i + 1 ?></td>
                                        <td>
                                            <a href="<?= BASE_URL ?>admin/guides/show&id=<?= $g['id'] ?>" class="fw-semibold text-decoration-none">
                                                <?= htmlspecialchars($g['full_name'] ?? '') ?>
                                            </a>
                                        </td>
                                        <td><?= ($g['group'] === 'quocte') ? 'Quốc tế' : 'Nội địa' ?></td>
                                        <td><?= htmlspecialchars($g['languages'] ?? '') ?></td>
                                        <td><?= htmlspecialchars(mb_strimwidth($g['experience'] ?? '', 0, 60, '...')) ?></td>
                                        <td>
                                            <?php if ((int)($g['status'] ?? 1) === 1): ?>
                                                <span class="badge bg-success-subtle text-success">Hoạt động</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Ẩn</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <a href="<?= BASE_URL ?>admin/guides/edit&id=<?= $g['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <form action="<?= BASE_URL ?>admin/guides/delete" method="post" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa HDV này?');">
                                                <input type="hidden" name="id" value="<?= $g['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-trash"></i>
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
<?php
$content = ob_get_clean();

view('layouts.AdminLayout', [
    'title' => $title ?? 'Quản lý HDV',
    'pageTitle' => 'Quản lý HDV',
    'content' => $content,
    'breadcrumb' => [
        ['label' => 'Trang chủ', 'url' => BASE_URL . 'home'],
        ['label' => 'HDV', 'url' => BASE_URL . 'admin/guides', 'active' => true],
    ],
]);
?>
