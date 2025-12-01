<?php
ob_start();
?>
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h3 class="card-title mb-0">Chỉnh sửa hướng dẫn viên</h3>
            </div>
            <div class="card-body">
                <form action="<?= BASE_URL ?>admin/guides/update" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($guide['id'] ?? '') ?>">
                    <div class="mb-3">
                        <label class="form-label">Họ và tên</label>
                        <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($guide['full_name'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ngày sinh</label>
                        <input type="date" name="dob" class="form-control" value="<?= htmlspecialchars($guide['dob'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ảnh</label>
                        <?php if (!empty($guide['photo'])): ?>
                            <div class="mb-2"><img src="<?= asset($guide['photo']) ?>" alt="photo" style="max-height:120px"></div>
                        <?php endif; ?>
                        <input type="file" name="photo" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Liên hệ</label>
                        <input type="text" name="contact" class="form-control" value="<?= htmlspecialchars($guide['contact'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Chứng chỉ chuyên môn</label>
                        <textarea name="certificates" class="form-control" rows="2"><?= htmlspecialchars($guide['certificates'] ?? '') ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ngôn ngữ</label>
                        <input type="text" name="languages" class="form-control" value="<?= htmlspecialchars($guide['languages'] ?? '') ?>" placeholder="VD: Tiếng Việt, Tiếng Anh">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kinh nghiệm</label>
                        <textarea name="experience" class="form-control" rows="3"><?= htmlspecialchars($guide['experience'] ?? '') ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Lịch sử dẫn tour</label>
                        <textarea name="tour_history" class="form-control" rows="3"><?= htmlspecialchars($guide['tour_history'] ?? '') ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Đánh giá (số)</label>
                        <input type="number" step="0.1" name="rating" class="form-control" value="<?= htmlspecialchars($guide['rating'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tình trạng sức khoẻ</label>
                        <input type="text" name="health_status" class="form-control" value="<?= htmlspecialchars($guide['health_status'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nhóm</label>
                        <select name="group" class="form-control">
                            <option value="noidia" <?= (($guide['group'] ?? '') === 'noidia') ? 'selected' : '' ?>>Nội địa</option>
                            <option value="quocte" <?= (($guide['group'] ?? '') === 'quocte') ? 'selected' : '' ?>>Quốc tế</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Trạng thái</label>
                        <select name="status" class="form-control">
                            <option value="1" <?= ((int)($guide['status'] ?? 1) === 1) ? 'selected' : '' ?>>Hoạt động</option>
                            <option value="0" <?= ((int)($guide['status'] ?? 1) === 0) ? 'selected' : '' ?>>Ẩn</option>
                        </select>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary">Lưu thay đổi</button>
                        <a href="<?= BASE_URL ?>admin/guides" class="btn btn-secondary">Hủy</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();

view('layouts.AdminLayout', [
    'title' => $title ?? 'Chỉnh sửa HDV',
    'pageTitle' => 'Chỉnh sửa HDV',
    'content' => $content,
    'breadcrumb' => [
        ['label' => 'Trang chủ', 'url' => BASE_URL . 'home'],
        ['label' => 'HDV', 'url' => BASE_URL . 'admin/guides'],
        ['label' => 'Chỉnh sửa', 'url' => BASE_URL . 'admin/guides/edit', 'active' => true],
    ],
]);
?>
