<?php
ob_start();
?>
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-warning text-dark py-3">
                <h3 class="card-title mb-0 d-flex align-items-center">
                    <i class="bi bi-pencil-square me-2 fs-5"></i>Cập nhật danh mục
                </h3>
            </div>
            <div class="card-body p-4">
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
                    <div class="mb-4">
                        <label for="categoryName" class="form-label fw-semibold">
                            <i class="bi bi-tag me-1 text-primary"></i>Tên danh mục <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               class="form-control form-control-lg"
                               id="categoryName"
                               name="name"
                               value="<?= htmlspecialchars($category['name'] ?? '') ?>"
                               placeholder="Nhập tên danh mục"
                               required>
                    </div>

                    <div class="mb-4">
                        <label for="categoryDescription" class="form-label fw-semibold">
                            <i class="bi bi-file-text me-1 text-primary"></i>Mô tả
                        </label>
                        <textarea class="form-control"
                                  id="categoryDescription"
                                  name="description"
                                  rows="5"
                                  placeholder="Nhập mô tả cho danh mục..."><?= htmlspecialchars($category['description'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold d-block">
                            <i class="bi bi-toggle-on me-1 text-primary"></i>Trạng thái
                        </label>
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" role="switch" id="categoryStatus" name="status" value="1" <?= ((int)($category['status'] ?? 1) === 1) ? 'checked' : '' ?> style="width: 3rem; height: 1.5rem;">
                            <label class="form-check-label ms-2 fw-semibold" for="categoryStatus">Hoạt động</label>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-3 pt-3 border-top">
                        <a href="<?= BASE_URL ?>admin/categories" class="btn btn-outline-secondary btn-lg">
                            <i class="bi bi-arrow-left me-1"></i>Quay lại
                        </a>
                        <button type="submit" class="btn btn-warning text-white btn-lg px-4">
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
