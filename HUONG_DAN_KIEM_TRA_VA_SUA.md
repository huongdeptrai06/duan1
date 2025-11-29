# Hướng dẫn kiểm tra và sửa lỗi hiển thị thông tin hướng dẫn viên

## Vấn đề
Thông tin đã được cập nhật nhưng không hiển thị trên trang chi tiết hướng dẫn viên.

## Các bước kiểm tra và sửa:

### Bước 1: Kiểm tra dữ liệu trong database

1. Mở phpMyAdmin
2. Chọn database `duan1`
3. Chạy file SQL: `database/check_guide_data.sql`
   - Copy nội dung file và paste vào SQL tab
   - Hoặc chạy các câu lệnh từng cái một

4. Kiểm tra:
   - Có dữ liệu trong bảng `guide_profiles` không?
   - Dữ liệu nằm ở cột cũ hay cột mới?
   - `user_id` có đúng không?

### Bước 2: Chạy script cập nhật cấu trúc bảng

1. Chạy file SQL: `database/SUA_BANG_GUIDE_PROFILES.sql`
   - File này sẽ:
     - Thêm các cột mới
     - Copy dữ liệu từ cột cũ sang cột mới
     - Map các giá trị

2. Sau khi chạy, kiểm tra lại bằng `check_guide_data.sql`

### Bước 3: Kiểm tra log lỗi

1. Mở file log PHP (thường ở `C:\laragon\logs\php_error.log` hoặc tương tự)
2. Tìm các dòng có chứa:
   - "Guide profile saved successfully"
   - "Guide profile found"
   - "Save guide profile failed"
   - "Mapped profile data"

3. Xem có lỗi gì không

### Bước 4: Kiểm tra lại trên website

1. Làm mới trang chi tiết hướng dẫn viên
2. Xem thông tin có hiển thị không
3. Kiểm tra ảnh có hiển thị không

### Bước 5: Nếu vẫn không hiển thị

**Kiểm tra SQL trực tiếp:**
```sql
-- Xem dữ liệu của một hướng dẫn viên cụ thể (thay USER_ID)
SELECT * FROM guide_profiles WHERE user_id = USER_ID;

-- Kiểm tra xem có dữ liệu trong các cột mới không
SELECT 
    user_id,
    full_name,
    dob,
    avatar_url,
    phone,
    guide_group,
    guide_type
FROM guide_profiles 
WHERE user_id = USER_ID;
```

**Kiểm tra cấu trúc bảng:**
```sql
DESCRIBE guide_profiles;
```

Xem các cột sau có tồn tại không:
- `full_name`
- `dob`
- `avatar_url`
- `guide_group`
- `guide_type`
- v.v.

## Các lỗi thường gặp:

### Lỗi 1: Cột chưa tồn tại
**Giải pháp:** Chạy file `SUA_BANG_GUIDE_PROFILES.sql` để thêm các cột

### Lỗi 2: Dữ liệu ở cột cũ
**Giải pháp:** File SQL sẽ tự động copy dữ liệu từ cột cũ sang cột mới

### Lỗi 3: user_id không khớp
**Giải pháp:** Kiểm tra lại `user_id` trong bảng `guide_profiles` có khớp với `id` trong bảng `users` không

### Lỗi 4: Dữ liệu NULL hoặc rỗng
**Giải pháp:** Cập nhật lại thông tin hướng dẫn viên thông qua form chỉnh sửa

## Lưu ý:

- Luôn backup database trước khi chạy các script SQL
- Chạy từng bước một và kiểm tra kết quả
- Nếu có lỗi, xem thông báo lỗi chi tiết để biết nguyên nhân

