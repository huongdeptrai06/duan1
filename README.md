# Website Quản Lý Tour

Dự án PHP cơ bản mô phỏng cấu trúc MVC tối giản phục vụ học tập.

## Tổng quan cấu trúc

- `index.php`: điểm vào, định tuyến request bằng `match`.
- `config/config.php`: cấu hình chung và thông tin kết nối DB.
- `src/helpers/`: các hàm tiện ích (`render`, `asset`...).
- `src/models/`: các lớp đại diện dữ liệu ví dụ (`User`).
- `src/controllers/`: nghiệp vụ mẫu (`HomeController` với nhiều action).
- `views/`: giao diện tương ứng mỗi action (trang chủ, giỏ hàng, thanh toán...).
- `public/`: tài nguyên tĩnh (css/js/images).
- `.htaccess`: Dùng Rewrite URL Chuyển từ dạng "index.php?act=home" thành "/home"

## Cài đặt nhanh

1. Clone dự án về máy.
2. Cập nhật `config/config.php` cho đúng thông tin DB và BASE_URL theo môi trường của bạn.
3. Khởi động webserver (Laragon/XAMPP/Nginx...) và truy cập đường dẫn tương ứng để sử dụng.

### Bảng bổ sung: guide_profiles (khuyến nghị)

Trang chi tiết hướng dẫn viên đọc thêm dữ liệu chuyên sâu từ bảng `guide_profiles`. Nếu bảng chưa tồn tại, hệ thống vẫn chạy và hiển thị “Chưa cập nhật” cho các trường thiếu thông tin.

```sql
CREATE TABLE guide_profiles (
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
);
```

- Nếu đã có bảng `guide_profiles`, thêm các cột mới bằng các lệnh `ALTER TABLE` tương ứng với danh sách trên để lưu ảnh đại diện, phân nhóm HDV, lịch sử dẫn tour, đánh giá năng lực và tình trạng sức khỏe.