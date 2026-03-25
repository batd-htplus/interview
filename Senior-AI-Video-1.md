# Senior AI Video & Computer Vision Interview Guide
## Dành cho: Tran Chi Cuong — AI Engineer (Senior)

> **Nguyên tắc phỏng vấn:**
> - Trả lời đơn giản trước → đào sâu sau
> - Luôn nói về **trade-off** và **production reality**
> - CV-specific questions đánh dấu `[CV]` — khai thác trực tiếp từ kinh nghiệm ứng viên

---

# PHẦN I — LÝ THUYẾT NỀN TẢNG VIDEO AI

---

## 1. Video vs Image — Tại sao Video khó hơn?

**Q: Tại sao xử lý video khó hơn xử lý ảnh? Phân tích chi tiết.**

`Difficulty: 🟢 Cơ bản`

**Giải thích đơn giản:**
- Ảnh = 1 khoảnh khắc tĩnh
- Video = chuỗi ảnh theo thứ tự thời gian với sự phụ thuộc ngữ nghĩa giữa các frame

**Phân tích sâu — 5 chiều phức tạp:**

1. **Temporal Dependency** — Các frame phụ thuộc lẫn nhau
   - Không thể xử lý độc lập từng frame
   - Thông tin ngữ nghĩa nằm trong *chuyển động*, không phải frame đơn lẻ
   - Ví dụ: 1 frame người giơ tay ≠ ngữ nghĩa; chuỗi frames = "đang vẫy tay"

2. **Temporal Redundancy** — Dư thừa dữ liệu
   - Video 30 FPS → phần lớn frame kề nhau gần giống nhau
   - Xử lý hết = lãng phí compute khổng lồ
   - Cần: frame sampling, keyframe detection

3. **Motion & Optical Flow** — Chuyển động phi tuyến
   - Motion blur, camera shake làm mất thông tin pixel
   - Chuyển động bị che khuất (occlusion), multi-scale
   - Cần model hiểu không gian-thời gian (spatiotemporal)

4. **Temporal Coherence** — Nhất quán qua thời gian
   - Output phải nhất quán (tracking ID không đổi, label không nhảy frame)
   - Noise trong 1 frame → error lan sang frame sau → **error tích lũy**

5. **Scale & Compute** — Tài nguyên
   - Video 1 phút × 30 FPS = 1800 frames → gấp 1800 lần so với 1 ảnh
   - Memory, latency, cost đều là vấn đề production

**Kết luận:**
> Video = Spatial (như ảnh) + Temporal + Coherence → độ phức tạp tăng theo cấp số nhân

---

## 2. Frame Sampling — Chiến lược chọn frame

**Q: Bạn có chạy model AI trên mọi frame không? Chiến lược sampling của bạn là gì?**

`Difficulty: 🟡 Trung bình`

**Không.** Chạy model trên mọi frame = không scalable trong production.

**4 chiến lược sampling:**

| Phương pháp | Cách hoạt động | Khi nào dùng |
|---|---|---|
| **Uniform Sampling** | Lấy mỗi N frame (vd: 1/5) | Object di chuyển chậm, scene đơn giản |
| **Keyframe Detection** | Phát hiện frame có nhiều thông tin mới (scene change, motion spike) | Action recognition, highlight detection |
| **Adaptive Sampling** | Tăng rate khi nhiều motion, giảm khi static | Surveillance, general purpose |
| **Event-driven Sampling** | Chỉ sample khi có trigger (motion, audio event) | Tiết kiệm nhất cho camera 24/7 |

**Trade-off:**
- Ít frame → nhanh hơn nhưng có thể miss short events
- Nhiều frame → chính xác hơn nhưng tốn compute

> 💡 **Senior note:** Trong production, kết hợp **event-driven + adaptive**: dùng motion detection nhẹ để trigger, rồi adaptive sampling trong window xung quanh event đó. Tiết kiệm 80-90% inference cost.

---

## 3. Multi-Object Tracking — Pipeline Chi Tiết

**Q: Giải thích chi tiết cách multi-object tracking hoạt động từ detection đến ID assignment.**

`Difficulty: 🔴 Senior`

**Pipeline đầy đủ:**

```
Frame(t) → [Detection] → [Feature Extraction] → [Matching] → [Track Update]
                                                      ↑
                                         [Kalman Prediction từ frame(t-1)]
```

**6 bước chi tiết:**

1. **Detection:** Tìm bounding box + class trong frame t
2. **Feature Extraction:** Trích appearance embedding cho mỗi detection
3. **Prediction:** Kalman Filter dự đoán vị trí track ở frame t
4. **Matching:** Hungarian Algorithm match detection vs prediction
5. **Update:** Cập nhật track với detection được match
6. **Track Management:** Tạo track mới / xóa track đã mất quá lâu

**Chi tiết kỹ thuật:**

*Kalman Filter — dự đoán vị trí:*
- State: `[cx, cy, w, h, vx, vy]` (vị trí + vận tốc)
- Giả định constant velocity → predict tiếp theo
- Update: hiệu chỉnh khi có measurement mới
- Quan trọng khi bị occlusion tạm thời

*Hungarian Algorithm — assignment tối ưu:*
- Bài toán: N detections, M tracks → tìm matching tối thiểu cost
- Cost = `α × IOU_cost + (1-α) × embedding_distance`
- Complexity: O(n³) — chấp nhận được vì n thường nhỏ (<100)

*Re-ID / Appearance Model:*
- Dùng cosine distance trên appearance embedding
- Quan trọng khi track bị mất > 30 frames (occlusion dài)

**Vấn đề thực tế:**
- **ID Switch:** 2 người đi gần nhau và bị nhầm ID → metric: IDSW
- **Occlusion:** Người đứng sau vật cản → cần buffer track + re-ID
- **Initialization lag:** Track mới cần N frame để confirm → miss short events

> 💡 **Senior note:** ByteTrack (2022) cải tiến bằng cách dùng cả low-confidence detection để giảm miss rate. BoT-SORT thêm camera motion compensation. Nắm được xu hướng này gây ấn tượng tốt.

---

## 4. Temporal Modeling — 3 Kiến trúc Chính

**Q: So sánh 3D CNN, RNN/LSTM, và Transformer cho temporal modeling. Khi nào dùng cái nào?**

`Difficulty: 🔴 Senior`

| Kiến trúc | Điểm mạnh | Điểm yếu | Dùng khi nào |
|---|---|---|---|
| **3D CNN** (C3D, I3D) | Local motion tốt, fast inference | Fixed temporal window, không long-range | Action recognition ngắn < 2 giây |
| **LSTM/RNN** | Sequential, nhớ state | Vanishing gradient, slow training | Sequential event detection, state machine |
| **Transformer** (TimeSformer, VideoSwin) | Long-range attention, SOTA | O(n²) memory, cần nhiều data | Video understanding, video QA |
| **Mamba (SSM, 2024)** | Linear complexity | Còn mới, ít tool support | Long video, memory-constrained |

