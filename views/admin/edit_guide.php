<?php
ob_start();
$guide = $guide ?? [];
$profileData = $profileData ?? [];
$guideGroups = $guideGroups ?? [];
?>
<div class="row justify-content-center">
    <div class="col-lg-6 col-md-8">
        <?php if (!empty($errors)): ?>
        <div class="alert alert-danger" role="alert">
            <div class="d-flex align-items-center mb-2">
                <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                <strong>Không thể cập nhật</strong>
            </div>
            <ul class="mb-0 ps-3">
                <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-pencil-square me-2"></i>Chỉnh sửa hướng dẫn viên
                </h5>
                <a href="<?= BASE_URL ?>admin-guide-list" class="btn btn-light btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Danh sách
                </a>
            </div>
            <div class="card-body">
                <form action="<?= BASE_URL ?>admin-guide-update" method="post" novalidate>
                    <input type="hidden" name="id" value="<?= (int)($guide['id'] ?? 0) ?>" />

                    <div class="mb-3">
                        <label for="guideName" class="form-label fw-semibold">Họ và tên</label>
                        <input type="text"
                               class="form-control"
                               id="guideName"
                               name="name"
                               value="<?= htmlspecialchars($guide['name'] ?? '') ?>"
                               placeholder="Nguyễn Văn A"
                               required />
                    </div>

                    <div class="mb-3">
                        <label for="guideEmail" class="form-label fw-semibold">Email</label>
                        <input type="email"
                               class="form-control"
                               id="guideEmail"
                               name="email"
                               value="<?= htmlspecialchars($guide['email'] ?? '') ?>"
                               placeholder="huongdanvien@example.com"
                               required />
                    </div>

                    <div class="mb-3">
                        <label for="guideStatus" class="form-label fw-semibold">Trạng thái</label>
                        <select class="form-select" id="guideStatus" name="status">
                            <option value="1" <?= (int)($guide['status'] ?? 1) === 1 ? 'selected' : '' ?>>Đang hoạt động</option>
                            <option value="0" <?= (int)($guide['status'] ?? 1) === 0 ? 'selected' : '' ?>>Bị khóa</option>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="guidePassword" class="form-label fw-semibold">Mật khẩu mới (tùy chọn)</label>
                            <input type="password"
                                   class="form-control"
                                   id="guidePassword"
                                   name="password"
                                   placeholder="Để trống nếu không đổi" />
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="guidePasswordConfirm" class="form-label fw-semibold">Xác nhận mật khẩu</label>
                            <input type="password"
                                   class="form-control"
                                   id="guidePasswordConfirm"
                                   name="password_confirmation"
                                   placeholder="Nhập lại mật khẩu mới" />
                        </div>
                    </div>

                    <div class="alert alert-info small d-flex align-items-start">
                        <i class="bi bi-info-circle-fill me-2 mt-1"></i>
                        <span>Nếu không nhập mật khẩu mới, thông tin đăng nhập của hướng dẫn viên sẽ được giữ nguyên.</span>
                    </div>

                    <hr class="my-4">

                    <h6 class="text-uppercase text-muted fw-semibold small mb-3">Hồ sơ hướng dẫn viên</h6>

                    <div class="mb-3">
                        <label for="profileFullName" class="form-label">Họ tên đầy đủ</label>
                        <input type="text"
                               class="form-control"
                               id="profileFullName"
                               name="profile_full_name"
                               value="<?= htmlspecialchars($profileData['full_name'] ?? ($guide['name'] ?? '')) ?>"
                               required />
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="profileDob" class="form-label">Ngày sinh</label>
                            <input type="date"
                                   class="form-control"
                                   id="profileDob"
                                   name="profile_dob"
                                   value="<?= htmlspecialchars($profileData['dob'] ?? '') ?>" />
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="profileGender" class="form-label">Giới tính</label>
                            <?php
                            $gender = $profileData['gender'] ?? '';
                            $genderOptions = [
                                '' => 'Chưa xác định',
                                'Nam' => 'Nam',
                                'Nữ' => 'Nữ',
                                'Khác' => 'Khác',
                            ];
                            ?>
                            <select class="form-select" id="profileGender" name="profile_gender">
                                <?php foreach ($genderOptions as $value => $label): ?>
                                    <option value="<?= $value ?>" <?= $gender === $value ? 'selected' : '' ?>><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="profileAvatar" class="form-label">Ảnh đại diện (URL)</label>
                        <input type="url"
                               class="form-control"
                               id="profileAvatar"
                               name="profile_avatar_url"
                               value="<?= htmlspecialchars($profileData['avatar_url'] ?? '') ?>" />
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="profilePhone" class="form-label">Số điện thoại</label>
                            <input type="text"
                                   class="form-control"
                                   id="profilePhone"
                                   name="profile_phone"
                                   value="<?= htmlspecialchars($profileData['phone'] ?? '') ?>" />
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="profileEmail" class="form-label">Email liên hệ</label>
                            <?php
                            $profileEmail = $profileData['contact_email'] ?? $profileData['email'] ?? ($guide['email'] ?? '');
                            ?>
                            <input type="email"
                                   class="form-control"
                                   id="profileEmail"
                                   name="profile_email"
                                   value="<?= htmlspecialchars($profileEmail) ?>" />
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="profileIdNumber" class="form-label">CMND/CCCD</label>
                            <input type="text"
                                   class="form-control"
                                   id="profileIdNumber"
                                   name="profile_id_number"
                                   value="<?= htmlspecialchars($profileData['id_number'] ?? '') ?>" />
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="profileGroup" class="form-label">Nhóm hướng dẫn</label>
                            <select class="form-select" id="profileGroup" name="profile_guide_group">
                                <option value="">Chưa phân nhóm</option>
                                <?php foreach ($guideGroups as $value => $label): ?>
                                    <option value="<?= htmlspecialchars($value) ?>" <?= ($profileData['guide_group'] ?? '') === $value ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($label) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="profileAddress" class="form-label">Địa chỉ</label>
                        <input type="text"
                               class="form-control"
                               id="profileAddress"
                               name="profile_address"
                               value="<?= htmlspecialchars($profileData['address'] ?? '') ?>" />
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="profileLicense" class="form-label">Chứng chỉ nghề nghiệp</label>
                            <input type="text"
                                   class="form-control"
                                   id="profileLicense"
                                   name="profile_license"
                                   value="<?= htmlspecialchars($profileData['license'] ?? '') ?>" />
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="profileGuideType" class="form-label">Loại hình hướng dẫn</label>
                            <input type="text"
                                   class="form-control"
                                   id="profileGuideType"
                                   name="profile_guide_type"
                                   value="<?= htmlspecialchars($profileData['guide_type'] ?? '') ?>" />
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="profileLanguages" class="form-label">Ngôn ngữ sử dụng</label>
                            <input type="text"
                                   class="form-control"
                                   id="profileLanguages"
                                   name="profile_languages"
                                   value="<?= htmlspecialchars($profileData['languages'] ?? '') ?>" />
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="profileExperienceYears" class="form-label">Số năm kinh nghiệm</label>
                            <input type="number"
                                   min="0"
                                   class="form-control"
                                   id="profileExperienceYears"
                                   name="profile_experience_years"
                                   value="<?= htmlspecialchars($profileData['experience_years'] ?? '') ?>" />
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="profileRating" class="form-label">Đánh giá năng lực (0-5)</label>
                            <input type="number"
                                   step="0.1"
                                   min="0"
                                   max="5"
                                   class="form-control"
                                   id="profileRating"
                                   name="profile_rating"
                                   value="<?= htmlspecialchars($profileData['rating'] ?? '') ?>" />
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="profileExperienceDetail" class="form-label">Chi tiết kinh nghiệm</label>
                        <textarea class="form-control"
                                  id="profileExperienceDetail"
                                  name="profile_experience_detail"
                                  rows="3"><?= htmlspecialchars($profileData['experience_detail'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="profileNotableTours" class="form-label">Các tour tiêu biểu</label>
                        <textarea class="form-control"
                                  id="profileNotableTours"
                                  name="profile_notable_tours"
                                  rows="3"><?= htmlspecialchars($profileData['notable_tours'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="profileTourHistory" class="form-label">Lịch sử dẫn tour</label>
                        <textarea class="form-control"
                                  id="profileTourHistory"
                                  name="profile_tour_history"
                                  rows="3"><?= htmlspecialchars($profileData['tour_history'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="profileStrengths" class="form-label">Đánh giá năng lực/điểm mạnh</label>
                        <textarea class="form-control"
                                  id="profileStrengths"
                                  name="profile_strengths"
                                  rows="2"><?= htmlspecialchars($profileData['strengths'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-4">
                        <label for="profileHealthStatus" class="form-label">Tình trạng sức khỏe</label>
                        <textarea class="form-control"
                                  id="profileHealthStatus"
                                  name="profile_health_status"
                                  rows="2"><?= htmlspecialchars($profileData['health_status'] ?? '') ?></textarea>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="<?= BASE_URL ?>admin-guide-list" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-1"></i>Hủy
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>Lưu thay đổi
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
    'title' => $title ?? 'Chỉnh sửa hướng dẫn viên',
    'pageTitle' => $pageTitle ?? 'Chỉnh sửa hướng dẫn viên',
    'breadcrumb' => $breadcrumb ?? [],
    'content' => $content,
]);
?>

