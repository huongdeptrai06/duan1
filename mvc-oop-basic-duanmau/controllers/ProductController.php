<?php

class ProductController {
    
    public $modelProduct;

    public function __construct() {
        // Khởi tạo Model (Đảm bảo file ProductModel đã được require trong index.php)
        $this->modelProduct = new ProductModel();
    }

    // ----------------------------------------------------------------
    // TỔNG QUAN & ĐĂNG NHẬP
    // ----------------------------------------------------------------

    // Hàm gọi views/login.php
    public function login() {
        // Đã sửa: Chuẩn hóa đường dẫn ROOT_PATH
        require_once ROOT_PATH . 'views/login.php';
    }
    
    // Hàm gọi views/tongquat.php
    public function tongquat() {
        // Đã sửa: Chuẩn hóa đường dẫn ROOT_PATH
        require_once ROOT_PATH . 'views/tongquat.php';
    }

    // ----------------------------------------------------------------
    // QUẢN LÝ TOUR & DANH MỤC
    // ----------------------------------------------------------------

    // Hàm gọi views/tour_list.php
    public function listTours() {
        // Đã sửa: Chuẩn hóa đường dẫn ROOT_PATH
        require_once ROOT_PATH . 'views/tour_list.php'; 
    }
    
    // Hàm gọi views/tour_form.php (Thêm)
    public function addTour() {
        $is_edit = false; 
        // Đã sửa: Chuẩn hóa đường dẫn ROOT_PATH
        require_once ROOT_PATH . 'views/tour_form.php'; 
    }
    
    // Hàm gọi views/tour_form.php (Sửa)
    public function editTour() {
        $is_edit = true; 
        // Đã sửa: Chuẩn hóa đường dẫn ROOT_PATH
        require_once ROOT_PATH . 'views/tour_form.php'; 
    }
    
    // Hàm gọi views/category_list.php
    public function listCategories() {
        // Đã sửa: Chuẩn hóa đường dẫn ROOT_PATH
        require_once ROOT_PATH . 'views/category_list.php'; 
    }

    // ----------------------------------------------------------------
    // QUẢN LÝ NGHIỆP VỤ (TÊN FILE CỦA BẠN)
    // ----------------------------------------------------------------

    // Hàm gọi views/quan_li_booking.php
    public function quanLiBooking() {
        // Đã sửa: Chuẩn hóa đường dẫn ROOT_PATH
        require_once ROOT_PATH . 'views/quan_li_booking.php'; 
    }

    // Hàm gọi views/quan_li_taikhoan.php
    public function quanLiTaiKhoan() {
        // Đã sửa: Chuẩn hóa đường dẫn ROOT_PATH
        require_once ROOT_PATH . 'views/quan_li_taikhoan.php'; 
    }

    // Xử lý khi không tìm thấy trang
    public function error404() {
        header("HTTP/1.0 404 Not Found");
        echo "<h1>404 - KHÔNG TÌM THẤY TRANG</h1>";
    }
}