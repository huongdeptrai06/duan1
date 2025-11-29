# Hướng dẫn mở và chạy file SQL trong phpMyAdmin

## Cách 1: Mở file SQL trong Cursor/Editor

1. **Mở file SQL:**
   - Trong Cursor, mở file: `database/SUA_BANG_GUIDE_PROFILES.sql`
   - Hoặc tìm file tại: `C:\laragon\www\website_quan_ly_tour\database\SUA_BANG_GUIDE_PROFILES.sql`

2. **Copy toàn bộ nội dung:**
   - Nhấn `Ctrl + A` để chọn tất cả
   - Nhấn `Ctrl + C` để copy

## Cách 2: Chạy SQL trong phpMyAdmin

### Bước 1: Mở phpMyAdmin
1. Mở trình duyệt (Chrome, Firefox, Edge...)
2. Truy cập: `http://localhost/phpmyadmin` hoặc `http://localhost:8080/phpmyadmin`
3. Đăng nhập (thường là `root`, mật khẩu để trống)

### Bước 2: Chọn database
1. Ở cột bên trái, click vào database `duan1`
2. Nếu không thấy, kiểm tra lại tên database trong file `config/config.php`

### Bước 3: Mở tab SQL
1. Ở thanh menu phía trên, click tab **"SQL"**
2. Hoặc click vào biểu tượng SQL ở thanh công cụ

### Bước 4: Dán code SQL
1. Click vào khung text lớn (nơi có chữ "Run SQL query/queries on database...")
2. Nhấn `Ctrl + V` để dán code đã copy
3. Hoặc click vào nút **"Import files"** nếu muốn upload file trực tiếp

### Bước 5: Chạy SQL
1. Click nút **"Go"** (màu xanh) ở góc dưới bên phải
2. Hoặc nhấn phím `F5`

### Bước 6: Kiểm tra kết quả
- Nếu thành công: Sẽ hiển thị thông báo màu xanh "X rows affected"
- Nếu có lỗi: Sẽ hiển thị thông báo màu đỏ với chi tiết lỗi

## Cách 3: Import file SQL trực tiếp

1. Trong phpMyAdmin, chọn database `duan1`
2. Click tab **"Import"** (thay vì SQL)
3. Click **"Choose File"** hoặc **"Browse"**
4. Tìm và chọn file: `database/SUA_BANG_GUIDE_PROFILES.sql`
5. Click **"Go"** để import

## Lưu ý quan trọng:

⚠️ **BACKUP TRƯỚC KHI CHẠY:**
1. Trong phpMyAdmin, chọn database `duan1`
2. Click tab **"Export"**
3. Chọn **"Quick"** và format **"SQL"**
4. Click **"Go"** để tải file backup

## Nếu gặp lỗi:

### Lỗi "Duplicate column name":
- **Bình thường!** Cột đã tồn tại rồi
- Bỏ qua và tiếp tục chạy các câu lệnh khác

### Lỗi "Table doesn't exist":
- Kiểm tra xem bảng `guide_profiles` có tồn tại không
- Chạy file `database/create_guide_profiles.sql` trước

### Lỗi "Access denied":
- Kiểm tra quyền truy cập database
- Đảm bảo đã đăng nhập đúng tài khoản

## Đường dẫn file SQL:

```
C:\laragon\www\website_quan_ly_tour\database\SUA_BANG_GUIDE_PROFILES.sql
```

## Tóm tắt nhanh:

1. Mở file SQL trong Cursor → Copy toàn bộ
2. Mở phpMyAdmin → Chọn database `duan1` → Tab SQL
3. Dán code → Click "Go"
4. Xong! ✅

