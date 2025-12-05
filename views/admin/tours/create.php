<?php
ob_start();
$formData = $formData ?? [];
?>
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white py-3">
                <h3 class="card-title mb-0 d-flex align-items-center">
                    <i class="bi bi-plus-circle me-2 fs-5"></i>Thêm tour mới
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

                <form action="<?= BASE_URL ?>admin/tours/store" method="post" novalidate>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="tourName" class="form-label fw-semibold">
                                <i class="bi bi-tag me-1 text-primary"></i>Tên tour <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control form-control-lg"
                                   id="tourName"
                                   name="name"
                                   value="<?= htmlspecialchars($formData['name'] ?? '') ?>"
                                   placeholder="Ví dụ: Tour Đà Nẵng 3 ngày 2 đêm"
                                   required>
                        </div>

                        <div class="col-md-6">
                            <label for="tourCategory" class="form-label fw-semibold">
                                <i class="bi bi-folder me-1 text-primary"></i>Danh mục <span class="text-danger">*</span>
                            </label>
                            <select class="form-select form-select-lg" id="tourCategory" name="category_id" required>
                                <option value="">-- Chọn danh mục --</option>
                                <?php if (!empty($categories) && is_array($categories)): ?>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?= $category['id'] ?>" <?= (isset($formData['category_id']) && $formData['category_id'] == $category['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($category['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="tourPrice" class="form-label fw-semibold">
                                <i class="bi bi-currency-dollar me-1 text-primary"></i>Giá (VNĐ)
                            </label>
                            <div class="input-group input-group-lg">
                                <input type="text"
                                       class="form-control form-control-lg"
                                       id="tourPrice"
                                       name="price"
                                       value="<?= isset($formData['price']) && $formData['price'] ? number_format((float)$formData['price'], 0, '', '') : '' ?>"
                                       placeholder="Ví dụ: 5000000"
                                       oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                <span class="input-group-text bg-light">₫</span>
                            </div>
                            <small class="text-muted">
                                <i class="bi bi-info-circle me-1"></i>Nhập số tiền không có dấu phẩy hoặc dấu chấm (ví dụ: 5000000)
                            </small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold d-block">
                                <i class="bi bi-toggle-on me-1 text-primary"></i>Trạng thái
                            </label>
                            <div class="form-check form-switch mt-3">
                                <input class="form-check-input" type="checkbox" role="switch" id="tourStatus" name="status" value="1" <?= ((int)($formData['status'] ?? 1) === 1) ? 'checked' : '' ?> style="width: 3rem; height: 1.5rem;">
                                <label class="form-check-label ms-2 fw-semibold" for="tourStatus">Hoạt động</label>
                            </div>
                        </div>

                        <div class="col-12">
                            <label for="tourDescription" class="form-label fw-semibold">
                                <i class="bi bi-file-text me-1 text-primary"></i>Mô tả
                            </label>
                            <textarea class="form-control"
                                      id="tourDescription"
                                      name="description"
                                      rows="5"
                                      placeholder="Mô tả về tour..."><?= htmlspecialchars($formData['description'] ?? '') ?></textarea>
                        </div>

                        <div class="col-12">
                            <label for="tourSchedule" class="form-label fw-semibold">
                                <i class="bi bi-calendar-event me-1 text-primary"></i>Lịch trình
                            </label>
                            <textarea class="form-control"
                                      id="tourSchedule"
                                      name="schedule"
                                      rows="6"
                                      placeholder="Nhập lịch trình chi tiết của tour..."><?= htmlspecialchars($formData['schedule'] ?? '') ?></textarea>
                        </div>

                        <div class="col-12">
                            <label for="tourPolicies" class="form-label fw-semibold">
                                <i class="bi bi-shield-check me-1 text-primary"></i>Chính sách
                            </label>
                            <textarea class="form-control"
                                      id="tourPolicies"
                                      name="policies"
                                      rows="4"
                                      placeholder="Chính sách hủy tour, hoàn tiền..."><?= htmlspecialchars($formData['policies'] ?? '') ?></textarea>
                        </div>

                        <div class="col-12">
                            <label for="tourSuppliers" class="form-label fw-semibold">
                                <i class="bi bi-building me-1 text-primary"></i>Nhà cung cấp
                            </label>
                            <input type="text"
                                   class="form-control form-control-lg"
                                   id="tourSuppliers"
                                   name="suppliers"
                                   value="<?= htmlspecialchars($formData['suppliers'] ?? '') ?>"
                                   placeholder="Ví dụ: Công ty Du lịch ABC">
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-3 mt-4 pt-3 border-top">
                        <a href="<?= BASE_URL ?>admin/tours" class="btn btn-outline-secondary btn-lg">
                            <i class="bi bi-arrow-left me-1"></i>Quay lại
                        </a>
                        <button type="submit" class="btn btn-primary btn-lg px-4">
                            <i class="bi bi-save me-1"></i>Lưu tour
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
    'title' => $title ?? 'Thêm tour',
    'pageTitle' => 'Thêm tour',
    'content' => $content,
    'breadcrumb' => [
        ['label' => 'Trang chủ', 'url' => BASE_URL . 'home'],
        ['label' => 'Tour', 'url' => BASE_URL . 'admin/tours'],
        ['label' => 'Thêm mới', 'url' => BASE_URL . 'admin/tours/create', 'active' => true],
    ],
]);
?>






