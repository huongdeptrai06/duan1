# Hướng dẫn chạy SQL để sửa bảng guide_profiles

## Mục đích
File SQL này sẽ:
- Thêm các cột mới mà code đang cần
- Copy dữ liệu từ cột cũ sang cột mới
- Map các giá trị (ví dụ: "Tour trong nước" → "noi_dia")
- Xử lý dữ liệu JSON thành text

## Các bước thực hiện:

### Bước 1: Backup database
⚠️ **QUAN TRỌNG:** Luôn backup database trước khi chạy SQL!

1. Mở phpMyAdmin
2. Chọn database `duan1`
3. Click tab "Export"
4. Chọn "Quick" và "SQL"
5. Click "Go" để tải file backup

### Bước 2: Chạy file SQL
1. Mở phpMyAdmin
2. Chọn database `duan1`
3. Click tab "SQL"
4. Mở file `database/SUA_BANG_GUIDE_PROFILES.sql`
5. Copy toàn bộ nội dung
6. Dán vào khung SQL
7. Click "Go" để chạy

### Bước 3: Xử lý lỗi (nếu có)
- Nếu báo lỗi "Duplicate column name": Bỏ qua và tiếp tục (cột đã tồn tại)
- Nếu báo lỗi khác: Xem chi tiết lỗi và sửa lại

### Bước 4: Kiểm tra kết quả
1. Chạy câu lệnh kiểm tra trong file SQL (phần BƯỚC 3)
2. Hoặc chạy:
```sql
SELECT * FROM guide_profiles WHERE user_id = 11;
```
(Thay 11 bằng ID hướng dẫn viên của bạn)

3. Kiểm tra xem các cột mới đã có dữ liệu chưa:
   - `full_name`
   - `dob`
   - `avatar_url`
   - `guide_group`
   - `guide_type`
   - `license`
   - v.v.

### Bước 5: Làm mới trang web
1. Làm mới (refresh) trang chi tiết hướng dẫn viên
2. Kiểm tra xem thông tin đã hiển thị đúng chưa

## Lưu ý:
- File SQL sẽ KHÔNG xóa dữ liệu cũ, chỉ thêm cột mới và copy dữ liệu
- Các cột cũ vẫn được giữ lại để đảm bảo an toàn
- Nếu có vấn đề, có thể khôi phục từ backup

## Nếu vẫn không hiển thị:
1. Kiểm tra log PHP để xem có lỗi gì không
2. Kiểm tra lại database xem dữ liệu có được copy đúng không
3. Kiểm tra xem code có đọc đúng các cột không

