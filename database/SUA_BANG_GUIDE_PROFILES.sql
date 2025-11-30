-- ============================================
-- SCRIPT SỬA BẢNG guide_profiles
-- Đảm bảo khớp với form thêm/sửa hướng dẫn viên
-- Chạy file này trong phpMyAdmin
-- ============================================

USE duan1;

-- ============================================
-- BƯỚC 1: THÊM CÁC CỘT MỚI (nếu chưa có)
-- ============================================
-- Chạy từng dòng, nếu báo lỗi "Duplicate column" thì bỏ qua dòng đó
-- Đây là bình thường nếu cột đã tồn tại

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
-- BƯỚC 2: COPY DỮ LIỆU TỪ CỘT CŨ SANG CỘT MỚI
-- ============================================

-- Copy birthdate -> dob
UPDATE guide_profiles SET dob = birthdate WHERE birthdate IS NOT NULL AND (dob IS NULL OR dob = '');

-- Copy avatar -> avatar_url (nếu avatar chỉ là tên file, thêm đường dẫn)
UPDATE guide_profiles 
SET avatar_url = CONCAT('/uploads/guides/', avatar) 
WHERE avatar IS NOT NULL AND avatar != '' AND (avatar_url IS NULL OR avatar_url = '');

-- Nếu avatar đã là URL đầy đủ, copy trực tiếp
UPDATE guide_profiles 
SET avatar_url = avatar
WHERE avatar LIKE 'http://%' OR avatar LIKE 'https://%'
  AND (avatar_url IS NULL OR avatar_url = '');

-- Copy certificate -> license (xử lý JSON và text)
-- Xử lý JSON array như ["HDV quốc tế"]
UPDATE guide_profiles 
SET license = JSON_UNQUOTE(JSON_EXTRACT(certificate, '$[0]'))
WHERE certificate IS NOT NULL 
  AND certificate LIKE '[%]' 
  AND (license IS NULL OR license = '');

-- Xử lý text thường
UPDATE guide_profiles 
SET license = certificate
WHERE certificate IS NOT NULL 
  AND certificate != ''
  AND certificate NOT LIKE '[%]'
  AND (license IS NULL OR license = '');

-- Copy group_type -> guide_group (chuyển đổi sang mã code)
-- Map các giá trị từ form: "Tour trong nước" = noi_dia, "Nội địa" = noi_dia
UPDATE guide_profiles 
SET guide_group = CASE 
    WHEN group_type = 'quốc tế' OR group_type = 'Tour quốc tế' OR group_type LIKE '%quốc tế%' THEN 'quoc_te'
    WHEN group_type = 'nội địa' OR group_type = 'Tour trong nước' OR group_type LIKE '%nội địa%' OR group_type LIKE '%trong nước%' THEN 'noi_dia'
    WHEN group_type LIKE '%tuyến%' THEN 'chuyen_tuyen'
    WHEN group_type LIKE '%đoàn%' THEN 'chuyen_khach_doan'
    WHEN group_type LIKE '%sinh thái%' THEN 'du_lich_sinh_thai'
    WHEN group_type LIKE '%mạo hiểm%' THEN 'du_lich_mao_hiem'
    ELSE NULL
END
WHERE group_type IS NOT NULL AND group_type != '' AND (guide_group IS NULL OR guide_group = '');

-- Copy speciality -> guide_type
UPDATE guide_profiles 
SET guide_type = speciality 
WHERE speciality IS NOT NULL AND speciality != '' AND (guide_type IS NULL OR guide_type = '');

-- Copy history -> tour_history
-- Nếu history là JSON, chuyển thành text
UPDATE guide_profiles 
SET tour_history = CAST(history AS CHAR)
WHERE history IS NOT NULL 
  AND (tour_history IS NULL OR tour_history = '');

-- Copy experience -> experience_detail
UPDATE guide_profiles 
SET experience_detail = experience 
WHERE experience IS NOT NULL 
  AND (experience_detail IS NULL OR experience_detail = '');

