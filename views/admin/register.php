<?php
ob_start();
$formData = $formData ?? [];
$selectedRole = $formData['role'] ?? array_key_first($roles ?? []);
?>
<div class="login-wrapper">
    <div class="col-12 col-md-8 col-lg-5 col-xl-4">
        <div class="card login-card shadow-lg border-0">
            <div class="login-header text-center text-white">
                <a href="<?= BASE_URL ?>" class="text-white text-decoration-none">
                    <div class="brand-icon mb-2">
                        <i class="bi bi-airplane-fill"></i>
                    </div>
                    <h2>
                        <strong>Tạo tài khoản quản trị</strong>
                    </h2>
                </a>
                <div class="mt-2 fw-light fst-italic" style="font-size: 1rem;">
                    Áp dụng cho quản trị viên và hướng dẫn viên
                </div>
            </div>
            <div class="card-body">
                <h4 class="card-title text-center mb-4 fw-bold card-title-login">
                    Đăng ký tài khoản mới
                </h4>
                <?php if (!empty($errors)): ?>
                <div class="alert alert-danger fade show" role="alert">
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-exclamation-circle-fill me-2 fs-5"></i>
                        <strong>Không thể đăng ký</strong>
                    </div>
                    <ul class="mb-0 ps-3">
                        <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <form action="<?= BASE_URL ?>handle-register" method="post" novalidate>
                    <input type="hidden" name="redirect" value="<?= $redirect ?? BASE_URL . 'home' ?>" />

                    <div class="mb-3">
                        <label for="registerName" class="form-label fw-semibold">Họ và tên</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                            <input type="text"
                                   class="form-control"
                                   id="registerName"
                                   name="name"
                                   value="<?= htmlspecialchars($formData['name'] ?? '') ?>"
                                   placeholder="Nhập họ tên"
                                   required
                                   autofocus />
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="registerEmail" class="form-label fw-semibold">Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email"
                                   class="form-control"
                                   id="registerEmail"
                                   name="email"
                                   value="<?= htmlspecialchars($formData['email'] ?? '') ?>"
                                   placeholder="Nhập email"
                                   required />
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="registerPassword" class="form-label fw-semibold">Mật khẩu</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                            <input type="password"
                                   class="form-control"
                                   id="registerPassword"
                                   name="password"
                                   placeholder="Ít nhất 6 ký tự"
                                   required />
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="registerPasswordConfirm" class="form-label fw-semibold">Xác nhận mật khẩu</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password"
                                   class="form-control"
                                   id="registerPasswordConfirm"
                                   name="password_confirmation"
                                   placeholder="Nhập lại mật khẩu"
                                   required />
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="registerRole" class="form-label fw-semibold">Vai trò</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-people"></i></span>
                            <select class="form-select" id="registerRole" name="role">
                                <?php foreach ($roles as $roleValue => $label): ?>
                                <option value="<?= htmlspecialchars($roleValue) ?>"
                                    <?= $selectedRole === $roleValue ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($label) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-login btn-lg">
                            <i class="bi bi-person-plus me-2"></i>Đăng ký
                        </button>
                    </div>
                </form>

                <div class="login-divider"></div>
                <div class="text-center">
                    <span class="text-muted me-2">Đã có tài khoản?</span>
                    <a href="<?= BASE_URL ?>login" class="text-decoration-none text-fpt-orange fw-semibold">
                        <i class="bi bi-box-arrow-in-right me-1"></i>Đăng nhập
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();

view('layouts.AuthLayout', [
    'title' => $title ?? 'Đăng ký tài khoản',
    'content' => $content,
    'extraJs' => ['js/login.js'],
]);
?>

