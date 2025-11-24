<?php
    $page_title = "Quản lý Tài khoản";
    $users = [
        ['id' => 1, 'name' => 'Dieuthh2', 'email' => 'dieuthh2@fpt.edu.vn', 'role' => 'Quản trị viên', 'status' => 'active'],
        ['id' => 2, 'name' => 'Anhnd120', 'email' => 'anhnd120@fpt.edu.vn', 'role' => 'Nhân viên', 'status' => 'inactive'],
        ['id' => 3, 'name' => 'NV-KinhDoanh', 'email' => 'kd01@fpt.edu.vn', 'role' => 'Nhân viên', 'status' => 'active'],
    ];
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
        .sidebar .nav-link.active { background-color: var(--active-bg); color: var(--fpt-orange); font-weight: bold; border-left: 5px solid var(--fpt-orange); }
        .main-content { margin-left: 250px; padding: 20px; }
        .top-navbar { background-color: white; padding: 10px 20px; border-radius: 10px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05); }
        .user-table th { font-weight: bold; background-color: #e9ecef; color: #6c757d; }
        .status-badge.active { background-color: #d4edda; color: #155724; }
        .status-badge.inactive { background-color: #f8d7da; color: #721c24; }
        .btn-add-user { background-color: #007bff; border-color: #007bff; color: white; font-weight: bold; }
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
        <li class="nav-item"><a class="nav-link" href="index.php?act=tour_list"><i class="fas fa-list-alt"></i> Danh sách tour</a></li>
        <li class="nav-item"><a class="nav-link" href="booking_list.php"><i class="fas fa-book"></i> Quản lý booking</a></li>
        <hr class="mx-3 my-2">
        <li class="nav-item"><a class="nav-link active" href="user_list.php"><i class="fas fa-user-circle"></i> Quản lý tài khoản</a></li>
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

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="input-group" style="width: 350px;">
            <input type="text" class="form-control" placeholder="Tìm kiếm theo tên hoặc email..." name="search_user">
            <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
        </div>
        <a href="user_form.php" class="btn btn-add-user d-flex align-items-center">
            <i class="fas fa-user-plus me-1"></i> Thêm Tài khoản mới
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 user-table">
                    <thead>
                        <tr>
                            <th scope="col" style="width: 5%;">#</th>
                            <th scope="col" style="width: 25%;">Tên người dùng</th>
                            <th scope="col" style="width: 25%;">Email</th>
                            <th scope="col" style="width: 15%;">Quyền hạn</th>
                            <th scope="col" style="width: 15%;" class="text-center">Trạng thái</th>
                            <th scope="col" style="width: 15%;" class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $index => $user): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo $user['name']; ?></td>
                            <td><?php echo $user['email']; ?></td>
                            <td><?php echo $user['role']; ?></td>
                            <td class="text-center">
                                <span class="status-badge <?php echo $user['status']; ?>">
                                    <?php echo $user['status'] == 'active' ? 'Hoạt động' : 'Đã khóa'; ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <a href="user_form.php?id=<?php echo $user['id']; ?>" class="text-info me-2" title="Sửa"><i class="fas fa-edit"></i></a>
                                <a href="delete_user.php?id=<?php echo $user['id']; ?>" class="text-danger" title="Xóa" onclick="return confirm('Xóa tài khoản này?');"><i class="fas fa-trash-alt"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>