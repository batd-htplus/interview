# Câu hỏi phỏng vấn & gợi ý trả lời — Full-stack PHP

Mỗi **Câu hỏi** là lời thoại phỏng vấn (có bối cảnh). **Trả lời** là ý ứng viên *nên* nói — không phải đọc thuộc từng từ.

---

## 1. Git & quy trình làm việc

**Câu hỏi:** Hai người cùng sửa một file, cả hai đều push. Một người bị conflict hoặc bị từ chối merge. Bạn giải thích cho họ sự khác nhau giữa **merge** và **rebase** là gì? Trường hợp nào **tuyệt đối không** nên rebase?

**Trả lời:** Merge giữ nguyên “đường đi” của hai nhánh và tạo một commit gộp — an toàn khi nhánh đã nhiều người dùng. Rebase là “đặt lại” nhánh của mình lên đỉnh nhánh gốc để lịch sử nhìn thẳng hơn, thường làm trước khi mở PR. Không nên rebase các commit **đã push lên nhánh chung** vì sẽ phải force push và làm lệch máy mọi người.

---

**Câu hỏi:** Production đang lỗi nặng. Trên nhánh `develop` đã có commit sửa, nhưng chưa merge. Bạn cần đưa **đúng phần sửa đó** lên nhánh hotfix/production nhanh nhất có thể. Bạn làm thế nào? Sau đó trên `main` có một merge **xấu** cần “lùi lại” mà không làm hỏng lịch sử cho cả team — dùng lệnh gì, vì sao không `reset --hard` + push ép?

**Trả lời:** **Cherry-pick** từng commit cần thiết sang nhánh hotfix. Để lùi thay đổi đã công bố trên `main`, dùng **revert** (tạo commit mới đảo ngược) vì không rewrite history — an toàn cho repo đã nhiều người kéo. `reset` + force push làm lịch sử trên máy người khác không khớp, trừ khi team có quy tắc rất đặc biệt.

---

**Câu hỏi:** Team 5–10 dev, mỗi người làm feature song song. Bạn sẽ quy định tên nhánh, nhánh chính (`main` / `develop`), và trước khi merge bắt buộc những bước nào — để tránh “merge xong production chết”?

**Trả lời:** Quy ước rõ nhánh (ví dụ `main` luôn deploy được, hoặc trunk-based). Mọi thay đổi qua **PR**, **CI chạy test xanh**, có người review; không merge khi build đỏ; có thể bắt buộc 1–2 người duyệt tùy rủi ro.

---

## 2. Database (SQL, hiệu năng, vận hành)

**Câu hỏi:** Khách phản ánh: tiền đã trừ, nhưng trong hệ thống **không thấy đơn hàng** (hoặc ngược lại). Bạn thiết kế luồng + database để **hai bên không bao giờ lệch** — hoặc nếu có cổng thanh toán bên ngoài thì xử lý thế nào?

**Trả lời:** Nếu cùng một database: gom tạo đơn + ghi thanh toán trong **một giao dịch** (commit cùng lúc, lỗi thì rollback cả hai). Nếu có hệ thống ngoài: đơn ở trạng thái **chờ**, webhook có **khóa idempotent** (gửi trùng không tạo hai đơ), ràng buộc **unique** trên mã giao dịch để không trừ tiền hai lần; retry phải an toàn. Nói rõ user nhìn thấy trạng thái “đang xác nhận” thế nào.

---

**Câu hỏi:** Một bảng vừa bị đọc nhiều vừa bị ghi nhiều. Dev cứ thêm index cho mọi filter, giờ **ghi chậm**. Bạn xử lý **theo thứ tự** như thế nào?

**Trả lời:** (1) Tìm query chậm bằng slow log và **EXPLAIN**, (2) bỏ hoặc gộp index thừa, (3) thiết kế lại **index ghép** đúng thứ tự cột theo đúng cách filter/sort, (4) nếu đọc nặng — **cache** hoặc **replica chỉ đọc** (5) bảng cực lớn có thể **chia partition** theo thời gian. Nhắc tránh N+1 ở ORM.

---

**Câu hỏi:** Bảng vài trăm triệu / tỷ dòng, cần đổi cấu trúc (thêm/sửa cột, index) nhưng **app không được tắt**. Bạn làm từng bước ra sao?

**Trả lời:** Mô hình **mở rộng → chuyển dữ liệu → thu hẹp**: thêm cột mới (nullable) → **backfill theo lô** (theo khóa chính, có nghỉ giữa các lô để DB thở) → deploy code đọc/ghi cả cũ và mới → chuyển hết sang cột mới → xóa cột cũ. Tránh `ALTER` khóa bảng lâu: dùng công cụ online schema (pt-osc MySQL, index concurrent Postgres) hoặc bảng bóng + trigger tùy case.

---

**Câu hỏi:** Cấu trúc PostgreSQL và MySQL InnoDB
**Trả lời:** 
PostgreSQL
    File vật lý:
        data file (heap) → chứa row
        WAL (Write-Ahead Log) → log thay đổi
    Format: page 8KB
    Mỗi table = nhiều file segment

    Luồng ghi (WRITE flow)
        INSERT/UPDATE
        Ghi vào WAL trước (quan trọng)
        Ghi vào buffer (RAM)
        Flush xuống disk sau

    👉 Nguyên tắc: WAL first → data later

    Luồng đọc (READ flow)
        Query → check buffer cache
        Nếu có → dùng luôn (rất nhanh)
        Nếu không → đọc từ disk
        Check MVCC visibility

    PostgreSQL
        Version nằm trong table
        Filter bằng xmin/xmax

    👉 Đọc = scan + check version