**Ví dụ thực tế:**
- `3D CNN`: Nhận diện cú đấm boxing — local motion trong 0.5 giây
- `LSTM`: Activity recognition dài — "ngủ → thức → ăn sáng"
- `Transformer`: Video captioning — hiểu toàn bộ nội dung clip

> 💡 **Senior note:** Trong production, 3D CNN thường được ưu tiên vì inference speed. Transformer chỉ dùng khi accuracy tối quan trọng và latency cho phép.

---

## 5. Video Generation — Temporal Consistency

**Q: Tại sao video AI thường bị flicker/nhảy frame? Giải thích nguyên nhân và cách khắc phục.**

`Difficulty: 🟣 Expert`

**Nguyên nhân gốc rễ:**
- Diffusion models tạo từng frame độc lập → không có temporal constraint
- Noise trong latent space không tương quan giữa frames
- GAN discriminator thường chỉ nhìn 1 frame → không phạt temporal inconsistency

**Giải pháp:**

1. **Temporal Attention** (Video Diffusion): Cross-frame attention giúp frames "nhìn" nhau
2. **Optical Flow Warping:** Warp frame t để tạo t+1, giữ pixel consistency
3. **Temporal Loss:** `L_temporal = MSE(flow_warped(frame_t), frame_t+1)`
4. **Noise Correlation:** Dùng cùng noise pattern giữa các frames (Tune-A-Video)
5. **Video-aware Discriminator:** Discriminator nhìn clip thay vì frame đơn lẻ (DVD-GAN)

> 💡 **Senior note:** Text-to-video SOTA 2024 (Sora, CogVideoX) dùng **3D VAE** để compress video thành spatiotemporal tokens trước khi diffusion → temporal consistency tự nhiên hơn.

---

# PHẦN II — SYSTEM DESIGN

---

## 6. Realtime Video Processing Pipeline

**Q: Thiết kế hệ thống AI xử lý video realtime cho 1000 camera surveillance. Requirements: detect người, tracking, alert khi có bất thường.**

`Difficulty: 🟣 Expert`

**Bước 1 — Clarify requirements trước:**
- Latency: < 500ms từ frame đến alert?
- Scale: 1000 cameras × 30 FPS = 30,000 frames/giây
- Availability: 99.9%?
- Storage: raw video, annotated video, hay chỉ events?

**Kiến trúc đề xuất:**

```
Camera (RTSP) → [Edge Node] → [Kafka] → [Inference Cluster] → [Post-Process] → [Alert/DB]

Edge Node (per camera group):        Inference Cluster:
  - Frame decode (NVDEC)               - GPU workers (async batch)
  - Motion detection (lightweight)     - Object detection (YOLO)
  - Frame sampling                     - Tracking engine (ByteTrack)
  - Publish to Kafka                   - Anomaly scoring

Message Queue (Kafka):               Post-Process:
  - Topic per camera                   - Temporal smoothing
  - Retention: 1 hour                  - Business logic / zone rules
  - Partition by camera_id             - Alert dedup & throttling
```

**Back-of-envelope:**
```
1000 cameras × 30 FPS × 10% active (motion filter) = 3,000 frames/s effective
YOLOv8n: ~5ms/frame trên T4 GPU (batch=8) → 1,600 fps/GPU
→ Cần ~2 GPUs + 3x headroom cho HA = 6 GPUs
```

**Failure modes & mitigations:**

| Failure | Mitigation |
|---|---|
| GPU crash | Kafka consumer group tự rebalance |
| Network drop | Edge buffer 30s + retry |
| Camera fault | Health check: black frame, FPS drop → alert |
| Model drift | Shadow deployment + monitor precision/recall |

---

## 7. Video AI cho Mobile / Edge Device

**Q: Thiết kế hệ thống action recognition chạy trên smartphone, không cần internet.**

`Difficulty: 🔴 Senior`

**Constraints:**
- CPU: ARM + Neural Engine / DSP
- Memory: < 150MB model size
- Power: battery — cần tối ưu FLOPS
- Latency: < 100ms per frame

**Chiến lược tối ưu (theo thứ tự ROI):**

1. **Smaller architecture:** MobileNet / EfficientNet thay vì ResNet
2. **Quantization INT8:** 4x nhỏ hơn, 2x nhanh, ~1% accuracy drop
3. **Knowledge Distillation:** Train small model từ large teacher
4. **Pruning:** Cắt 30-50% weights ít quan trọng
5. **Temporal reduction:** 8 frames thay vì 32
6. **Platform runtime:** CoreML (iOS), TFLite (Android), ONNX Runtime

| Kỹ thuật | Size reduction | Speed gain | Accuracy cost |
|---|---|---|---|
| INT8 Quantization | 4x | 2x | ~1% |
| Pruning 50% | 2x | 1.5x | 2-5% |
| Distillation | tùy | tùy | thấp nhất |
| Fewer frames (32→8) | N/A | 4x | 3-8% |

---

# PHẦN III — PRODUCTION ISSUES

---

## 8. Debug Model Fail Production

**Q: Model accuracy 95% offline nhưng chỉ đạt 60% trong production. Bạn debug thế nào?**

`Difficulty: 🔴 Senior`

**Quy trình debug có hệ thống:**

1. Thu thập evidence: Log input/output production, so sánh với test set
2. Kiểm tra data distribution: Plot histogram features, so sánh train vs production
3. Kiểm tra preprocessing pipeline: Có mismatch không? (normalize khác nhau?)
4. Phân tích failure cases: Cluster theo loại lỗi, tìm pattern
5. Shadow test: Chạy model trên production data offline → benchmark

**Nguyên nhân thường gặp:**

- **Data drift:** Ánh sáng, góc camera, độ phân giải khác training data
- **Preprocessing mismatch:** Normalize mean/std khác, resize method khác → bug silent nhất
- **Label leakage:** Test set vô tình dùng thông tin từ tương lai
- **Hardware difference:** FP16 vs FP32 → output khác một chút → threshold fail
- **Concept drift:** World thay đổi theo thời gian (mùa, trend)

**Cách phòng ngừa:**
- Continuous evaluation: Chạy evaluation tự động mỗi ngày trên production samples
- Data flywheel: Log hard cases → label → retrain
- Canary deployment: 5% traffic trước khi full roll out

---

## 9. Tối ưu hệ thống chậm

