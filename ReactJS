# ReactJS – Cơ chế và Vòng đời (Chi tiết & Tường minh)

ReactJS là **UI library component-based**, sử dụng **Virtual DOM** và **One-way Data Flow**, giúp UI **predictable, performant và maintainable**.

---

## 1️⃣ Cơ chế hoạt động ReactJS

- **Virtual DOM:**  
  - React render vào bộ nhớ trước, so sánh diff với Virtual DOM cũ → chỉ patch node thay đổi trên Real DOM → tối ưu performance.
- **One-way Data Flow:**  
  - Props đi từ parent → child; state quản lý dữ liệu cục bộ.  
  - Dữ liệu không đi ngược.
- **Re-rendering:**  
  - Khi state/props thay đổi → render lại Virtual DOM → diff → update Real DOM.
- **Synthetic Event:**  
  - React tổng hợp event, chuẩn hóa cross-browser.

---

## 2️⃣ Vòng đời Class Component

| Giai đoạn       | Methods                                                | Ý nghĩa |
|-----------------|--------------------------------------------------------|---------|
| **Mounting**    | `constructor()`, `static getDerivedStateFromProps()`, `render()`, `componentDidMount()` | Component khởi tạo, gắn DOM, fetch data, subscribe events |
| **Updating**    | `static getDerivedStateFromProps()`, `shouldComponentUpdate()`, `render()`, `getSnapshotBeforeUpdate()`, `componentDidUpdate()` | State/props thay đổi → quyết định re-render, snapshot trước update, side-effect sau update |
| **Unmounting**  | `componentWillUnmount()`                               | Cleanup: unsubscribe, clear timer, cancel API calls |
| **Error Handling** | `componentDidCatch(error, info)`                     | Bắt và xử lý lỗi trong subtree UI |

**Flow Mounting:**
```
constructor() → getDerivedStateFromProps() → render() → componentDidMount()
```

**Flow Updating:**
```
getDerivedStateFromProps() → shouldComponentUpdate() → render() → getSnapshotBeforeUpdate() → componentDidUpdate()
```

---

## 3️⃣ Vòng đời Functional Component (Hooks)

- **useState:** quản lý state cục bộ; setState → trigger re-render.
- **useEffect:** side-effect & cleanup.

```js
useEffect(() => {
  // side-effect: fetch data, subscription
  return () => {
    // cleanup khi unmount hoặc dependency change
  }
}, [dependencies]);
```

- **useMemo / useCallback:** tối ưu performance, tránh re-compute/re-create.
- **useRef:** giữ giá trị mutable mà không trigger re-render; thao tác DOM trực tiếp.

**Flow Render Functional Component:**
```
User Interaction / Props Change
          ↓
     setState / dispatch
          ↓
      Render Function
          ↓
      Virtual DOM diff
          ↓
      Real DOM patch
          ↓
      useEffect cleanup + run
```

---

## 4️⃣ Key Concepts

- **Virtual DOM:** tối ưu re-render
- **One-way Data Flow:** predictable UI updates
- **Diffing / Reconciliation:** minimal DOM updates
- **Hooks:** quản lý state & side-effect
- **Synthetic Event:** chuẩn hóa event cross-browser
- **Component-based:** UI tái sử dụng, modular

---

## 5️⃣ Flow tổng thể (tóm tắt)

```
User Action / Props Change
          ↓
     setState / dispatch
          ↓
      Render Component → Virtual DOM
          ↓
        Diffing
          ↓
      Real DOM Update
          ↓
      Side-effects (useEffect / componentDidMount / componentDidUpdate)
          ↓
      Cleanup (useEffect return / componentWillUnmount)
```

---

## 6️⃣ Chốt

> ReactJS = **Virtual DOM + Component-based + One-way Data Flow**  
> Class component: mounting → updating → unmounting → error handling  
> Functional component: **state + useEffect + hooks** thay thế vòng đời, side-effects, cleanup  
> Diffing Virtual DOM tối ưu re-render, Synthetic Event chuẩn hóa event handling.

---

> **Nguồn tham khảo chính thống:**  
> - [ReactJS Official Docs – Functional Components & Hooks](https://react.dev/learn)  
> - [ReactJS Official Docs – Class Components & Lifecycle](https://react.dev/learn/choosing-the-right-component)

