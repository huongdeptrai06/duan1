-- ============================================
-- SCRIPT ĐƠN GIẢN - SỬA BẢNG guide_profiles
-- Chạy từng phần một để tránh lỗi
-- ============================================

USE duan1;

-- ============================================
-- PHẦN 1: THÊM CÁC CỘT MỚI
-- ============================================
-- Chạy từng dòng, nếu báo "Duplicate column" thì bỏ qua (bình thường)

ALTER TABLE guide_profiles ADD COLUMN full_name VARCHAR(255) NULL;
ALTER TABLE guide_profiles ADD COLUMN dob DATE NULL;
ALTER TABLE guide_profiles ADD COLUMN gender VARCHAR(50) NULL;
ALTER TABLE guide_profiles ADD COLUMN avatar_url VARCHAR(255) NULL;
ALTER TABLE guide_profiles ADD COLUMN id_number VARCHAR(50) NULL;
ALTER TABLE guide_profiles ADD COLUMN address VARCHAR(255) NULL;
ALTER TABLE guide_profiles ADD COLUMN contact_email VARCHAR(255) NULL;
ALTER TABLE guide_profiles ADD COLUMN license VARCHAR(255) NULL;
ALTER TABLE guide_profiles ADD COLUMN guide_type VARCHAR(255) NULL;
ALTER TABLE guide_profiles ADD COLUMN guide_group VARCHAR(100) NULL;
ALTER TABLE guide_profiles ADD COLUMN experience_years INT NULL;
ALTER TABLE guide_profiles ADD COLUMN experience_detail TEXT NULL;
ALTER TABLE guide_profiles ADD COLUMN notable_tours TEXT NULL;
ALTER TABLE guide_profiles ADD COLUMN tour_history TEXT NULL;
ALTER TABLE guide_profiles ADD COLUMN strengths TEXT NULL;

-- ============================================
-- PHẦN 2: COPY DỮ LIỆU
-- ============================================

-- Copy birthdate -> dob
UPDATE guide_profiles SET dob = birthdate WHERE birthdate IS NOT NULL;

-- Copy avatar -> avatar_url
UPDATE guide_profiles 
SET avatar_url = CONCAT('/uploads/guides/', avatar) 
WHERE avatar IS NOT NULL AND avatar != '';

-- Copy certificate -> license
UPDATE guide_profiles 
SET license = certificate
WHERE certificate IS NOT NULL AND certificate != '';

-- Copy group_type -> guide_group
UPDATE guide_profiles 
SET guide_group = CASE 
    WHEN group_type LIKE '%quốc tế%' OR group_type LIKE '%quoc te%' THEN 'quoc_te'
    WHEN group_type LIKE '%nội địa%' OR group_type LIKE '%trong nước%' OR group_type LIKE '%noi dia%' THEN 'noi_dia'
    WHEN group_type LIKE '%tuyến%' THEN 'chuyen_tuyen'
    WHEN group_type LIKE '%đoàn%' THEN 'chuyen_khach_doan'
    ELSE NULL
END
WHERE group_type IS NOT NULL;

-- Copy speciality -> guide_type
UPDATE guide_profiles 
SET guide_type = speciality 
WHERE speciality IS NOT NULL;

-- Copy history -> tour_history
UPDATE guide_profiles 
SET tour_history = history 
WHERE history IS NOT NULL;

-- Copy experience -> experience_detail
UPDATE guide_profiles 
SET experience_detail = experience 
WHERE experience IS NOT NULL;

-- Lấy số năm từ experience
UPDATE guide_profiles 
SET experience_years = CAST(SUBSTRING_INDEX(experience, ' ', 1) AS UNSIGNED)
WHERE experience REGEXP '^[0-9]+';

-- Lấy full_name từ users
UPDATE guide_profiles gp
INNER JOIN users u ON gp.user_id = u.id
SET gp.full_name = u.name
WHERE gp.full_name IS NULL OR gp.full_name = '';

-- Lấy contact_email từ users
UPDATE guide_profiles gp
INNER JOIN users u ON gp.user_id = u.id
SET gp.contact_email = u.email
WHERE gp.contact_email IS NULL OR gp.contact_email = '';

-- ============================================
-- PHẦN 3: KIỂM TRA
-- ============================================

-- Xem kết quả
SELECT 
    user_id,
    full_name,
    dob,
    phone,
    guide_group,
    guide_type,
    license,
    languages,
    experience_years
FROM guide_profiles
LIMIT 5;