**Q: Hệ thống video AI đang chạy 4 FPS nhưng requirement là 15 FPS. Bạn làm gì?**

`Difficulty: 🔴 Senior`

> ⚠️ **Anti-pattern:** Đừng optimize ngẫu nhiên. 80% time thường nằm trong 20% code. **Phải đo (profile) trước.**

**Quy trình đúng:**

1. **Profile toàn pipeline** — Đo từng stage (decode, preprocess, inference, postprocess)
2. **Identify bottleneck** — Thường là inference hoặc decode
3. **Optimize bottleneck** — Rồi lặp lại

**Công cụ profile:**
- NVIDIA Nsight / nvprof: GPU utilization, memory bandwidth
- Python cProfile + line_profiler: CPU bottleneck
- OpenTelemetry: Distributed tracing trong pipeline

**Optimization theo layer:**

*Model-level:*
- TensorRT / ONNX Runtime: thường 2-4x speedup
- Quantization INT8: 2x faster, minimal accuracy loss
- Smaller model: YOLOv8n vs YOLOv8x → 10x faster

*System-level:*
- Batch inference: Tăng batch size → tăng GPU utilization
- Async pipeline: Decode / preprocess / inference song song (CUDA streams)
- GPU decoding: NVDEC thay vì CPU decode

*Data-level:*
- Giảm resolution: 1080p → 720p thường không mất accuracy nhiều
- Frame skipping khi GPU overloaded: Adaptive QoS
- ROI cropping: Chỉ inference trên vùng quan trọng

---

## 10. Xử lý False Positive

**Q: Hệ thống detection báo alert sai quá nhiều. Giải quyết thế nào mà không ảnh hưởng recall?**

`Difficulty: 🟡 Trung bình`

**Chiến lược giảm FP:**

1. **Temporal smoothing:** Object phải xuất hiện ≥ N frames liên tiếp mới trigger alert
2. **Confidence decay:** Score = running average của N frames, không dùng single-frame score
3. **Context filtering:** Kết hợp context (time of day, zone rules, camera angle)
4. **Two-stage verification:** Model nhẹ detect → Model nặng verify
5. **Negative mining:** Thu thập FP cases → add vào training data → retrain

> ⚠️ **Trade-off quan trọng:** Precision vs Recall là zero-sum. Tăng threshold → giảm FP nhưng tăng FN. Luôn hỏi business: FP hay FN tệ hơn với use case này?

> 💡 **Thực tế:** Temporal smoothing (N=3 frames) thường giảm FPR 50-70% mà hầu như không ảnh hưởng recall, vì real events thường kéo dài > 3 frames.

---

# PHẦN IV — CÂU HỎI NÂNG CAO

---

## 11. Video Anomaly Detection

**Q: Thiết kế hệ thống detect anomaly trong video (không biết trước anomaly trông như thế nào).**

`Difficulty: 🟣 Expert`

**Thách thức:**
- "Anomaly" là gì? → cần define rõ với business (fighting, falling, loitering...)
- Thường không có labeled anomaly data → unsupervised/semi-supervised
- High variety: cùng hành động nhưng context khác nhau

**4 approach chính:**

1. **Reconstruction-based (Autoencoder)**
   - Train AE trên normal video
   - Anomaly = reconstruction error cao
   - Giả định: AE không thể reconstruct well những gì chưa thấy

2. **Prediction-based**
   - Train model predict frame t+1 từ frames t-N..t
   - Anomaly = prediction error cao
   - Tốt hơn AE vì khai thác temporal structure

3. **One-class classification (DeepSVDD)**
   - Học "sphere" bao quanh normal data trong feature space
   - Anomaly = nằm ngoài sphere

4. **Score fusion**
   - Kết hợp reconstruction error + motion abnormality + pose abnormality
   - Weights tuning dựa trên validation set

> 💡 **Threshold tuning:** Dùng percentile của validation normal score thay vì fixed threshold. Ví dụ: P95 of normal scores = threshold.

---

## 12. Multi-Camera Re-Identification

**Q: Thiết kế hệ thống track người qua nhiều camera không overlap (cross-camera re-ID).**

`Difficulty: 🟣 Expert`

**Thách thức so với single-camera tracking:**
- Không có temporal continuity giữa cameras
- Appearance thay đổi: góc, ánh sáng, distance khác
- Thời gian xuất hiện ở camera mới có thể cách hàng phút

**Kiến trúc giải pháp:**

1. **Local Tracking:** Single-camera tracking cho mỗi camera riêng
2. **Tracklet extraction:** Mỗi track → sequence of crops → aggregate embedding
3. **Re-ID Model:** Train metric learning (triplet loss / ArcFace)
4. **Cross-camera matching:** Compare gallery embeddings across cameras
5. **Spatio-temporal reasoning:** Constraint matching bằng camera topology

**Key techniques:**
- **Part-based features:** Chia người thành regions → robust với partial occlusion
- **Domain adaptation:** Fine-tune Re-ID model với data từ target cameras
- **Camera link model:** Learn transition time distribution giữa camera pairs
- **Batch hard triplet mining:** Hardest positive/negative trong batch → better embedding

---

## 13. Optimize AI Cost ở Scale

**Q: Bạn có hệ thống SaaS AI serving 1000 images/10s. Làm sao giảm cost mà không ảnh hưởng quality?**

`Difficulty: 🟣 Expert`

**Chiến lược tổng thể:**

1. **Request batching:** Group requests từ nhiều users vào 1 batch → tăng GPU utilization
2. **Caching:** Cache kết quả cho duplicate/similar inputs (perceptual hashing)
3. **Model cascade:** Model nhẹ filter → model nặng chỉ khi cần
4. **Quantization:** FP16 → INT8 thường không ảnh hưởng quality cho user-facing
5. **Spot instances:** Dùng preemptible GPUs cho non-realtime tasks
6. **Routing:** Đơn giản → cheap model; phức tạp → expensive model (LLM routing)

**Metrics cần track:**
- Cost per image (target: < $0.15)
- GPU utilization (target: > 70%)
- P99 latency
- Error rate

---

# PHẦN V — THUẬT TOÁN & CODING

---

## Algo 1: IOU Calculation

**Q: Implement hàm tính Intersection over Union (IOU) giữa 2 bounding boxes.**

`Difficulty: 🟢 Cơ bản`

- **Input:** `box1 = [x1, y1, x2, y2]`, `box2 = [x1, y1, x2, y2]`
- **Output:** float trong [0, 1]

