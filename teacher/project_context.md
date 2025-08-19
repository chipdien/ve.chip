# Bối cảnh Dự án: Webapp cho Giáo viên VietElite

- **Ngày tạo:** 16/05/2024
- **Phiên bản:** 1.0
- **Người tạo:** Agent The Architect

## 1. Tổng quan Dự án (Project Overview)

- **Tên dự án:** VietElite Teacher Workspace (VTW)
- **Mục tiêu:** Xây dựng một nền tảng webapp "tất cả trong một" (all-in-one) nhằm số hóa và tối ưu hóa các quy trình nghiệp vụ hàng ngày của giáo viên tại Hệ thống Giáo dục VietElite.
- **Vấn đề cần giải quyết:** Công việc của giáo viên hiện đang phân mảnh trên nhiều công cụ (sổ sách giấy, Zalo, Excel, Google Drive), gây lãng phí thời gian, khó quản lý tập trung và thiếu tính nhất quán.
- **Giải pháp đề xuất:** Một webapp tập trung, cung cấp đầy đủ công cụ từ quản lý lớp học, lịch biểu, giao tiếp đến các tiện ích cá nhân, giúp giáo viên làm việc hiệu quả và chuyên nghiệp hơn.

## 2. Nguồn lực & Ràng buộc (Resources & Constraints)

### 2.1. Nguồn lực chính

- **Cơ sở dữ liệu (Database):**
    - **Hệ quản trị CSDL:** **MySQL**.
    - **Trạng thái:** Đã có sẵn, đang hoạt động (live) và chứa dữ liệu thật của trung tâm.
    - **Nhiệm vụ:** **PHẢI TẬN DỤNG TỐI ĐA** dữ liệu từ CSDL này. Đây là nguồn dữ liệu chính (single source of truth) cho các thông tin về lớp học, học sinh, giáo viên, thời khóa biểu.

### 2.2. Ràng buộc & Quy tắc

- **Tương thích ngược (Backward Compatibility):** Việc phát triển webapp **KHÔNG ĐƯỢC LÀM GIÁN ĐOẠN** hoặc **GÂY LỖI** cho các hệ thống hiện tại đang sử dụng CSDL này.
- **Mở rộng CSDL:**
    - Được phép đề xuất thay đổi, mở rộng schema của CSDL (thêm bảng, thêm cột).
    - **Quy tắc:** Mọi thay đổi phải tuân thủ nguyên tắc "an toàn":
        - **Ưu tiên thêm bảng mới** có quan hệ (foreign key) với các bảng cũ thay vì thêm nhiều cột vào bảng cũ. Ví dụ: Tạo bảng `vtw_announcements` để lưu thông báo thay vì thêm cột vào bảng `classes`.
        - Khi thêm cột mới vào bảng cũ, cột đó phải có giá trị mặc định (DEFAULT) hoặc cho phép NULL để không ảnh hưởng đến các bản ghi hiện có.
        - Mọi thay đổi schema phải được ghi lại (document) cẩn thận.

## 3. Chiến lược Tích hợp CSDL (Database Integration Strategy)

Đây là bước **TIÊN QUYẾT** trước khi bắt đầu code.

1. **Giai đoạn 0: Phân tích & Ánh xạ (Schema Analysis & Mapping)**
    - **Nhiệm vụ:**
        - Phân tích toàn bộ schema của CSDL MySQL hiện có.
        - Xác định các bảng và cột chứa thông tin quan trọng: `teachers`, `students`, `classes`, `schedules`, `parent_contacts`, `grades` (nếu có).
        - Tạo một tài liệu ánh xạ (mapping document) chỉ rõ: "Tính năng X trong webapp sẽ sử dụng dữ liệu từ Bảng Y, Cột Z".
2. **Giai đoạn 0.5: Đề xuất Mở rộng Schema (Schema Extension Proposal)**
    - Dựa trên tài liệu ánh xạ và yêu cầu chức năng, xác định những dữ liệu còn thiếu.
    - Soạn thảo một tài liệu đề xuất các thay đổi/bổ sung vào schema. Mỗi đề xuất phải có lý do rõ ràng và đánh giá tác động.
    - **Ví dụ:** Để làm tính năng điểm danh, cần kiểm tra xem đã có bảng `attendance_records` chưa. Nếu chưa, đề xuất tạo mới với cấu trúc: `id`, `schedule_id`, `student_id`, `status`, `note`, `timestamp`.

## 4. Yêu cầu Chức năng (Functional Requirements)

Dự án được chia thành 3 giai đoạn phát triển chính.

### Giai đoạn 1: MVP - Nền tảng Vận hành Cơ bản (2-3 tháng)

*Mục tiêu: Đáp ứng nhu cầu cấp thiết nhất, giúp giáo viên làm quen hệ thống.*