MySQL InnoDB
    File:
        .ibd → data + index (nếu file-per-table)
        redo log (ib_logfile)
        undo log
    Format: page 16KB
    Table = B+Tree nằm trong file

    UPDATE/INSERT
        Ghi redo log
        Ghi undo log (cho MVCC)
        Update page trong buffer pool
        Flush xuống disk sau

    👉 Nguyên tắc: redo + undo + buffer

    Row hiện tại + lần ngược undo log
    Tìm version đúng snapshot

    👉 Đọc = row + undo chain
---


**Câu hỏi:** Index trong PostgreSQL vs MySQL
**Trả lời:** 
PostgreSQL
    Index tách khỏi table
    Query: index → heap fetch

MySQL InnoDB
    Primary key = clustered index
    Dữ liệu nằm ngay trong index → PK lookup rất nhanh

==> InnoDB nhanh hơn PostgreSQL khi lookup PK, insert/update OLTP (Insert / Update / Delete). PostgreSQL mạnh hơn cho query phức tạp, JSON, full-text, analytic.

Nguyên tắc chung
    Chỉ index cột cần thiết → tránh quá nhiều index (INSERT/UPDATE chậm).
    Chọn cột high cardinality (giá trị khác nhau nhiều) → hiệu quả cao.
    Composite index → thứ tự phải theo WHERE + ORDER BY của query.
    Covering index → index chứa luôn cột query SELECT → tránh heap fetch.
    Tránh dùng index cho cột ít phân biệt hoặc LIKE '%…'.

Sai lầm:
    Index cột low cardinality
    LIKE '%…' → không dùng index
    Functional / expression mà không tạo index tương ứng
    Composite index thứ tự sai
    Quá nhiều index → giảm tốc độ ghi
---



**Câu hỏi:** Dùng soft delete (`deleted_at`). Báo cáo doanh thu đôi khi **lẫn** đơn đã huỷ, hoặc admin “mất” bản ghi vì query quên điều kiện. Bạn quy ước trong team thế nào? Với yêu cầu xóa dữ liệu cá nhân (GDPR) thì sao?

**Trả lời:** Global “ẩn bản ghi xóa” trong ORM dễ gây bug — tách rõ chỗ **có/không** gồm bản ghi đã xóa (admin vs báo cáo). Index hợp lý. GDPR: **ẩn danh** hoặc **xóa cứng** theo chính sách, lưu log, job dọn dữ liệu theo giai đoạn.

---

**Câu hỏi:** Sau khi user **vừa tạo** đơn hàng, chuyển sang trang chi tiết lại báo không có — nhưng vài giây sau lại thấy. Hệ thống đọc từ **replica**. Bạn xử lý ở tầng ứng dụng thế nào?

**Trả lời:** Ngay sau thao tác ghi quan trọng, **đọc từ primary** (hoặc gắn cờ “vừa tạo” để luồng đó chỉ đọc primary). Hoặc UI retry ngắn / trạng thái “đang đồng bộ”. Đo **độ trễ replica**.

---

**Câu hỏi:** Team lưu mọi thứ linh tinh vào một cột JSON vì “nhanh”. Sau này cần lọc/search theo field trong JSON trên bảng lớn. Bạn phân tích **được / mất** gì và hướng chỉnh?

**Trả lời:** JSON tiện khi schema hay đổi nhưng khó kiểm tra đúng sai, migration phức tạp, index/search thường **đắt**. Những field hay dùng để where/order nên **đưa ra cột thường**. Giữ version trong JSON nếu cần tương thích nhiều phiên bản client.

---

**Câu hỏi:** Công ty nói “có backup hàng ngày”. Khi sự cố xảy ra mới biết **restore** không lấy được đúng tới phút giao dịch, hoặc chưa bao giờ tập restore thật. Bạn giải thích RPO/RTO và việc cần làm **ngoài** việc bật backup?

**Trả lời:** **RPO**: chấp nhận mất bao nhiêu dữ liệu gần nhất. **RTO**: bao lâu phải lên lại dịch vụ. Cần **binlog/WAL** và retention đủ để point-in-time; **tập restore định kỳ**; mã hóa bản sao. Snapshot máy ảo không thay thế việc thử restore cơ sở dữ liệu thật.

---

## 3. PHP & Laravel

**Câu hỏi:** Laravel là gì? Có bao nhiêu lớp và ý nghĩa mỗi lớp thành phần?

**Trả lời:** 

| Layer | Vị trí / Class | Ý nghĩa |
|-------|----------------|---------|
| **Entry Point** | `public/index.php` | Bootstrapping app, đưa request vào Kernel |
| **HTTP Kernel / Middleware** | `app/Http/Kernel.php`, `app/Http/Middleware/` | Chạy global & route-specific middleware (auth, CSRF, logging) |
| **Routing** | `routes/web.php`, `routes/api.php` | Map URL → Controller, gắn middleware, route group |
| **Controller** | `app/Http/Controllers/` | Xử lý request, gọi Service/Model, trả Response |
| **Service / Business Logic** | `app/Services/` (tùy) | Tách logic nghiệp vụ, testable, maintainable |
| **Model / Eloquent ORM** | `app/Models/` | Mapping table → class, relationships, query, soft delete |
| **Database / Query Builder** | Core DB + Migration | Thực thi query, hỗ trợ nhiều DB, migration, seeder, factory |
| **View / Blade** | `resources/views/` | Template engine, layout, components, directives (`@if`, `@foreach`) |
| **Service Container / Facades** | Core Laravel | Dependency Injection, Facades proxy static-like access |
| **Events & Listeners** | `app/Events/`, `app/Listeners/` | Event-driven, tách concerns (ví dụ: gửi mail khi UserRegistered) |
| **Queues / Jobs** | `app/Jobs/` | Xử lý async tasks (email, notification, import), driver: DB/Redis/SQS |