```python
def iou(box1, box2):
    # Tính intersection
    inter_x1 = max(box1[0], box2[0])
    inter_y1 = max(box1[1], box2[1])
    inter_x2 = min(box1[2], box2[2])
    inter_y2 = min(box1[3], box2[3])

    inter_w = max(0, inter_x2 - inter_x1)
    inter_h = max(0, inter_y2 - inter_y1)
    intersection = inter_w * inter_h

    # Tính union
    area1 = (box1[2] - box1[0]) * (box1[3] - box1[1])
    area2 = (box2[2] - box2[0]) * (box2[3] - box2[1])
    union = area1 + area2 - intersection

    return intersection / union if union > 0 else 0.0
```

**Follow-up:**
- Vectorize tính IOU cho N×M box pairs cùng lúc (NumPy)?
- DIoU, GIoU, CIoU khác IOU như thế nào và dùng khi nào trong training?
- Generalize sang 3D IoU?

---

## Algo 2: Non-Maximum Suppression (NMS)

**Q: Implement NMS từ scratch. Explain time complexity và cách optimize.**

`Difficulty: 🟡 Trung bình`

**Mục đích:** Loại bỏ duplicate detections — giữ box confidence cao nhất, xóa các box overlap nhiều.

```python
def nms(boxes, scores, iou_threshold=0.5):
    """
    boxes: List[[x1,y1,x2,y2]], scores: List[float]
    Returns: indices of kept boxes
    """
    if len(boxes) == 0:
        return []

    # Sort by score descending
    order = sorted(range(len(scores)),
                   key=lambda i: scores[i], reverse=True)

    kept = []
    while order:
        i = order[0]        # box with highest score
        kept.append(i)
        order = order[1:]   # remaining

        # Remove boxes that overlap too much with i
        order = [j for j in order
                 if iou(boxes[i], boxes[j]) < iou_threshold]

    return kept

# Time: O(n²) naive | Space: O(n)
# Production: torchvision.ops.nms (CUDA) → O(n log n)
```

**Follow-up:**
- Soft-NMS khác gì NMS và khi nào dùng? (overlapping objects cùng class)
- Class-agnostic vs per-class NMS?
- Implement Soft-NMS: thay vì xóa, giảm score theo Gaussian decay của IOU

---

## Algo 3: Hungarian Algorithm cho Tracking

**Q: Implement bipartite matching giữa detections và tracks. Explain tại sao dùng Hungarian thay vì greedy.**

`Difficulty: 🔴 Senior`

**Bài toán:** N detections, M tracks → tìm assignment tối thiểu tổng cost (1 - IOU).

```python
from scipy.optimize import linear_sum_assignment
import numpy as np

def match_detections_to_tracks(detections, tracks, iou_thresh=0.3):
    """
    detections: List[bbox], tracks: List[predicted_bbox]
    Returns: matched pairs, unmatched_dets, unmatched_tracks
    """
    if len(detections) == 0 or len(tracks) == 0:
        return [], list(range(len(detections))), list(range(len(tracks)))

    # Build cost matrix (1 - IOU)
    cost = np.zeros((len(detections), len(tracks)))
    for i, det in enumerate(detections):
        for j, trk in enumerate(tracks):
            cost[i, j] = 1 - iou(det, trk)

    # Hungarian algorithm — O(n³)
    det_idx, trk_idx = linear_sum_assignment(cost)

    matches, unmatched_dets, unmatched_trks = [], [], []

    for d, t in zip(det_idx, trk_idx):
        if cost[d, t] < (1 - iou_thresh):
            matches.append((d, t))
        else:
            unmatched_dets.append(d)
            unmatched_trks.append(t)

    # Remaining unmatched
    matched_d = {m[0] for m in matches}
    matched_t = {m[1] for m in matches}
    unmatched_dets += [i for i in range(len(detections)) if i not in matched_d]
    unmatched_trks += [j for j in range(len(tracks)) if j not in matched_t]

    return matches, unmatched_dets, unmatched_trks

# Tại sao không greedy?
# Greedy: match cái tốt nhất trước → có thể "chiếm" match của track khác
# Hungarian: globally optimal → tổng cost nhỏ nhất
```

**Follow-up:**
- Thêm appearance embedding vào cost function như thế nào?
- Khi N >> M, cost matrix shape như thế nào?
- ByteTrack cải tiến step này như thế nào?

---

## Algo 4: Motion Detection — Frame Differencing

**Q: Implement motion detection để làm pre-filter trước inference. Trade-off giữa frame diff vs background subtraction?**

`Difficulty: 🟢 Cơ bản`

```python
import cv2
import numpy as np

class MotionDetector:
    def __init__(self, threshold=25, min_area=500):
        self.threshold = threshold
        self.min_area = min_area
        # MOG2: adaptive background model, handles gradual lighting change
        self.bg_subtractor = cv2.createBackgroundSubtractorMOG2(
            history=500, varThreshold=16, detectShadows=False
        )

    def detect(self, frame):
        # Background subtraction (robust hơn simple frame diff)
        fg_mask = self.bg_subtractor.apply(frame)

        # Morphological ops để giảm noise
        kernel = np.ones((5, 5), np.uint8)
        fg_mask = cv2.morphologyEx(fg_mask, cv2.MORPH_OPEN, kernel)
        fg_mask = cv2.dilate(fg_mask, kernel, iterations=2)

        # Tìm motion regions
        contours, _ = cv2.findContours(
            fg_mask, cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE
        )

        motion_rois = []
        for c in contours:
            if cv2.contourArea(c) > self.min_area:
                x, y, w, h = cv2.boundingRect(c)
                motion_rois.append((x, y, x + w, y + h))

        return len(motion_rois) > 0, motion_rois

# Trade-off:
# Frame diff: đơn giản, nhanh, nhưng bị ảnh hưởng bởi ánh sáng thay đổi
# MOG2: robust hơn, học background adaptively, nhưng cần warmup ~30 frames
```

**Follow-up:**
- Xử lý camera shake như thế nào?
- Kết hợp motion ROI với model inference ra sao?
- Khi nào MOG2 fail? (ví dụ: người đứng im lâu)

---

## Algo 5: Temporal Smoothing

**Q: Implement temporal smoother để giảm flickering detection. Phân tích latency vs stability trade-off.**

`Difficulty: 🟡 Trung bình`

