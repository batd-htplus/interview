# CHƯƠNG TRÌNH ĐÀO TẠO INTERN FULLSTACK DEVELOPER
## 12 Tuần · Fulltime 

> **Dành cho:** Engineering Manager / Tech Lead  
> **Đối tượng:** Intern Fullstack Developer (có kiến thức lập trình cơ bản)  
> **Mục tiêu cuối:** Intern có thể join team như Junior Developer, tự build và maintain feature production-level

---

# PHẦN 1 — TỔNG QUAN CHƯƠNG TRÌNH

## 1.1 Triết lý đào tạo

Chương trình này **không** dạy theo kiểu tutorial từng bước. Thay vào đó:

- **Learn by doing first** — Intern gặp vấn đề thật trước, học lý thuyết sau khi có context
- **Production mindset từ ngày 1** — Code review nghiêm túc, không chấp nhận "chạy là được"
- **Accountability loop** — Mỗi tuần có deliverable rõ ràng, mentor review thật sự
- **Fail fast & learn** — Cho phép sai nhưng phải tự debug, tự tìm ra vấn đề

---

## 1.2 Phân chia Phase

```
PHASE 1 — FOUNDATION (Tuần 1–2)
├── Git workflow, TypeScript, cấu trúc project
└── Mục tiêu: Intern tự tin làm việc trong môi trường team thật

PHASE 2 — BACKEND CORE (Tuần 3–5)
├── Node.js, NestJS architecture, REST API, validation
└── Mục tiêu: Tự build một CRUD API production-ready

PHASE 3 — DATABASE (Tuần 6–7)
├── PostgreSQL, TypeORM, query optimization, migration
└── Mục tiêu: Thiết kế schema, viết query hiệu quả, tự tối ưu slow query

PHASE 4 — FRONTEND (Tuần 8–9)
├── React, TypeScript, state management, API integration
└── Mục tiêu: Tự build UI hoàn chỉnh kết nối backend thật

PHASE 5 — PROJECT INTEGRATION (Tuần 10)
├── Kết hợp toàn bộ stack, refactor, viết test
└── Mục tiêu: Feature hoàn chỉnh end-to-end

PHASE 6 — PRODUCTION & DEPLOYMENT (Tuần 11–12)
├── Docker, CI/CD, deploy, monitoring, performance
└── Mục tiêu: Tự deploy lên môi trường production giả lập, debug production issue
```

---

## 1.3 Mục tiêu kỹ năng sau mỗi Phase

| Phase | Kỹ năng đạt được | Level mong muốn |
|-------|-----------------|-----------------|
| Foundation | Git flow, TypeScript strict mode, đọc hiểu codebase | Biết dùng đúng cách |
| Backend Core | NestJS modules/guards/pipes, REST API design, error handling | Tự build không cần hướng dẫn |
| Database | Schema design, migration, index, EXPLAIN ANALYZE | Hiểu tại sao query chậm |
| Frontend | React hooks, async state, form handling, error boundary | Build UI kết nối real API |
| Integration | End-to-end flow, unit test, code review mindset | Review được code của người khác |
| Production | Docker compose, GitHub Actions, deploy, log debug | Tự xử lý incident cơ bản |

---

# PHẦN 2 — BREAKDOWN THEO TUẦN (12 TUẦN)

---

## TUẦN 1 — Git, TypeScript & Developer Workflow

### Chủ đề chính
Làm quen môi trường team, Git branching strategy, TypeScript fundamentals

### Learning Blocks

**Block A — Git & GitHub Team Workflow (10h)**
- Git internals: commit graph, HEAD, refs
- Branching strategy: GitFlow vs Trunk-based (team dùng cái nào, tại sao)
- Pull Request process: mở PR, review, request changes, approve
- Conflict resolution thực chiến
- Commit message convention (Conventional Commits)
- `.gitignore`, `.gitattributes`

**Block B — TypeScript Fundamentals (15h)**
- Types vs Interfaces, khi nào dùng cái nào
- Generics cơ bản (ít nhất hiểu `Array<T>`, `Promise<T>`)
- Enums, Union types, Intersection types
- `strict` mode — tại sao bắt buộc
- `tsconfig.json` — các options quan trọng
- Type narrowing, type guards

