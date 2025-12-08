<?php
ob_start();
$formData = $formData ?? [];
$guide = $guide ?? null;
$guideGroups = $guideGroups ?? [];
$errors = $errors ?? [];

// Nếu có guide thì merge vào formData
if ($guide && empty($formData['id'])) {
    $formData = array_merge($formData, $guide);
}
?>
<div class="row">
    <div class="col-12 col-lg-11 col-xl-11 mx-auto">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title mb-0">
                    <i class="bi bi-pencil-square me-2"></i>
                    Sửa thông tin hướng dẫn viên
                </h3>
            </div>
            <div class="card-body p-4 p-lg-5">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                            <strong>Có lỗi xảy ra</strong>
                        </div>
                        <ul class="mb-0 ps-3">
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form action="<?= BASE_URL ?>admin-guide-update" method="post" enctype="multipart/form-data" novalidate>
                    <input type="hidden" name="id" value="<?= htmlspecialchars($formData['id'] ?? '') ?>">

                    <div class="mb-3">
                        <label for="guideName" class="form-label fw-semibold">Họ và tên <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                            <input type="text"
                                   class="form-control"
                                   id="guideName"
                                   name="name"
                                   value="<?= htmlspecialchars($formData['name'] ?? '') ?>"
                                   placeholder="Nhập họ tên HDV"
                                   required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="guideEmail" class="form-label fw-semibold">Email đăng nhập <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email"
                                   class="form-control"
                                   id="guideEmail"
                                   name="email"
                                   value="<?= htmlspecialchars($formData['email'] ?? '') ?>"
                                   placeholder="Nhập email liên hệ"
                                   required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="guideGroup" class="form-label fw-semibold">Loại tour</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-tags"></i></span>
                            <select class="form-select" id="guideGroup" name="guide_group" required>
                                <option value="">-- Chọn loại tour --</option>
                                <?php 
                                // Map từ group trong guides sang guide_group nếu có
                                $currentGroup = $formData['guide_group'] ?? $formData['group'] ?? '';
                                foreach ($guideGroups as $key => $label): 
                                ?>
                                    <option value="<?= htmlspecialchars($key) ?>" 
                                            <?= $currentGroup === $key ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($label) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <small class="text-muted">Chọn loại tour mà hướng dẫn viên chuyên dẫn</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Trạng thái</label>
                        <div class="form-check">
                            <input class="form-check-input" 
                                   type="radio" 
                                   name="status" 
                                   id="statusActive" 
                                   value="1"
                                   <?= ((int)($formData['status'] ?? 1)) === 1 ? 'checked' : '' ?>>
                            <label class="form-check-label" for="statusActive">
                                <span class="badge bg-success">Hoạt động</span>
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" 
                                   type="radio" 
                                   name="status" 
                                   id="statusInactive" 
                                   value="0"
                                   <?= ((int)($formData['status'] ?? 1)) === 0 ? 'checked' : '' ?>>
                            <label class="form-check-label" for="statusInactive">
                                <span class="badge bg-secondary">Đã khóa</span>
                            </label>
                        </div>
                    </div>

                    <hr class="my-4">
                    
                    <h5 class="mb-3"><i class="bi bi-file-person me-2"></i>Thông tin hồ sơ hướng dẫn viên</h5>
                    
                    <div class="mb-3">
                        <label for="fullName" class="form-label fw-semibold">Họ tên đầy đủ</label>
                        <input type="text"
                               class="form-control"
                               id="fullName"
                               name="full_name"
                               value="<?= htmlspecialchars($formData['full_name'] ?? $formData['name'] ?? '') ?>"
                               placeholder="Họ tên đầy đủ">
                    </div>
                    
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="dob" class="form-label">Ngày sinh</label>
                            <input type="date"
                                   class="form-control"
                                   id="dob"
                                   name="dob"
                                   value="<?= htmlspecialchars($formData['dob'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="contact" class="form-label">Số điện thoại</label>
                            <input type="text"
                                   class="form-control"
                                   id="contact"
                                   name="contact"
                                   value="<?= htmlspecialchars($formData['contact'] ?? '') ?>"
                                   placeholder="0912345678">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="photo" class="form-label">Ảnh đại diện</label>
                        <?php 
                        $avatarPath = $formData['photo'] ?? $formData['avatar'] ?? null;
                        if (!empty($avatarPath)): 
                            // Nếu là tên file đơn giản, thêm path
                            $imgSrc = strpos($avatarPath, 'uploads/') === 0 ? BASE_URL . $avatarPath : BASE_URL . 'uploads/guides/' . $avatarPath;
                        ?>
                            <div class="mb-2">
                                <img src="<?= htmlspecialchars($imgSrc) ?>" 
                                     alt="Ảnh hiện tại" 
                                     class="img-thumbnail" 
                                     style="max-width: 150px; max-height: 150px;">
                            </div>
                        <?php endif; ?>
                        <input type="file"
                               class="form-control"
                               id="photo"
                               name="photo"
                               accept="image/*">
                        <small class="text-muted">Để trống nếu không muốn thay đổi ảnh</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="certificates" class="form-label">Chứng chỉ/Giấy phép</label>
                        <textarea class="form-control"
                                  id="certificates"
                                  name="certificates"
                                  rows="2"
                                  placeholder="VD: HDV quốc tế, HDV nội địa"><?= htmlspecialchars($formData['certificates'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="languages" class="form-label">Ngôn ngữ sử dụng</label>
                        <input type="text"
                               class="form-control"
                               id="languages"
                               name="languages"
                               value="<?= htmlspecialchars($formData['languages'] ?? '') ?>"
                               placeholder="VD: Tiếng Anh, Tiếng Việt">
                    </div>
                    
                    <div class="mb-3">
                        <label for="experience" class="form-label">Kinh nghiệm</label>
                        <textarea class="form-control"
                                  id="experience"
                                  name="experience"
                                  rows="3"
                                  placeholder="VD: 5 năm kinh nghiệm dẫn tour"><?= htmlspecialchars($formData['experience'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="tourHistory" class="form-label">Lịch sử dẫn tour</label>
                        <textarea class="form-control"
                                  id="tourHistory"
                                  name="tour_history"
                                  rows="3"
                                  placeholder="Mô tả các tour đã dẫn"><?= htmlspecialchars($formData['tour_history'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="rating" class="form-label">Đánh giá năng lực (0-5)</label>
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   max="5"
                                   class="form-control"
                                   id="rating"
                                   name="rating"
                                   value="<?= htmlspecialchars($formData['rating'] ?? '') ?>"
                                   placeholder="4.50">
                            <small class="text-muted">Định dạng: 4.50 (2 chữ số thập phân)</small>
                        </div>
                        <div class="col-md-6">
                            <label for="healthStatus" class="form-label">Tình trạng sức khỏe</label>
                            <input type="text"
                                   class="form-control"
                                   id="healthStatus"
                                   name="health_status"
                                   value="<?= htmlspecialchars($formData['health_status'] ?? '') ?>"
                                   placeholder="VD: Tốt, Khá">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="speciality" class="form-label">Chuyên môn</label>
                        <input type="text"
                               class="form-control"
                               id="speciality"
                               name="speciality"
                               value="<?= htmlspecialchars($formData['speciality'] ?? '') ?>"
                               placeholder="VD: chuyên tuyến miền Bắc, chuyên khách đoàn"
                               maxlength="100">
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="<?= BASE_URL ?>admin-guide-list" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i>
                            Hủy
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-1"></i>
                            Cập nhật
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
    'title' => $title ?? 'Sửa thông tin hướng dẫn viên',
    'pageTitle' => 'Sửa thông tin hướng dẫn viên',
    'content' => $content,
    'breadcrumb' => [
        ['label' => 'Trang chủ', 'url' => BASE_URL . 'home'],
        ['label' => 'Danh sách HDV', 'url' => BASE_URL . 'admin-guide-list'],
        ['label' => 'Sửa thông tin', 'url' => BASE_URL . 'admin-guide-edit', 'active' => true],
    ],
]);
?>