```python
from collections import deque

class TemporalSmoother:
    """
    State machine: UNCONFIRMED → CONFIRMED → DISAPPEARED
    Giảm FPR mà không cần thay đổi model.
    """
    def __init__(self, confirm_frames=3, disappear_frames=5, window=10):
        self.confirm_frames = confirm_frames
        self.disappear_frames = disappear_frames
        self.history = deque(maxlen=window)
        self.confirmed = False
        self.disappear_count = 0

    def update(self, detected: bool) -> bool:
        """Returns smoothed decision."""
        self.history.append(detected)
        positive_count = sum(self.history)

        if not self.confirmed:
            # Confirm: cần N positives trong window
            if positive_count >= self.confirm_frames:
                self.confirmed = True
                self.disappear_count = 0
        else:
            if not detected:
                self.disappear_count += 1
            else:
                self.disappear_count = 0

            if self.disappear_count >= self.disappear_frames:
                self.confirmed = False

        return self.confirmed

# Trade-off:
# confirm_frames tăng → ít FP hơn nhưng latency cao hơn
# disappear_frames tăng → ổn định hơn nhưng slow response khi object thật sự rời đi
# Latency: confirm_frames × frame_interval = 3 × 33ms = ~100ms
```

---

## Algo 6: Kalman Filter cho Tracking

**Q: Implement Kalman Filter để predict bounding box khi detection bị miss (occlusion).**

`Difficulty: 🔴 Senior`

```python
import numpy as np

class KalmanBoxTracker:
    """
    State: [cx, cy, w, h, vx, vy, 0, 0]
    Giả định constant velocity model.
    """
    def __init__(self, bbox):
        cx = (bbox[0] + bbox[2]) / 2
        cy = (bbox[1] + bbox[3]) / 2
        w  = bbox[2] - bbox[0]
        h  = bbox[3] - bbox[1]

        self.x = np.array([[cx], [cy], [w], [h],
                            [0.], [0.], [0.], [0.]])

        # Transition matrix: x_new = F @ x
        self.F = np.eye(8)
        for i in range(4):
            self.F[i, i + 4] = 1.0   # position += velocity

        # Measurement: observe [cx, cy, w, h] only
        self.H = np.eye(4, 8)

        # Covariances (tuned empirically)
        self.P = np.eye(8) * 10     # state uncertainty
        self.Q = np.eye(8) * 0.01  # process noise
        self.R = np.eye(4) * 1.0   # measurement noise

    def predict(self):
        """Predict next position (no measurement needed)."""
        self.x = self.F @ self.x
        self.P = self.F @ self.P @ self.F.T + self.Q
        cx, cy, w, h = self.x[:4].flatten()
        return [cx - w/2, cy - h/2, cx + w/2, cy + h/2]

    def update(self, bbox):
        """Correct prediction with actual measurement."""
        z = np.array([[(bbox[0] + bbox[2]) / 2],
                       [(bbox[1] + bbox[3]) / 2],
                       [bbox[2] - bbox[0]],
                       [bbox[3] - bbox[1]]])
        S = self.H @ self.P @ self.H.T + self.R
        K = self.P @ self.H.T @ np.linalg.inv(S)  # Kalman gain
        self.x = self.x + K @ (z - self.H @ self.x)
        self.P = (np.eye(8) - K @ self.H) @ self.P
```

**Follow-up:**
- Tune Q vs R có ý nghĩa gì? (Q lớn → trust measurement; R lớn → trust prediction)
- Constant velocity fail khi nào? (acceleration, sudden direction change)
- Extended Kalman Filter (EKF) khác gì standard KF?

---

# PHẦN VI — METRICS & EVALUATION

---

## 14. Chọn Metrics Đúng

**Q: Bạn dùng metric nào để đánh giá từng loại task trong video AI? Tại sao?**

`Difficulty: 🟡 Trung bình`

| Task | Metric chính | Giải thích | Lưu ý |
|---|---|---|---|
| Classification | F1, AUC-ROC | Imbalanced data → dùng F1, không dùng Accuracy | Tách per-class F1 |
| Object Detection | mAP@0.5, mAP@0.5:0.95 | COCO standard, multiple IoU thresholds | Check AP per class |
| Tracking | MOTA, IDF1 | MOTA = 1 - (FP+FN+IDSW)/GT; IDF1 = ID F1 | IDSW quan trọng |
| Video Generation | FVD, FID | Fréchet Video/Image Distance | Human eval vẫn cần |
| Re-ID | mAP, Rank-1 | Rank-1: % query đúng top-1 | Test multi-cam |
| Action Recognition | Top-1, Top-5 Acc | Top-5: đúng nếu GT trong top 5 | Per-action breakdown |
| Anomaly Detection | AUC-ROC, AUPR | Frame-level hoặc event-level | Define event boundary rõ |
| Face Recognition | EER, TAR@FAR | EER: Equal Error Rate; TAR@FAR=0.001 | Test cross-demographic |

> 💡 **Senior note:** Luôn giải thích tại sao chọn metric. "Tôi chọn F1 thay vì Accuracy vì data imbalanced 1:99" → tốt hơn nhiều "tôi chọn F1".

---

# PHẦN VII — CÂU HỎI KHAI THÁC TỪ CV

> 🎯 Phần này dành riêng cho **Tran Chi Cuong**. Câu hỏi khai thác trực tiếp từ kinh nghiệm thực tế trong CV.

---

## [CV-01] Face Recognition System — 2D

**Q `[CV]`: Bạn achieved EER=0.008 cho 2D Face Recognition. Giải thích EER là gì và bạn đã làm gì để đạt được con số đó?**

`Difficulty: 🔴 Senior` | `Source: Applied Recognition Corp`

**Kỳ vọng ứng viên nói được:**

- EER (Equal Error Rate) = điểm giao của FAR và FRR
  - FAR (False Accept Rate): nhận nhầm người khác
  - FRR (False Reject Rate): từ chối nhầm người đúng
  - EER thấp → model cân bằng tốt giữa security và convenience
  - EER=0.008 = 0.8% → top-tier production quality

- Trade-off FAR vs FRR tùy use case:
  - Bank auth: FAR phải rất thấp (security > convenience)
  - Phone unlock: FRR phải thấp (convenience > security)

- Techniques thường dùng để đạt EER thấp:
  - ArcFace loss thay vì softmax → better embedding margin
  - Hard negative mining trong training
  - Data augmentation: lighting, pose, expression variation
  - Anti-spoofing: liveness detection để tránh photo attack

**Follow-up:**
- Bạn test EER trên dataset nào? In-house hay public (LFW, IJB-C)?
- Làm sao bạn handle cross-demographic bias trong model?
- Windows Credential Provider deploy như thế nào? DLL injection?

---

## [CV-02] 3D Face Recognition — EER=0.0001

**Q `[CV]`: EER=0.0001 cho 3D Face Recognition là cực kỳ ấn tượng. Walk me through pipeline từ sensor đến authentication.**

`Difficulty: 🟣 Expert` | `Source: Applied Recognition Corp`

**Kỳ vọng ứng viên giải thích được:**

