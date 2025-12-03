<?php
ob_start();
?>
<div class="row">
    <div class="col-12 col-lg-11 col-xl-11 mx-auto">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-info text-white d-flex align-items-center">
                <h3 class="card-title mb-0">
                    <i class="bi bi-eye me-2"></i>Chi tiết danh mục
                </h3>
                <div class="d-flex gap-2 ms-auto">
                    <a href="<?= BASE_URL ?>admin/categories/edit&id=<?= $category['id'] ?>" class="btn btn-light btn-sm">
                        <i class="bi bi-pencil-square me-1"></i>Chỉnh sửa
                    </a>
                </div>
            </div>
            <div class="card-body p-4 p-lg-5">
                <dl class="row mb-0">
                    <dt class="col-sm-3">Tên danh mục</dt>
                    <dd class="col-sm-9"><?= htmlspecialchars($category['name'] ?? '') ?></dd>

                    <dt class="col-sm-3">Mô tả</dt>
                    <dd class="col-sm-9"><?= nl2br(htmlspecialchars($category['description'] ?? '')) ?></dd>

                    <dt class="col-sm-3">Trạng thái</dt>
                    <dd class="col-sm-9">
                        <?php if ((int)($category['status'] ?? 0) === 1): ?>
                            <span class="badge bg-success">Hoạt động</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Ẩn</span>
                        <?php endif; ?>
                    </dd>

                    <dt class="col-sm-3">Ngày tạo</dt>
                    <dd class="col-sm-9"><?= htmlspecialchars(date('d/m/Y H:i', strtotime($category['created_at'] ?? 'now'))) ?></dd>

                    <dt class="col-sm-3">Cập nhật lần cuối</dt>
                    <dd class="col-sm-9"><?= htmlspecialchars(date('d/m/Y H:i', strtotime($category['updated_at'] ?? 'now'))) ?></dd>
                </dl>
            </div>
            <div class="card-footer d-flex justify-content-end gap-2">
                <a href="<?= BASE_URL ?>admin/categories" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Quay lại
                </a>
                <form action="<?= BASE_URL ?>admin/categories/delete" method="post" onsubmit="return confirm('Bạn có chắc chắn muốn xóa danh mục này?');" class="d-inline">
                    <input type="hidden" name="id" value="<?= $category['id'] ?>">
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-1"></i>Xóa danh mục
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();

view('layouts.AdminLayout', [
    'title' => $title ?? 'Chi tiết danh mục',
    'pageTitle' => 'Chi tiết danh mục',
    'content' => $content,
    'breadcrumb' => [
        ['label' => 'Trang chủ', 'url' => BASE_URL . 'home'],
        ['label' => 'Danh mục', 'url' => BASE_URL . 'admin/categories'],
        ['label' => htmlspecialchars($category['name'] ?? ''), 'url' => BASE_URL . 'admin/categories/show&id=' . urlencode($category['id']), 'active' => true],
    ],
]);
?>

