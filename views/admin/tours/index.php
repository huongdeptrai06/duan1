<?php
ob_start();
?>
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white border-0">
                <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-2">
                    <div>
                        <h3 class="card-title mb-1">
                            <i class="bi bi-airplane-engines me-2"></i>
                            Danh sách tour
                        </h3>
                        <small class="text-muted">Xem danh sách các tour du lịch.</small>
                    </div>
                    <?php if (isAdmin()): ?>
                    <div class="d-flex gap-2">
                        <a href="<?= BASE_URL ?>admin/tours/create" class="btn btn-primary">
                            <i class="bi bi-plus-lg me-1"></i> Thêm tour mới
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <?php if (!empty($_GET['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <?php
                        $successMsg = match($_GET['success']) {
                            '1' => 'Tour đã được thêm thành công!',
                            'updated' => 'Tour đã được cập nhật thành công!',
                            'deleted' => 'Tour đã được xóa thành công!',
                            default => 'Thao tác thành công!',
                        };
                        echo htmlspecialchars($successMsg);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (!empty($_GET['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="bi bi-exclamation-circle-fill me-2"></i>
                        <?php
                        $errorMsg = match($_GET['error']) {
                            'db' => 'Không thể kết nối cơ sở dữ liệu.',
                            'notfound' => 'Tour không tồn tại.',
                            'delete' => 'Không thể xóa tour. Vui lòng thử lại.',
                            default => 'Có lỗi xảy ra.',
                        };
                        echo htmlspecialchars($errorMsg);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0 ps-3">
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if (empty($tours)): ?>
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        Chưa có tour nào.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Tên tour</th>
                                    <th>Danh mục</th>
                                    <th>Giá</th>
                                    <th>Trạng thái</th>
                                    <th class="text-end">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tours as $index => $tour): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td>
                                            <a href="<?= BASE_URL ?>admin/tours/show&id=<?= $tour['id'] ?>" class="fw-semibold text-decoration-none">
                                                <?= htmlspecialchars($tour['name'] ?? 'N/A') ?>
                                            </a>
                                        </td>
                                        <td><?= htmlspecialchars($tour['category_name'] ?? 'N/A') ?></td>
                                        <td>
                                            <?php if ($tour['price']): ?>
                                                <?= number_format($tour['price'], 0, ',', '.') ?> đ
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ((int)($tour['status'] ?? 1) === 1): ?>
                                                <span class="badge bg-success">Hoạt động</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Ẩn</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <a href="<?= BASE_URL ?>admin/tours/show&id=<?= $tour['id'] ?>" class="btn btn-sm btn-outline-info" title="Xem chi tiết">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <?php if (isAdmin()): ?>
                                            <a href="<?= BASE_URL ?>admin/tours/edit&id=<?= $tour['id'] ?>" class="btn btn-sm btn-outline-primary" title="Chỉnh sửa">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <form action="<?= BASE_URL ?>admin/tours/delete" method="post" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa tour này?');">
                                                <input type="hidden" name="id" value="<?= $tour['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Xóa">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();

view('layouts.AdminLayout', [
    'title' => $title ?? 'Danh sách tour',
    'pageTitle' => 'Danh sách tour',
    'content' => $content,
    'breadcrumb' => [
        ['label' => 'Trang chủ', 'url' => BASE_URL . 'home'],
        ['label' => 'Tour', 'url' => BASE_URL . 'admin/tours', 'active' => true],
    ],
]);
?>