- **Module 1: Xác thực & Tài khoản**
    - `FEAT-1.1`: Đăng nhập bằng tài khoản do trung tâm cấp.
    - `FEAT-1.2`: Trang thông tin cá nhân cơ bản (lấy từ CSDL).
- **Module 2: Lịch biểu & Lớp học**
    - `FEAT-2.1`: Hiển thị Thời khóa biểu cá nhân trực quan (theo tuần/tháng).
    - `FEAT-2.2`: Xem danh sách các lớp mình phụ trách.
    - `FEAT-2.3`: Xem danh sách học sinh và thông tin liên hệ phụ huynh của từng lớp.
- **Module 3: Vận hành Lớp học**
    - `FEAT-3.1`: **Điểm danh điện tử** theo buổi học.
    - `FEAT-3.2`: **Nhập điểm số** (điểm thành phần, điểm cuối kỳ) cho học sinh.
- **Module 4: Giao tiếp**
    - `FEAT-4.1`: Nhận thông báo chung từ ban quản lý trung tâm.

### Giai đoạn 2: Mở rộng Tương tác & Tự động hóa (2 tháng)

*Mục tiêu: Tăng cường hiệu quả và giao tiếp hai chiều.*

- **Module 5: Bài tập & Tài nguyên**
    - `FEAT-5.1`: Giao bài tập về nhà (tiêu đề, nội dung, file đính kèm, hạn nộp).
    - `FEAT-5.2`: Kho tài liệu học tập: Upload và chia sẻ tài liệu cho từng lớp.
- **Module 4: Giao tiếp (Nâng cấp)**
    - `FEAT-4.2`: Gửi thông báo đến một hoặc nhiều lớp học mình quản lý.
- **Module 6: Báo cáo**
    - `FEAT-6.1`: Xuất file Excel báo cáo điểm danh theo tháng/quý.
    - `FEAT-6.2`: Xuất file Excel sổ điểm của lớp học.

### Giai đoạn 3: Hoàn thiện & Tiện ích Nâng cao (2-3 tháng)

*Mục tiêu: Biến webapp thành trợ lý ảo toàn diện.*

- **Module 5: Bài tập & Tài nguyên (Nâng cấp)**
    - `FEAT-5.3`: Xây dựng không gian soạn và lưu trữ giáo án cá nhân.
- **Module 7: Tiện ích Cá nhân**
    - `FEAT-7.1`: Ghi chú cá nhân và danh sách công việc (To-do list).
- **Module 8: Tích hợp Hệ thống**
    - `FEAT-8.1` (Tùy chọn): Tích hợp API để xem thông tin chấm công, phiếu lương (nếu hệ thống nhân sự cho phép).
    - `FEAT-8.2`: Đồng bộ lịch dạy với Google Calendar/Microsoft Calendar.

## 5. Yêu cầu Phi chức năng (Non-Functional Requirements)

- **Giao diện người dùng (UI/UX):**
    - Thân thiện, dễ sử dụng, sạch sẽ, chuyên nghiệp.
    - Tương thích tốt trên các trình duyệt phổ biến (Chrome, Firefox, Safari).
    - **Responsive:** Hoạt động tốt trên cả máy tính và máy tính bảng. Ưu tiên trải nghiệm trên desktop.
- **Hiệu suất (Performance):**
    - Thời gian tải trang < 3 giây.
    - Phản hồi các thao tác (điểm danh, nhập điểm) < 1 giây.
- **Bảo mật (Security):**
    - Dữ liệu phải được mã hóa khi truyền tải (HTTPS).
    - Phân quyền chặt chẽ: Giáo viên chỉ được xem/sửa dữ liệu của các lớp mình được phân công.

## 6. Đề xuất Công nghệ (Recommended Tech Stack)

- **Frontend:**
    - **Framework:** React.js hoặc Vue.js (Ưu tiên React vì cộng đồng lớn và hệ sinh thái mạnh).
    - **UI Library:** Material-UI (MUI) hoặc Ant Design để phát triển nhanh và đồng bộ.
- **Backend:**
    - **Ngôn ngữ:** Node.js (Express.js) hoặc PHP (Laravel). Lựa chọn tùy thuộc vào kinh nghiệm của team, nhưng Node.js sẽ cho hiệu năng tốt với các tác vụ real-time.
    - **API:** Xây dựng theo kiến trúc RESTful API hoặc GraphQL.
- **Database:** **MySQL** (Bắt buộc).

## 7. Tiêu chí Thành công (Success Criteria)

- **Mức độ ứng dụng:** > 85% giáo viên sử dụng các tính năng của Giai đoạn 1 hàng tuần sau 1 tháng triển khai.
- **Hiệu quả công việc:** Giảm > 30% thời gian cho các công việc hành chính thủ công (theo khảo sát trước và sau khi triển khai).
- **Mức độ hài lòng:** Điểm hài lòng trung bình của giáo viên > 4.0/5.0.
- **Độ ổn định:** Uptime của hệ thống > 99.5%.