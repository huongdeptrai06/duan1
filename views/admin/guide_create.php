<?php
ob_start();
$formData = $formData ?? [];
?>
<<<<<<< HEAD
<div class="row">
    <div class="col-12 col-lg-11 col-xl-11 mx-auto">
        <div class="card shadow-sm border-0">
=======
<div class="row justify-content-center">
    <div class="col-12 col-lg-8 col-xl-6">
        <div class="card shadow-sm">
>>>>>>> origin/hieu
            <div class="card-header bg-primary text-white">
                <h3 class="card-title mb-0">
                    <i class="bi bi-person-plus-fill me-2"></i>
                    Cấp tài khoản hướng dẫn viên
                </h3>
            </div>
<<<<<<< HEAD
            <div class="card-body p-4 p-lg-5">
=======
            <div class="card-body">
>>>>>>> origin/hieu
                <p class="text-muted">
                    Nhập thông tin bên dưới để tạo tài khoản HDV. Tài khoản được tạo sẽ nhận email thông báo đăng nhập từ quản trị viên.
                </p>

                <?php if (!empty($successMessage)): ?>
                    <div class="alert alert-success">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                            <strong><?= htmlspecialchars($successMessage) ?></strong>
                        </div>
                        <?php if (!empty($successEmail)): ?>
                            <div>Email: <strong><?= htmlspecialchars($successEmail) ?></strong></div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                            <strong>Không thể tạo tài khoản</strong>
                        </div>
                        <ul class="mb-0 ps-3">
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form action="<?= BASE_URL ?>admin/guide/store" method="post" novalidate>
                    <div class="mb-3">
                        <label for="guideName" class="form-label fw-semibold">Họ và tên</label>
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
                        <label for="guideEmail" class="form-label fw-semibold">Email đăng nhập</label>
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

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="guidePassword" class="form-label fw-semibold">Mật khẩu tạm</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                <input type="password"
                                       class="form-control"
                                       id="guidePassword"
                                       name="password"
                                       placeholder="Ít nhất 6 ký tự"
                                       required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="guidePasswordConfirm" class="form-label fw-semibold">Xác nhận mật khẩu</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password"
                                       class="form-control"
                                       id="guidePasswordConfirm"
                                       name="password_confirmation"
                                       placeholder="Nhập lại mật khẩu"
                                       required>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?= BASE_URL ?>home" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i>
                            Về trang chủ
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-person-plus me-1"></i>
                            Tạo tài khoản
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
    'title' => $title ?? 'Cấp tài khoản hướng dẫn viên',
    'pageTitle' => 'Cấp tài khoản HDV',
    'content' => $content,
    'breadcrumb' => [
        ['label' => 'Trang chủ', 'url' => BASE_URL . 'home'],
        ['label' => 'Cấp tài khoản HDV', 'url' => BASE_URL . 'admin/guide/create', 'active' => true],
    ],
]);
?>



