# XpGame_WordPress

**Phát triển phần mềm mã nguồn mở Kì 7 LHU (Cuối kỳ)**  
Repository này chứa mã nguồn dự án WordPress mang tên **XpGame**, được phát triển trong khóa học phần mềm mở của trường LHU.

---

## 🧐 Giới thiệu

XpGame là một website dựa trên WordPress, có thể được sử dụng để triển khai tính năng blog, tin tức hoặc trò chơi (game) trực tuyến, tận dụng hệ quản trị nội dung (CMS) và plugin có sẵn. Mục tiêu là làm quen với phát triển website bằng WordPress, tùy biến theme/plugin, cấu hình hosting và bảo mật.

---

## 📂 Cấu trúc dự án

Một vài thư mục / file chính:

| Tên | Mô tả |
|---|---|
| `wp-admin/` | Phần quản trị backend của WordPress |
| `wp-content/` | Chứa theme, plugin, media, … |
| `wp-includes/` | Thư viện core của WordPress |
| `.htaccess` | Cấu hình URL rewrite & bảo mật nếu dùng Apache |
| `index.php` | File khởi tạo chính khi truy cập site |
| `wp-config-sample.php` / `wp-config.php` | File cấu hình kết nối CSDL và các thiết lập quan trọng |
| `readme.html` | Thông tin giới thiệu WordPress mặc định |
| `license.txt` | Thông tin bản quyền, giấy phép sử dụng |

---

## ⚙️ Yêu cầu & Cài đặt

### Yêu cầu

- PHP (phiên bản tương đương hoặc cao hơn so với yêu cầu WordPress bản bạn dùng)  
- MySQL / MariaDB  
- Web server hỗ trợ PHP & MySQL (Apache, Nginx, …)  
- Truy cập FTP hoặc quyền đặt file lên server

### Cài đặt

1. Clone repository vào thư mục server (hoặc host):
   ```bash
   git clone https://github.com/Persinus/XpGame_WordPress.git
   ```

2. Đặt tên file cấu hình:
   - Từ file `wp-config-sample.php`, sao chép và đổi tên thành `wp-config.php`
   - Điền thông tin database: tên DB, user, password, host

3. Tải toàn bộ file lên server hoặc copy vào thư mục public_html (hoặc tương đương).

4. Truy cập vào trang `/wp-admin/install.php` từ trình duyệt để chạy quá trình cài đặt (nếu chưa có database/tables).

5. Sau khi cài đặt, đăng nhập với tài khoản quản trị, tùy chỉnh theme, plugin nếu muốn.

---

## 🧰 Công nghệ sử dụng

- WordPress (PHP, MySQL)  
- HTML / CSS / JavaScript  
- Web server Apache hoặc Nginx  
- Sử dụng các plugin và theme WordPress có sẵn (chưa rõ cụ thể trong repo)

---

## 🔐 Bản quyền

- License: theo file `license.txt` trong repository.  
- WordPress là mã nguồn mở theo giấy phép GPL (GNU General Public License).  

---

## 🚀 Chạy và phát triển

Nếu bạn muốn phát triển tiếp:

- Tùy chỉnh theme / plugin trong `wp-content`  
- Chỉnh sửa cấu hình trong `wp-config.php`  
- Backup database thường xuyên  
- Tối ưu bảo mật (ví dụ: giới hạn truy cập `/wp-admin`, sử dụng HTTPS)

---

## 🤝 Đóng góp

Rất hoan nghênh pull request hoặc issue để cải thiện:

- Báo lỗi  
- Gợi ý tính năng mới  
- Tối ưu giao diện hoặc performance

---

## 📄 License

Xem chi tiết trong file `license.txt`.

---

*“WordPress is free software, and is released under the terms of the GPL v2 (or later).”* — Theo giấy phép WordPress và bản phân phối của dự án này.  

