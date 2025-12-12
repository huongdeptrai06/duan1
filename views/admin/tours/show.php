<?php
ob_start();
?>
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">
                    <i class="bi bi-airplane-engines me-2"></i>Chi tiết tour #<?= $tour['id'] ?>
                </h3>
                <div class="d-flex justify-content-end">
                    <a href="<?= BASE_URL ?>admin/tours" class="btn btn-light btn-sm">
                        <i class="bi bi-arrow-left me-1"></i>Quay lại
                    </a>
                </div>
            </div>
            <div class="card-body">
                <?php 
                $tourImages = $tourImages ?? [];
                
                // Debug info (chỉ hiển thị trong development)
                $isLocal = in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1']) || 
                          strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost') !== false;
                
                if ($isLocal && !empty($tourImages)) {
                    error_log('Tour #' . ($tour['id'] ?? 'N/A') . ' - Images count: ' . count($tourImages));
                    error_log('Tour Images data: ' . print_r($tourImages, true));
                }
                
                if (!empty($tourImages) && is_array($tourImages) && count($tourImages) > 0): 
                ?>
                    <div class="mb-4">
                        <h5 class="mb-3">
                            <i class="bi bi-images me-2 text-primary"></i>Hình ảnh tour (<?= count($tourImages) ?> ảnh)
                        </h5>
                        <div class="row g-3" id="tourImagesGallery">
                            <?php foreach ($tourImages as $index => $img): 
                                // Xử lý đường dẫn ảnh
                                $imagePath = $img['image_path'] ?? '';
                                
                                // Xử lý đường dẫn: có thể là 'uploads/tours/xxx' hoặc chỉ 'xxx'
                                if (empty($imagePath)) {
                                    continue; // Bỏ qua nếu không có đường dẫn
                                }
                                
                                // Loại bỏ dấu / ở đầu nếu có
                                $imagePath = ltrim($imagePath, '/');
                                
                                // Tạo đường dẫn đầy đủ
                                // File nằm trong public/uploads/tours/
                                // Thử cả 2 cách: với và không có public/
                                if (strpos($imagePath, 'uploads/') === 0) {
                                    // Đã có 'uploads/' ở đầu
                                    // Thử với public/ trước (vì file thực tế nằm trong public/)
                                    $fullImagePath = BASE_URL . 'public/' . $imagePath;
                                    $fullImagePathWithoutPublic = BASE_URL . $imagePath;
                                } else {
                                    // Chỉ có tên file, thêm đường dẫn
                                    $fullImagePath = BASE_URL . 'public/uploads/tours/' . $imagePath;
                                    $fullImagePathWithoutPublic = BASE_URL . 'uploads/tours/' . $imagePath;
                                }
                                
                                // Đảm bảo không có dấu / thừa
                                $fullImagePath = str_replace('//', '/', $fullImagePath);
                                $fullImagePathWithoutPublic = str_replace('//', '/', $fullImagePathWithoutPublic);
                                
                                // Debug: Log đường dẫn và kiểm tra file (chỉ trong development)
                                if ($isLocal) {
                                    error_log('Image #' . ($index + 1) . ' - DB path: ' . $img['image_path'] . ' -> URL: ' . $fullImagePath);
                                    // Kiểm tra file có tồn tại không
                                    $physicalPath = BASE_PATH . '/public/' . $imagePath;
                                    if (file_exists($physicalPath)) {
                                        error_log('  -> File exists: ' . $physicalPath);
                                    } else {
                                        error_log('  -> File NOT found: ' . $physicalPath);
                                    }
                                }
                            ?>
                                <div class="col-md-4 col-lg-3">
                                    <div class="position-relative">
                                        <img src="<?= htmlspecialchars($fullImagePath) ?>" 
                                             class="img-fluid rounded shadow-sm cursor-pointer tour-image-thumb"
                                             style="width: 100%; height: 200px; object-fit: cover; cursor: pointer;"
                                             alt="Tour image <?= $index + 1 ?>"
                                             onerror="console.error('Image failed: <?= htmlspecialchars($fullImagePath) ?>'); var fallback='<?= htmlspecialchars($fullImagePathWithoutPublic) ?>'; if(this.src !== fallback) { console.log('Trying fallback:', fallback); this.src=fallback; }"
                                             data-bs-toggle="modal"
                                             data-bs-target="#imageModal"
                                             data-image-src="<?= htmlspecialchars($fullImagePath) ?>"
                                             onclick="openImageModal('<?= htmlspecialchars($fullImagePath) ?>')">
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <hr>
                <?php else: ?>
                    <div class="alert alert-info mb-4">
                        <i class="bi bi-info-circle me-2"></i>Tour này chưa có hình ảnh.
                    </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-6">
                        <dl class="row mb-0">
                            <dt class="col-sm-4">Tên tour</dt>
                            <dd class="col-sm-8">
                                <strong><?= htmlspecialchars($tour['name'] ?? 'N/A') ?></strong>
                            </dd>

                            <dt class="col-sm-4">Danh mục</dt>
                            <dd class="col-sm-8">
                                <?= htmlspecialchars($tour['category_name'] ?? 'N/A') ?>
                            </dd>

                            <dt class="col-sm-4">Giá</dt>
                            <dd class="col-sm-8">
                                <?php if ($tour['price']): ?>
                                    <strong class="text-primary"><?= number_format($tour['price'], 0, ',', '.') ?> đ</strong>
                                <?php else: ?>
                                    <span class="text-muted">Chưa có</span>
                                <?php endif; ?>
                            </dd>

                            <dt class="col-sm-4">Trạng thái</dt>
                            <dd class="col-sm-8">
                                <?php if ((int)($tour['status'] ?? 1) === 1): ?>
                                    <span class="badge bg-success">Hoạt động</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Ẩn</span>
                                <?php endif; ?>
                            </dd>
                        </dl>
                    </div>
                    <div class="col-md-6">
                        <dl class="row mb-0">
                            <dt class="col-sm-4">Ngày tạo</dt>
                            <dd class="col-sm-8">
                                <?php if ($tour['created_at']): ?>
                                    <?= date('d/m/Y H:i', strtotime($tour['created_at'])) ?>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </dd>

                            <dt class="col-sm-4">Cập nhật lần cuối</dt>
                            <dd class="col-sm-8">
                                <?php if ($tour['updated_at']): ?>
                                    <?= date('d/m/Y H:i', strtotime($tour['updated_at'])) ?>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </dd>
                        </dl>
                    </div>
                </div>

                <?php if ($tour['description']): ?>
                    <hr>
                    <div>
                        <h5>Mô tả</h5>
                        <div class="text-muted">
                            <?= nl2br(htmlspecialchars($tour['description'])) ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($tour['schedule']): ?>
                    <hr>
                    <div>
                        <h5>Lịch trình</h5>
                        <div class="text-muted">
                            <?= nl2br(htmlspecialchars($tour['schedule'])) ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();

view('layouts.AdminLayout', [
    'title' => $title ?? 'Chi tiết tour',
    'pageTitle' => 'Chi tiết tour',
    'content' => $content,
    'breadcrumb' => [
        ['label' => 'Trang chủ', 'url' => BASE_URL . 'home'],
        ['label' => 'Tour', 'url' => BASE_URL . 'admin/tours'],
        ['label' => 'Chi tiết', 'url' => BASE_URL . 'admin/tours/show&id=' . $tour['id'], 'active' => true],
    ],
]);
?>

<!-- Modal xem ảnh lớn -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageModalLabel">
                    <i class="bi bi-image me-2"></i>Hình ảnh tour
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-0">
                <img id="modalImage" src="" class="img-fluid" alt="Tour image" style="max-height: 80vh; width: 100%; object-fit: contain;">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<script>
function openImageModal(imageSrc) {
    document.getElementById('modalImage').src = imageSrc;
}

// Thêm hiệu ứng hover cho ảnh
document.addEventListener('DOMContentLoaded', function() {
    const images = document.querySelectorAll('.tour-image-thumb');
    images.forEach(img => {
        img.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.05)';
            this.style.transition = 'transform 0.3s ease';
        });
        img.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
    });
});
</script>