**Block C — Dev Environment & Tooling (5h)**
- ESLint + Prettier setup và cấu hình cho team
- Husky + lint-staged (pre-commit hooks)
- EditorConfig
- VS Code extensions: ESLint, Prettier, GitLens, REST Client

### Thời lượng tuần
| Activity | Giờ |
|---------|-----|
| Learning Blocks | 20h |
| Assignment | 10h |
| Review & Discussion | 5h |
| Mentor session | 2h |
| **Tổng** | **37h** |

### Assignment Tuần 1
1. Fork repo template của team, clone về local
2. Setup môi trường đầy đủ (ESLint, Prettier, Husky chạy được)
3. Viết TypeScript file mô hình hóa domain của project (interfaces, enums, types)
4. Tạo 1 PR thật, tự review, tự sửa theo checklist

### Deliverables
- [ ] PR lên GitHub với mô tả rõ ràng (what/why/how)
- [ ] `types/` folder với ít nhất 10 types/interfaces có JSDoc
- [ ] ESLint + Prettier + Husky chạy clean (0 lỗi)
- [ ] Commit history sạch, message đúng convention

### Code Review Checkpoint
Mentor review PR và comment trực tiếp vào code theo các tiêu chí:
- Naming conventions
- Type correctness (không dùng `any`)
- Tổ chức file logic

### Mentor Feedback Session (2h — cuối tuần)
- Demo môi trường cho mentor thấy
- Walk-through từng commit trong PR
- Q&A về những điểm chưa hiểu

### KPI Đạt / Chưa đạt

| Tiêu chí | Đạt | Chưa đạt |
|---------|-----|----------|
| Tạo và merge PR đúng quy trình | Tự làm được | Cần hướng dẫn từng bước |
| TypeScript không có `any` | 0 `any` | Có `any` hoặc `@ts-ignore` |
| Commit message convention | Đúng 100% | Sai > 2 commits |
| Git conflict resolution | Tự giải quyết | Cần mentor xử lý |

---

## TUẦN 2 — Node.js & Project Architecture

### Chủ đề chính
Node.js runtime, module system, project structure chuẩn production

### Learning Blocks

**Block A — Node.js Core (10h)**
- Event loop — thực sự hiểu, không chỉ thuộc lòng
- `async/await` và Promise chains, error propagation
- Streams và Buffer (biết tồn tại, hiểu use-case)
- `process`, `path`, `fs` module
- Environment variables & config management

**Block B — Project Structure (8h)**
- Monorepo vs Polyrepo — team chọn gì
- Folder structure chuẩn production (xem Phần 4)
- Dependency Injection concept (chuẩn bị cho NestJS)
- SOLID principles — áp dụng thực tế, không lý thuyết suông
- Module boundaries — tại sao phải tách module

**Block C — Dev Tooling nâng cao (7h)**
- Debugging Node.js với VS Code debugger (không dùng `console.log`)
- `npm workspaces` cơ bản
- Package.json scripts best practices
- `nodemon` vs `ts-node-dev`

### Assignment Tuần 2
Xây dựng scaffolding cho project xuyên suốt 12 tuần:
- Setup folder structure hoàn chỉnh
- Config module với environment validation (dùng `zod` hoặc `class-validator`)
- Logger module (dùng `pino` hoặc `winston`)
- Viết README.md đầy đủ (setup guide, tech decisions)

### Deliverables
- [ ] Project skeleton với folder structure chuẩn
- [ ] Config validation chạy được (throw error nếu thiếu env var)
- [ ] Logger hoạt động với log levels
- [ ] README.md được mentor approve

### KPI Đạt / Chưa đạt

| Tiêu chí | Đạt | Chưa đạt |
|---------|-----|----------|
| Giải thích được event loop | Giải thích bằng lời rõ ràng | Không giải thích được |
| Folder structure | Đúng, có lý do cho mỗi folder | Copy template không hiểu |
| Error handling | Có try/catch đúng chỗ | `unhandledPromiseRejection` |
| README chất lượng | Người mới clone được ngay | Thiếu steps hoặc outdated |

---

## TUẦN 3 — NestJS Core & REST API Design

### Chủ đề chính
NestJS fundamentals, dependency injection, REST API design chuẩn

### Learning Blocks

