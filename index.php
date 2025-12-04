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
require_once __DIR__ . '/src/models/Booking.php';
require_once __DIR__ . '/src/models/Tour.php';

// Nạp các file chứa controller
require_once __DIR__ . '/src/controllers/HomeController.php';
require_once __DIR__ . '/src/controllers/AuthController.php';
require_once __DIR__ . '/src/controllers/CategoryController.php';
require_once __DIR__ . '/src/controllers/GuideController.php';
require_once __DIR__ . '/src/controllers/BookingController.php';
require_once __DIR__ . '/src/controllers/TourController.php';
require_once __DIR__ . '/src/controllers/ReportController.php';

// Khởi tạo các controller
$homeController = new HomeController();
$authController = new AuthController();
$categoryController = new CategoryController();
$guideController = new GuideController();
$bookingController = new BookingController();
$tourController = new TourController();
$reportController = new ReportController();

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
    // Guide dashboard (for guides only)
    'guides/dashboard' => $guideController->dashboard(),
    'guides/request-leave' => $guideController->requestLeave(),
    'guides/add-note' => $guideController->addNote(),
    'guides/confirm-tour' => $guideController->confirmTour(),
    'admin/guides/store' => $guideController->store(),
    'admin/guides/edit' => $guideController->edit(),
    'admin/guides/update' => $guideController->update(),
    'admin/guides/delete' => $guideController->delete(),
    'admin/guides/show' => $guideController->show(),
    'admin/guides/process-leave' => $guideController->processLeaveRequest(),
    'admin/guides/requests' => $guideController->requests(),
    'admin/guides/process-note' => $guideController->processNote(),
    'admin/guides/process-confirmation' => $guideController->processConfirmation(),
    // Bookings management
    'admin/bookings' => $bookingController->index(),
    'admin/bookings/create' => $bookingController->create(),
    'admin/bookings/store' => $bookingController->store(),
    'admin/bookings/show' => $bookingController->show(),
    'admin/bookings/edit' => $bookingController->edit(),
    'admin/bookings/update' => $bookingController->update(),
    'admin/bookings/delete' => $bookingController->delete(),
    'admin/bookings/change-status' => $bookingController->changeStatus(),
    'admin/bookings/assign-guide' => $bookingController->assignGuide(),
    'admin/bookings/schedule' => $bookingController->schedule(),
    'admin/bookings/customers' => $bookingController->customerList(),
    'admin/bookings/add-note' => $bookingController->addNote(),
    'admin/bookings/add-feedback' => $bookingController->addFeedback(),
    // Tours management
    'admin/tours' => $tourController->index(),
    'admin/tours/create' => $tourController->create(),
    'admin/tours/store' => $tourController->store(),
    'admin/tours/show' => $tourController->show(),
    'admin/tours/edit' => $tourController->edit(),
    'admin/tours/update' => $tourController->update(),
    'admin/tours/delete' => $tourController->delete(),
    // Reports management
    'admin/reports' => $reportController->index(),
    'logout' => $authController->logout(),

    // Đường dẫn không tồn tại
    default => $homeController->notFound(),
};
