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
                <div>
                    <a href="<?= BASE_URL ?>admin/tours" class="btn btn-light btn-sm">
                        <i class="bi bi-arrow-left me-1"></i>Quay lại
                    </a>
                </div>
            </div>
            <div class="card-body">
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

