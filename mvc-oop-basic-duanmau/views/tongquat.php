<?php
    // Giả định biến PHP
    $page_title = "Báo cáo Tổng quan";
    
    // Dữ liệu thống kê giả định
    $stats = [
        ['icon' => 'fas fa-money-check-alt', 'title' => 'Tổng Doanh Thu (T11)', 'value' => '987,450,000₫', 'growth' => '+12.5%'],
        ['icon' => 'fas fa-book', 'title' => 'Booking Mới (Tháng)', 'value' => '125 đơn', 'growth' => '+5.2%'],
        ['icon' => 'fas fa-list-alt', 'title' => 'Tổng số Tour', 'value' => '45 Tour', 'growth' => '0%'],
        ['icon' => 'fas fa-users', 'title' => 'Khách hàng mới', 'value' => '300 người', 'growth' => '+25%'],
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <style>
        :root { 
            --sidebar-width: 250px; 
            --fpt-orange: #ff6600; 
            --active-bg: #ffe6d9;
        }
        body { background-color: #f8f9fa; }
        .sidebar { width: var(--sidebar-width); background-color: white; position: fixed; top: 0; left: 0; bottom: 0; padding: 15px 0; box-shadow: 2px 0 5px rgba(0, 0, 0, 0.05); z-index: 1000; }
        .sidebar .nav-link { color: #495057; padding: 12px 15px; border-radius: 0; transition: background-color 0.2s; font-size: 0.95rem; }
        .sidebar .nav-link.active { 
            background-color: var(--active-bg); 
            color: var(--fpt-orange); 
            font-weight: bold; 
            border-left: 5px solid var(--fpt-orange); 
        }
        .main-content { margin-left: var(--sidebar-width); padding: 20px; }
        .top-navbar { background-color: white; padding: 10px 20px; border-radius: 10px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05); }
        
        /* Dashboard specific styles */
        .stat-card {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s;
        }
        .stat-card:hover { transform: translateY(-5px); }
        .stat-icon {
            font-size: 2rem;
            color: var(--fpt-orange);
        }
        .stat-value {
            font-size: 1.8rem;
            font-weight: bold;
            color: #343a40;
        }
        .stat-growth {
            font-size: 0.9rem;
            font-weight: bold;
        }
        .growth-positive { color: #28a745; }
        .growth-neutral { color: #6c757d; }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-header d-flex align-items-center px-3 pb-3 border-bottom">
        <img src="https://upload.wikimedia.org/wikipedia/commons/2/22/FPT_Polytechnic.png" alt="Logo" height="30" class="me-2">
        <h5 class="mb-0 fw-bold" style="color: var(--fpt-orange);">FPT POLYTECHNIC</h5>
    </div>
    <ul class="nav flex-column">
    <li class="nav-item">
        <a class="nav-link active" href="index.php?act=tongquat">
            <i class="fas fa-chart-line"></i> Báo cáo
        </a>
    </li>
    
    <li class="nav-item">
        <a class="nav-link" href="index.php?act=category_list">
            <i class="fas fa-stream"></i> Danh mục tour
        </a>
    </li>
    
    <li class="nav-item">
        <a class="nav-link" href="index.php?act=tour_list">
            <i class="fas fa-list-alt"></i> Danh sách tour
        </a>
    </li>
    
    <li class="nav-item">
        <a class="nav-link" href="index.php?act=quan_li_booking">
            <i class="fas fa-book"></i> Quản lý booking
        </a>
    </li>
    
    <hr class="mx-3 my-2">
    
    <li class="nav-item">
        <a class="nav-link" href="index.php?act=quan_li_taikhoan">
            <i class="fas fa-user-circle"></i> Quản lý tài khoản
        </a>
    </li>
    
    <li class="nav-item">
        <a class="nav-link" href="#">
            <i class="fas fa-cog"></i> Cài đặt
        </a>
    </li>
    
    <li class="nav-item">
        <a class="nav-link" href="index.php?act=login">
            <i class="fas fa-sign-out-alt"></i> Đăng xuất
        </a>
    </li>
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
                    <img src="" alt="Admin Avatar" width="32" height="32" class="rounded-circle me-2">
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
    
    <div class="row g-4 mb-5">
        <?php foreach ($stats as $stat): ?>
        <div class="col-lg-3 col-md-6">
            <div class="stat-card d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small mb-1"><?php echo $stat['title']; ?></div>
                    <div class="stat-value"><?php echo $stat['value']; ?></div>
                    <span class="stat-growth <?php echo strpos($stat['growth'], '+') !== false ? 'growth-positive' : 'growth-neutral'; ?>">
                        <?php echo $stat['growth']; ?> so với tháng trước
                    </span>
                </div>
                <div class="stat-icon p-3 rounded-circle bg-light">
                    <i class="<?php echo $stat['icon']; ?>"></i>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white fw-bold"><i class="fas fa-chart-bar me-2"></i> Biểu đồ Doanh thu (6 tháng gần nhất)</div>
                <div class="card-body">
                    <canvas id="revenueChart" style="max-height: 400px;"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white fw-bold"><i class="fas fa-chart-pie me-2"></i> Tỷ lệ Booking theo loại Tour</div>
                <div class="card-body d-flex justify-content-center">
                    <canvas id="tourTypeChart" style="max-height: 300px;"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-12">
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-bell me-2"></i> 5 Đơn hàng mới nhất</span>
                    <a href="booking_list.php" class="btn btn-sm btn-outline-primary">Xem tất cả</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Mã Booking</th>
                                    <th>Khách hàng</th>
                                    <th>Tên Tour</th>
                                    <th>Tổng tiền</th>
                                    <th>Ngày đặt</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td>BK0010</td><td>Phạm Thu Hằng</td><td>Tour Vịnh Hạ Long</td><td>3.500.000₫</td><td>24/11/2025</td></tr>
                                <tr><td>BK0009</td><td>Nguyễn Minh Khôi</td><td>Tour khám phá Phú Quốc</td><td>6.100.000₫</td><td>24/11/2025</td></tr>
                                <tr><td>BK0008</td><td>Trần Văn Nam</td><td>Tour Du lịch Thái Lan</td><td>10.400.000₫</td><td>23/11/2025</td></tr>
                                <tr><td>BK0007</td><td>Lê Thị Hoa</td><td>Tour Hội An 3N2Đ</td><td>1.200.000₫</td><td>23/11/2025</td></tr>
                                <tr><td>BK0006</td><td>Vũ Văn Tùng</td><td>Tour Miền Tây sông nước</td><td>2.000.000₫</td><td>22/11/2025</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // --- Chart.js Initialization ---
    
    // 1. Biểu đồ Doanh thu (Bar Chart)
    const revenueCtx = document.getElementById('revenueChart');
    new Chart(revenueCtx, {
        type: 'bar',
        data: {
            labels: ['T6', 'T7', 'T8', 'T9', 'T10', 'T11'],
            datasets: [{
                label: 'Doanh thu (VNĐ)',
                data: [500, 750, 600, 850, 920, 987],
                backgroundColor: 'rgba(255, 102, 0, 0.8)', // FPT Orange
                borderColor: 'rgba(255, 102, 0, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    title: { display: true, text: 'Doanh thu (Triệu VNĐ)' }
                }
            },
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: (context) => context.dataset.label + ': ' + context.parsed.y + ' Triệu VNĐ' } }
            }
        }
    });

    // 2. Biểu đồ Tỷ lệ Tour được đặt (Doughnut Chart)
    const tourTypeCtx = document.getElementById('tourTypeChart');
    new Chart(tourTypeCtx, {
        type: 'doughnut',
        data: {
            labels: ['Tour Trong nước', 'Tour Quốc tế', 'Tour Nội địa'],
            datasets: [{
                label: 'Số Booking',
                data: [45, 30, 25], // 45% Trong nước, 30% Quốc tế, 25% Nội địa
                backgroundColor: [
                    '#ff6600', // FPT Orange
                    '#007bff', // Blue
                    '#28a745'  // Green
                ],
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' },
            }
        }
    });

</script>
</body>
</html>