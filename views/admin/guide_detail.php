<?php
ob_start();
$profile = $profile ?? [];

function displayValue(?string $value, string $fallback = 'Chưa cập nhật'): string {
    $value = trim((string)$value);
    return $value !== '' ? htmlspecialchars($value) : $fallback;
}

$avatarUrl = trim($profile['avatar_url'] ?? '');
$rating = $profile['rating'] ?? null;
$health = $profile['health_status'] ?? null;
$groupLabel = $profile['guide_group_label'] ?? null;
?>
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body d-flex flex-column flex-md-row align-items-center gap-3">
        <div class="position-relative">
            <?php if ($avatarUrl !== ''): ?>
                <img src="<?= htmlspecialchars($avatarUrl) ?>"
                     alt="Ảnh hướng dẫn viên"
                     class="rounded-circle border"
                     style="width: 120px; height: 120px; object-fit: cover;">
            <?php else: ?>
                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center border"
                     style="width: 120px; height: 120px;">
                    <i class="bi bi-person-bounding-box fs-1 text-muted"></i>
                </div>
            <?php endif; ?>
        </div>
        <div class="flex-grow-1 text-center text-md-start">
            <h4 class="mb-1"><?= displayValue($profile['full_name'] ?? null) ?></h4>
            <p class="text-muted mb-2"><?= displayValue($profile['guide_type'] ?? null) ?></p>
            <div class="d-flex flex-wrap justify-content-center justify-content-md-start gap-2">
                <span class="badge bg-primary-subtle text-primary fw-semibold">
                    <?= htmlspecialchars($groupLabel ?? 'Chưa phân nhóm') ?>
                </span>
                <?php if ($rating !== null && $rating !== ''): ?>
                <span class="badge bg-warning-subtle text-warning fw-semibold">
                    <i class="bi bi-star-fill me-1"></i><?= number_format((float)$rating, 1) ?>/5 năng lực
                </span>
                <?php endif; ?>
                <?php if ($health !== null && trim($health) !== ''): ?>
                <span class="badge bg-success-subtle text-success fw-semibold">
                    <i class="bi bi-heart-pulse-fill me-1"></i><?= htmlspecialchars($health) ?>
                </span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-person-badge me-2"></i>Thông tin cá nhân</h5>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Họ tên đầy đủ</dt>
                    <dd class="col-sm-8"><?= displayValue($profile['full_name'] ?? null) ?></dd>

                    <dt class="col-sm-4">Ngày sinh</dt>
                    <dd class="col-sm-8"><?= displayValue($profile['dob'] ?? null) ?></dd>

                    <dt class="col-sm-4">Giới tính</dt>
                    <dd class="col-sm-8"><?= displayValue($profile['gender'] ?? null) ?></dd>

                    <dt class="col-sm-4">CMND/CCCD</dt>
                    <dd class="col-sm-8"><?= displayValue($profile['id_number'] ?? null) ?></dd>

                    <dt class="col-sm-4">Địa chỉ</dt>
                    <dd class="col-sm-8"><?= displayValue($profile['address'] ?? null) ?></dd>

                    <dt class="col-sm-4">Số điện thoại</dt>
                    <dd class="col-sm-8"><?= displayValue($profile['phone'] ?? null) ?></dd>

                    <dt class="col-sm-4">Email liên hệ</dt>
                    <dd class="col-sm-8"><?= displayValue($profile['email'] ?? null) ?></dd>
                </dl>
            </div>
        </div>
    </div>
    <div class="col-lg-6 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="bi bi-mortarboard-fill me-2"></i>Thông tin nghề nghiệp</h5>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-5">Chứng chỉ/giấy phép</dt>
                    <dd class="col-sm-7"><?= displayValue($profile['license'] ?? null) ?></dd>

                    <dt class="col-sm-5">Loại hình hướng dẫn</dt>
                    <dd class="col-sm-7"><?= displayValue($profile['guide_type'] ?? null) ?></dd>

                    <dt class="col-sm-5">Ngôn ngữ sử dụng</dt>
                    <dd class="col-sm-7"><?= displayValue($profile['languages'] ?? null) ?></dd>

                    <dt class="col-sm-5">Nhóm phụ trách</dt>
                    <dd class="col-sm-7"><?= htmlspecialchars($groupLabel ?? 'Chưa phân nhóm') ?></dd>

                    <dt class="col-sm-5">Kinh nghiệm</dt>
                    <dd class="col-sm-7">
                        <?= displayValue($profile['experience_years'] ?? null) ?>
                        <?= isset($profile['experience_years']) && $profile['experience_years'] !== null ? 'năm' : '' ?>
                    </dd>

                    <dt class="col-sm-5">Chi tiết kinh nghiệm</dt>
                    <dd class="col-sm-7"><?= displayValue($profile['experience_detail'] ?? null) ?></dd>

                    <dt class="col-sm-5">Các tour tiêu biểu</dt>
                    <dd class="col-sm-7"><?= displayValue($profile['notable_tours'] ?? null) ?></dd>

                    <dt class="col-sm-5">Điểm mạnh</dt>
                    <dd class="col-sm-7"><?= displayValue($profile['strengths'] ?? null) ?></dd>
                </dl>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="bi bi-map-fill me-2"></i>Lịch sử & đánh giá</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h6 class="text-uppercase text-muted fw-semibold small">Lịch sử dẫn tour</h6>
                    <p class="mb-0"><?= displayValue($profile['tour_history'] ?? null) ?></p>
                </div>
                <div class="mb-3">
                    <h6 class="text-uppercase text-muted fw-semibold small">Đánh giá năng lực</h6>
                    <p class="mb-0">
                        <?php if ($rating !== null && $rating !== ''): ?>
                            <?= number_format((float)$rating, 1) ?>/5 – <?= displayValue($profile['strengths'] ?? null, 'Chưa có ghi chú') ?>
                        <?php else: ?>
                            Chưa cập nhật
                        <?php endif; ?>
                    </p>
                </div>
                <div>
                    <h6 class="text-uppercase text-muted fw-semibold small">Tình trạng sức khỏe</h6>
                    <p class="mb-0"><?= displayValue($health ?? null) ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="mt-3">
    <a href="<?= BASE_URL ?>admin-guide-list" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Quay lại danh sách
    </a>
    <a href="<?= BASE_URL . 'admin-guide-edit?id=' . urlencode($guide['id'] ?? '') ?>" class="btn btn-primary ms-2">
        <i class="bi bi-pencil-square me-1"></i>Chỉnh sửa
    </a>
</div>
<?php
$content = ob_get_clean();

view('layouts.AdminLayout', [
    'title' => $title ?? 'Thông tin hướng dẫn viên',
    'pageTitle' => $pageTitle ?? 'Thông tin hướng dẫn viên',
    'breadcrumb' => $breadcrumb ?? [],
    'content' => $content,
]);
?>