**Block A — NestJS Architecture (15h)**
- Modules, Controllers, Services, Providers
- Dependency Injection — cách NestJS quản lý scope
- Decorators và metadata
- Guards: authentication vs authorization
- Interceptors: logging, response transformation
- Pipes: validation, transformation
- Exception Filters: error handling nhất quán

**Block B — REST API Design (8h)**
- RESTful principles — resource naming, HTTP verbs
- Response structure chuẩn (status code, body, pagination)
- `class-validator` + `class-transformer` cho DTO
- Swagger/OpenAPI documentation

**Block C — Config & Middleware (5h)**
- `ConfigModule` với `@nestjs/config`
- CORS setup
- Rate limiting cơ bản
- Request logging middleware

### Assignment Tuần 3
Xây dựng `UsersModule` hoàn chỉnh:
- CRUD endpoints: GET/POST/PUT/DELETE `/users`
- DTO validation đầy đủ
- Response transformation nhất quán
- Error handling: 400, 404, 409 đúng case
- Swagger docs auto-generated

### Deliverables
- [ ] `UsersModule` với đầy đủ CRUD
- [ ] DTO validation không để lọt dữ liệu xấu
- [ ] Swagger UI chạy tại `/api/docs`
- [ ] Postman collection test tất cả endpoints

### KPI Đạt / Chưa đạt

| Tiêu chí | Đạt | Chưa đạt |
|---------|-----|----------|
| Không có business logic trong Controller | Service xử lý toàn bộ | Logic lẫn lộn Controller/Service |
| DTO validation | Bắt được tất cả invalid input | Bypass được validation |
| HTTP status codes | Đúng với mọi case | Trả 200 cho mọi thứ |
| DI hiểu đúng | Giải thích được scope | Dùng `new Service()` thay inject |

---

## TUẦN 4 — Authentication & Authorization

### Chủ đề chính
JWT auth, refresh token, Role-based access control (RBAC)

### Learning Blocks

**Block A — Authentication (12h)**
- `@nestjs/passport` + `passport-jwt`
- JWT: access token vs refresh token strategy
- Bcrypt password hashing — salt rounds, timing attacks
- Auth Guard implementation
- Token blacklisting cơ bản (Redis hoặc DB)

**Block B — Authorization (8h)**
- RBAC design pattern
- Custom decorator: `@Roles()`, `@CurrentUser()`
- Guard chaining
- Resource-level authorization (chỉ owner mới edit được)

**Block C — Security Basics (5h)**
- OWASP Top 10 — nhận diện, không cần fix hết
- SQL Injection prevention (tại sao ORM giúp)
- XSS, CSRF basics
- Helmet middleware
- Secret management — không commit `.env`

### Assignment Tuần 4
Implement authentication đầy đủ:
- Register/Login/Logout/Refresh endpoints
- JWT guard bảo vệ private routes
- Role: ADMIN, USER
- Chỉ ADMIN có thể xóa user
- `@CurrentUser()` decorator

### Deliverables
- [ ] Auth flow hoàn chỉnh (register → login → access protected route)
- [ ] Refresh token strategy chạy đúng
- [ ] RBAC bảo vệ được admin routes
- [ ] Unit test cho AuthService (ít nhất 5 test cases)

### KPI Đạt / Chưa đạt

| Tiêu chí | Đạt | Chưa đạt |
|---------|-----|----------|
| Password không lưu plaintext | Luôn hash | Bất kỳ trường hợp nào lưu plain |
| Token handling | Access/refresh đúng expire | Không có refresh hoặc expire sai |
| Authorization | Không thể bypass role check | Có thể trick API |
| Security mindset | Chủ động hỏi "có thể abuse không?" | Không nghĩ đến edge cases |

---

## TUẦN 5 — PostgreSQL & TypeORM

### Chủ đề chính
Database design, TypeORM, relations, basic query optimization

### Learning Blocks

**Block A — PostgreSQL Fundamentals (10h)**
- Data types: jsonb, uuid, timestamp with timezone
- Constraints: NOT NULL, UNIQUE, CHECK, FOREIGN KEY
- Indexes: B-tree, hash — khi nào dùng gì
- Transactions: ACID, isolation levels
- `EXPLAIN ANALYZE` — đọc query plan

