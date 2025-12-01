<?php
ob_start();
$guide = $guide ?? null;
$errors = $errors ?? [];
$guideGroups = $guideGroups ?? [];

function displayValue(?string $value, string $fallback = 'Chưa cập nhật'): string {
    $value = trim((string)$value);
    return $value !== '' ? htmlspecialchars($value) : $fallback;
}
?>
<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <?= htmlspecialchars(implode(' ', $errors)) ?>
    </div>
<?php endif; ?>

<?php if (isset($_GET['updated']) && $_GET['updated'] == '1'): ?>
    <div class="alert alert-success">
        <i class="bi bi-check-circle-fill me-2"></i>
        Đã cập nhật thông tin hướng dẫn viên thành công.
    </div>
<?php endif; ?>

<?php if ($guide): ?>
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <div class="d-flex flex-column flex-md-row align-items-center gap-3">
            <div class="position-relative">
                <?php 
                $avatarPath = $guide['photo'] ?? $guide['avatar'] ?? null;
                if (!empty($avatarPath)): 
                    // Nếu là tên file đơn giản, thêm path
                    $imgSrc = strpos($avatarPath, 'uploads/') === 0 ? BASE_URL . $avatarPath : BASE_URL . 'uploads/guides/' . $avatarPath;
                ?>
                    <img src="<?= htmlspecialchars($imgSrc) ?>"
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
                <h4 class="mb-1"><?= displayValue($guide['full_name'] ?? $guide['name'] ?? null) ?></h4>
                <p class="text-muted mb-2">Hướng dẫn viên</p>
                <div class="d-flex flex-wrap justify-content-center justify-content-md-start gap-2">
                    <?php
                    $groupKey = $guide['guide_group'] ?? null;
                    $groupLabel = $groupKey && isset($guideGroups[$groupKey])
                        ? $guideGroups[$groupKey]
                        : 'Chưa phân nhóm';
                    ?>
                    <span class="badge bg-primary-subtle text-primary fw-semibold">
                        <?= htmlspecialchars($groupLabel) ?>
                    </span>
                    <?php if (!empty($guide['rating'])): ?>
                        <span class="badge bg-warning-subtle text-warning fw-semibold">
                            <i class="bi bi-star-fill me-1"></i><?= number_format((float)$guide['rating'], 1) ?>/5
                        </span>
                    <?php endif; ?>
                    <?php $isActive = (int)($guide['status'] ?? 0) === 1; ?>
                    <span class="badge <?= $isActive ? 'bg-success' : 'bg-secondary' ?>">
                        <?= $isActive ? 'Đang hoạt động' : 'Bị khóa' ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-person-badge me-2"></i>Thông tin tài khoản</h5>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Họ tên</dt>
                    <dd class="col-sm-8"><?= displayValue($guide['full_name'] ?? $guide['name'] ?? null) ?></dd>

                    <dt class="col-sm-4">Email</dt>
                    <dd class="col-sm-8"><?= displayValue($guide['email'] ?? null) ?></dd>

                    <dt class="col-sm-4">Ngày sinh</dt>
                    <dd class="col-sm-8">
                        <?php 
                        $dob = $guide['birthdate'] ?? $guide['dob'] ?? null;
                        if ($dob && $dob !== '0000-00-00' && $dob !== '' && $dob !== null) {
                            echo date('d/m/Y', strtotime($dob));
                        } else {
                            echo 'Chưa cập nhật';
                        }
                        ?>
                    </dd>

                    <dt class="col-sm-4">Số điện thoại</dt>
                    <dd class="col-sm-8"><?= displayValue($guide['phone'] ?? $guide['contact'] ?? null) ?></dd>

                    <dt class="col-sm-4">Chứng chỉ</dt>
                    <dd class="col-sm-8"><?= displayValue($guide['certificate'] ?? $guide['certificates'] ?? null) ?></dd>

                    <dt class="col-sm-4">Ngôn ngữ</dt>
                    <dd class="col-sm-8"><?= displayValue($guide['languages'] ?? null) ?></dd>

                    <dt class="col-sm-4">Vai trò</dt>
                    <dd class="col-sm-8">
                        <span class="badge bg-info">Hướng dẫn viên</span>
                    </dd>

                    <dt class="col-sm-4">Trạng thái</dt>
                    <dd class="col-sm-8">
                        <?php if ((int)($guide['status'] ?? 0) === 1): ?>
                            <span class="badge bg-success">Hoạt động</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Đã khóa</span>
                        <?php endif; ?>
                    </dd>
                </dl>
            </div>
        </div>
    </div>
    <div class="col-lg-6 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Thông tin bổ sung</h5>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-5">Nhóm chuyên môn</dt>
                    <dd class="col-sm-7">
                        <?php
                        $groupKey = $guide['guide_group'] ?? $guide['group'] ?? $guide['group_type'] ?? null;
                        $groupLabel = $groupKey && isset($guideGroups[$groupKey])
                            ? $guideGroups[$groupKey]
                            : 'Chưa phân nhóm';
                        ?>
                        <span class="badge bg-info-subtle text-info fw-semibold">
                            <?= htmlspecialchars($groupLabel) ?>
                        </span>
                    </dd>

                    <dt class="col-sm-5">Kinh nghiệm</dt>
                    <dd class="col-sm-7"><?= displayValue($guide['experience'] ?? null) ?></dd>

                    <dt class="col-sm-5">Lịch sử dẫn tour</dt>
                    <dd class="col-sm-7"><?= displayValue($guide['history'] ?? $guide['tour_history'] ?? null) ?></dd>

                    <dt class="col-sm-5">Đánh giá năng lực</dt>
                    <dd class="col-sm-7">
                        <?php if (!empty($guide['rating'])): ?>
                            <span class="badge bg-warning"><?= number_format((float)$guide['rating'], 1) ?>/5</span>
                        <?php else: ?>
                            Chưa cập nhật
                        <?php endif; ?>
                    </dd>

                    <dt class="col-sm-5">Tình trạng sức khỏe</dt>
                    <dd class="col-sm-7"><?= displayValue($guide['health_status'] ?? null) ?></dd>

                    <dt class="col-sm-5">Chuyên môn</dt>
                    <dd class="col-sm-7"><?= displayValue($guide['speciality'] ?? null) ?></dd>

                    <dt class="col-sm-5">Ngày tạo</dt>
                    <dd class="col-sm-7">
                        <?= $guide['created_at'] 
                            ? date('d/m/Y H:i', strtotime($guide['created_at'])) 
                            : '-' ?>
                    </dd>

                    <dt class="col-sm-5">Cập nhật lần cuối</dt>
                    <dd class="col-sm-7">
                        <?= $guide['updated_at'] 
                            ? date('d/m/Y H:i', strtotime($guide['updated_at'])) 
                            : '-' ?>
                    </dd>
                </dl>
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
<?php else: ?>
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle me-2"></i>
        Không tìm thấy thông tin hướng dẫn viên.
    </div>
    <a href="<?= BASE_URL ?>admin-guide-list" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Quay lại danh sách
    </a>
<?php endif; ?>
<?php
$content = ob_get_clean();

view('layouts.AdminLayout', [
    'title' => $title ?? 'Chi tiết hướng dẫn viên',
    'pageTitle' => $pageTitle ?? 'Chi tiết hướng dẫn viên',
    'breadcrumb' => [
        ['label' => 'Trang chủ', 'url' => BASE_URL . 'home'],
        ['label' => 'Danh sách HDV', 'url' => BASE_URL . 'admin-guide-list'],
        ['label' => 'Chi tiết', 'url' => BASE_URL . 'admin-guide-detail', 'active' => true],
    ],
    'content' => $content,
]);
?>

