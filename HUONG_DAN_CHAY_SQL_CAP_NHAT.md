# Hướng dẫn chạy SQL để cập nhật bảng guide_profiles

## Mục đích
File SQL này sẽ:
✅ Thêm các cột mới cần thiết cho form thêm/sửa hướng dẫn viên
✅ Copy dữ liệu từ cột cũ sang cột mới
✅ Đảm bảo dữ liệu hiển thị đúng sau khi thêm/sửa

## Các bước thực hiện:

### ⚠️ BƯỚC 0: BACKUP DATABASE (QUAN TRỌNG!)
1. Mở phpMyAdmin → Chọn database `duan1`
2. Tab **"Export"** → Chọn **"Quick"** → Format **"SQL"**
3. Click **"Go"** để tải file backup

### Bước 1: Mở file SQL
- File: `database/SUA_BANG_GUIDE_PROFILES.sql`
- Hoặc đường dẫn: `C:\laragon\www\website_quan_ly_tour\database\SUA_BANG_GUIDE_PROFILES.sql`

### Bước 2: Copy toàn bộ nội dung
- Nhấn `Ctrl + A` → `Ctrl + C`

### Bước 3: Chạy trong phpMyAdmin
1. Mở trình duyệt: `http://localhost/phpmyadmin`
2. Đăng nhập (user: `root`, pass: để trống)
3. Chọn database `duan1` ở sidebar trái
4. Click tab **"SQL"** ở menu trên
5. Dán code vào khung text (`Ctrl + V`)
6. Click nút **"Go"** để chạy

### Bước 4: Xử lý lỗi (nếu có)

#### ✅ Lỗi "Duplicate column name"
- **Bình thường!** Cột đã tồn tại rồi
- Bỏ qua và tiếp tục chạy các câu lệnh khác

#### ❌ Lỗi khác
- Xem chi tiết lỗi
- Kiểm tra lại cấu trúc bảng hiện tại
- Có thể cần chạy từng phần một

### Bước 5: Kiểm tra kết quả
Chạy câu lệnh này để kiểm tra:
```sql
SELECT * FROM guide_profiles WHERE user_id = 11;
```
(Thay `11` bằng ID hướng dẫn viên của bạn)

Xem các cột sau đã có dữ liệu chưa:
- ✅ `full_name`
- ✅ `dob`
- ✅ `gender`
- ✅ `id_number`
- ✅ `address`
- ✅ `phone`
- ✅ `contact_email`
- ✅ `license`
- ✅ `guide_type`
- ✅ `guide_group`
- ✅ `languages`
- ✅ `experience_years`
- ✅ `experience_detail`
- ✅ `notable_tours`
- ✅ `tour_history`
- ✅ `strengths`
- ✅ `rating`
- ✅ `health_status`
- ✅ `avatar_url`

### Bước 6: Test lại trên website
1. Làm mới trang chi tiết hướng dẫn viên
2. Kiểm tra xem thông tin đã hiển thị đúng chưa
3. Thử thêm một hướng dẫn viên mới
4. Kiểm tra xem dữ liệu có được lưu và hiển thị không

## Lưu ý quan trọng:

1. **Luôn backup trước khi chạy SQL**
2. **Lỗi "Duplicate column" là bình thường** - bỏ qua
3. **Chạy toàn bộ file** - không bỏ sót
4. **Kiểm tra kết quả** sau khi chạy

## Nếu vẫn không hiển thị dữ liệu:

1. Kiểm tra log PHP để xem có lỗi gì không
2. Kiểm tra xem dữ liệu có được lưu vào database không
3. Kiểm tra lại cấu trúc bảng xem có đủ các cột không
4. Xem lại code có đọc đúng các cột không

## File SQL bao gồm:

- ✅ **BƯỚC 1**: Thêm các cột mới
- ✅ **BƯỚC 2**: Copy dữ liệu từ cột cũ
- ✅ **BƯỚC 3**: Đảm bảo cấu trúc bảng đúng
- ✅ **BƯỚC 4**: Kiểm tra kết quả

Sau khi chạy xong, dữ liệu sẽ hiển thị đúng trên trang chi tiết hướng dẫn viên! 🎉

