# Tài liệu Phân tích & Ánh xạ CSDL - Dự án VTW

- **Ngày tạo:** 16/05/2024
- **Phiên bản:** 2.2 (Cập nhật chi tiết về Xác thực & Phân quyền)
- **Dựa trên file:** `sql_v3api_vietel_structure_2025 08 19.sql`

## 1. Đánh giá Tổng quan

CSDL hiện tại rất **phong phú và chi tiết**, đặc biệt là các bảng liên quan đến nghiệp vụ giáo dục (edu_*). Hầu hết các chức năng cốt lõi cho **Giai đoạn 1 (MVP)** đều có thể được xây dựng dựa trên dữ liệu có sẵn. Điều này là một lợi thế rất lớn, giúp giảm thiểu rủi ro và đẩy nhanh tiến độ.

Các bảng quan trọng nhất đã được xác định là:

- **`edu_teachers`**: Thông tin giáo viên.
- **`users`**: Bảng người dùng hệ thống.
- **`link_users_teachers`**: Bảng nối giữa `users` và `edu_teachers`.
- **`roles`**, **`roles_users`**: Bảng phân quyền (Chỉ đọc).
- **`edu_classes`**: Thông tin lớp học.
- **`edu_courses`**: Thông tin khóa học (VD: Toán 9, Lý 10).
- **`edu_domains`**: Thông tin môn học (VD: Toán, Lý, Hóa).
- **`students`**: Thông tin học sinh.
- **`parents`**: Thông tin phụ huynh.
- **`edu_sessions`**: Thông tin các buổi học (lịch trình).
- **`centers`**: Thông tin các chi nhánh/cơ sở.
- **`edu_student_session_attendance`**: Dữ liệu điểm danh.
- **`edu_student_academic_result`**: Dữ liệu điểm số và nhận xét.

## 2. Ánh xạ Chức năng và Dữ liệu (Giai đoạn 1 - MVP)

Bảng dưới đây ánh xạ các tính năng yêu cầu trong file `Project_Context.md` với các bảng và cột tương ứng trong CSDL.

| ID Tính năng | Mô tả Tính năng | Bảng CSDL liên quan | Cột CSDL chính | Trạng thái & Ghi chú |
| --- | --- | --- | --- | --- |
| **Module 1: Xác thực & Tài khoản** |  |  |  |  |
| `FEAT-1.1` | Đăng nhập | `users`, `link_users_teachers`, `edu_teachers` | `users.username`, `users.portal_password` | **Khả thi.** Webapp sẽ sử dụng cột `portal_password` để xác thực (dùng `password_hash` và `password_verify` của PHP). Join qua `link_users_teachers` để lấy `teacher_id`. |
| `FEAT-1.2` | Trang thông tin cá nhân | `edu_teachers` | `name`, `email`, `phone`, `school`, `dob` | **Khả thi.** Dữ liệu đã có sẵn. |
| **Module 2: Lịch biểu & Lớp học** |  |  |  |  |
| `FEAT-2.1` | Xem Thời khóa biểu | `edu_sessions`, `centers` | `teacher_id`, `class_id`, `from`, `to`, `date`, `center_id` | **Khả thi.** Truy vấn `edu_sessions` với `teacher_id`. Cần join với `centers` để hiển thị thông tin chi nhánh/cơ sở của buổi học. |
| `FEAT-2.2` | Xem danh sách lớp học | `edu_classes`, `edu_sessions` | `edu_sessions.teacher_id`, `edu_classes.name`, `edu_classes.code` | **Khả thi.** Lấy danh sách `class_id` duy nhất từ `edu_sessions` dựa trên `teacher_id`. |
| `FEAT-2.3` | Xem danh sách học sinh | `students`, `parents`, `edu_student_class` | `students.full_name`, `students.dob`, `students.avatar`, `students.aspiration`, `parents.fullname`, `parents.phone`, `parents.email` | **Khả thi.** Từ `class_id`, truy vấn `edu_student_class` để lấy `student_id`, sau đó join với `students` và `parents` để lấy đủ các thông tin yêu cầu. |
| **Module 3: Vận hành Lớp học** |  |  |  |  |
| `FEAT-3.1` | Điểm danh điện tử | `edu_student_session_attendance` | `student_id`, `session_id`, `attendance` | **Hoàn toàn khả thi.** Bảng này được thiết kế chính xác cho mục đích điểm danh. Webapp sẽ thực hiện các thao tác `INSERT` và `UPDATE` trên bảng này. |
| `FEAT-3.2` | Nhập điểm số & Nhận xét | `edu_student_academic_result` | `student_id`, `session_id`, `score`, `comment`, `btvn_max`, `btvn_complete`, `btvn_score`, `btvn_comment`, `btvn_complete_percent`, `btvn_score_100` | **Hoàn toàn khả thi.** Bảng này đã có đủ các trường chi tiết để ghi nhận tình hình học tập và làm bài tập về nhà của học sinh. |
| **Module 4: Giao tiếp** |  |  |  |  |
| `FEAT-4.1` | Nhận thông báo | `edu_class_tasks`, `edu_class_task_notifications` | `user_id`, `content`, `created_at` | **Không phù hợp/Không dùng nữa.** Bảng `edu_class_tasks` hiện đang dùng làm bảng tin lớp học nhưng cấu trúc không còn phù hợp. Bảng `edu_class_task_notifications` đã bị bỏ. Sẽ thay thế bằng bảng mới. |

### 2.1. Ghi chú Quan trọng về Xác thực & Phân quyền