-- Trích xuất số năm từ experience
-- Nếu experience là "5 năm" -> 5
UPDATE guide_profiles 
SET experience_years = CAST(SUBSTRING_INDEX(experience, ' ', 1) AS UNSIGNED)
WHERE experience IS NOT NULL 
  AND experience REGEXP '^[0-9]+'
  AND (experience_years IS NULL OR experience_years = 0);

-- Nếu experience là số trực tiếp
UPDATE guide_profiles 
SET experience_years = CAST(experience AS UNSIGNED)
WHERE experience IS NOT NULL 
  AND experience REGEXP '^[0-9]+$'
  AND (experience_years IS NULL OR experience_years = 0);

-- Lấy full_name từ bảng users (nếu chưa có)
UPDATE guide_profiles gp
INNER JOIN users u ON gp.user_id = u.id
SET gp.full_name = u.name
WHERE (gp.full_name IS NULL OR gp.full_name = '') AND u.name IS NOT NULL;

-- Copy contact_email từ users nếu chưa có
UPDATE guide_profiles gp
INNER JOIN users u ON gp.user_id = u.id
SET gp.contact_email = u.email
WHERE (gp.contact_email IS NULL OR gp.contact_email = '') AND u.email IS NOT NULL;

-- Xử lý languages nếu là JSON array
-- Chuyển từ ["Tiếng Anh","Tiếng Việt"] thành "Tiếng Anh, Tiếng Việt"
UPDATE guide_profiles 
SET languages = REPLACE(REPLACE(REPLACE(languages, '[', ''), ']', ''), '"', '')
WHERE languages IS NOT NULL 
  AND languages LIKE '[%]'
  AND languages != '';

-- ============================================
-- BƯỚC 3: ĐẢM BẢO CẤU TRÚC BẢNG ĐÚNG
-- ============================================

-- Đảm bảo user_id là NOT NULL
ALTER TABLE guide_profiles 
MODIFY COLUMN user_id INT NOT NULL;

-- Xóa PRIMARY KEY cũ nếu có cột id riêng và user_id chưa phải là PRIMARY KEY
-- (Chỉ chạy nếu chắc chắn, có thể comment lại)
-- ALTER TABLE guide_profiles DROP PRIMARY KEY;

-- Đặt user_id làm PRIMARY KEY (nếu chưa có)
-- Chạy câu này nếu user_id chưa phải là PRIMARY KEY
-- ALTER TABLE guide_profiles ADD PRIMARY KEY (user_id);

-- Hoặc nếu muốn giữ cột id riêng, tạo UNIQUE INDEX cho user_id
-- (Cần thiết cho ON DUPLICATE KEY UPDATE để lưu được dữ liệu)
-- Chạy câu này nếu user_id chưa có UNIQUE constraint
-- CREATE UNIQUE INDEX uk_guide_profiles_user_id ON guide_profiles(user_id);

-- Kiểm tra và tạo UNIQUE nếu chưa có (thử tạo, nếu lỗi thì bỏ qua)
-- Lưu ý: Nếu đã có PRIMARY KEY trên user_id thì không cần UNIQUE INDEX

-- ============================================
-- BƯỚC 4: KIỂM TRA KẾT QUẢ
-- ============================================

-- Kiểm tra dữ liệu sau khi chuyển đổi
SELECT 
    user_id,
    full_name,
    dob,
    gender,
    avatar_url,
    id_number,
    address,
    phone,
    contact_email,
    license,
    guide_type,
    guide_group,
    languages,
    experience_years,
    experience_detail,
    notable_tours,
    tour_history,
    strengths,
    rating,
    health_status
FROM guide_profiles
LIMIT 5;

-- Kiểm tra cấu trúc bảng
DESCRIBE guide_profiles;

-- Kiểm tra dữ liệu của một hướng dẫn viên cụ thể (thay USER_ID bằng ID thực tế)
-- SELECT * FROM guide_profiles WHERE user_id = USER_ID;

-- Kiểm tra xem dữ liệu đã được copy chưa
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
