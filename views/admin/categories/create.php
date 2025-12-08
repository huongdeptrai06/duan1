<?php
ob_start();
$formData = $formData ?? [];
?>
<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title mb-0">
                    <i class="bi bi-plus-circle me-2"></i>Thêm danh mục mới
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

                <form action="<?= BASE_URL ?>admin/categories/store" method="post" novalidate>
                    <div class="mb-3">
                        <label for="categoryName" class="form-label fw-semibold">Tên danh mục</label>
                        <input type="text"
                               class="form-control"
                               id="categoryName"
                               name="name"
                               value="<?= htmlspecialchars($formData['name'] ?? '') ?>"
                               placeholder="Ví dụ: Tour miền Bắc"
                               required>
                    </div>

                    <div class="mb-3">
                        <label for="categoryDescription" class="form-label fw-semibold">Mô tả</label>
                        <textarea class="form-control"
                                  id="categoryDescription"
                                  name="description"
                                  rows="4"
                                  placeholder="Mô tả ngắn cho danh mục">
<?= htmlspecialchars($formData['description'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Trạng thái</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="categoryStatus" name="status" value="1" <?= ((int)($formData['status'] ?? 1) === 1) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="categoryStatus">Hiển thị danh mục</label>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="<?= BASE_URL ?>admin/categories" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i>Quay lại
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i>Lưu danh mục
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
    'title' => $title ?? 'Thêm danh mục',
    'pageTitle' => 'Thêm danh mục',
    'content' => $content,
    'breadcrumb' => [
        ['label' => 'Trang chủ', 'url' => BASE_URL . 'home'],
        ['label' => 'Danh mục', 'url' => BASE_URL . 'admin/categories'],
        ['label' => 'Thêm mới', 'url' => BASE_URL . 'admin/categories/create', 'active' => true],
    ],
]);
?>