---

**Câu hỏi:** Người dùng upload file vài trăm MB. Server báo timeout / hết RAM. Tăng `upload_max_filesize` và `max_execution_time` có đủ không? Giải pháp “đúng bài” cho production là gì?

**Trả lời:** Chỉ tăng cấu hình là **vá tạm**. Nên **chia nhỏ tải (chunk)** hoặc cho trình duyệt upload thẳng lên **S3/OSS** bằng **link ký sẵn**; server chỉ nhận tên file / id. Xử lý nặng đưa vào **queue**. Đọc file bằng **stream** không nạp hết vào bộ nhớ.

---

**Câu hỏi:** Cùng một API cho web React và app mobile. PM hỏi: “Mình dùng **session cookie** hay **JWT**?” — bạn trả lời theo hướng phải nói được **ưu/nhược** cho từng kịch bản.

**Trả lời:** **Session + cookie httpOnly** phù hợp web cùng site với API, dễ **huỷ phiên** trên server, cần chống **CSRF**. **JWT** tiện cho mobile / nhiều service nhưng **khó huỷ gấp**, dễ to payload, nếu cất ở localStorage thì nguy cơ **XSS**. Thường web first-party + Laravel → Sanctum cookie; mobile/OAuth tách luồng token có refresh.

---

**Câu hỏi:** Một API **đa số lúc nhanh**, nhưng thỉnh thoảng **chậm gấp nhiều lần**, log không có pattern cố định. Anh/chị sẽ **đi điều tra** theo thứ tự nào?

**Trả lời:** Thêm **correlation id** cho mỗi request; đo thời gian từng bước (DB, gọi HTTP ngoài, cache). Nghi ngờ: **cache miss dồn**, **chờ khóa**, IO đĩa, API đối tác chập chờn, **cạn pool** kết nối. Xem **p95/p99**, không chỉ trung bình; dùng APM nếu có.

---

**Câu hỏi:** Trong code có interface cổng thanh toán, hai bên triển khai khác nhau. Trên Laravel anh/chị **đăng ký** implementation đó ở đâu, và khi viết test anh/chị **thay** implementation giả thế nào?

**Trả lời:** `AppServiceProvider`: `bind` hoặc `singleton` theo config. Trong test: `swap`/`instance` trên container hoặc `Http::fake()` nếu gọi HTTP.

---

**Câu hỏi:** Endpoint trả 100 bài viết kèm tác giả, nhưng log SQL có **hơn 100** câu lệnh. Chuyện gì đang xảy ra và sửa ra sao?

**Trả lời:** **N+1**: trong vòng lặp render, mỗi bài lại lazy load tác giả. Dùng **eager load** `with('author')` hoặc tương đương; `loadMissing` khi cần.

---

**Câu hỏi:** Một request đi qua **middleware**, **Form Request**, rồi **Policy**. Mỗi thứ đảm nhiệm việc gì — ví dụ cụ thể?

**Trả lời:** **Middleware**: chuỗi xử lý HTTP chung — đăng nhập, giới hạn tốc độ. **Form Request**: kiểm tra **dữ liệu vào** hợp lệ. **Policy**: user **có được** thực hiện hành động đó trên model đó không (ví dụ chỉ chủ đơn mới huỷ được).

---

**Câu hỏi:** Queue xử lý gửi mail / đồng bộ đối tác. Worker restart, job chạy **hai lần**. Thanh toán webhook từ cổng ngoài cũng có thể **gửi trùng**. “Chạy job đúng một lần” có đảm bảo được không? Anh/chị thiết kế thế nào để **lặp lại vẫn an toàn**?

**Trả lời:** Hàng đợi thường là **ít nhất một lần (at-least-once)** — không tin “đúng một lần”. Mỗi tác vụ có **khóa idempotent** hoặc kiểm tra “đã xử lý chưa” trước khi có hậu quả (trừ tiền, tạo đơn). Cấu hình `tries`, `timeout`, **backoff**. Job độc nhất thời điểm khi cần (`ShouldBeUnique`). Poison message → DLQ/monitor.

---

**Câu hỏi:** Báo cáo tháng được cache 1 giờ. Sau deploy, số liệu “cố” sai một lúc; hoặc cache hết hạn **cùng lúc**, DB bị dồn toàn request. Cách xử lý?

**Trả lời:** **Phiên bản trong key** cache sau deploy; làm nóng cache **có khóa** (`Cache::lock`) để một tiến trình build, các request khác đợi hoặc đọc bản cũ có giới hạn; hết hạn cache **thêm jitter**; xóa cache theo sự kiện nghiệp vụ khi có thể.

---

**Câu hỏi:** Trên máy dev mọi thứ đọc `.env` bình thường, lên production sau `php artisan config:cache` thì app đọc sai cấu hình — có chỗ dùng `env()` trực tiếp trong code. Giải thích và cách chuẩn.

**Trả lời:** Sau `config:cache`, trong runtime **chỉ nên đọc `config()`** — giá trị đã gom từ file config. **`env()`** chỉ dùng trong file config khi build cache. Sửa code: thay `env('X')` bằng `config('xxx.x')`.

---

**Câu hỏi:** API dùng Resource của Laravel nhưng một field trong resource gọi thêm query (ví dụ tổng đơn) — lại N+1 ẩn. Làm sao **phát hiện** và **sửa**?

**Trả lời:** Bật cảnh báo lazy load trên môi trường dev; Telescope. Sửa bằng `withSum`, `loadCount`, subquery; **không** để field “tiện” gây query ngầm; list lớn bắt buộc **phân trang**.

---