- **Cơ chế Mật khẩu (`users` table):**
    - `password`: Được mã hóa bằng `scrypt` cho hệ thống NocoBase. **KHÔNG** sử dụng trường này cho webapp mới.
    - `portal_password`: Sẽ là trường mật khẩu chính cho webapp. Cần được xử lý bằng hàm `password_hash()` và `password_verify()` của PHP.
    - `login_token` & `login_token_expires_at`: Có thể được tận dụng để phát triển tính năng đăng nhập không cần mật khẩu (magic link) trong tương lai.
- **Cơ chế Phân quyền (Read-Only):**
    - **`roles`**: Bảng định nghĩa các quyền trong hệ thống (admin, gvch, qllh...). Webapp có thể đọc thông tin từ bảng này để hiển thị hoặc kiểm tra logic. **Tuyệt đối không được ghi/sửa/xóa.**
    - **`roles_users`**: Bảng gán quyền cho người dùng. Webapp sẽ đọc bảng này để xác định quyền hạn của người dùng đang đăng nhập. **Tuyệt đối không được ghi/sửa/xóa.**

## 3. Phân tích "Lỗ hổng" & Đề xuất Mở rộng

Dựa trên phân tích, đây là những chức năng (chủ yếu ở Giai đoạn 2 & 3) chưa có cấu trúc dữ liệu hỗ trợ trực tiếp hoặc cần cấu trúc mới tốt hơn.

| Chức năng yêu cầu | Phân tích "Lỗ hổng" | Đề xuất Giải pháp | Mức độ ưu tiên |
| --- | --- | --- | --- |
| **Bảng tin/Thông báo lớp học** (`FEAT-4.2`) | Bảng `edu_class_tasks` đang được dùng cho mục đích này nhưng cấu trúc phức tạp, không phù hợp để phát triển tiếp. | **Tạo bảng mới:** `edu_announcements` (`id`, `teacher_id`, `class_id`, `title`, `content` (`LONGTEXT`), `created_at`, `created_by_id`). | **Rất cao (GĐ 1.5)** |
| **Kho tài liệu** (`FEAT-5.2`) | Bảng `lms_documents` thuộc hệ thống khác. Dữ liệu tài liệu hiện tại đang lưu phân mảnh trong `edu_sessions` (cột `content_files`, `exercice_files`). | **Tạo bảng mới:** `edu_class_resources` (`id`, `session_id`, `class_id`, `teacher_id`, `title`, `file_url`, `description`, `type` ('content' | 'exercice'), `uploaded_at`). | Cao (GĐ 2) |
| **Soạn giáo án cá nhân** (`FEAT-5.3`) | Hoàn toàn chưa có bảng nào để lưu trữ giáo án cá nhân của giáo viên. | **Tạo bảng mới:** `edu_lesson_plans` (`id`, `teacher_id`, `title`, `content` (`LONGTEXT`), `file_url`, `created_at`, `updated_at`). | Trung bình (GĐ 3) |
| **To-do list cá nhân** (`FEAT-7.1`) | Hoàn toàn chưa có. | **Tạo bảng mới:** `edu_teacher_todos` (`id`, `teacher_id`, `content`, `is_completed`, `due_date`, `created_at`). | Trung bình (GĐ 3) |

### 3.1. Vấn đề Đồng bộ & Di chuyển Dữ liệu Tài liệu

Một thách thức quan trọng là dữ liệu tài liệu cũ đang nằm trong các cột `content_files` và `exercice_files` của bảng `edu_sessions` dưới dạng chuỗi URL phân tách bằng dấu phẩy.

**Phương án giải quyết:**

1. **Giai đoạn chuyển tiếp:** Webapp mới khi phát triển tính năng tài liệu sẽ:
    - **Ghi dữ liệu mới:** Mọi file upload mới sẽ được ghi vào bảng `edu_class_resources`.
    - **Đọc dữ liệu kết hợp:** Khi hiển thị tài liệu của một buổi học, hệ thống sẽ truy vấn từ cả bảng mới `edu_class_resources` VÀ đọc, tách chuỗi từ các cột `content_files`, `exercice_files` của buổi học đó để đảm bảo không bỏ sót dữ liệu cũ.
2. **Di chuyển dữ liệu nền (Background Migration):**
    - Viết một script (tác vụ chạy nền) để quét qua tất cả các bản ghi trong `edu_sessions` có chứa dữ liệu trong `content_files` và `exercice_files`.
    - Script sẽ tách chuỗi URL, và với mỗi URL, tạo một bản ghi tương ứng trong bảng `edu_class_resources` mới.
    - Quá trình này có thể thực hiện từ từ để không ảnh hưởng đến hiệu năng hệ thống.

### 3.2. Tóm tắt các bảng cần tạo mới:

1. `edu_announcements`: Để lưu thông báo, thay thế chức năng bảng tin của `edu_class_tasks`.
2. `edu_class_resources`: Để quản lý tài liệu lớp học một cách tập trung.
3. `edu_lesson_plans`: Để lưu giáo án cá nhân.
4. `edu_teacher_todos`: Để quản lý công việc cá nhân.

**Quy ước đặt tên:** Sử dụng tiền tố `edu_` cho các bảng mới để đồng nhất với cấu trúc hiện có của CSDL.

## 4. Các bước Tiếp theo

1. **Xác nhận:** Vui lòng xem lại tài liệu đã cập nhật này.
2. **Thiết kế Schema Mở rộng:** Dựa trên các đề xuất đã được điều chỉnh, chúng ta sẽ viết các câu lệnh SQL `CREATE TABLE` chi tiết cho các bảng mới.
3. **Bắt đầu phát triển Giai đoạn 1 (MVP):** Sau khi có sự đồng thuận, đội ngũ lập trình có thể bắt đầu xây dựng các tính năng của Giai đoạn 1 vì CSDL đã sẵn sàng.