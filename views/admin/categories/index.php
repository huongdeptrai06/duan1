<?php
ob_start();
?>
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white border-0">
                <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-2">
                <div>
                    <h3 class="card-title mb-0">
                        <i class="bi bi-tags-fill me-2"></i>
                        Danh sách danh mục
                    </h3>
                </div>
                <div class="d-flex gap-2">
                    <a href="<?= BASE_URL ?>admin/categories/create" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-1"></i> Thêm danh mục
                    </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <?php if (!empty($successMessage)): ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <?= htmlspecialchars($successMessage) ?>
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

                <?php if (empty($categories)): ?>
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        Chưa có danh mục nào. Hãy thêm danh mục đầu tiên.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Tên danh mục</th>
                                    <th>Mô tả</th>
                                    <th>Trạng thái</th>
                                    <th>Ngày tạo</th>
                                    <th class="text-end">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($categories as $index => $category): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td>
                                            <a href="<?= BASE_URL ?>admin/categories/show&id=<?= $category['id'] ?>" class="fw-semibold text-decoration-none">
                                                <?= htmlspecialchars($category['name']) ?>
                                            </a>
                                        </td>
                                        <td>
                                            <?php
                                                $descriptionPreview = $category['description'] ?? '';
                                                if ($descriptionPreview !== '') {
                                                    if (function_exists('mb_strimwidth')) {
                                                        $descriptionPreview = mb_strimwidth($descriptionPreview, 0, 60, '...');
                                                    } else {
                                                        $descriptionPreview = substr($descriptionPreview, 0, 57) . (strlen($descriptionPreview) > 57 ? '...' : '');
                                                    }
                                                }
                                            ?>
                                            <?= htmlspecialchars($descriptionPreview) ?>
                                        </td>
                                        <td>
                                            <?php if ((int)$category['status'] === 1): ?>
                                                <span class="badge bg-success-subtle text-success">Hoạt động</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Ẩn</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars(date('d/m/Y', strtotime($category['created_at'] ?? 'now'))) ?></td>
                                        <td class="text-end">
                                            <a href="<?= BASE_URL ?>admin/categories/edit&id=<?= $category['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <form action="<?= BASE_URL ?>admin/categories/delete" method="post" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa danh mục này?');">
                                                <input type="hidden" name="id" value="<?= $category['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
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
    'title' => $title ?? 'Quản lý danh mục',
    'pageTitle' => 'Quản lý danh mục',
    'content' => $content,
    'breadcrumb' => [
        ['label' => 'Trang chủ', 'url' => BASE_URL . 'home'],
        ['label' => 'Danh mục', 'url' => BASE_URL . 'admin/categories', 'active' => true],
    ],
]);
?>