**Câu hỏi:** Người gọi API gửi thêm field ẩn `is_admin: true` trong body update profile. Nếu không cẩn thận có thể nâng quyền. Chống thế nào?

**Trả lời:** **Whitelist** mass assignment; field nhạy cảm chỉ gán trong service/admin có kiểm tra **Policy**; test tự động từ chối field lạ.

---

**Câu hỏi:** Đổi định dạng ngày trong JSON response làm app cũ crash. Làm sao **đổi API** mà không “cắt cổ” khách hàng cũ?

**Trả lời:** **Phiên bản API** (`/v1`, `/v2`) hoặc **header** Accept; duy trì song song một thời gian; thông báo **ngừng dùng**; lỗi trả dạng chuẩn (problem+json); theo dõi client còn dùng version nào.

---

**Câu hỏi:** SPA React cùng domain với API Laravel vs app cần đăng nhập Google cho bên thứ ba — chọn **Sanctum** hay **Passport**? Nói ngắn gọn lý do.

**Trả lời:** **Sanctum** (cookie + CSRF) cho SPA “nhà làm” cùng site. **Passport/OAuth** khi làm **nhà cung cấp** token/OAuth cho app khác hoặc flow phức tạp. Luôn xoay refresh token hợp lý, không dính token lên URL.

---

**Câu hỏi:** Dùng **Octane** / worker sống lâu: thỉnh thoảng user A thấy dữ liệu của user B. Nguyên nhân kiểu gì và nguyên tắc code?

**Trả lời:** Biến **static/singleton** giữ **dữ liệu theo request** không được xóa giữa các request. Code phải **stateless** theo request; không cache “user hiện tại” trong singleton; theo dõi rò rỉ bộ nhớ, restart định kỳ.

---

**Câu hỏi:** SaaS nhiều công ty (tenant) dùng chung bảng với `tenant_id`. Làm sao **chắc chắn** không bao giờ lộ dữ liệu sang tenant khác — kể cả raw SQL và job nền?

**Trả lời:** Mọi truy vấn có **tenant_id**; middleware gắn tenant từ đăng nhập; unique `(tenant_id, business_key)`; job phải mang **tenant** trong payload; review mọi raw query. Yêu cầu cao: DB/connection tách hoặc RLS trên Postgres.

---

**Câu hỏi:** Sau khi `Order` được tạo, có gửi mail, push notification, sync kho. Đặt hết trong **Observer** có ổn không? Event nên chạy **trước hay sau** khi transaction commit?

**Trả lời:** Observer dễ thành “ổ” logic ẩn, khó test. Nên **event/listener** rõ ràng; side-effect chạy **sau `DB::afterCommit`** để tránh gửi mail rồi transaction rollback.

---

**Câu hỏi:** Sắp xếp danh sách theo tham số `?sort=` từ URL, dev nối thẳng tên cột vào SQL. Rủi ro và cách làm đúng?

**Trả lời:** **SQL injection**. Chỉ cho phép sort theo **danh sách cột cho phép** (whitelist); giá trị bind bình thường; không nối chuỗi người dùng vào SQL.

---

**Câu hỏi:** File upload được phục vụ bằng URL tĩnh, link chia sẻ là lộ file của người khác. Upload được file `.exe`. Xử lý?

**Trả lời:** Bucket **private**; URL **có chữ ký, hết hạn**. Kiểm tra **loại MIME/kích thước**; quét virus qua queue; ảnh/public qua CDN, tài liệu nhạy cảm không.

---

## 4. React & Vue — **đặc tính kỹ thuật** và **khác nhau cốt lõi**

*Mục tiêu:* kiểm tra ứng viên có hiểu **cơ chế** của từng công cụ (render, state/reactivity, build) — không chỉ làm được giao diện.

---

### 4.1 React — đặc trưng kỹ thuật (middle → senior)

| Đặc tính | Giải thích ngắn (đủ để phỏng vấn nói miệng) |
|----------|-----------------------------------------------|
| **Thư viện UI, không “full-stack framework” mặc định** | Cốt lõi: cây giao diện + đồng bộ DOM. **Router, store, fetch data** là lớp team gắn thêm. |
| **Component + JSX** | JSX biên dịch thành lời gọi tạo element. Gần HTML nhưng là **JavaScript**. |
| **Virtual DOM + reconciliation** | State đổi → tạo **cây mới**, **diff** với cây cũ, cập nhật DOM tối thiểu. Engine hiện đại: **Fiber** (có thể chia nhỏ công việc – nền tảng concurrent). |
| **Một chiều (props xuống)** | Con nhận `props`; báo lên cha bằng **callback**. Form **controlled**: state là nguồn đúng. |
| **`useState` / `useReducer`** | Batched updates; cập nhật phụ thuộc giá trị trước → dùng **hàm** trong setter. |
| **Hooks** | Logic tái sử dụng theo vòng đời component — **Rules of Hooks** (top-level, thứ tự cố định). |
| **`useEffect`** | Chạy **sau render**; side-effect & cleanup — dễ sai **dependency** và **race** request. |
| **`memo` / memo hóa con** | So sánh **nông** props; object/array **reference mới** mới kích hoạt render. **Mutate** state cũ → dễ không vẽ hoặc vẽ sai. |
| **Strict Mode (dev)** | Cố tình **gọi đôi** một số thứ để lộ side-effect — không phải bug production. |
| **SSR / hydration** | HTML server + kích hoạt React client; lần đầu **phải khớp** (tránh `Date.now`, `window` trong render đầu). |

---

### 4.2 Vue — đặc trưng kỹ thuật (middle → senior)