**Block B — TypeORM (12h)**
- Entity, Repository, DataSource
- Relations: OneToOne, OneToMany, ManyToMany
- Query Builder vs find options
- Eager vs lazy loading — performance implications
- Migrations: generate, run, revert

**Block C — Database Design (5h)**
- Normalization (1NF → 3NF, biết khi nào denormalize)
- Naming conventions trong DB
- Soft delete pattern
- Audit columns: `createdAt`, `updatedAt`, `deletedAt`

### Assignment Tuần 5
- Thiết kế và implement schema đầy đủ cho project
- Viết migrations (không dùng `synchronize: true` trong production)
- Implement repository pattern với TypeORM
- Tìm và tối ưu 1 N+1 query problem

### Deliverables
- [ ] Schema diagram (dùng dbdiagram.io hoặc drawio)
- [ ] Migrations folder với ít nhất 3 migrations
- [ ] `synchronize: false` trong DataSource config
- [ ] Trình bày được cách fix N+1 query mình tìm thấy

### KPI Đạt / Chưa đạt

| Tiêu chí | Đạt | Chưa đạt |
|---------|-----|----------|
| Schema design | Foreign keys đúng, indexes hợp lý | Không có index, quan hệ sai |
| Migration | Không dùng `synchronize: true` | Dùng sync hoặc không có migration |
| N+1 detection | Tự nhận ra trong code review | Không biết N+1 là gì |
| EXPLAIN ANALYZE | Đọc được output cơ bản | Chưa biết dùng |

---

## TUẦN 6 — Database Advanced & Query Optimization

### Chủ đề chính
Performance tuning, advanced indexing, DBA cơ bản

### Learning Blocks

**Block A — Query Optimization (12h)**
- Index types nâng cao: Partial index, Composite index, GIN index
- `EXPLAIN ANALYZE` chi tiết: Seq Scan vs Index Scan vs Bitmap Scan
- Common Table Expressions (CTE)
- Window functions: `ROW_NUMBER`, `RANK`, `LAG`, `LEAD`
- Pagination chuẩn: cursor-based vs offset-based

**Block B — PostgreSQL Performance (8h)**
- Connection pooling với `pg-pool` / `pgbouncer`
- Vacuum, autovacuum — bloat là gì
- `pg_stat_statements` — tìm slow queries
- Table partitioning cơ bản (biết khái niệm)
- Read replicas concept

**Block C — DBA Basics (5h)**
- Backup strategy: `pg_dump`, continuous WAL archiving
- Role và Permission management
- `pg_stat_activity` — monitor active queries
- Lock detection và giải quyết deadlock

### Assignment Tuần 6
**Slow Query Optimization Sprint:**
1. Seed database với 100,000+ records
2. Chạy `pg_stat_statements`, tìm top 3 slow queries
3. Dùng `EXPLAIN ANALYZE` phân tích từng query
4. Tối ưu (thêm index, rewrite query, pagination)
5. Benchmark trước/sau bằng `pgbench` hoặc script

### Deliverables
- [ ] Report: trước/sau optimization với số liệu cụ thể (ms)
- [ ] Ít nhất 2 indexes mới có giải thích tại sao thêm
- [ ] Cursor-based pagination implementation
- [ ] Không có N+1 trong toàn bộ codebase

### KPI Đạt / Chưa đạt

| Tiêu chí | Đạt | Chưa đạt |
|---------|-----|----------|
| Query optimization | Cải thiện ≥ 50% với data lớn | Không có baseline để so sánh |
| Index strategy | Giải thích được tại sao thêm index | Thêm index không có lý do |
| Pagination | Cursor-based cho list lớn | Offset pagination cho 100k+ records |
| Benchmark | Có số liệu cụ thể | "Cảm giác nhanh hơn" |

---

## TUẦN 7 — Testing Strategy

### Chủ đề chính
Unit test, integration test, test-driven mindset

### Learning Blocks

**Block A — Testing Fundamentals (8h)**
- Testing pyramid: unit vs integration vs e2e
- Jest: describe, it, beforeEach, afterEach
- Mocking: jest.fn(), jest.spyOn(), module mocking
- Coverage: không phải 100% là tốt nhất

