<?php
    // Giả định biến PHP để xác định đây là trang SỬA hay THÊM
    $is_edit = false; // Đặt là true nếu đang chỉnh sửa
    $page_title = $is_edit ? "Chỉnh sửa Tour: Du lịch Hội An" : "Thêm Tour mới";
    $button_text = $is_edit ? "Cập Nhật Tour" : "Thêm Tour";
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - FPT Polytechnic</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        :root { --sidebar-width: 250px; --fpt-orange: #ff6600; --active-bg: #ffe6d9; }
        body { background-color: #f8f9fa; }
        .sidebar .nav-link.active { background-color: var(--active-bg); color: var(--fpt-orange); font-weight: bold; border-left: 5px solid var(--fpt-orange); }
        .main-content { margin-left: 250px; padding: 20px; }
        .top-navbar { background-color: white; padding: 10px 20px; border-radius: 10px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05); }
        .card-header { background-color: #f1f1f1; font-weight: bold; }
        .form-control, .form-select { border-radius: 8px; padding: 10px; }
        .btn-save { background-color: #007bff; border-color: #007bff; color: white; padding: 10px 20px; font-weight: bold; }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-header d-flex align-items-center px-3 pb-3 border-bottom">
        <img src="https://upload.wikimedia.org/wikipedia/commons/2/22/FPT_Polytechnic.png" alt="Logo" height="30" class="me-2">
        <h5 class="mb-0 fw-bold" style="color: var(--fpt-orange);">FPT POLYTECHNIC</h5>
    </div>
    <ul class="nav flex-column">
        <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="fas fa-chart-line"></i> Báo cáo</a></li>
        <li class="nav-item"><a class="nav-link" href="category_list.php"><i class="fas fa-stream"></i> Danh mục tour</a></li>
        <li class="nav-item"><a class="nav-link active" href="tour_list.php"><i class="fas fa-list-alt"></i> Danh sách tour</a></li>
        <li class="nav-item"><a class="nav-link" href="booking_list.php"><i class="fas fa-book"></i> Quản lý booking</a></li>
        <hr class="mx-3 my-2">
        <li class="nav-item"><a class="nav-link" href="user_list.php"><i class="fas fa-user-circle"></i> Quản lý tài khoản</a></li>
        <li class="nav-item"><a class="nav-link" href="#"><i class="fas fa-cog"></i> Cài đặt</a></li>
        <li class="nav-item"><a class="nav-link" href="login.php"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a></li>
    </ul>
</div>

<div class="main-content">
    <nav class="top-navbar d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
            <div class="input-group" style="width: 300px;">
                <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                <input type="text" class="form-control border-start-0" placeholder="Tìm kiếm">
            </div>
        </div>
        <div class="d-flex align-items-center">
             <a href="#" class="text-secondary me-4 position-relative"><i class="fas fa-bell fa-lg"></i></a>
            <div class="dropdown me-4"><a href="#" class="d-flex align-items-center text-decoration-none text-dark"><i class="fas fa-globe me-2"></i> Tiếng Việt</a></div>
            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle text-dark" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="https://via.placeholder.com/32/ff6600/ffffff" alt="Admin Avatar" width="32" height="32" class="rounded-circle me-2">
                    <strong>hieunv34</strong>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><h6 class="dropdown-header">Admin</h6></li>
                    <li><a class="dropdown-item" href="#">Profile</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="login.php">Đăng xuất</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <h2 class="mb-4 fw-bold"><?php echo $page_title; ?></h2>

    <form method="POST" action="process_tour.php" class="needs-validation" novalidate>
        <?php if ($is_edit): ?><input type="hidden" name="tour_id" value="123"><?php endif; ?>

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-header"><i class="fas fa-info-circle me-2"></i> Thông tin cơ bản</div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="tourName" class="form-label">Tên Tour <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="tourName" name="tour_name" required value="<?php echo $is_edit ? 'Du lịch Hội An' : ''; ?>">
                            <div class="invalid-feedback">Vui lòng nhập tên tour.</div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="tourCode" class="form-label">Mã Tour</label>
                                <input type="text" class="form-control" id="tourCode" name="tour_code" value="<?php echo $is_edit ? 'HOIAN_001' : ''; ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="tourCategory" class="form-label">Danh mục Tour <span class="text-danger">*</span></label>
                                <select class="form-select" id="tourCategory" name="category_id" required>
                                    <option selected disabled value="">Chọn...</option>
                                    <option value="1" <?php echo $is_edit ? 'selected' : ''; ?>>Tour Trong nước</option>
                                    <option value="2">Tour Quốc tế</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="destination" class="form-label">Địa điểm (Nơi đến)</label>
                            <input type="text" class="form-control" id="destination" name="destination" value="<?php echo $is_edit ? 'Phố cổ Hội An - Đà Nẵng - VN' : ''; ?>">
                        </div>
                        <div class="mb-3">
                            <label for="duration" class="form-label">Thời gian (Số ngày/đêm)</label>
                            <input type="text" class="form-control" id="duration" name="duration" value="<?php echo $is_edit ? '3 ngày 2 đêm' : ''; ?>">
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-header"><i class="fas fa-align-left me-2"></i> Mô tả & Lịch trình</div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="shortDescription" class="form-label">Mô tả ngắn</label>
                            <textarea class="form-control" id="shortDescription" name="short_description" rows="3"><?php echo $is_edit ? 'Tour du lịch khám phá vẻ đẹp cổ kính...' : ''; ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="fullDescription" class="form-label">Lịch trình chi tiết</label>
                            <textarea class="form-control" id="fullDescription" name="full_description" rows="6"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-header"><i class="fas fa-images me-2"></i> Hình ảnh đại diện</div>
                    <div class="card-body text-center">
                        <img src="https://via.placeholder.com/200x150?text=Tour+Image" class="img-fluid rounded mb-3 border" alt="Hình ảnh Tour">
                        <input class="form-control" type="file" id="tourImage" name="tour_image">
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-header"><i class="fas fa-dollar-sign me-2"></i> Giá & Trạng thái</div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="basePrice" class="form-label">Giá cơ bản (VNĐ) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="basePrice" name="base_price" required value="<?php echo $is_edit ? '1200000' : ''; ?>">
                        </div>
                         <div class="mb-3">
                            <label for="tourStatus" class="form-label">Trạng thái Tour <span class="text-danger">*</span></label>
                            <select class="form-select" id="tourStatus" name="tour_status" required>
                                <option value="active" <?php echo $is_edit ? 'selected' : ''; ?>>Hoạt động</option>
                                <option value="inactive">Tạm dừng</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="d-flex justify-content-end gap-2 mb-5">
            <a href="tour_list.php" class="btn btn-secondary px-4">Hủy bỏ</a>
            <button type="submit" class="btn btn-save px-4"><i class="fas fa-save me-1"></i> <?php echo $button_text; ?></button>
        </div>
    </form>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // JS cho Validation
    (function () {
        'use strict'
        var forms = document.querySelectorAll('.needs-validation')
        Array.prototype.slice.call(forms).forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }
                form.classList.add('was-validated')
            }, false)
        })
    })()
</script>
</body>
</html>