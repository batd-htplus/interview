Hệ thống Quản lý Phân bổ Nhân sự vào Dự án (Resource Allocation System)

**Flow nghiệp vụ chi tiết từng bước (Business Process Flow)** :

1. Flow tổng thể hệ thống
2. Flow quản lý Developer
3. Flow quản lý Project
4. Flow tạo Allocation (chi tiết nhất)
5. Flow tính Capacity
6. Flow Dashboard
7. Flow Forecast
8. Flow Audit & Permission

---

# I. FLOW TỔNG THỂ HỆ THỐNG

## 1. Chu trình vận hành cơ bản

1. Admin tạo User hệ thống
2. HR tạo Developer
3. PM tạo Project
4. PM phân bổ Developer vào Project
5. Hệ thống validate allocation
6. Dashboard tự động cập nhật capacity
7. Quản lý xem báo cáo

---

# II. FLOW QUẢN LÝ DEVELOPER

## Mục tiêu

Tạo và quản lý hồ sơ Developer phục vụ phân bổ nguồn lực.

---

## 1. Tạo Developer

### Actor: HR / Admin

### Bước thực hiện:

1. Vào màn hình "Quản lý Developer"
2. Nhấn "Tạo mới"
3. Nhập:

   * Tên
   * Email
   * Level
   * Team
   * Base capacity (mặc định 40h/tuần)
4. Thêm skill (tuỳ chọn)
5. Nhấn lưu

### Hệ thống xử lý:

* Validate email không trùng
* Lưu record vào bảng developers
* Lưu skill mapping
* Ghi log audit

---

## 2. Cập nhật Developer

### Bước:

1. Chọn Developer
2. Chỉnh thông tin
3. Nhấn lưu

### Hệ thống:

* Kiểm tra trạng thái
* Nếu dev inactive → không cho allocation mới
* Log thay đổi

---

# III. FLOW QUẢN LÝ PROJECT

## 🎯 Mục tiêu

Quản lý thông tin project để phục vụ phân bổ nguồn lực.

---

## 1. Tạo Project

### Actor: PM / Admin

### Bước:

1. Vào màn hình "Project"
2. Tạo mới
3. Nhập:

   * Tên project
   * Khách hàng
   * Start date
   * End date
   * Status
4. Lưu

### Hệ thống:

* Validate ngày bắt đầu < ngày kết thúc
* Lưu vào bảng projects
* Log audit

---

#IV. FLOW PHÂN BỔ DEVELOPER (QUAN TRỌNG NHẤT)

Đây là core nghiệp vụ.

---

## 🎯 Mục tiêu

Đảm bảo:

* Không vượt 100% capacity
* Không tạo allocation sai thời gian
* Không phân bổ dev inactive

---

## 1. Tạo Allocation

### Actor: PM

---

### Bước 1: Nhập thông tin

PM chọn:

* Developer
* Project
* Start date
* End date
* Allocation percent (VD: 50%)

---

### Bước 2: Hệ thống kiểm tra điều kiện cơ bản

1. Developer có active không?

   * Nếu không → reject

2. Project có active không?

   * Nếu không → reject

3. Ngày phân bổ có nằm trong thời gian project không?

   * Nếu ngoài → reject

---

### Bước 3: Kiểm tra overlap

Hệ thống:

* Lấy tất cả allocation hiện có của dev
* Lọc những allocation có overlap với khoảng thời gian mới

Overlap condition:

existing.start_date <= new.end_date
AND
existing.end_date >= new.start_date

---

### Bước 4: Tính tổng allocation theo từng tuần

Với mỗi tuần trong khoảng thời gian:

* Tính tổng allocation_percent hiện có
* Cộng thêm allocation mới
* Nếu > 100% → reject

---

### Bước 5: Lưu allocation

Nếu hợp lệ:

* Insert vào bảng allocations
* Ghi log audit
* Trigger cập nhật dashboard cache (nếu có)

---

## 2. Sửa Allocation

Flow tương tự tạo mới:

* Tạm loại allocation hiện tại
* Tính lại overlap
* Validate
* Update

---

## 3. Xoá Allocation

* Soft delete
* Log audit
* Recalculate capacity

---

# V. FLOW TÍNH CAPACITY

## 🎯 Mục tiêu

Tính trạng thái:

* Overloaded
* Normal
* Underutilized

---

## Logic

Ví dụ tính theo tháng:

1. Lấy tất cả allocation của dev trong tháng
2. Chia theo tuần
3. Tính trung bình allocation_percent

Nếu:

* > 100 → Overload
* 60–100 → Normal
* <60 → Underutilized

---

# VI. FLOW DASHBOARD

## 1. Dashboard Developer Capacity

### Khi mở dashboard:

1. User chọn tháng
2. Hệ thống:

   * Query allocation
   * Aggregate theo developer
   * Tính capacity
3. Trả về:

   * name
   * total_percent
   * status

---

## 2. Dashboard Project Utilization

1. Query allocations theo project
2. Tính tổng allocation_percent
3. Hiển thị:

   * Tổng resource
   * % sử dụng

---

## 3. Danh sách Overload

Query:

SELECT developer_id
GROUP BY week
HAVING SUM(allocation_percent) > 100

---

# VII. FLOW FORECAST

## 🎯 Mục tiêu

Biết tháng tới:

* Dev nào rảnh
* Project nào thiếu người

---

## Flow

1. User chọn tháng tương lai
2. Hệ thống:

   * Kiểm tra allocation sắp kết thúc
   * Tính dev không có allocation
3. Trả về:

   * Danh sách dev rảnh
   * % free capacity

---

# VIII. FLOW PHÂN QUYỀN (RBAC)

## Admin

* Toàn quyền

## HR

* Manage developer
* Không chỉnh allocation

## PM

* Manage project
* Manage allocation

## Viewer

* Chỉ xem dashboard

---

# IX. FLOW AUDIT LOG

Mỗi hành động:

* Tạo allocation
* Sửa allocation
* Xoá allocation
* Sửa developer
* Sửa project

Hệ thống ghi:

* user_id
* action
* entity
* old_value
* new_value
* timestamp

---

# KẾT LUẬN NGHIỆP VỤ

Hệ thống có 3 trọng tâm chính:

1. Allocation validation
2. Capacity calculation
3. Dashboard aggregation

