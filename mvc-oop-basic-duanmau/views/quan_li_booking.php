<?php
    $page_title = "Quản lý Booking";
    $bookings = [
        ['id' => 1, 'code' => 'BK0001', 'customer' => 'Nguyễn Văn A', 'tour_name' => 'Du lịch Hội An', 'date' => '24/11/2025', 'total' => '1.200.000₫', 'status' => 'pending'],
        ['id' => 2, 'code' => 'BK0002', 'customer' => 'Lê Thị B', 'tour_name' => 'Du lịch Thái Lan', 'date' => '23/11/2025', 'total' => '10.400.000₫', 'status' => 'confirmed'],
        ['id' => 3, 'code' => 'BK0003', 'customer' => 'Trần Văn C', 'tour_name' => 'Du lịch Miền Tây', 'date' => '22/11/2025', 'total' => '2.000.000₫', 'status' => 'cancelled'],
    ];
    
    function get_status_class($status) {
        if ($status == 'pending') return 'status-pending';
        if ($status == 'confirmed') return 'status-confirmed';
        if ($status == 'cancelled') return 'status-cancelled';
        return 'text-muted';
    }
    function get_status_text($status) {
        if ($status == 'pending') return 'Chờ xử lý';
        if ($status == 'confirmed') return 'Đã xác nhận';
        if ($status == 'cancelled') return 'Đã hủy';
        return 'Không rõ';
    }
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
        .status-badge { padding: 5px 10px; border-radius: 50px; font-weight: bold; font-size: 0.8rem; }
        .status-pending { background-color: #fff3cd; color: #856404; }
        .status-confirmed { background-color: #d4edda; color: #155724; }
        .status-cancelled { background-color: #f8d7da; color: #721c24; }
        .btn-detail { background-color: #20c997; border-color: #20c997; color: white; font-size: 0.85rem; }
        .booking-table th { font-weight: bold; background-color: #e9ecef; color: #6c757d; }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-header d-flex align-items-center px-3 pb-3 border-bottom">
        <img src="https://upload.wikimedia.org/wikipedia/commons/2/22/FPT_Polytechnic.png" alt="Logo" height="30" class="me-2">
        <h5 class="mb-0 fw-bold" style="color: var(--fpt-orange);">FPT POLYTECHNIC</h5>
    </div>
    <ul class="nav flex-column">
        <li class="nav-item"><a class="nav-link" href="index.php?act=tongquat">Báo cáo</a></li>
        <li class="nav-item"><a class="nav-link" href="index.php?act=category_list">Danh mục tour</a></li>
        <li class="nav-item"><a class="nav-link" href="index.php?act=tour_list">Danh sách tour</a></li>
        <li class="nav-item"><a class="nav-link active" href="index.php?act=quan_li_booking">Quản lý booking</a></li>
        <hr class="mx-3 my-2">
        <li class="nav-item"><a class="nav-link" href="index.php?act=quan_li_taikhoan">Quản lý tài khoản</a></li>
        <li class="nav-item"><a class="nav-link" href="#">Cài đặt</a></li>
        <li class="nav-item"><a class="nav-link" href="index.php?act=login">Đăng xuất</a></li>
    </ul>
</div>

<div class="main-content">
    <nav class="top-navbar d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
            <div class="input-group" style="width: 300px;">
                <span class="input-group-text bg-white border-end-0"></span>
                <input type="text" class="form-control border-start-0" placeholder="Tìm kiếm">
            </div>
        </div>
        <div class="d-flex align-items-center">
             <a href="#" class="text-secondary me-4 position-relative"></a>
            <div class="dropdown me-4"><a href="#" class="d-flex align-items-center text-decoration-none text-dark"> Tiếng Việt</a></div>
            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle text-dark" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="https://via.placeholder.com/32/ff6600/ffffff" alt="Admin Avatar" width="32" height="32" class="rounded-circle me-2">
                    <strong>hieunv34</strong>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><h6 class="dropdown-header">Admin</h6></li>
                    <li><a class="dropdown-item" href="#">Profile</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="index.php?act=login">Đăng xuất</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <h2 class="mb-4 fw-bold"><?php echo $page_title; ?></h2>

    <div class="d-flex gap-3 mb-4 p-3 bg-white rounded shadow-sm align-items-center">
        <input type="text" class="form-control" name="keyword" placeholder="Mã BK, Tên KH, Tên Tour" style="flex-grow: 1;">
        <select class="form-select" name="status_filter" style="width: 180px;">
            <option selected>Lọc theo Trạng thái</option>
            <option value="pending">Chờ xử lý</option>
            <option value="confirmed">Đã xác nhận</option>
            <option value="cancelled">Đã hủy</option>
        </select>
        <input type="date" class="form-control" name="date_filter" style="width: 180px;">
        <button class="btn btn-primary d-flex align-items-center"> Lọc dữ liệu</button>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 booking-table">
                    <thead>
                        <tr>
                            <th scope="col" style="width: 5%;">#</th>
                            <th scope="col" style="width: 10%;">Mã Booking</th>
                            <th scope="col" style="width: 15%;">Khách hàng</th>
                            <th scope="col" style="width: 25%;">Tour đặt</th>
                            <th scope="col" style="width: 10%;">Ngày đặt</th>
                            <th scope="col" style="width: 10%;" class="text-end">Tổng tiền</th>
                            <th scope="col" style="width: 15%;" class="text-center">Trạng thái</th>
                            <th scope="col" style="width: 10%;" class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $index => $book): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td>**<?php echo $book['code']; ?>**</td>
                            <td><?php echo $book['customer']; ?></td>
                            <td><?php echo $book['tour_name']; ?></td>
                            <td><?php echo $book['date']; ?></td>
                            <td class="text-end fw-bold"><?php echo $book['total']; ?></td>
                            <td class="text-center">
                                <span class="status-badge <?php echo get_status_class($book['status']); ?>">
                                    <?php echo get_status_text($book['status']); ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <a href="index.php?act=booking_detail&id=<?php echo $book['id']; ?>" class="btn btn-detail btn-sm" title="Xem chi tiết">Chi tiết</a>
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