-- Tạo bảng guide_profiles để lưu thông tin chi tiết hướng dẫn viên
-- Chạy file này trong MySQL/phpMyAdmin để tạo bảng

CREATE TABLE IF NOT EXISTS guide_profiles (
    user_id INT PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    dob DATE NULL,
    gender VARCHAR(50) NULL,
    avatar_url VARCHAR(255) NULL,
    id_number VARCHAR(50) NULL,
    address VARCHAR(255) NULL,
    phone VARCHAR(50) NULL,
    contact_email VARCHAR(255) NULL,
    license VARCHAR(255) NULL,
    guide_type VARCHAR(255) NULL,
    guide_group VARCHAR(100) NULL,
    languages VARCHAR(255) NULL,
    experience_years INT NULL,
    experience_detail TEXT NULL,
    notable_tours TEXT NULL,
    tour_history TEXT NULL,
    strengths TEXT NULL,
    rating DECIMAL(2,1) NULL,
    health_status VARCHAR(255) NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