**Block B — NestJS Testing (10h)**
- `TestingModule` setup
- Mocking repositories và services
- Supertest cho controller integration tests
- Testing Guards và Interceptors
- Database testing với test database

**Block C — Test Quality (7h)**
- What to test vs what not to test
- Test naming: "should do X when Y"
- Arrange-Act-Assert pattern
- Test isolation — không phụ thuộc thứ tự test

### Assignment Tuần 7
- Viết unit tests cho toàn bộ services (target: 80% coverage)
- Integration test cho auth flow (register → login → access protected)
- Test 3 edge cases quan trọng của domain

### Deliverables
- [ ] Test coverage report ≥ 80%
- [ ] Ít nhất 20 test cases, tất cả pass
- [ ] Integration test auth flow chạy clean
- [ ] Không có test nào dùng `any` hoặc mock toàn bộ class

### KPI Đạt / Chưa đạt

| Tiêu chí | Đạt | Chưa đạt |
|---------|-----|----------|
| Test coverage | ≥ 80% meaningful coverage | < 80% hoặc test fake |
| Test quality | Test behavior, không test implementation | Test gọi function bao nhiêu lần |
| Mock usage | Mock đúng boundary | Mock quá nhiều, test không có giá trị |
| Test naming | Đọc tên hiểu ngay test gì | Tên chung chung `test1`, `it works` |

---

## TUẦN 8 — React & Frontend Foundation

### Chủ đề chính
React với TypeScript, hooks, component design

### Learning Blocks

**Block A — React Core với TypeScript (12h)**
- Functional components, props typing
- Hooks: `useState`, `useEffect`, `useCallback`, `useMemo`, `useRef`
- Custom hooks — khi nào tách, khi nào không
- Context API vs prop drilling
- Error Boundaries
- React.memo — khi nào nên dùng

**Block B — Async State Management (8h)**
- `TanStack Query` (React Query) — caching, refetching, mutations
- `axios` hoặc `fetch` wrapper với interceptors
- Optimistic updates
- Error states và loading states
- Environment-based API base URL

**Block C — Forms & UX (5h)**
- `React Hook Form` + `zod` validation
- Form error display
- Disabled states khi submitting
- Toast notifications

### Assignment Tuần 8
Build frontend cho auth flow và user management:
- Login/Register form với validation
- Protected routes với redirect
- User list với search/pagination
- Create/edit user form

### Deliverables
- [ ] Auth flow hoàn chỉnh kết nối backend thật
- [ ] Protected routing hoạt động đúng
- [ ] Form validation match với backend validation
- [ ] Loading/error states cho mọi async operation

### KPI Đạt / Chưa đạt

| Tiêu chí | Đạt | Chưa đạt |
|---------|-----|----------|
| Type safety | Không có `any`, typed response | `any` cho API responses |
| Custom hooks | Tách logic ra hook riêng | Toàn bộ logic trong component |
| Error handling | Hiển thị lỗi từ API đúng cách | Chỉ log console, không show UI |
| Loading states | Mọi async action có loading | Không có feedback cho user |

---

## TUẦN 9 — Frontend Advanced & Integration

### Chủ đề chính
Component architecture, performance, full-stack integration

### Learning Blocks

**Block A — Component Design (8h)**
- Compound components pattern
- Render props vs Custom hooks
- Component composition
- Atomic design concept
- Storybook cơ bản (biết setup, không cần master)

**Block B — Performance (7h)**
- React Profiler — tìm re-render không cần thiết
- Code splitting với `React.lazy` + `Suspense`
- Image optimization
- Bundle size analysis với `vite-bundle-analyzer`
- Virtualization cho list dài (`react-window`)

**Block C — Fullstack Integration (10h)**
- Token refresh flow phía frontend
- Axios interceptors cho 401 auto-refresh
- WebSocket cơ bản với NestJS (nếu project cần)
- CORS troubleshooting
- API versioning handling

### Assignment Tuần 9
Complete integration sprint:
- Frontend kết nối toàn bộ backend endpoints
- Token refresh tự động
- Xử lý mọi API error state
- Performance audit với React Profiler

### Deliverables
- [ ] Toàn bộ CRUD hoạt động end-to-end
- [ ] Refresh token tự động không cần logout
- [ ] Lighthouse score ≥ 80
- [ ] Không có re-render không cần thiết (Profiler)

