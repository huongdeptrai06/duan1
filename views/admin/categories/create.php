<?php
ob_start();
$formData = $formData ?? [];
?>
<style>
.category-form-card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.08);
    overflow: hidden;
    transition: all 0.3s ease;
}

.category-form-card:hover {
    box-shadow: 0 12px 40px rgba(0,0,0,0.12);
    transform: translateY(-2px);
}

.category-form-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 2rem;
    border: none;
}

.category-form-header h3 {
    font-size: 1.75rem;
    font-weight: 600;
    letter-spacing: -0.5px;
}

.category-form-body {
    padding: 2.5rem;
    background: #ffffff;
}

.form-label-custom {
    font-weight: 600;
    color: #2d3748;
    font-size: 0.95rem;
    margin-bottom: 0.75rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.form-label-custom i {
    color: #667eea;
    font-size: 1.1rem;
}

.form-control-custom {
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    padding: 0.75rem 1rem;
    font-size: 0.95rem;
    transition: all 0.3s ease;
    background: #f8fafc;
}

.form-control-custom:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
    background: #ffffff;
    transform: translateY(-1px);
}

.form-control-custom::placeholder {
    color: #a0aec0;
}

textarea.form-control-custom {
    resize: vertical;
    min-height: 120px;
}

.status-toggle-container {
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    border-radius: 12px;
    padding: 1.25rem;
    border: 2px solid #e2e8f0;
    transition: all 0.3s ease;
}

.status-toggle-container:hover {
    border-color: #cbd5e0;
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
}

.form-check-input-custom {
    width: 3.5rem;
    height: 1.75rem;
    cursor: pointer;
    border: none;
    background-color: #cbd5e0;
    transition: all 0.3s ease;
}

.form-check-input-custom:checked {
    background-color: #48bb78;
    border-color: #48bb78;
}

.form-check-input-custom:focus {
    box-shadow: 0 0 0 4px rgba(72, 187, 120, 0.2);
}

.form-check-label-custom {
    font-weight: 500;
    color: #4a5568;
    font-size: 1rem;
    margin-left: 0.75rem;
    cursor: pointer;
}

.btn-custom-secondary {
    padding: 0.75rem 1.5rem;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.95rem;
    border: 2px solid #e2e8f0;
    background: #ffffff;
    color: #4a5568;
    transition: all 0.3s ease;
}

.btn-custom-secondary:hover {
    background: #f7fafc;
    border-color: #cbd5e0;
    color: #2d3748;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.btn-custom-primary {
    padding: 0.75rem 2rem;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.95rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    color: #ffffff;
    transition: all 0.3s ease;
}

.btn-custom-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
    background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
}

.btn-custom-primary:active {
    transform: translateY(0);
}

.alert-danger-custom {
    background: linear-gradient(135deg, #fff5f5 0%, #fed7d7 100%);
    border: 2px solid #fc8181;
    border-radius: 12px;
    padding: 1rem 1.25rem;
    margin-bottom: 2rem;
}

.alert-danger-custom ul {
    margin: 0;
    padding-left: 1.25rem;
    color: #c53030;
}
</style>

<div class="row">
    <div class="col-12">
        <div class="card category-form-card">
            <div class="card-header category-form-header text-white">
                <h3 class="card-title mb-0">
                    <i class="bi bi-plus-circle-fill me-2"></i>Thêm danh mục mới
                </h3>
            </div>
            <div class="card-body category-form-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger-custom">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><i class="bi bi-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form action="<?= BASE_URL ?>admin/categories/store" method="post" novalidate>
                    <div class="mb-4">
                        <label for="categoryName" class="form-label-custom">
                            <i class="bi bi-tag-fill"></i>
                            Tên danh mục
                        </label>
                        <input type="text"
                               class="form-control form-control-custom"
                               id="categoryName"
                               name="name"
                               value="<?= htmlspecialchars($formData['name'] ?? '') ?>"
                               placeholder="Ví dụ: Tour miền Bắc"
                               required>
                    </div>

                    <div class="mb-4">
                        <label for="categoryDescription" class="form-label-custom">
                            <i class="bi bi-text-paragraph"></i>
                            Mô tả
                        </label>
                        <textarea class="form-control form-control-custom"
                                  id="categoryDescription"
                                  name="description"
                                  placeholder="Mô tả ngắn cho danh mục"><?= htmlspecialchars($formData['description'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label-custom mb-3">
                            <i class="bi bi-toggle-on"></i>
                            Trạng thái
                        </label>
                        <div class="status-toggle-container">
                            <div class="form-check form-switch">
                                <input class="form-check-input form-check-input-custom" 
                                       type="checkbox" 
                                       role="switch" 
                                       id="categoryStatus" 
                                       name="status" 
                                       value="1" 
                                       <?= ((int)($formData['status'] ?? 1) === 1) ? 'checked' : '' ?>>
                                <label class="form-check-label form-check-label-custom" for="categoryStatus">
                                    Hiển thị danh mục
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-3 mt-5 pt-3 border-top">
                        <a href="<?= BASE_URL ?>admin/categories" class="btn btn-custom-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Quay lại
                        </a>
                        <button type="submit" class="btn btn-custom-primary">
                            <i class="bi bi-save me-2"></i>Lưu danh mục
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
