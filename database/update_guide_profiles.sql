-- Script SQL để cập nhật bảng guide_profiles cho khớp với code
-- Chạy từng bước một trong phpMyAdmin

USE duan1;

-- Bước 1: Thêm các cột mới từng cột một (chạy từng dòng nếu báo lỗi)
-- Nếu báo lỗi "Duplicate column name", bỏ qua dòng đó và tiếp tục

ALTER TABLE guide_profiles ADD COLUMN full_name VARCHAR(255) NULL AFTER user_id;
ALTER TABLE guide_profiles ADD COLUMN dob DATE NULL AFTER full_name;
ALTER TABLE guide_profiles ADD COLUMN gender VARCHAR(50) NULL AFTER dob;
ALTER TABLE guide_profiles ADD COLUMN avatar_url VARCHAR(255) NULL AFTER gender;
ALTER TABLE guide_profiles ADD COLUMN id_number VARCHAR(50) NULL AFTER avatar_url;
ALTER TABLE guide_profiles ADD COLUMN address VARCHAR(255) NULL AFTER id_number;
ALTER TABLE guide_profiles ADD COLUMN contact_email VARCHAR(255) NULL AFTER phone;
ALTER TABLE guide_profiles ADD COLUMN license VARCHAR(255) NULL AFTER contact_email;
ALTER TABLE guide_profiles ADD COLUMN guide_type VARCHAR(255) NULL AFTER license;
ALTER TABLE guide_profiles ADD COLUMN guide_group VARCHAR(100) NULL AFTER guide_type;
ALTER TABLE guide_profiles ADD COLUMN experience_years INT NULL AFTER languages;
ALTER TABLE guide_profiles ADD COLUMN experience_detail TEXT NULL AFTER experience_years;
ALTER TABLE guide_profiles ADD COLUMN notable_tours TEXT NULL AFTER experience_detail;
ALTER TABLE guide_profiles ADD COLUMN tour_history TEXT NULL AFTER notable_tours;
ALTER TABLE guide_profiles ADD COLUMN strengths TEXT NULL AFTER tour_history;

-- Nếu báo lỗi "Duplicate column name", bỏ qua và tiếp tục bước 2

-- Bước 2: Copy dữ liệu từ cột cũ sang cột mới
-- Copy birthdate -> dob
UPDATE guide_profiles SET dob = birthdate WHERE birthdate IS NOT NULL;

-- Copy avatar -> avatar_url (giả sử ảnh ở thư mục uploads)
UPDATE guide_profiles 
SET avatar_url = CONCAT('/uploads/guides/', avatar) 
WHERE avatar IS NOT NULL AND avatar != '' AND avatar_url IS NULL;

-- Copy certificate -> license 
-- Nếu certificate là JSON array như ["HDV quốc tế"], lấy giá trị đầu tiên
UPDATE guide_profiles 
SET license = REPLACE(REPLACE(REPLACE(SUBSTRING_INDEX(SUBSTRING_INDEX(certificate, ',', 1), ',', -1), '[', ''), ']', ''), '"', '')
WHERE certificate IS NOT NULL AND certificate != '' AND license IS NULL;

-- Copy group_type -> guide_group (chuyển đổi tên)
UPDATE guide_profiles 
SET guide_group = CASE 
    WHEN group_type = 'quốc tế' THEN 'quoc_te'
    WHEN group_type = 'nội địa' THEN 'noi_dia'
    WHEN group_type LIKE '%tuyến%' THEN 'chuyen_tuyen'
    WHEN group_type LIKE '%đoàn%' THEN 'chuyen_khach_doan'
    WHEN group_type LIKE '%sinh thái%' THEN 'du_lich_sinh_thai'
    WHEN group_type LIKE '%mạo hiểm%' THEN 'du_lich_mao_hiem'
    ELSE 'noi_dia'
END
WHERE group_type IS NOT NULL AND guide_group IS NULL;

-- Copy history -> tour_history
UPDATE guide_profiles SET tour_history = history WHERE history IS NOT NULL;

-- Copy experience -> experience_detail
UPDATE guide_profiles SET experience_detail = experience WHERE experience IS NOT NULL;

-- Trích xuất số năm từ experience (ví dụ: "5 năm" -> 5)
-- Lấy số đầu tiên trong chuỗi
UPDATE guide_profiles 
SET experience_years = CAST(SUBSTRING_INDEX(experience, ' ', 1) AS UNSIGNED)
WHERE experience IS NOT NULL 
  AND experience REGEXP '^[0-9]+'
  AND experience_years IS NULL;

-- Copy speciality -> guide_type
UPDATE guide_profiles SET guide_type = speciality WHERE speciality IS NOT NULL;

-- Copy languages (giữ nguyên vì cột đã có sẵn)

-- Copy rating (giữ nguyên vì cột đã có sẵn)

-- Copy health_status (giữ nguyên vì cột đã có sẵn)

-- Lấy full_name từ bảng users
UPDATE guide_profiles gp
INNER JOIN users u ON gp.user_id = u.id
SET gp.full_name = u.name
WHERE (gp.full_name IS NULL OR gp.full_name = '') AND u.name IS NOT NULL;

-- Bước 3: Kiểm tra kết quả
SELECT 
    user_id,
    full_name,
    dob,
    avatar_url,
    phone,
    guide_group,
    guide_type,
    languages,
    experience_years,
    rating,
    health_status
FROM guide_profiles;

-- Nếu có lỗi khi chạy, chạy từng câu lệnh một và kiểm tra

