# Hướng dẫn sửa lỗi "Không thể tải danh sách hướng dẫn viên"

## Vấn đề
Khi vào trang danh sách hướng dẫn viên, hiển thị thông báo lỗi: **"Không thể tải danh sách hướng dẫn viên"**

## Nguyên nhân có thể

1. **Bảng `guide_profiles` chưa được tạo** trong database
2. **Bảng `users` chưa có cột `role`** hoặc dữ liệu không đúng
3. **Lỗi kết nối database**

## Cách khắc phục

### Bước 1: Kiểm tra và tạo bảng `guide_profiles`

1. Mở phpMyAdmin hoặc công cụ quản lý MySQL của bạn
2. Chọn database `duan1` (hoặc tên database của bạn trong `config/config.php`)
3. Chạy SQL sau để tạo bảng `guide_profiles`:

```sql
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
```

**Hoặc** chạy file SQL có sẵn:
- Mở file: `database/create_guide_profiles.sql`
- Copy toàn bộ nội dung và chạy trong phpMyAdmin

### Bước 2: Kiểm tra bảng `users` có cột `role` không

Chạy SQL để kiểm tra:

```sql
DESCRIBE users;
```

Nếu không có cột `role`, chạy:

```sql
ALTER TABLE users ADD COLUMN role VARCHAR(50) DEFAULT 'huong_dan_vien';
```

### Bước 3: Kiểm tra hướng dẫn viên đã được tạo chưa

Chạy SQL để xem danh sách users có role là 'guide' hoặc 'huong_dan_vien':

```sql
SELECT id, name, email, role, status, created_at 
FROM users 
WHERE role IN ('guide', 'huong_dan_vien');
```

### Bước 4: Kiểm tra kết nối database

Kiểm tra file `config/config.php` đã cấu hình đúng chưa:

```php
'db' => [
    'host' => 'localhost',
    'name' => 'duan1',  // Đổi thành tên database của bạn
    'user' => 'root',
    'pass' => '',  // Nhập mật khẩu nếu có
    'charset' => 'utf8mb4',
],
```

## Sau khi sửa

1. Làm mới trang danh sách hướng dẫn viên
2. Nếu vẫn lỗi, kiểm tra file log PHP để xem chi tiết lỗi
3. Thử tạo lại một hướng dẫn viên mới để kiểm tra

## Lưu ý

- Code đã được cập nhật để xử lý trường hợp bảng `guide_profiles` chưa tồn tại
- Nếu bảng chưa có, hệ thống vẫn hiển thị danh sách từ bảng `users`
- Bảng `guide_profiles` chỉ để lưu thông tin chi tiết bổ sung

