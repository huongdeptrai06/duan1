-- Script kiểm tra dữ liệu trong bảng guide_profiles
-- Chạy trong phpMyAdmin để xem dữ liệu hiện có

USE duan1;

-- Kiểm tra cấu trúc bảng
DESCRIBE guide_profiles;

-- Xem tất cả dữ liệu trong bảng
SELECT * FROM guide_profiles;

-- Kiểm tra dữ liệu theo user_id (thay USER_ID bằng ID thực tế)
-- Ví dụ: SELECT * FROM guide_profiles WHERE user_id = 1;

-- Kiểm tra xem có dữ liệu trong các cột mới không
SELECT 
    user_id,
    full_name,
    dob,
    avatar_url,
    guide_group,
    guide_type,
    phone,
    languages,
    rating
FROM guide_profiles
WHERE user_id IN (SELECT id FROM users WHERE role IN ('guide', 'huong_dan_vien'));

-- Kiểm tra xem có dữ liệu trong các cột cũ không
SELECT 
    user_id,
    birthdate,
    avatar,
    group_type,
    speciality,
    phone,
    languages,
    rating
FROM guide_profiles
WHERE user_id IN (SELECT id FROM users WHERE role IN ('guide', 'huong_dan_vien'));

-- So sánh dữ liệu giữa users và guide_profiles
SELECT 
    u.id,
    u.name,
    u.email,
    gp.user_id,
    gp.full_name,
    gp.avatar_url,
    gp.avatar as avatar_cu
FROM users u
LEFT JOIN guide_profiles gp ON u.id = gp.user_id
WHERE u.role IN ('guide', 'huong_dan_vien');

