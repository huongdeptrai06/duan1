<?php
ob_start();
$formData = $formData ?? [];
$categories = $categories ?? [];
?>
<div class="row">
    <div class="col-12 col-lg-11 col-xl-11 mx-auto">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title mb-0">
                    <i class="bi bi-plus-circle me-2"></i>Thêm tour mới
                </h3>
            </div>
            <div class="card-body p-4 p-lg-5">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                            <strong>Không thể tạo tour</strong>
                        </div>
                        <ul class="mb-0 ps-3">
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form action="<?= BASE_URL ?>admin/tours/store" method="post" novalidate>
                    <div class="mb-3">
                        <label for="tourName" class="form-label fw-semibold">Tên tour <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-airplane-engines"></i></span>
                            <input type="text"
                                   class="form-control"
                                   id="tourName"
                                   name="name"
                                   value="<?= htmlspecialchars($formData['name'] ?? '') ?>"
                                   placeholder="VD: Tour Đà Nẵng 3 ngày 2 đêm"
                                   required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="tourCategory" class="form-label fw-semibold">Danh mục</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-tags"></i></span>
                            <select class="form-select" id="tourCategory" name="category_id">
                                <option value="">-- Chọn danh mục --</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category['id'] ?>" 
                                            <?= (isset($formData['category_id']) && $formData['category_id'] == $category['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($category['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="tourPrice" class="form-label fw-semibold">Giá tour (VNĐ)</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                            <input type="number"
                                   class="form-control"
                                   id="tourPrice"
                                   name="price"
                                   value="<?= htmlspecialchars($formData['price'] ?? '') ?>"
                                   placeholder="VD: 5000000"
                                   min="0"
                                   step="1000">
                        </div>
                        <small class="text-muted">Nhập giá tour (không cần dấu phẩy hoặc chấm)</small>
                    </div>

                    <div class="mb-3">
                        <label for="tourDescription" class="form-label fw-semibold">Mô tả</label>
                        <textarea class="form-control"
                                  id="tourDescription"
                                  name="description"
                                  rows="5"
                                  placeholder="Mô tả chi tiết về tour..."><?= htmlspecialchars($formData['description'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="tourSchedule" class="form-label fw-semibold">Lịch trình</label>
                        <textarea class="form-control"
                                  id="tourSchedule"
                                  name="schedule"
                                  rows="6"
                                  placeholder="Ngày 1: ...&#10;Ngày 2: ...&#10;Ngày 3: ..."><?= htmlspecialchars($formData['schedule'] ?? '') ?></textarea>
                        <small class="text-muted">Nhập lịch trình chi tiết của tour</small>
                    </div>

                    <div class="mb-3">
                        <label for="tourPolicies" class="form-label fw-semibold">Chính sách</label>
                        <textarea class="form-control"
                                  id="tourPolicies"
                                  name="policies"
                                  rows="4"
                                  placeholder="Chính sách hủy tour, đổi ngày, hoàn tiền..."><?= htmlspecialchars($formData['policies'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="tourSuppliers" class="form-label fw-semibold">Nhà cung cấp</label>
                        <input type="text"
                               class="form-control"
                               id="tourSuppliers"
                               name="suppliers"
                               value="<?= htmlspecialchars($formData['suppliers'] ?? '') ?>"
                               placeholder="VD: Công ty Du lịch ABC">
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Trạng thái</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   role="switch" 
                                   id="tourStatus" 
                                   name="status" 
                                   value="1" 
                                   <?= ((int)($formData['status'] ?? 1) === 1) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="tourStatus">Hiển thị tour</label>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?= BASE_URL ?>admin/tours" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i>
                            Hủy
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i>
                            Lưu tour
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
    'title' => $title ?? 'Thêm tour mới',
    'pageTitle' => 'Thêm tour mới',
    'content' => $content,
    'breadcrumb' => [
        ['label' => 'Trang chủ', 'url' => BASE_URL . 'home'],
        ['label' => 'Danh sách Tour', 'url' => BASE_URL . 'admin/tours'],
        ['label' => 'Thêm mới', 'url' => BASE_URL . 'admin/tours/create', 'active' => true],
    ],
]);
?>