---

## TUẦN 10 — Project Integration & Code Quality

### Chủ đề chính
Kết hợp toàn stack, refactor, code review thực sự

### Learning Blocks

**Block A — Code Review Culture (5h)**
- Cách đọc code của người khác
- Comment hữu ích vs comment vô nghĩa
- Khi nào approve, khi nào request changes
- Không PR review quá 400 lines

**Block B — Refactoring (8h)**
- Technical debt identification
- Refactoring patterns: extract method, extract class
- Không refactor và thêm feature cùng lúc
- Refactoring với test safety net

**Block C — Documentation (5h)**
- JSDoc cho public APIs
- Architecture Decision Records (ADR)
- Runbook cho operations
- API changelog

### Assignment Tuần 10
**Full Project Audit:**
1. Tự code review toàn bộ codebase, list issues
2. Prioritize: critical / medium / low
3. Fix tất cả critical issues
4. Viết ADR cho 2 quyết định architecture quan trọng

### Deliverables
- [ ] Code review checklist tự làm
- [ ] Technical debt backlog (Jira / Notion / GitHub Issues)
- [ ] 2 ADR documents
- [ ] 0 critical issues còn tồn tại

---

## TUẦN 11 — Docker & Production Environment

### Chủ đề chính
Containerization, Docker Compose, production deployment

### Learning Blocks

**Block A — Docker (12h)**
- Docker concepts: image, container, volume, network
- Dockerfile best practices: multi-stage build, layer caching
- `.dockerignore`
- Docker Compose: services, dependencies, environment
- Docker networking giữa các services

**Block B — Production Config (8h)**
- Environment separation: dev / staging / production
- Secrets management (không hardcode, không `.env` trong image)
- Health checks
- Graceful shutdown
- Resource limits cho container

**Block C — Monitoring Basics (5h)**
- Structured logging (JSON logs)
- Log aggregation concept (ELK / Loki)
- Health endpoint `/health`
- Basic metrics: memory, CPU, request latency

### Assignment Tuần 11
Containerize toàn bộ ứng dụng:
- `Dockerfile` cho backend (multi-stage)
- `Dockerfile` cho frontend
- `docker-compose.yml` chạy được local
- `docker-compose.prod.yml` cho production
- Health checks cho tất cả services

### Deliverables
- [ ] `docker compose up` chạy được mọi thứ
- [ ] Multi-stage build (image size ≤ 200MB)
- [ ] Không có secrets trong image
- [ ] Health check pass cho tất cả services

### KPI Đạt / Chưa đạt

| Tiêu chí | Đạt | Chưa đạt |
|---------|-----|----------|
| Image size | ≤ 200MB với multi-stage | > 500MB |
| Secrets | Dùng env vars / secrets | Hardcode trong Dockerfile |
| Compose | Tất cả services start đúng thứ tự | Race condition khi start |
| Health checks | Tất cả services có health endpoint | Không có health check |

---

## TUẦN 12 — CI/CD, Deploy & Production Hardening

### Chủ đề chính
GitHub Actions, deployment, production debugging

### Learning Blocks

**Block A — CI/CD Pipeline (10h)**
- GitHub Actions: workflow, jobs, steps
- CI pipeline: lint → test → build → Docker build
- CD pipeline: push to registry → deploy
- Branch protection rules
- Deployment strategies: rolling, blue-green (concept)

**Block B — Production Deploy (8h)**
- VPS setup cơ bản (Ubuntu)
- Nginx reverse proxy
- SSL/TLS với Certbot
- Systemd service management
- Database backup automation

**Block C — Production Debug Simulation (7h)**
- Reading production logs
- Identifying bottlenecks với `htop`, `pg_stat_activity`
- Rollback strategy
- Incident response process

### Assignment Tuần 12
**Production Simulation:**
1. Setup GitHub Actions CI/CD pipeline hoàn chỉnh
2. Deploy lên VPS (hoặc staging environment)
3. Xử lý 3 production incidents được giả lập (xem Phần 6)
4. Viết post-mortem cho 1 incident

### Deliverables
- [ ] GitHub Actions pipeline xanh toàn bộ
- [ ] Application live trên staging URL
- [ ] SSL certificate active
- [ ] 3 incidents giải quyết được, có post-mortem

