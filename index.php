<?php

// Nạp cấu hình chung của ứng dụng
$config = require __DIR__ . '/config/config.php';

// Nạp các file chứa hàm trợ giúp
require_once __DIR__ . '/src/helpers/helpers.php'; // Helper chứa các hàm trợ giúp (hàm xử lý view, block, asset, session, ...)
require_once __DIR__ . '/src/helpers/database.php'; // Helper kết nối database(kết nối với cơ sở dữ liệu)

// Nạp các file chứa model
require_once __DIR__ . '/src/models/User.php';
require_once __DIR__ . '/src/models/Category.php';
require_once __DIR__ . '/src/models/Guide.php';
require_once __DIR__ . '/src/models/GuideLog.php';

// Nạp các file chứa controller
require_once __DIR__ . '/src/controllers/HomeController.php';
require_once __DIR__ . '/src/controllers/AuthController.php';
require_once __DIR__ . '/src/controllers/CategoryController.php';
require_once __DIR__ . '/src/controllers/GuideController.php';

// Khởi tạo các controller
$homeController = new HomeController();
$authController = new AuthController();
$categoryController = new CategoryController();
$guideController = new GuideController();

// Xác định route dựa trên tham số act (mặc định là trang chủ '/')
$act = $_GET['act'] ?? '/';

// Match đảm bảo chỉ một action tương ứng được gọi
match ($act) {
    // Trang welcome (cho người chưa đăng nhập) - mặc định khi truy cập '/'
    '/', 'welcome' => $homeController->welcome(),

    // Trang home (cho người đã đăng nhập)
    'home' => $homeController->home(),

    // Đường dẫn đăng nhập, đăng xuất
    'login' => $authController->login(),
    'check-login' => $authController->checkLogin(),
    'admin-guide-list' => $authController->showGuideList(),
    'admin/guide/list' => $authController->showGuideList(),
    'admin/guide/create' => $authController->showGuideCreationForm(),
    'admin-guide-create' => $authController->showGuideCreationForm(),
    'admin/guide/store' => $authController->handleGuideCreation(),
    'admin-guide-store' => $authController->handleGuideCreation(),
    'admin-guide-detail' => $authController->showGuideDetail(),
    'admin-guide-edit' => $authController->showGuideEditForm(),
    'admin-guide-update' => $authController->handleGuideUpdate(),
    'admin-guide-delete' => $authController->handleGuideDelete(),
    'admin/users' => $authController->listUsers(),
    'admin/categories' => $categoryController->index(),
    'admin/categories/create' => $categoryController->create(),
    'admin/categories/store' => $categoryController->store(),
    'admin/categories/edit' => $categoryController->edit(),
    'admin/categories/update' => $categoryController->update(),
    'admin/categories/delete' => $categoryController->delete(),
    'admin/categories/show' => $categoryController->show(),
    // Guides management
    'admin/guides' => $guideController->index(),
    'admin/guides/create' => $guideController->create(),
    'admin/guides/store' => $guideController->store(),
    'admin/guides/edit' => $guideController->edit(),
    'admin/guides/update' => $guideController->update(),
    'admin/guides/delete' => $guideController->delete(),
    'admin/guides/show' => $guideController->show(),
    'logout' => $authController->logout(),

    // Đường dẫn không tồn tại
    default => $homeController->notFound(),
};