*Pipeline:*
```
3D Sensor (ORBBEC/RealSense) → Depth map → Point cloud → 
Face detection (2D RGB + depth) → Face crop → 
3D preprocessing (alignment, normalization) → 
Feature extraction (PointNet / 3DMM) → 
Embedding → Comparison (cosine/L2) → Decision
```

*Synthetic data generation (điểm đặc biệt trong CV):*
- Tại sao cần? 3D face data khan hiếm so với 2D
- Cách generate: 3DMM (3D Morphable Model) → render nhiều pose/expression/lighting
- Augmentation: random rotation, depth noise, sensor noise simulation

*<1s latency trên iOS:*
- CoreML compilation của model
- Metal GPU acceleration
- Point cloud preprocessing tối ưu (voxelization hoặc FPS sampling)

**Follow-up:**
- Bạn dùng PointNet hay PointNet++ hay kiến trúc khác cho 3D feature extraction?
- 3DMM fitting pipeline của bạn là gì? (Basel Face Model, FLAME, hay custom?)
- Làm sao handle sensor noise từ ORBBEC vs RealSense khác nhau?
- Anti-spoofing cho 3D: 3D mask attack thì xử lý thế nào?

---

## [CV-03] Gait Recognition — Graduation Project

**Q `[CV]`: Bạn tốt nghiệp với dự án Gait Recognition. Giải thích approach và tại sao gait là biometric đặc biệt?**

`Difficulty: 🟡 Trung bình` | `Source: Education`

**Kỳ vọng ứng viên nói được:**

*Tại sao Gait đặc biệt?*
- **Covert recognition:** Nhận diện từ xa, không cần cooperation
- **Khó giả mạo:** Khó fake walking pattern so với face/fingerprint
- **Multi-modal potential:** Kết hợp với face re-ID cho cross-camera

*Approach phổ biến:*
- **Silhouette-based:** GEI (Gait Energy Image) — aggregate silhouettes → CNN
- **Skeleton-based:** Pose estimation → Graph Neural Network (ST-GCN)
- **Model-based:** 3D body model fitting + kinematics

*Thách thức thực tế:*
- Clothing change → silhouette thay đổi
- Carrying objects → gait thay đổi
- Camera angle dependency → cần view-invariant features
- Speed variation

**Follow-up:**
- Bạn dùng approach nào? Tại sao?
- Dataset nào? CASIA-B, OUMVLP?
- Accuracy đạt được? Metric là gì (Rank-1 CMC)?
- Nếu deploy production, thách thức lớn nhất là gì?

---

## [CV-04] Jetson Nano Deployment — 16 persons/sec

**Q `[CV]`: Bạn deployed facial recognition trên Jetson Nano đạt 16 persons/sec. Explain optimization pipeline.**

`Difficulty: 🔴 Senior` | `Source: TMT Corp`

**Kỳ vọng ứng viên breakdown được:**

*16 persons/sec = ???:*
- 1 person = detect 1 face + extract embedding + compare gallery
- Nếu gallery 1000 người: 16 × (detection + embedding + 1000 comparisons) / giây
- Jetson Nano: 4-core ARM + 128-core Maxwell GPU (4GB unified memory)

*Optimization techniques:*
```
Face Detection:
  - MTCNN → lightweight detector (RetinaFace-mobile / Scrfd-mobile)
  - TensorRT INT8 compilation
  - Batch processing nhiều camera inputs

Embedding:
  - MobileNet backbone thay vì ResNet50
  - FP16 inference
  - Pre-allocate GPU memory

Gallery Search:
  - FAISS (Flat L2 hoặc IVF) thay vì brute-force
  - Precompute + cache gallery embeddings
  - KD-tree cho small gallery
```

**Follow-up:**
- Bạn measure throughput như thế nào? (frames/sec hay persons/sec?)
- Memory footprint của toàn pipeline là bao nhiêu?
- Latency P99 là bao nhiêu? (throughput ≠ latency)
- Khi gallery tăng từ 100 → 10000 người, bạn xử lý scaling thế nào?

---

## [CV-05] SaaS AI — 1000 images/10s với cost $0.15

**Q `[CV]`: Bạn xây SaaS AI serving 1000 images/10s với cost <$0.15/image. Giải thích architecture và cost optimization.**

`Difficulty: 🟣 Expert` | `Source: Applied Recognition Corp`

**Kỳ vọng ứng viên phân tích được:**

*Back-of-envelope:*
```
1000 images / 10s = 100 images/s
Cost target: $0.15/image = $15/s = $54,000/hour
(thực ra $0.15 rất competitive, cần optimize kỹ)

GPU cost (A10G): ~$1.5/hour → 3600s/hour
Break even: $1.5/3600s = $0.00042/s → cần ~3600 images/s để profitable ở $0.15
→ GPU utilization và batching cực kỳ quan trọng
```

*VLM + DSPy (đây là điểm đặc biệt trong CV):*
- DSPy: prompt optimization framework → giảm prompt length → giảm token cost
- DSPy compile: optimize prompts automatically trên validation set
- Giảm API cost 30-50% thường gặp với DSPy vs manual prompting

*Architecture:*
- Request queue (Redis/SQS) → batch collector → GPU inference → response
- Dynamic batching: wait max 50ms hoặc batch_size=32, whatever comes first
- Async processing: return job_id immediately, callback khi done

**Follow-up:**
- Face-swap pipeline cụ thể là gì? (detection → segmentation → GAN/diffusion swap → blending)
- DSPy bạn dùng như thế nào? Optimize prompt hay dùng program với Chain-of-Thought?
- Làm sao handle content moderation ở scale này?
- 300+ customers, SLA của bạn là gì? Làm sao handle burst traffic?

---

## [CV-06] Large-scale Image Pipeline — 100k images/day

**Q `[CV]`: Bạn xây pipeline xử lý 100,000 images/day. Giải thích architecture, bottleneck, và cách bạn ensure data quality.**

`Difficulty: 🔴 Senior` | `Source: Applied Recognition Corp`

**Kỳ vọng ứng viên nói được:**

*100k/day = ~1.15 images/sec (manageable nhưng cần pipeline tốt):*
- Peak load có thể 10x → cần handle burst
- Storage: 100k × 5MB average = 500GB/day → lifecycle policy cần thiết

*Pipeline stages:*
```
Input → [Validation] → [Queue] → [Processing Workers] → [Quality Check] → [Output/Storage]

Processing: person recognition + scene recognition + attribute extraction + ExifTool metadata
```

*Quality assurance tại scale:*
- Checksum validation: đảm bảo image không bị corrupt
- Confidence threshold: flag low-confidence results cho human review
- Sampling-based QA: review 1% results manually mỗi ngày
- Dedup: perceptual hash để detect duplicate images

