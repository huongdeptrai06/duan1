<?php
ob_start();
?>
<div class="row">
    <div class="col-8">
        <div class="card shadow-sm">
            <div class="card-body">
                <h4 class="mb-3"><?= htmlspecialchars($guide['full_name'] ?? '') ?></h4>
                <?php if (!empty($guide['photo'])): ?>
                    <div class="mb-3"><img src="<?= asset($guide['photo']) ?>" alt="photo" style="max-height:200px"></div>
                <?php endif; ?>
                <p><strong>Nhóm:</strong> <?= ($guide['group'] === 'quocte') ? 'Quốc tế' : 'Nội địa' ?></p>
                <p><strong>Ngày sinh:</strong> <?= htmlspecialchars($guide['dob'] ?? '') ?></p>
                <p><strong>Liên hệ:</strong> <?= htmlspecialchars($guide['contact'] ?? '') ?></p>
                <p><strong>Ngôn ngữ:</strong> <?= htmlspecialchars($guide['languages'] ?? '') ?></p>
                <p><strong>Kinh nghiệm:</strong><br><?= nl2br(htmlspecialchars($guide['experience'] ?? '')) ?></p>
                <p><strong>Lịch sử dẫn tour:</strong><br><?= nl2br(htmlspecialchars($guide['tour_history'] ?? '')) ?></p>
                <p><strong>Chứng chỉ:</strong><br><?= nl2br(htmlspecialchars($guide['certificates'] ?? '')) ?></p>
                <p><strong>Đánh giá:</strong> <?= htmlspecialchars($guide['rating'] ?? '') ?></p>
                <p><strong>Sức khoẻ:</strong> <?= htmlspecialchars($guide['health_status'] ?? '') ?></p>
                <div class="d-flex gap-2">
                    <a href="<?= BASE_URL ?>admin/guides/edit&id=<?= $guide['id'] ?>" class="btn btn-primary">Chỉnh sửa</a>
                    <a href="<?= BASE_URL ?>admin/guides" class="btn btn-secondary">Quay lại</a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Lịch sử chỉnh sửa</h5>
            </div>
            <div class="card-body">
                <?php if (empty($logs)): ?>
                    <div class="text-muted">Chưa có lịch sử chỉnh sửa.</div>
                <?php else: ?>
                    <ul class="list-unstyled">
                        <?php foreach ($logs as $log): ?>
                            <li class="mb-3">
                                <div class="small text-muted"><?= htmlspecialchars(date('d/m/Y H:i', strtotime($log['created_at'] ?? 'now'))) ?></div>
                                <div><?= nl2br(htmlspecialchars(json_encode(json_decode($log['change_data']), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) ?></div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();

view('layouts.AdminLayout', [
    'title' => $title ?? 'Chi tiết HDV',
    'pageTitle' => 'Chi tiết HDV',
    'content' => $content,
    'breadcrumb' => [
        ['label' => 'Trang chủ', 'url' => BASE_URL . 'home'],
        ['label' => 'HDV', 'url' => BASE_URL . 'admin/guides'],
        ['label' => 'Chi tiết', 'url' => BASE_URL . 'admin/guides/show', 'active' => true],
    ],
]);
?>
