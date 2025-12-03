<?php
ob_start();
?>
<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-warning text-dark">
                <h3 class="card-title mb-0">
                    <i class="bi bi-pencil-square me-2"></i>Cập nhật danh mục
                </h3>
            </div>
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0 ps-3">
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form action="<?= BASE_URL ?>admin/categories/update" method="post" novalidate>
                    <input type="hidden" name="id" value="<?= htmlspecialchars($category['id']) ?>">
                    <div class="mb-3">
                        <label for="categoryName" class="form-label fw-semibold">Tên danh mục</label>
                        <input type="text"
                               class="form-control"
                               id="categoryName"
                               name="name"
                               value="<?= htmlspecialchars($category['name'] ?? '') ?>"
                               required>
                    </div>

                    <div class="mb-3">
                        <label for="categoryDescription" class="form-label fw-semibold">Mô tả</label>
                        <textarea class="form-control"
                                  id="categoryDescription"
                                  name="description"
                                  rows="4"><?= htmlspecialchars($category['description'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Trạng thái</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="categoryStatus" name="status" value="1" <?= ((int)($category['status'] ?? 1) === 1) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="categoryStatus">Hiển thị danh mục</label>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="<?= BASE_URL ?>admin/categories" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i>Quay lại
                        </a>
                        <button type="submit" class="btn btn-warning text-white">
                            <i class="bi bi-save me-1"></i>Lưu thay đổi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();

view('layouts.AdminLayout', [
    'title' => $title ?? 'Cập nhật danh mục',
    'pageTitle' => 'Cập nhật danh mục',
    'content' => $content,
    'breadcrumb' => [
        ['label' => 'Trang chủ', 'url' => BASE_URL . 'home'],
        ['label' => 'Danh mục', 'url' => BASE_URL . 'admin/categories'],
        ['label' => 'Cập nhật', 'url' => BASE_URL . 'admin/categories/edit&id=' . urlencode($category['id']), 'active' => true],
    ],
]);
?>