**Follow-up:**
- ExifTool integration: bạn extract metadata gì? EXIF có GPS data không → privacy concern?
- Khi 1 worker fail giữa chừng, bạn handle idempotency thế nào?
- Person recognition ở đây là face re-ID hay general person attribute?
- Pipeline này chạy on-premise hay cloud? Cost structure như thế nào?

---

## [CV-07] OCR + Signboard Reader — Production ở HCMC & Đà Nẵng

**Q `[CV]`: Bạn xây OCR signboard reader tích hợp với vehicle-based collection và geo-mapping. Thách thức thực tế là gì?**

`Difficulty: 🟡 Trung bình` | `Source: TMT Corp`

**Kỳ vọng ứng viên nói được:**

*Challenges đặc thù cho signboard OCR trên đường:*
- **Motion blur:** Vehicle đang chạy → cần shutter speed cao hoặc deblur
- **Angle variation:** Camera góc thấp → perspective distortion → cần rectification
- **Vietnamese text:** Dấu tone marks phức tạp, font đa dạng
- **Lighting:** Ban ngày/tối, bóng, phản chiếu
- **Partial occlusion:** Cây, người che một phần biển

*Pipeline:*
```
Camera Frame → [Signboard Detection] → [Crop + Perspective Correction] → 
[Text Detection (CRAFT)] → [Text Recognition (CRNN/VLM)] → 
[Post-process Vietnamese] → [GPS tagging (ExifTool/NMEA)] → [Map storage]
```

*Geo-mapping accuracy:*
- GPS drift: xe đang chạy, GPS latency ~100ms → position error 2-3m
- Cần timestamp sync giữa camera và GPS receiver

**Follow-up:**
- CRNN vs VLM-based OCR: bạn đã dùng gì và tại sao?
- Làm sao handle trường hợp biển hiệu bị mờ hoặc chỉ thấy một phần?
- Update map khi signboard thay đổi (cửa hàng đổi tên) thì xử lý thế nào?
- Scale: bao nhiêu km đường đã được map?

---

## [CV-08] Liveness Detection & Anti-Spoofing

**Q `[CV]`: Với hệ thống Face Recognition deployed trong authentication, bạn handle anti-spoofing như thế nào?**

`Difficulty: 🔴 Senior` | `Source: Applied Recognition Corp`

**Kỳ vọng ứng viên phân tích được:**

*Attack types:*
- **Print attack:** In ảnh người dùng rồi giơ lên camera
- **Replay attack:** Phát video trên điện thoại
- **3D mask attack:** In mặt nạ 3D (khó nhất)
- **Deepfake attack:** Realtime face swap (mới nổi)

*2D Liveness (Passive):*
- Texture analysis: real face có micro-texture, print có dot pattern
- Frequency domain: high-frequency pattern của in ấn
- Depth cue từ monocular: face thật có depth variation tự nhiên

*3D Liveness (với depth sensor — relevant với CV của ứng viên):*
- Depth map validation: check depth profile có khớp với face shape không
- Point cloud liveness: surface normal pattern của real face vs mask

*Active Liveness:*
- Challenge-response: nhấp mắt, quay đầu, mỉm cười
- Overhead cao nhưng robust hơn

**Follow-up:**
- EER=0.0001 của bạn đo *với* hay *không có* liveness check?
- Bạn có test với deepfake attack không?
- iOS deployment: bạn dùng Face ID depth sensor hay camera thường?
- Compliance: có standards nào cho liveness detection không? (ISO 30107-3)

---

## [CV-09] Edge AI — Jetson vs Hailo vs NPU

**Q `[CV]`: Bạn có experience với Jetson Nano và nhiều edge platforms. So sánh khi nào chọn Jetson vs Hailo vs mobile NPU.**

`Difficulty: 🔴 Senior` | `Source: Skills section`

**Kỳ vọng ứng viên so sánh được:**

| Platform | TOPS | Power | Use case | Limitation |
|---|---|---|---|---|
| Jetson Nano | ~0.5 TFLOPS FP16 | 5-10W | Flexible, full Linux, easy dev | Expensive, power-hungry |
| Jetson Orin | 275 TOPS | 15-60W | High-end edge inference | Very expensive |
| Hailo-8 | 26 TOPS | 2.5W | Mass production, very efficient | Less flexible, fixed ops |
| Apple NPU (A17) | ~35 TOPS | ~0.5W | iOS app, CoreML native | iOS only, closed ecosystem |
| Raspberry Pi + Hailo | | Low | Cost-sensitive deployment | |

*Decision framework:*
- **Jetson:** R&D, prototype, flexible workload, need full Linux ecosystem
- **Hailo:** Mass production, cost-sensitive, power budget < 5W, model đã fix
- **Mobile NPU:** App trên device người dùng, không cần server

**Follow-up:**
- Bạn đã deploy lên Hailo chưa? Compile workflow như thế nào?
- TensorRT vs CoreML: workflow khác nhau ra sao?
- Bạn handle model update (OTA) trên edge device thế nào?

---

## [CV-10] LG Experience — Cybersecurity AI + SWPCT

**Q `[CV]`: Ở LG, bạn làm AI-powered cybersecurity. Explain cách AI áp dụng vào cybersecurity khác gì với computer vision thông thường.**

`Difficulty: 🟡 Trung bình` | `Source: LG Electronics`

**Kỳ vọng ứng viên nói được:**

*AI trong cybersecurity:*
- **Anomaly detection:** Network traffic → detect unusual patterns (autoencoders, LSTM)
- **Malware classification:** Binary analysis → CNN trên byte sequences
- **Intrusion detection:** System call sequences → RNN/Transformer
- **Vulnerability detection:** Static code analysis với ML

*Khác với CV thông thường:*
- **Adversarial robustness quan trọng hơn:** Attackers actively try to evade detection
- **Imbalanced data cực đoan:** Normal traffic >> attack traffic
- **False positive cost rất cao:** FP = block legitimate traffic = business impact
- **Concept drift nhanh hơn:** New attack patterns liên tục xuất hiện

*LG SWPCT — top 10%:*
- Chứng tỏ strong algorithm foundation
- Expectation: có thể giải được medium-hard LeetCode

**Follow-up:**
- Bạn đã làm anomaly detection trên loại data gì? (system call, network packet, log?)
- Adversarial attacks trên AI model cybersecurity: bạn có test không?
- Manage team 4 người ở LG như thế nào? Conflict resolution?

---

# PHẦN VIII — RAPID-FIRE QUESTIONS (20 câu)

> Dùng để check nhanh kiến thức. Kỳ vọng trả lời trong 60-90 giây mỗi câu.