---

# PHẦN 3 — BREAKDOWN THEO NGÀY (8H/NGÀY)

## Structure ngày học chuẩn

```
8:00 – 9:30   │ THEORY BLOCK (1.5h)
               │ Đọc docs, xem video reference, ghi notes
               │
9:30 – 9:45   │ BREAK
               │
9:45 – 12:00  │ CODING BLOCK 1 (2.25h)
               │ Implement theo learning goal của ngày
               │
12:00 – 13:00 │ LUNCH
               │
13:00 – 14:00 │ THEORY / READING BLOCK (1h)
               │ Deep dive vào concept vừa implement
               │ Đọc source code của library đang dùng
               │
14:00 – 16:30 │ CODING BLOCK 2 (2.5h)
               │ Continue coding / fix bugs / thêm features
               │
16:30 – 17:00 │ REVIEW BLOCK (0.5h)
               │ Self-code-review, refactor, commit sạch
               │
17:00 – 18:00 │ DISCUSSION / WRAP-UP (1h)
               │ Daily standup với mentor (15 phút)
               │ Ghi learning log
               │ Chuẩn bị plan cho ngày mai
```

## Daily Standup Format (15 phút, 17:00)
- **Yesterday**: Làm được gì, học được gì
- **Today**: Sẽ làm gì
- **Blockers**: Bị block ở đâu (cần giải quyết trước 30 phút)
- **Learning**: 1 điều mới học được

## Quy tắc cứng trong ngày
1. **Không stuck quá 30 phút** — sau 30 phút phải document vấn đề rồi hỏi
2. **Commit ít nhất 1 lần/ngày** — không có ngày không có commit
3. **Viết learning log cuối ngày** — ghi lại điều mới học, điều chưa hiểu
4. **Không dùng AI để generate code chưa hiểu** — phải đọc, hiểu, rồi mới dùng

---

# PHẦN 4 — PROJECT-BASED PROGRESSION

## Tổng quan Project: "TaskFlow" — Task Management System

Đây là project xuyên suốt 12 tuần. Đủ phức tạp để học tất cả concepts, đủ thực tế để đưa vào portfolio.

**Domain:** Task management cho team nội bộ  
**Users:** Có phân quyền Admin / Manager / Member  
**Core features:** Projects, Tasks, Comments, Attachments, Notifications

---

## 4.1 Project Milestones

### Tuần 1–4: Build Core Backend
- [x] Auth module (register, login, refresh, logout)
- [x] Users CRUD với RBAC
- [x] Projects CRUD với membership
- [x] Tasks CRUD với assignment
- [x] Comment system

### Tuần 5–8: DB Optimization + Frontend
- [x] Query optimization với proper indexes
- [x] Cursor-based pagination
- [x] N+1 queries eliminated
- [x] React frontend auth flow
- [x] Projects và Tasks UI

### Tuần 9–12: Production Hardening
- [x] Unit + integration tests (80% coverage)
- [x] Docker containerization
- [x] GitHub Actions CI/CD
- [x] Deploy lên staging
- [x] Performance audit

---

# PHẦN 5 — EVALUATION SYSTEM

## 5.1 Rubric Chấm Điểm

### Technical Skills (60 điểm)

| Tiêu chí | 0 điểm | 5 điểm | 10 điểm |
|---------|--------|---------|---------|
| Code quality | Không readable, không có structure | Có structure, nhưng inconsistent | Clean, consistent, dễ maintain |
| TypeScript | Dùng `any` nhiều, không type-safe | Có types nhưng thiếu strict | Strict mode, proper generics |
| Error handling | Không handle, crash dễ | Handle một số cases | Comprehensive, graceful |
| Testing | Không có test | Test có nhưng chất lượng thấp | Meaningful tests, 80%+ coverage |
| Database | N+1 queries, không có index | Có index nhưng chưa tối ưu | Proper indexes, optimized queries |
| Git workflow | Commit lộn xộn, không convention | Convention nhưng thiếu nhất quán | Clean history, proper PR process |

### Mindset (25 điểm)

