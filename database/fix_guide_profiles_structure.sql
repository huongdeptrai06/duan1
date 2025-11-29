-- Script SQL để sửa cấu trúc bảng guide_profiles cho khớp với code
-- Chạy file này trong phpMyAdmin để cập nhật cấu trúc bảng
-- LƯU Ý: Backup dữ liệu trước khi chạy!

USE duan1;

-- Bước 1: Thêm các cột mới mà code đang mong đợi (nếu chưa có)
ALTER TABLE guide_profiles 
ADD COLUMN IF NOT EXISTS full_name VARCHAR(255) NULL AFTER user_id,
ADD COLUMN IF NOT EXISTS dob DATE NULL AFTER full_name,
ADD COLUMN IF NOT EXISTS gender VARCHAR(50) NULL AFTER dob,
ADD COLUMN IF NOT EXISTS avatar_url VARCHAR(255) NULL AFTER gender,
ADD COLUMN IF NOT EXISTS id_number VARCHAR(50) NULL AFTER avatar_url,
ADD COLUMN IF NOT EXISTS address VARCHAR(255) NULL AFTER id_number,
ADD COLUMN IF NOT EXISTS contact_email VARCHAR(255) NULL AFTER phone,
ADD COLUMN IF NOT EXISTS license VARCHAR(255) NULL AFTER contact_email,
ADD COLUMN IF NOT EXISTS guide_type VARCHAR(255) NULL AFTER license,
ADD COLUMN IF NOT EXISTS guide_group VARCHAR(100) NULL AFTER guide_type,
ADD COLUMN IF NOT EXISTS experience_years INT NULL AFTER languages,
ADD COLUMN IF NOT EXISTS experience_detail TEXT NULL AFTER experience_years,
ADD COLUMN IF NOT EXISTS notable_tours TEXT NULL AFTER experience_detail,
ADD COLUMN IF NOT EXISTS tour_history TEXT NULL AFTER notable_tours,
ADD COLUMN IF NOT EXISTS strengths TEXT NULL AFTER tour_history;

-- Nếu MySQL không hỗ trợ IF NOT EXISTS, dùng cách này (bỏ comment):
/*
ALTER TABLE guide_profiles 
ADD COLUMN full_name VARCHAR(255) NULL AFTER user_id,
ADD COLUMN dob DATE NULL AFTER full_name,
ADD COLUMN gender VARCHAR(50) NULL AFTER dob,
ADD COLUMN avatar_url VARCHAR(255) NULL AFTER gender,
ADD COLUMN id_number VARCHAR(50) NULL AFTER avatar_url,
ADD COLUMN address VARCHAR(255) NULL AFTER id_number,
ADD COLUMN contact_email VARCHAR(255) NULL AFTER phone,
ADD COLUMN license VARCHAR(255) NULL AFTER contact_email,
ADD COLUMN guide_type VARCHAR(255) NULL AFTER license,
ADD COLUMN guide_group VARCHAR(100) NULL AFTER guide_type,
ADD COLUMN experience_years INT NULL AFTER languages,
ADD COLUMN experience_detail TEXT NULL AFTER experience_years,
ADD COLUMN notable_tours TEXT NULL AFTER experience_detail,
ADD COLUMN tour_history TEXT NULL AFTER notable_tours,
ADD COLUMN strengths TEXT NULL AFTER tour_history;
*/

-- Bước 2: Di chuyển dữ liệu từ cột cũ sang cột mới
-- Chuyển birthdate sang dob
UPDATE guide_profiles SET dob = birthdate WHERE dob IS NULL AND birthdate IS NOT NULL;

-- Chuyển avatar sang avatar_url (giữ nguyên tên file hoặc URL)
UPDATE guide_profiles SET avatar_url = CONCAT('/uploads/guides/', avatar) 
WHERE avatar_url IS NULL AND avatar IS NOT NULL AND avatar != '';

-- Chuyển certificate sang license (lấy giá trị đầu tiên nếu là JSON array)
UPDATE guide_profiles SET license = REPLACE(REPLACE(REPLACE(certificate, '[', ''), ']', ''), '"', '') 
WHERE license IS NULL AND certificate IS NOT NULL AND certificate != '';

-- Chuyển group_type sang guide_group (map giá trị)
UPDATE guide_profiles 
SET guide_group = CASE 
    WHEN group_type = 'quốc tế' THEN 'quoc_te'
    WHEN group_type = 'nội địa' THEN 'noi_dia'
    WHEN group_type LIKE '%tuyến%' THEN 'chuyen_tuyen'
    WHEN group_type LIKE '%đoàn%' THEN 'chuyen_khach_doan'
    ELSE 'noi_dia'
END
WHERE guide_group IS NULL AND group_type IS NOT NULL;

-- Chuyển history sang tour_history
UPDATE guide_profiles SET tour_history = history 
WHERE tour_history IS NULL AND history IS NOT NULL;

-- Chuyển experience sang experience_detail
UPDATE guide_profiles SET experience_detail = experience 
WHERE experience_detail IS NULL AND experience IS NOT NULL;

-- Lấy số năm kinh nghiệm từ experience (nếu có)
UPDATE guide_profiles 
SET experience_years = CAST(SUBSTRING_INDEX(experience, ' ', 1) AS UNSIGNED)
WHERE experience_years IS NULL 
  AND experience IS NOT NULL 
  AND experience REGEXP '^[0-9]+';

-- Chuyển speciality sang guide_type
UPDATE guide_profiles SET guide_type = speciality 
WHERE guide_type IS NULL AND speciality IS NOT NULL;

-- Cập nhật full_name từ bảng users nếu chưa có
UPDATE guide_profiles gp
JOIN users u ON gp.user_id = u.id
SET gp.full_name = u.name
WHERE (gp.full_name IS NULL OR gp.full_name = '') AND u.name IS NOT NULL;

-- Bước 3: Đảm bảo PRIMARY KEY là user_id (nếu đang dùng id)
-- CHỈ CHẠY NẾU BẠN CHẮC CHẮN - Uncomment các dòng dưới nếu cần:
/*
-- Xóa PRIMARY KEY cũ (nếu có)
ALTER TABLE guide_profiles DROP PRIMARY KEY;

-- Xóa cột id nếu không cần
ALTER TABLE guide_profiles DROP COLUMN id;

-- Đặt user_id làm PRIMARY KEY
ALTER TABLE guide_profiles ADD PRIMARY KEY (user_id);
*/

-- Bước 4: Đảm bảo có FOREIGN KEY constraint
-- Kiểm tra và thêm nếu chưa có (uncomment nếu cần):
/*
ALTER TABLE guide_profiles 
ADD CONSTRAINT fk_guide_profiles_user_id 
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;
*/

-- Kiểm tra kết quả
SELECT 
    user_id, 
    full_name, 
    dob, 
    avatar_url, 
    phone, 
    guide_group, 
    guide_type, 
    rating, 
    health_status
FROM guide_profiles 
LIMIT 10;
