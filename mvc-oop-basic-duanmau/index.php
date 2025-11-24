<?php
/**
 * TÊN FILE: index.php
 * CHỨC NĂNG: Front Controller - Điểm truy cập duy nhất, xử lý khởi tạo và định tuyến (Routing).
 */

// 1. ĐỊNH NGHĨA HẰNG SỐ ROOT_PATH
// Sử dụng __DIR__ để lấy đường dẫn tuyệt đối của thư mục gốc project.
define('ROOT_PATH', __DIR__ . '/');

// 2. REQUIRE TẤT CẢ CÁC FILE KHỞI TẠO VÀ LỚP HỌC (CLASSES)
// Sử dụng ROOT_PATH để đảm bảo không bị lỗi Fatal error: Class not found
require_once ROOT_PATH . 'commons/env.php'; 
require_once ROOT_PATH . 'commons/function.php'; 

// Require Controller và Model
require_once ROOT_PATH . 'controllers/ProductController.php';
require_once ROOT_PATH . 'models/ProductModel.php'; 


// 3. ROUTE (Định tuyến)
// Lấy action (hành động) từ URL qua tham số GET. Mặc định là 'tongquat'.
$act = $_GET['act'] ?? 'tongquat'; 

// Khối match (PHP 8+) định tuyến đến các phương thức (methods) trong ProductController
match ($act) {
    // ----------------------------------------------------------------
    // TỔNG QUAN & ĐĂNG NHẬP (TRANG CHỦ)
    // ----------------------------------------------------------------
    'home', 'tongquat' => (new ProductController())->tongquat(), // views/tongquat.php
    'login' => (new ProductController())->login(),                  // views/login.php

    // ----------------------------------------------------------------
    // QUẢN LÝ TOUR & DANH MỤC
    // ----------------------------------------------------------------
    'tour_list' => (new ProductController())->listTours(),           // views/tour_list.php
    'tour_add' => (new ProductController())->addTour(),              // views/tour_form.php (Chế độ Thêm)
    'tour_edit' => (new ProductController())->editTour(),            // views/tour_form.php (Chế độ Sửa)
    
    'category_list' => (new ProductController())->listCategories(),  // views/category_list.php

    // ----------------------------------------------------------------
    // QUẢN LÝ NGHIỆP VỤ (TÊN FILE KHÔNG CHUẨN CỦA BẠN)
    // ----------------------------------------------------------------
    'quan_li_booking' => (new ProductController())->quanLiBooking(),    // views/quan_li_booking.php
    'quan_li_taikhoan' => (new ProductController())->quanLiTaiKhoan(),      // views/quan_li_taikhoan.php
    
    // ----------------------------------------------------------------
    // XỬ LÝ LỖI
    // ----------------------------------------------------------------
    default => (new ProductController())->error404(),
};