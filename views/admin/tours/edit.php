<?php
ob_start();
$tour = $tour ?? [];
$categories = $categories ?? [];
?>
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-warning text-dark py-3">
                <h3 class="card-title mb-0 d-flex align-items-center">
                    <i class="bi bi-pencil-square me-2 fs-5"></i>Chỉnh sửa tour
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

                <form action="<?= BASE_URL ?>admin/tours/update" method="post" enctype="multipart/form-data" novalidate>
                    <input type="hidden" name="id" value="<?= htmlspecialchars($tour['id'] ?? '') ?>">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="tourName" class="form-label fw-semibold">
                                <i class="bi bi-tag me-1 text-primary"></i>Tên tour <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control form-control-lg"
                                   id="tourName"
                                   name="name"
                                   value="<?= htmlspecialchars($tour['name'] ?? '') ?>"
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
                                        <option value="<?= $category['id'] ?>" <?= (isset($tour['category_id']) && $tour['category_id'] == $category['id']) ? 'selected' : '' ?>>
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
                                       value="<?= isset($tour['price']) && $tour['price'] ? number_format((float)$tour['price'], 0, '', '') : '' ?>"
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
                                <input class="form-check-input" type="checkbox" role="switch" id="tourStatus" name="status" value="1" <?= ((int)($tour['status'] ?? 1) === 1) ? 'checked' : '' ?> style="width: 3rem; height: 1.5rem;">
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
                                      placeholder="Mô tả về tour..."><?= htmlspecialchars($tour['description'] ?? '') ?></textarea>
                        </div>

                        <div class="col-12">
                            <label for="tourSchedule" class="form-label fw-semibold">
                                <i class="bi bi-calendar-event me-1 text-primary"></i>Lịch trình
                            </label>
                            <textarea class="form-control"
                                      id="tourSchedule"
                                      name="schedule"
                                      rows="6"
                                      placeholder="Nhập lịch trình chi tiết của tour..."><?= htmlspecialchars($tour['schedule'] ?? '') ?></textarea>
                        </div>

                        <div class="col-12">
                            <label for="tourPolicies" class="form-label fw-semibold">
                                <i class="bi bi-shield-check me-1 text-primary"></i>Chính sách
                            </label>
                            <textarea class="form-control"
                                      id="tourPolicies"
                                      name="policies"
                                      rows="4"
                                      placeholder="Chính sách hủy tour, hoàn tiền..."><?= htmlspecialchars($tour['policies'] ?? '') ?></textarea>
                        </div>

                        <div class="col-12">
                            <label for="tourSuppliers" class="form-label fw-semibold">
                                <i class="bi bi-building me-1 text-primary"></i>Nhà cung cấp
                            </label>
                            <input type="text"
                                   class="form-control form-control-lg"
                                   id="tourSuppliers"
                                   name="suppliers"
                                   value="<?= htmlspecialchars($tour['suppliers'] ?? '') ?>"
                                   placeholder="Ví dụ: Công ty Du lịch ABC">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-images me-1 text-primary"></i>Hình ảnh tour hiện có
                            </label>
                            <div id="existingImages" class="d-flex flex-wrap gap-3 mb-3">
                                <?php 
                                $tourImages = $tourImages ?? [];
                                if (!empty($tourImages) && is_array($tourImages)): 
                                    foreach ($tourImages as $img): 
                                ?>
                                    <div class="position-relative existing-image-item" data-image-id="<?= htmlspecialchars($img['id']) ?>">
                                        <img src="<?= BASE_URL . htmlspecialchars($img['image_path']) ?>" 
                                             class="img-thumbnail" 
                                             style="width: 150px; height: 150px; object-fit: cover;"
                                             alt="Tour image">
                                        <button type="button" 
                                                class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 remove-existing-image"
                                                data-image-id="<?= htmlspecialchars($img['id']) ?>">
                                            <i class="bi bi-x"></i>
                                        </button>
                                        <input type="hidden" name="keep_images[]" value="<?= htmlspecialchars($img['id']) ?>">
                                    </div>
                                <?php 
                                    endforeach;
                                endif; 
                                ?>
                            </div>
                            
                            <label for="tourImages" class="form-label fw-semibold">
                                <i class="bi bi-plus-circle me-1 text-primary"></i>Thêm ảnh mới
                            </label>
                            <input type="file"
                                   class="form-control form-control-lg"
                                   id="tourImages"
                                   name="images[]"
                                   accept="image/*"
                                   multiple>
                            <small class="text-muted d-block mt-2">
                                <i class="bi bi-info-circle me-1"></i>Bạn có thể chọn nhiều ảnh cùng lúc (JPG, PNG, GIF, WEBP)
                            </small>
                            <div id="imagePreview" class="mt-3 d-flex flex-wrap gap-3"></div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-3 mt-4 pt-3 border-top">
                        <a href="<?= BASE_URL ?>admin/tours" class="btn btn-outline-secondary btn-lg">
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
    'title' => $title ?? 'Chỉnh sửa tour',
    'pageTitle' => 'Chỉnh sửa tour',
    'content' => $content,
    'breadcrumb' => [
        ['label' => 'Trang chủ', 'url' => BASE_URL . 'home'],
        ['label' => 'Tour', 'url' => BASE_URL . 'admin/tours'],
        ['label' => 'Chỉnh sửa', 'url' => BASE_URL . 'admin/tours/edit&id=' . urlencode($tour['id'] ?? ''), 'active' => true],
    ],
]);
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('tourImages');
    const previewContainer = document.getElementById('imagePreview');
    const existingImagesContainer = document.getElementById('existingImages');

    // Xử lý xóa ảnh hiện có
    if (existingImagesContainer) {
        existingImagesContainer.addEventListener('click', function(e) {
            if (e.target.closest('.remove-existing-image')) {
                const btn = e.target.closest('.remove-existing-image');
                const imageId = btn.getAttribute('data-image-id');
                const imageItem = btn.closest('.existing-image-item');
                
                if (confirm('Bạn có chắc muốn xóa ảnh này?')) {
                    // Xóa khỏi DOM
                    imageItem.remove();
                    // Thêm vào danh sách ảnh cần xóa
                    const deleteInput = document.createElement('input');
                    deleteInput.type = 'hidden';
                    deleteInput.name = 'delete_images[]';
                    deleteInput.value = imageId;
                    document.querySelector('form').appendChild(deleteInput);
                }
            }
        });
    }

    // Xử lý preview ảnh mới
    if (fileInput && previewContainer) {
        fileInput.addEventListener('change', function(e) {
            const files = e.target.files;
            if (files.length > 0) {
                Array.from(files).forEach((file, index) => {
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const div = document.createElement('div');
                            div.className = 'position-relative';
                            div.style.width = '150px';
                            div.style.height = '150px';
                            
                            const img = document.createElement('img');
                            img.src = e.target.result;
                            img.className = 'img-thumbnail w-100 h-100';
                            img.style.objectFit = 'cover';
                            
                            const removeBtn = document.createElement('button');
                            removeBtn.type = 'button';
                            removeBtn.className = 'btn btn-sm btn-danger position-absolute top-0 end-0 m-1';
                            removeBtn.innerHTML = '<i class="bi bi-x"></i>';
                            removeBtn.style.zIndex = '10';
                            removeBtn.onclick = function() {
                                div.remove();
                                // Tạo DataTransfer để xóa file khỏi input
                                const dt = new DataTransfer();
                                Array.from(fileInput.files).forEach((f, i) => {
                                    if (i !== index) {
                                        dt.items.add(f);
                                    }
                                });
                                fileInput.files = dt.files;
                            };
                            
                            div.appendChild(img);
                            div.appendChild(removeBtn);
                            previewContainer.appendChild(div);
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }
        });
    }
});
</script>