| Đặc tính | Giải thích ngắn |
|----------|-----------------|
| **SFC `.vue`** | `template` + `script` + `style`; build biên dịch template → **render function** (tối ưu compile-time). |
| **Template + directive** | `v-if` / `v-for` / `v-bind` / `v-on`; Vue 3: **static hoisting**, **block tree** — patch nhanh hơn vùng ít đổi. |
| **Reactivity Vue 3 (`Proxy`)** | Đọc → **track**, ghi → **trigger**. Gán thuộc tính reactive có thể tự cập nhật view (không cần `setState`). |
| **`ref` vs `reactive`** | `ref`: bọc giá trị (`.value` trong script); template **unwrap**. `reactive`: object được proxy. |
| **`v-model`** | **Cú pháp** cho cặp giá trị + sự kiện (2 chiều có kiểm soát). Vue 3 hỗ trợ **nhiều v-model** / tùy chỉnh. |
| **Composition API** | `setup` / `<script setup>` + **composables** — tương tự ý **tách logic** như hooks. |
| **Options API** | `data`, `computed`, `watch`, `methods` — vẫn dùng song song Vue 3. |
| **`watch` vs `watchEffect`** | `watch`: nguồn rõ ràng. `watchEffect`: auto **thu dependency** khi đọc reactive trong hàm. |
| **Pinia** | Store module; **`storeToRefs`** khi destructuring state (giữ reactive). |
| **Vue Router** | **Navigation guard**, lazy route, `meta` (quyền). |

---

### 4.3 Bảng so trực diện React **vs** Vue

| Khía cạnh | React | Vue |
|-----------|-------|-----|
| **Viết giao diện** | JSX (JS) | Template (HTML-like) + optional JSX |
| **Đổi dữ liệu → đổi màn** | `setState` / dispatch → schedule render | Gán lên reactive / `ref.value` |
| **Form 2 chiều** | Tự nối `value` + `onChange` | `v-model` |
| **Tái dùng logic** | Custom Hooks | Composables |
| **Hệ sinh thái “mặc định”** | Tự chọn router/store | Router + Pinia + compiler rất gắn Vue |

---

### 4.4 Câu “chốt” gắn đặc tính — **hỏi / đáp** cực ngắn

**Hỏi:** Sao `push` vào **cùng một mảng** trong state React đôi khi **không** vẽ lại?

**Đáp:** **Tham chiếu mảng không đổi** — cần **mảng mới** (`copy` + thêm phần tử) để React thấy state đổi.

---

**Hỏi:** Vue 3 **bóc** `props` trong `setup` ra biến rồi UI **không** theo?

**Đáp:** **Mất kết nối reactive** — dùng `toRefs(props)` hoặc đọc `props.tên` / `computed`.

---

**Hỏi:** `key={index}` trong `map` / `v-for` khi nào **rác**?

**Đáp:** Khi **đổi thứ tự / chèn xóa** — DOM tái dùng sai → **ô nhập / state dòng** lệch.

---

**Hỏi:** `useTransition` (React) **ý tưởng** là gì?

**Đáp:** **Ưu tiên** cập nhật nhẹ (gõ phím), **hoãn** cập nhật nặng (lọc list lớn) — cảm giác mượt hơn.

---

**Hỏi:** Hydration mismatch **một câu**?

**Đáp:** HTML **render lần đầu** trên server **≠** lần đầu trên client.

---

### 4.5 **Màu sắc, theme (sáng/tối)** — cách nói kỹ thuật khi phỏng vấn

| Chủ đề | React (hay gặp) | Vue (hay gặp) |
|--------|----------------|----------------|
| **Nguồn “màu đúng”** | `CSS custom properties` (`--color-bg`) trên `html`/`body` hoặc wrapper; class `dark` trên root | Cùng ý — thường gắn class/`data-theme` trên `#app` |
| **Bắt hệ thống** | `prefers-color-scheme` trong CSS hoặc `matchMedia` + `useEffect` để đồng bộ state | `window.matchMedia` trong `onMounted` hoặc chỉ dùng CSS |
| **Lưu lựa chọn user** | `localStorage` + **hydration cẩn thận** (tránh flash sai theme lần đầu) | Giống; có thể đọc cookie server (Nuxt) nếu cần SSR khớp màu |
| **Phân phối theme lên cây** | Context + Provider (`theme`, `setTheme`) | `provide` / `inject` hoặc Pinia |
| **Cách scope style** | CSS Modules, styled-components, Emotion, Tailwind | `<style scoped>`, CSS Modules, Tailwind |
| **Độ tương phản / a11y** | Không chỉ “đổi màu” — kiểm tra **WCAG** (chữ nền tối), `focus-visible` | Cùng nguyên tắc |

**Hỏi (ngắn):** Vì sao đổi theme bằng `localStorage` dễ bị **nháy màu** (FOUC) khi SSR / refresh?

**Đáp:** HTML tĩnh render **trước** khi JS chạy đọc storage — cần **inline script nhỏ** trước paint, hoặc **cookie** server biết, hoặc `color-scheme` + CSS mặc định trùng hệ.

---

## 5. Kiểm thử — **bao nhiêu case, case nào, test sao, đạt khi nào**

**Cách đọc:** Mỗi bảng = **một tính năng**. Mỗi dòng = **một case**. Cột **Test như nào** = làm gì trên app. Cột **Đánh giá** = nhìn đâu để biết **đạt / không đạt**.  
**Hỏi ứng viên:** “Với X em có **mấy case**, kể từng cái, test sao, pass là gì?”

---

### Ví dụ A — Giỏ hàng + giảm giá (≥10 món → **giảm 15%**; có **phiếu** → **trừ thêm 50.000đ**)

*(Giả định: áp % trước, phiếu sau — team có thể đổi, miễn **nhất quán**.)*

