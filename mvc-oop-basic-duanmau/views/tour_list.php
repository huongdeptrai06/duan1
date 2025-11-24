<?php
    $page_title = "Quản lý danh sách Tour";
    // Giả định dữ liệu tour
    $tours = [
        ['id' => 1, 'name' => 'Du lịch Hội An', 'type' => 'Trong nước', 'location' => 'Phố cổ Hội An - Đà Nẵng - VN', 'price' => '1.200.000₫', 'revenue' => '480.000.000₫', 'status' => 'active'],
        ['id' => 2, 'name' => 'Du lịch Cao Bằng', 'type' => 'Trong nước', 'location' => 'Danh lam Cao Bằng', 'price' => '1.900.000₫', 'revenue' => '18.000.000₫', 'status' => 'inactive'],
        ['id' => 3, 'name' => 'Du lịch Thái Lan', 'type' => 'Quốc tế', 'location' => 'Thủ đô Băng Cốc', 'price' => '5.200.000₫', 'revenue' => '79.000.000₫', 'status' => 'active'],
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
        body { background-color: #f8f9fa; }
        .sidebar { width: var(--sidebar-width); background-color: white; position: fixed; top: 0; left: 0; bottom: 0; padding: 15px 0; box-shadow: 2px 0 5px rgba(0, 0, 0, 0.05); z-index: 1000; }
        .sidebar .nav-link { color: #495057; padding: 12px 15px; border-radius: 0; transition: background-color 0.2s; font-size: 0.95rem; }
        .sidebar .nav-link.active { background-color: var(--active-bg); color: var(--fpt-orange); font-weight: bold; border-left: 5px solid var(--fpt-orange); }
        .main-content { margin-left: var(--sidebar-width); padding: 20px; }
        .top-navbar { background-color: white; padding: 10px 20px; border-radius: 10px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05); }
        .filter-bar .form-control, .filter-bar .btn, .filter-bar .dropdown-toggle { height: 45px; border-radius: 8px; }
        .btn-search { background-color: #ff9900; border-color: #ff9900; color: white; }
        .btn-add-tour { background-color: #28a745; border-color: #28a745; color: white; }
        .tour-table { background-color: white; border-radius: 8px; overflow: hidden; }
        .tour-table th { font-weight: bold; background-color: #e9ecef; color: #6c757d; }
        .status-badge { padding: 5px 10px; border-radius: 50px; font-weight: bold; font-size: 0.8rem; }
        .status-active { background-color: #d4edda; color: #155724; }
        .status-inactive { background-color: #fff3cd; color: #856404; }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-header d-flex align-items-center px-3 pb-3 border-bottom">
        <img src="https://upload.wikimedia.org/wikipedia/commons/2/22/FPT_Polytechnic.png" alt="Logo" height="30" class="me-2">
        <h5 class="mb-0 fw-bold" style="color: var(--fpt-orange);">FPT POLYTECHNIC</h5>
    </div>
    <ul class="nav flex-column">
        <li class="nav-item"><a class="nav-link" href="index.php?act=tongquat"><i class="fas fa-chart-line"></i> Báo cáo</a></li>
        <li class="nav-item"><a class="nav-link" href="index.php?act=category_list"><i class="fas fa-stream"></i> Danh mục tour</a></li>
        <li class="nav-item"><a class="nav-link active" href="tour_list.php"><i class="fas fa-list-alt"></i> Danh sách tour</a></li>
        <li class="nav-item"><a class="nav-link" href="index.php?act=quan_li_booking"><i class="fas fa-book"></i> Quản lý booking</a></li>
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

    <div class="filter-bar d-flex gap-3 mb-4 p-3 bg-white rounded shadow-sm align-items-center">
        <input type="text" class="form-control" placeholder="Nhập từ khóa tìm kiếm" name="keyword_search" style="flex-grow: 1;">
        <select class="form-select" name="tour_type" style="width: 150px;">
            <option selected>Chọn loại tour</option>
            <option value="domestic">Trong nước</option>
            <option value="international">Quốc tế</option>
        </select>
        <input type="text" class="form-control" placeholder="Nhập địa điểm tour" name="location_search" style="width: 180px;">
        <input type="number" class="form-control" placeholder="Giá cao nhất" name="max_price" style="width: 150px;">
        <button class="btn btn-search text-white fw-bold d-flex align-items-center"><i class="fas fa-search me-1"></i> Tìm kiếm</button>
        <a href="tour_form.php" class="btn btn-add-tour text-white fw-bold ms-auto d-flex align-items-center"><i class="fas fa-plus me-1"></i> Thêm tour</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 tour-table">
                    <thead>
                        <tr>
                            <th scope="col" style="width: 5%;">#</th>
                            <th scope="col" style="width: 18%;">Tên tour</th>
                            <th scope="col" style="width: 10%;">Loại tour</th>
                            <th scope="col" style="width: 20%;">Địa điểm</th>
                            <th scope="col" style="width: 10%;" class="text-end">Giá tour</th>
                            <th scope="col" style="width: 12%;" class="text-end">Doanh thu</th>
                            <th scope="col" style="width: 10%;" class="text-center">Trạng thái</th>
                            <th scope="col" style="width: 5%;" class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tours as $index => $tour): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><i class="fas fa-star me-2 text-warning"></i> <?php echo $tour['name']; ?></td>
                            <td><?php echo $tour['type']; ?></td>
                            <td><?php echo $tour['location']; ?></td>
                            <td class="text-end"><?php echo $tour['price']; ?></td>
                            <td class="text-end"><?php echo $tour['revenue']; ?></td>
                            <td class="text-center">
                                <span class="status-badge <?php echo $tour['status'] == 'active' ? 'status-active' : 'status-inactive'; ?>">
                                    <?php echo $tour['status'] == 'active' ? 'Hoạt động' : 'Tạm dừng'; ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <a href="tour_form.php?id=<?php echo $tour['id']; ?>" class="text-info me-2"><i class="fas fa-edit"></i></a>
                                <a href="delete_tour.php?id=<?php echo $tour['id']; ?>" class="text-danger" onclick="return confirm('Xóa tour này?');"><i class="fas fa-trash-alt"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <nav class="mt-4" aria-label="Page navigation">
        <ul class="pagination justify-content-end">
            <li class="page-item disabled"><a class="page-link" href="#">Trước</a></li>
            <li class="page-item active"><a class="page-link" href="#">1</a></li>
            <li class="page-item"><a class="page-link" href="#">2</a></li>
            <li class="page-item"><a class="page-link" href="#">Sau</a></li>
        </ul>
    </nav>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>