| Tiêu chí | 0 điểm | 5 điểm | 10 điểm |
|---------|--------|---------|---------|
| Problem solving | Chờ được giải quyết | Tự tìm nhưng không document | Tự tìm, document, học từ vấn đề |
| Proactivity | Chỉ làm khi được assign | Hoàn thành task, không hơn | Chủ động tìm vấn đề, cải thiện |
| Learning speed | Phải giải thích nhiều lần | Học được từ lần đầu | Tự học, hỏi đúng câu hỏi |
| Documentation | Không có | Có nhưng không cập nhật | Tự maintain, clear |
| Security mindset | Không nghĩ đến security | Biết nhưng không apply | Chủ động hỏi "có thể abuse không?" |

### Teamwork (15 điểm)

| Tiêu chí | 0 điểm | 5 điểm | 10 điểm |
|---------|--------|---------|---------|
| Communication | Không update, biến mất | Update khi được hỏi | Proactive, clear, đúng lúc |
| Code review | Không review hoặc review vô nghĩa | Review nhưng chỉ surface-level | Review quality, học từ review |
| Deadline adherence | Miss deadline không báo | Miss nhưng báo trước | Đúng hẹn hoặc renegotiate sớm |

---

## 5.2 Lỗi Thường Gặp Ở Intern

### Lỗi Mindset

```
❌ "Code chạy là được"
   → Code chạy chưa đủ. Code phải maintainable, readable.

❌ Hỏi ngay khi gặp vấn đề (< 5 phút)
   → Phải tự debug 30 phút, document vấn đề, rồi mới hỏi.

❌ Commit code chưa test
   → Test thủ công trước khi commit, ít nhất happy path.

❌ Không đọc error message đầy đủ
   → Error message cho biết chính xác vấn đề ở đâu.

❌ Copy code không hiểu (kể cả từ AI)
   → Phải hiểu từng dòng code mình viết.
```

---

## 5.3 Red Flags — Cần Xem Xét Loại

Những dấu hiệu sau đây, nếu xuất hiện liên tục sau tuần 4, cần họp review nghiêm túc:

- **Không có tiến bộ giữa các tuần** — cùng một lỗi xuất hiện 3 tuần liên tiếp
- **Che giấu blockers** — không báo đang stuck đến khi quá muộn
- **Không đọc feedback** — mentor comment PR nhưng không giải quyết, không hỏi lại
- **Copy-paste không hiểu** — không giải thích được code mình viết khi được hỏi
- **Không viết test** — liên tục nói "sẽ viết sau"
- **Attitude với code review** — phòng thủ, không tiếp thu
- **Không tự học** — chỉ làm đúng những gì được giao

---

## 5.4 Green Flags — Giữ Lại và Nurture

- **Hỏi "tại sao" thay vì "làm như thế nào"** — muốn hiểu root cause
- **Tự tìm thấy bugs chưa được giao** — chủ động scan codebase
- **Commit nhỏ và rõ ràng** — biết cách chia công việc thành increments nhỏ
- **Comment trên PR của người khác** — muốn học từ code của mọi người
- **Tự update README khi thay đổi** — documentation mindset tự nhiên
- **Hỏi về security implications** — tư duy defense-first
- **Viết test trước khi fix bug** — instinct tốt
- **Post-mortem tự nguyện** — phân tích tại sao mình sai, không chỉ fix

---

# PHẦN 6 — MENTOR GUIDELINES

## 6.1 Lịch Review Cố Định

| Thời điểm | Nội dung | Thời lượng |
|-----------|----------|------------|
| Hàng ngày 17:00 | Daily standup | 15 phút |
| Thứ Sáu hàng tuần | Weekly review + next week plan | 1 giờ |
| Cuối tuần 4 | Phase 1–2 checkpoint | 2 giờ |
| Cuối tuần 8 | Phase 3–4 checkpoint | 2 giờ |
| Cuối tuần 12 | Final assessment | 4 giờ |

## 6.2 Weekly Review Template

```
WEEK [N] REVIEW

Đánh giá tuần:
- Deliverables nộp đúng hẹn? Y/N
- Chất lượng deliverables: 1-5
- Attitude và effort: 1-5

Điểm mạnh tuần này:
- ...

Cần cải thiện:
- ...

Blockers chưa giải quyết:
- ...

Focus tuần tới:
- ...

Mentor notes (không share với intern):
- ...
```