| STT | Case | Test như nào | Đánh giá: đạt khi |
|-----|------|----------------|-------------------|
| 1 | **9 món** | Xem tổng | **Không** giảm 15% |
| 2 | **10 món** (vd 10×100k = 1.000.000) | Xem tổng | Còn **850.000** |
| 3 | 10 món + **bật phiếu** -50k | Bật phiếu | **800.000** |
| 4 | 10 món + **tắt phiếu** | Tắt | Lại **850.000** |
| 5 | **0 món** / **âm** (nếu sửa tay được) | Thử nhập | **Chặn** — không số vô lý |
| 6 | Vừa sửa bug chỗ giảm giá | Làm lại case **đã từng lỗi** + case 1, 4 | Đúng **và** không vỡ chỗ cạnh đó |

---

### Ví dụ B — Đăng nhập (email + mật khẩu)

| STT | Case | Test như nào | Đánh giá: đạt khi |
|-----|------|--------------|-------------------|
| 1 | Bỏ trống | Gửi form không điền | Báo lỗi rõ (thiếu email / mật khẩu), **không** vào trong |
| 2 | Email đúng, mật khẩu sai | Điền và gửi | Báo **đăng nhập thất bại**, **không** vào trong |
| 3 | Email sai định dạng | Nhập `abc` không có @ | Báo **sai định dạng**, không gọi server / hoặc chặn sớm |
| 4 | Đúng tài khoản | Điền đúng | Vào được trang đã đăng nhập / có dấu hiệu session |
| 5 | Bấm nút **hai lần** rất nhanh | Double-click Đăng nhập | **Một** phiên làm việc (không tạo hai session lạ), không lỗi 500 |

**Tổng cộng:** **5 case** cơ bản.

---

### Ví dụ C — Đặt hàng xong có **email** xác nhận

| STT | Case | Test như nào | Đánh giá: đạt khi |
|-----|------|--------------|-------------------|
| 1 | Đặt hàng **thành công** | Hoàn tất một đơn trên môi trường test | Có **đúng 1** email (hoặc 1 bản ghi trong hộp thư test); trong thư có **mã đơn** khớp đơn vừa tạo |
| 2 | Thanh toán **thất bại** | Giả thanh toán lỗi | **Không** gửi mail “đặt thành công”, hoặc gửi đúng nội dung “chưa thành công” theo thiết kế |

**Tổng cộng:** **2 nhóm** chính; có thể thêm case “gửi lại đơn” nếu nghiệp vụ có.

---

### Ví dụ D — Thanh toán (trên máy test **không** trừ tiền thật)

| STT | Case | Test như nào | Đánh giá: đạt khi |
|-----|------|--------------|-------------------|
| 1 | Thanh toán **thành công** | Dùng **cổng giả** trả “OK” | Đơn **đúng trạng thái đã trả tiền**, không có giao dịch thật |
| 2 | Thanh toán **từ chối** | Cổng giả trả “lỗi” | Đơn **không** báo đã trả, user thấy lỗi đúng |
| 3 | Bấm **thanh toán hai lần** nhanh | Double-click | Theo **quy định nghiệp vụ**: một lần trừ hoặc hai lần vẫn **một kết quả** — ứng viên nói rõ team chọn gì |

---

### Chấm nhanh (mục 5)

| Mức | Ứng viên |
|-----|----------|
| Yếu | Chỉ nói “test kỹ” — không **liệt kê số case** + **cách biết pass** |
| Đạt | ≥4 case cụ thể + biết nhìn **tiền / thông báo / email** |
| Khỏe | Có **9 vs 10 món**, **double-click**, **sau sửa bug** chạy lại case cũ + cạnh đó |

---

## 6. Thuật toán & tư duy — **câu đố đời thường** (dễ kể miệng)

*(Hỏi kiểu “đố vui + suy luận”. Ứng viên không cần biết tên thuật toán: kể được **chia nhóm**, **loại dần**, **vì sao tối thiểu** là được.)*

---

**Câu hỏi:** **7 viên bi** giống hệt nhau, **1 viên nặng hơn**, còn lại nhẹ như nhau. Chỉ có **cân hai đĩa** (biết bên nào nặng hơn hoặc bằng nhau). **Ít nhất mấy lần cân** là **luôn** tìm được viên nặng? **Kể cách làm** từng bước.

**Trả lời:** **Tối đa 2 lần** là đủ. **Lần 1:** để **3 viên đĩa trái**, **3 viên đĩa phải**, **1 viên không lên cân**. Nếu **hai đĩa cân bằng** ⇒ viên nặng là viên **ở ngoài** (xong luôn, **1 lần**). Nếu **một bên nặng hơn** ⇒ viên lạ nằm trong **3 viên bên nặng**. **Lần 2:** trong 3 viên đó, cân **1 — 1**; nếu bằng nhau thì viên **còn lại** là nặng; nếu lệch thì viên ở **đĩa nặng** là đáp án.  
*(Ý tư duy: mỗi lần cân **chia đôi / chia ba nhóm nghi vấn** thay vì cân từng viên.)*

---

**Câu hỏi:** **9 viên**, **1 nặng**, giống câu trên. Trường hợp **xấu nhất** cần **mấy lần cân**?

**Trả lời:** **2 lần**. Lần 1: cân **3 — 3**, còn 3 ở ngoài → biết nhóm 3 nào chứa viên nặng. Lần 2: trong nhóm 3, cân **1 — 1** như bài trên.

---

**Câu hỏi:** **12 viên**, **đúng 1 viên** lạ — không biết nó **nặng hơn hay nhẹ hơn** (các viên khác cân nhau). Vẫn chỉ có **cân hai đĩa**. (Bài khó — có thể chỉ hỏi senior hoặc chỉ hỏi “mấy lần là đủ trong lời giải cổ điển”.)