---

| # | Câu hỏi | Đáp án kỳ vọng |
|---|---|---|
| RF-01 | Optical flow là gì? | Vector field biểu diễn chuyển động pixel giữa 2 frames. Dùng cho: motion estimation, video stabilization, action recognition |
| RF-02 | EER = 0 có nghĩa là gì? | Perfect classifier — không có FAR và FRR đồng thời. Không thực tế trong production vì threshold phải chọn 1 điểm trên ROC curve |
| RF-03 | Stride trong 3D CNN là gì? | Bước nhảy theo 3 chiều (H, W, T). Temporal stride giảm temporal resolution và compute |
| RF-04 | P-frame và I-frame khác gì? | I-frame: independent keyframe. P-frame: predicted từ frame trước → cần decode sequence để lấy full frame |
| RF-05 | Batch normalization có vấn đề gì với video? | Statistics thay đổi theo temporal domain → Layer Norm hoặc Group Norm thường tốt hơn cho video |
| RF-06 | SORT vs DeepSORT? | SORT: Kalman + Hungarian, chỉ position. DeepSORT: thêm appearance embedding → giảm ID switch |
| RF-07 | ArcFace khác Softmax như thế nào? | ArcFace thêm angular margin trong loss → embedding space compact hơn, inter-class separation tốt hơn |
| RF-08 | EER vs TAR@FAR — khi nào dùng cái nào? | EER: tổng quan performance. TAR@FAR=0.001: khi security critical, cần biết True Accept Rate tại FAR rất thấp |
| RF-09 | PointNet xử lý unordered point cloud thế nào? | Dùng shared MLP + max pooling → symmetric function → order-invariant |
| RF-10 | 3DMM là gì? | 3D Morphable Model: statistical model của face shape và texture. Có thể fit vào 2D image để recover 3D geometry |
| RF-11 | TensorRT optimize model như thế nào? | Layer fusion, precision calibration (FP16/INT8), kernel autotuning, eliminate dead layers |
| RF-12 | CoreML vs TFLite — chọn gì cho iOS? | CoreML: native iOS, tận dụng Neural Engine tốt nhất. TFLite: cross-platform nhưng không tối ưu bằng CoreML trên Apple silicon |
| RF-13 | DSPy khác LangChain như thế nào? | DSPy: optimize prompts automatically (compiled). LangChain: orchestration framework, prompts manual |
| RF-14 | Perceptual hashing dùng để làm gì? | Tạo hash ảnh dựa trên visual content → detect near-duplicate images dù resize/compress |
| RF-15 | FAISS là gì? Dùng khi nào? | Facebook AI Similarity Search: library tìm nearest neighbor trong vector space. Dùng cho face gallery search, image retrieval |
| RF-16 | Memory leak điển hình trong video pipeline Python? | Không release GPU tensor, giữ reference trong global list → dùng del + torch.cuda.empty_cache() |
| RF-17 | Evaluation set cho face recognition phải đảm bảo gì? | Diverse demographics, varied lighting/pose/expression, genuine pairs vs impostor pairs balanced, no subject overlap với training |
| RF-18 | Latency vs throughput — phân biệt và khi nào quan trọng hơn? | Latency: thời gian 1 request. Throughput: số requests/giây. Realtime auth → latency. Batch processing → throughput |
| RF-19 | GStreamer vs FFmpeg — khi nào dùng cái nào? | FFmpeg: transcode, convert, simple pipeline. GStreamer: complex pipeline với custom plugins, plugin-based architecture tốt hơn cho integration |
| RF-20 | Camera calibration intrinsic vs extrinsic? | Intrinsic: focal length, principal point, distortion (của camera). Extrinsic: rotation + translation (vị trí camera trong world) |

---

# PHẦN IX — CHIẾN LƯỢC PHỎNG VẤN

---

## Scoring Rubric

| Level | Score | Biểu hiện | Action |
|---|---|---|---|
| Junior | 1-2/5 | Biết định nghĩa, không giải thích được sâu, không nói được trade-off | Reject cho Senior role |
| Mid | 3/5 | Giải thích được kỹ thuật nhưng không có production experience | Senior stretch |
| Senior | 4/5 | Nói được trade-off, production issues, system design tốt | Strong hire |
| Staff+ | 5/5 | Chủ động mention edge cases, đặt câu hỏi ngược, propose novel solutions | Strong hire + level up |

---

## Red Flags cần chú ý

- 🚩 Không nói được trade-off khi implement giải pháp
- 🚩 Chưa bao giờ profile/benchmark code trước khi optimize
- 🚩 Không phân biệt được offline metrics vs production metrics
- 🚩 Nói EER=0.0001 nhưng không giải thích được đo trên dataset nào, điều kiện thế nào
- 🚩 Claim "16 persons/sec" nhưng không biết đó là throughput hay latency, batch hay single
- 🚩 Không explain được tại sao chọn 1 architecture thay vì alternative

---

## Green Flags (cho ứng viên này)

- ✅ Tự connect experience giữa các role (gait → re-ID → 3D face)
- ✅ Đề cập đến production metrics cụ thể (EER, latency, cost/image)
- ✅ Giải thích được lý do deploy decision (TensorRT vs CoreML tùy platform)
- ✅ Mention failure cases và cách handle

---

## Suggested Interview Flow (90 phút cho Senior)

1. `[0-5 min]` Ice break — hỏi về current project tại Applied Recognition
2. `[5-20 min]` CV deep dive — chọn 2 trong CV-01, CV-02, CV-05 (impressive nhất)
3. `[20-40 min]` System design — Câu 6 hoặc câu 7 (yêu cầu vẽ diagram)
4. `[40-60 min]` Coding — Algo 2 (NMS) + Algo 3 (Hungarian) — liên quan trực tiếp tracking
5. `[60-75 min]` Theory drill — 5-10 rapid-fire questions từ Phần VIII
6. `[75-90 min]` Q&A — Cho ứng viên hỏi, assess cultural fit và growth mindset

---

## Follow-up questions universal

Bất kỳ câu nào ứng viên trả lời, có thể probe thêm bằng:

- *"Bạn đã implement cái này trong production chưa? Scale như thế nào?"*
- *"Trade-off quan trọng nhất bạn thấy là gì?"*
- *"Nếu requirement thay đổi từ offline sang realtime, bạn thay đổi gì?"*
- *"Bạn đo success của solution này như thế nào?"*
- *"Điều gì có thể fail và bạn handle thế nào?"*
- *"Có approach nào khác không? Tại sao bạn chọn cái này?"*

---

*— End of Document — Version 2.0 | Senior AI Video Interview Guide | Tran Chi Cuong*
