<?php
ob_start();
?>
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h3 class="card-title mb-0">Thêm hướng dẫn viên</h3>
            </div>
            <div class="card-body">
                <form action="<?= BASE_URL ?>admin/guides/store" method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">Họ và tên</label>
                        <input type="text" name="full_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ngày sinh</label>
                        <input type="date" name="dob" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ảnh</label>
                        <input type="file" name="photo" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Liên hệ</label>
                        <input type="text" name="contact" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Chứng chỉ chuyên môn</label>
                        <textarea name="certificates" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ngôn ngữ</label>
                        <input type="text" name="languages" class="form-control" placeholder="VD: Tiếng Việt, Tiếng Anh">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kinh nghiệm</label>
                        <textarea name="experience" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Lịch sử dẫn tour</label>
                        <textarea name="tour_history" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Đánh giá (số)</label>
                        <input type="number" step="0.1" name="rating" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tình trạng sức khoẻ</label>
                        <input type="text" name="health_status" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nhóm</label>
                        <select name="group" class="form-control">
                            <option value="noidia">Nội địa</option>
                            <option value="quocte">Quốc tế</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Trạng thái</label>
                        <select name="status" class="form-control">
                            <option value="1">Hoạt động</option>
                            <option value="0">Ẩn</option>
                        </select>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary">Lưu</button>
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
    'title' => $title ?? 'Thêm HDV',
    'pageTitle' => 'Thêm HDV',
    'content' => $content,
    'breadcrumb' => [
        ['label' => 'Trang chủ', 'url' => BASE_URL . 'home'],
        ['label' => 'HDV', 'url' => BASE_URL . 'admin/guides'],
        ['label' => 'Thêm', 'url' => BASE_URL . 'admin/guides/create', 'active' => true],
    ],
]);
?>