**Trả lời:** Có lời giải **3 lần cân** cho mọi trường hợp. Ý chính: mỗi lần cân không chỉ biết lệch mà còn **ghi nhớ** viên nào từng ở đĩa trái/phải hay ngoài, để lần sau **thu hẹp** và xác định được viên lạ **nặng hay nhẹ**. Không cần thuộc từng bảng; phỏng vấn: **biết 3 lần + lập luận có hệ thống** là tốt.

---

**Câu hỏi:** Ông nông dân qua sông với **sói**, **dê**, **bắp cải**. Thuyền chỉ chở thêm **một “món”** (hoặc không chở). Để sói với dê **một mình** thì sói ăn dê; để dê với bắp cải thì dê ăn bắp. Qua **hết** thế nào?

**Trả lời:** (1) Chở **dê** qua, ông về một mình. (2) Chở **sói** qua, **cho dê quay lại** (không để sói với dê). (3) Chở **bắp cải** qua, ông về (sói không ăn bắp). (4) Chở **dê** qua lần cuối.  
*(Tư duy: sau mỗi bước liệt kê **hai bờ** — cặp nào **cấm ở lại một mình**.)*

---

**Câu hỏi:** Hai ca: **3 lít** và **5 lít** (không có vạch lẻ). Làm sao đong ra **đúng 4 lít** (ở một ca hay chia hai ca cũng được)?

**Trả lời:** Một cách dễ nhớ: **Đầy ca 5**, đổ sang **3** → trong 5 còn **2**. **Đổ hết ca 3** đi. Đổ **2 lít** từ ca 5 sang ca 3. **Đầy ca 5**, rót sang ca 3 đến khi ca 3 đầy (ca 3 chỉ còn trống **1 lít**) ⇒ trong **ca 5 còn 4 lít**.  
*(Ứng viên khác lời giải cũng được miễn đúng — quan trọng là **theo dõi trạng thái** chứ không học thuộc.)*

---

**Câu hỏi:** **5 hộp** đựng toàn **cá 120g**, có **đúng 1 hộp** toàn **cá 100g**. Chỉ được **cân điện một lần** (một tổng khối lượng). Ai là hộp “gian”?

**Trả lời:** Lấy **1 con** từ hộp 1, **2 con** hộp 2, **3** hộp 3, **4** hộp 4, **5** hộp 5 — cân **một lần**. So với tổng nếu mọi con đều 120g: mỗi con 100g **thiếu 20g**; **số thiếu chia 20** (làm tròn) cho biết **hộp thứ mấy**.

---

**Câu hỏi:** **1000 chai**, **một chai có độc**. Có **10 con chuột**; uống xong **một ngày sau** mới biết con nào chết. Chỉ được cho uống **một vòng** rồi chờ — tìm chai độc thế nào?

**Trả lời:** Đánh số chai **0…999**. Viết số đó dưới dạng **nhị phân** (đủ **10 bit**). Chuột 1 uống các chai có **bit thứ 1 = 1**, chuột 2 uống bit thứ 2 = 1, … Ngày sau: con nào chết = **1**, sống = **0** ⇒ được một dãy bit ⇒ đó là **số thứ tự chai độc**.  
*(Nói miệng: “mỗi con là một số 0/1, 10 con ghép thành số có tới 1024 cách — đủ phân biệt 1000 chai”.)*

---

**Câu hỏi:** **25 con ngựa**, đua **5 con một lần**, không có đồng hồ — chỉ biết **thứ hạng trong từng cuộc**. Cần tìm **3 con nhanh nhất tuyệt đối** — **ít nhất mấy cuộc đua** (trường hợp xấu nhất)?

**Trả lời:** **7 cuộc**. **5 cuộc** đầu: chia 5 bảng, mỗi bảng 5 ngựa, đua trong bảng → xếp hạng trong từng bảng. **Cuộc 6:** cho **5 con nhất của 5 bảng** đua với nhau → biết **con nhất toàn cục** (gọi là A). **Cuộc 7:** trong số còn có thể là hạng 2–3 toàn cục chỉ còn **vài ứng viên** (A; ngựa xếp sau A một chút trong bảng của A; ngựa nhì cuộc 6; …) — đua hết ứng viên đó để chọn **hạng 2 và 3**.  
*(Không bắt thuộc lý do từng con — chỉ cần **khung 7 cuộc + “loại gần hết ứng viên bằng cấu trúc giải đấu”**.)*

---

**Câu hỏi:** Trong túi nhiều đôi **tất cùng màu**, chỉ có **đúng một đôi lệch** (một đỏ một xanh), còn lại là **đôi đủ** (hai đỏ hoặc hai xanh). Phải **bốc ngẫu nhiên tối thiểu bao nhiêu chiếc** (trường hợp **xấu nhất**) thì **chắc chắn** có **một đôi cùng màu**?

**Trả lời:** **3 chiếc**. Hai chiếc đầu có thể vô tình là **đỏ + xanh** (đôi lệch); chiếc thứ **ba** dù màu gì cũng **trùng** một trong hai ⇒ tạo đôi đủ.

---

**Câu hỏi:** Hai sợi dây cháy **hết trong 1 giờ** mỗi sợi, nhưng tốc độ cháy **không đều** dọc dây. Chỉ có **bật lửa** (châm được ở đầu hoặc giữa sợi). **Đo đúng 45 phút**.

**Trả lời:** Châm **đồng thời** **cả hai đầu** sợi A ⇒ A cháy hết trong **30 phút**. Cùng lúc châm **một đầu** sợi B. Khi A tắt, còn **30 phút “độ dài”** nếu B chỉ cháy một đầu. Ngay lúc đó châm **đầu còn lại** của B ⇒ phần còn của B cháy trong **15 phút**. **30 + 15 = 45**.  
*(Ứng viên vẽ hai đường thời gian trên giấy là hiểu nhau ngay.)*

---

**Câu hỏi:** A **một mình** làm xong việc trong **10 ngày**, B một mình trong **15 ngày**. **Cùng làm** mấy ngày xong? (Không dùng máy tính — giải thích bằng “mỗi ngày làm được bao nhiêu phần việc”.)

**Trả lời:** Mỗi ngày A làm **1/10**, B làm **1/15**. Cùng làm: **1/10 + 1/15 = 3/30 + 2/30 = 5/30 = 1/6** phần việc mỗi ngày ⇒ **6 ngày** xong.

---

**Câu hỏi:** Trong danh sách, **mọi số đều xuất hiện đôi**, chỉ **một số xuất hiện một lần**. Chỉ được đi một lượt, “không nhớ hết bảng đếm”. Gợi ý: tưởng tượng **hai số giống nhau thì huỷ nhau**.

**Trả lời:** XOR (phép **XOR bit**) toàn bộ các số: các cặp giống nhau triệt tiêu về 0, **chỉ sót** số lẻ. Nói miệng được là đạt.

---

**Câu hỏi:** **Hai file phim** nặng cả chục GB, máy không mở **hết file vào RAM**. Làm sao biết **hai file có y hệt nhau không**?

**Trả lời:** Đọc **từng khúc nhỏ**, so byte từng khúc (hoặc băm từng khúc); **chỗ nào khác** thì báo ngay **không cần** đọc nốt. Giống “so hai cuốn sách dày từng trang, thấy trang lệch là dừng”.

---

**Câu hỏi:** Sổ ghi **địa chỉ (IP)** cả ngày, sổ **quá dày** không nhét hết vào RAM để đếm. Làm sao biết **IP nào xuất hiện nhiều nhất** — ý chính?

**Trả lời:** Nếu RAM đủ: đếm như **phiếu bầu**. Nếu không: **chia sổ thành nhiều đống** (ví dụ theo chữ cái đầu), mỗi đống **nhỏ** đủ để đếm trong RAM, rồi **đem top** mỗi đống ra **so tiếp** — giống “chia công nhiều người đếm rồi họp lại”.

---

**Câu hỏi:** Danh sách số **đã xếp tăng dần**; tìm **hai số** cộng lại đúng **100**. Không thử hết mọi cặp — làm sao nhanh?

**Trả lời:** Một ngón ở **đầu dãy**, một ngón ở **cuối**. Nếu **tổng nhỏ** hơn 100 thì **đầu tiến vào trong**; nếu **lớn** hơn thì **cuối lùi vào trong** — tối đa **một lượt** dọc dãy.

---

**Câu hỏi:** Cho **dãy số** (có số **âm**). Tìm **một đoạn liên tiếp** sao cho **tổng đoạn đó lớn nhất**. Ví dụ: `3, -2, 5, -1, 4` — không nhất thiết bắt đầu từ số dương.

**Trả lời:** Đi từ trái sang phải, giữ **tổng đoạn đang “bám”** hiện tại. Mỗi số mới: hoặc **nối vào đoạn cũ**, hoặc nếu đoạn cũ **“đuối” quá** (tổng âm lớn) thì **coi như bắt đầu đoạn mới** từ số đang đứng. Cập nhật **tổng lớn nhất** từng thấy.  
*(Nói miệng: “đoạn đang cộng mà kéo theo lỗ quá thì cắt, bắt đầu lại”.)*

---

**Câu hỏi:** **10 lớp** trong một trường thi **tranh giải**; mỗi lớp đã xếp **thứ hạng nội bộ** (không so chéo lớp). Không thể cho **học sinh của 10 lớp** đứng một hàng để sort. Vẫn muốn tìm **học sinh nhất toàn trường** — ý tưởng?

**Trả lời:** Lấy **nhất mỗi lớp** (10 người), cho **10 người đó** thi một vòng — **nhất vòng này** là **nhất toàn trường** (cả lớp đều có người đại diện).  
*(So với bài ngựa: đây là phiên bản “một vòng loại” dễ kể. Bài ngựa đủ 3 nhất thì phải thêm vòng nữa.)*

---

## 7. Tổng hợp full-stack

**Câu hỏi:** Backend log cho thấy API **50ms**, nhưng người dùng than phiền UI **chậm**. Anh/chị kiểm tra **từ trình duyệt vào trong** ra sao?

**Trả lời:** Waterfall mạng, **payload** quá lớn, **hydration** SSR nặng, quá nhiều request **tuần tự** (nên gộp/BFF hoặc pagination), main thread bận (long task), ảnh/font chặn render, CDN/cache thiếu.

---

**Câu hỏi:** Mô tả end-to-end: user bấm **Đặt hàng** tới khi có bản ghi **ổn định** trong DB — các lớp phải làm gì để không “dở chừng”.

**Trả lời:** Kiểm input → **khóa idempotent** để double-click an toàn → giao dịch DB hoặc **outbox** nếu có hệ ngoài → trạng thái hiển thị rõ (đang xử lý/thành công/thất bại) → log/metrics → nếu thanh toán lỗi thì **bù/hoàn** hoặc đơn ở trạng thái lỗi rõ ràng.

---

**Câu hỏi:** Nếu phải nhắc **5 hạng mục bảo mật** cho full-stack junior, anh/chị nói gì?

**Trả lời:** **CSRF** (cookie session), **XSS** + CSP, **SQL injection** (ORM/parameterized), **mass assignment** / phân quyền, **rate limit**, không commit **secret**, mật khẩu **bcrypt/argon2**.

---
