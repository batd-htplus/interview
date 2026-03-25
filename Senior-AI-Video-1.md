# Senior AI Video Interview Template (Clear + Deep)

## Overview

Tài liệu này tập trung vào các câu hỏi phỏng vấn Senior AI Video với cách giải thích dễ hiểu nhưng vẫn đủ chiều sâu để dùng trong phỏng vấn Big Tech.

Nguyên tắc:

* Giải thích đơn giản (ai cũng hiểu)
* Sau đó đi sâu (để thể hiện senior)
* Luôn có trade-off và thực tế production

---

# 1. Video vs Image

## Question

Tại sao xử lý video khó hơn xử lý ảnh?

## Answer

Hiểu đơn giản:
* Ảnh = 1 khoảnh khắc
* Video = nhiều ảnh + thời gian

Chiều sâu:

1. Temporal dependency
* Các frame liên quan với nhau
* Không thể xử lý từng frame độc lập
2. Redundancy
* Nhiều frame giống nhau → lãng phí compute
3. Motion
* Chuyển động phức tạp, không tuyến tính
4. Noise thực tế
* Blur, ánh sáng, rung camera

Kết luận:
Video = Spatial + Temporal → phức tạp hơn nhiều

---

# 2. Tối ưu xử lý video

## Question

Bạn có chạy model trên mọi frame không?

## Answer

Không.

Hiểu đơn giản:
* Video 30 FPS → nếu chạy hết sẽ rất tốn tài nguyên

Cách làm thực tế:
1. Frame sampling
* Chỉ lấy 1 số frame (ví dụ: mỗi 5 frame)
2. Keyframe detection
* Chỉ xử lý frame quan trọng
3. Tracking
* Detect 1 lần → theo dõi ở các frame sau

Trade-off:
* Ít frame → nhanh hơn nhưng có thể mất thông tin

---

# 3. Multi-object tracking

## Question

Tracking hoạt động như thế nào?

## Answer

Hiểu đơn giản:
* Detect object
* Gán ID
* Theo dõi ID đó qua thời gian

Chi tiết:

1. Detection
* Tìm object trong frame
2. Matching
* So sánh object giữa 2 frame:
  * Vị trí (IOU)
  * Hình dạng (embedding)
3. Prediction
* Dùng Kalman Filter để đoán vị trí tiếp theo
4. Assignment
* Dùng Hungarian Algorithm để match tối ưu
Vấn đề thực tế:
* Bị che (occlusion)
* Nhầm ID (ID switch)

---

# 4. Temporal Modeling

## Question

Làm sao model hiểu được thời gian trong video?

## Answer

Có 3 cách chính:

1. 3D CNN
* Nhìn nhiều frame cùng lúc
* Hiểu local motion
2. RNN / LSTM
* Xử lý từng frame theo thứ tự
3. Transformer
* Attention giữa các frame
Hiểu đơn giản:
* CNN: nhìn gần
* Transformer: nhìn xa

Trade-off:
* Transformer mạnh nhưng tốn tài nguyên

---

# 5. Video Generation

## Question

Vì sao video AI hay bị giật (flicker)?

## Answer

Hiểu đơn giản:
* Model tạo từng frame riêng → không liên kết
Kết quả:
* Frame này đẹp, frame sau bị lệch

Giải pháp:

1. Temporal loss
* Phạt nếu frame thay đổi quá nhiều
2. Optical flow
* Giữ chuyển động consistent
3. Cross-frame attention
* Cho model nhìn nhiều frame cùng lúc

---

# 6. System Design cơ bản nhưng chuẩn Senior

## Question

Thiết kế hệ thống AI xử lý video realtime

## Answer

Pipeline:

1. Input
* Camera / video stream
2. Sampling
* Giảm số frame
3. Preprocess
* Resize, normalize
4. Inference
* Chạy model trên GPU
5. Post-process
* Tracking, logic
6. Output
* Database / alert

Điểm senior:
* Không chỉ pipeline, mà phải nói thêm:
Trade-off:
* Latency vs Accuracy
* Cost vs Performance

---

# 7. Tối ưu hệ thống

## Question

Nếu hệ thống chạy chậm, bạn làm gì?

## Answer

Cách tiếp cận đúng (rất quan trọng):

1. Profile system
* Tìm bottleneck trước

2. Optimize theo thứ tự:
Model:
* Quantization
* Pruning
System:
* Batch inference
* Async pipeline
Data:
* Giảm resolution
* Frame skipping

---

# 8. Production issues

## Question

Model tốt offline nhưng fail production?

## Answer

Hiểu đơn giản:
* Dữ liệu thật khác dữ liệu train

Chi tiết:
1. Data drift
* Ánh sáng, góc camera khác
2. Pipeline mismatch
* Preprocessing khác nhau
3. Noise thực tế

Cách xử lý:
* Log data
* Monitor realtime
* Retrain

---

# 9. Metrics

## Question

Đánh giá model video như thế nào?

## Answer

Tùy bài toán:
* Classification: Accuracy, F1
* Detection: mAP
* Tracking: MOTA, IDF1
* Generation: FVD

Điểm senior:
* Luôn giải thích tại sao chọn metric

---

# 10. Câu hỏi tư duy (Senior thật sự)

## Question

Trade-off quan trọng nhất trong video AI là gì?

## Answer

3 cái chính:
1. Latency vs Accuracy
* Nhanh hơn → kém chính xác hơn
2. Cost vs Scale
* Scale lớn → tốn tiền
3. Stability vs Quality
* Video đẹp nhưng dễ bị flicker

---

# 11. Câu hỏi nâng cao nhưng dễ hiểu

## Question

Tại sao không dùng model rất lớn cho mọi bài toán?

## Answer

Hiểu đơn giản:
* Model lớn = chậm + đắt

Chiều sâu:
* Không phù hợp realtime
* Khó scale
* Tốn GPU

Kết luận:
* Chọn model = trade-off giữa cost và performance

---

## Question
Bạn có một hệ thống detect người trong video, nhưng khi người đứng yên thì model vẫn chạy liên tục gây tốn tài nguyên. Bạn giải quyết thế nào?

## Answer

- Dùng motion detection (frame differencing / optical flow)
- Chỉ chạy model khi có chuyển động
- Kết hợp tracking để giữ state

Trade-off:
- Có thể miss object nếu motion nhỏ

---

## Question
Nếu video bị drop frame hoặc network không ổn định thì hệ thống xử lý sao?

## Answer

- Buffer frame
- Interpolate frame (nếu cần)
- Tracking để giữ object khi mất frame
- Fallback logic (giữ kết quả cũ)

---

## Question
Bạn thiết kế hệ thống để xử lý video 4K nhưng vẫn realtime?

## Answer

- Downscale input trước khi inference
- Crop vùng quan trọng (ROI)
- Multi-scale inference nếu cần

Trade-off:
- Mất chi tiết nhưng tăng tốc độ

---

## Question
Làm sao giảm false positive trong video detection?

## Answer

- Temporal smoothing (object phải xuất hiện nhiều frame)
- Confidence threshold theo thời gian
- Ensemble nhẹ

---

## Question
Nếu có nhiều model trong pipeline, bạn tối ưu như thế nào?

## Answer

- Model cascade (model nhẹ trước)
- Early exit
- Share feature giữa model

---

## Question
Bạn xử lý memory leak trong hệ thống video như thế nào?

## Answer

- Monitor memory usage
- Clear buffer định kỳ
- Tránh giữ reference không cần thiết

---

## Question
Tại sao video AI system dễ bị unstable hơn image system?

## Answer

- Input thay đổi liên tục theo thời gian
- Error tích lũy qua frame
- Tracking sai → lan sang các frame sau

---

## Question
Bạn xử lý việc model bias theo môi trường (ví dụ chỉ train ban ngày)?

## Answer

- Thu thập data đa dạng (ngày/đêm)
- Data augmentation (low light)
- Fine-tune theo domain

---

## Question
Làm sao detect anomaly trong video?

## Answer

- Dùng reconstruction error (autoencoder)
- Dùng prediction error (future frame prediction)
- Threshold bất thường

---

## Question
Nếu GPU quá tải, bạn degrade system như thế nào?

## Answer

- Giảm FPS
- Giảm resolution
- Chuyển sang model nhẹ

---

## Question
Bạn xử lý synchronization giữa audio và video?

## Answer

- Timestamp alignment
- Buffer đồng bộ
- Drift correction

---

## Question
Tại sao không dùng full video mà chỉ dùng clip ngắn?

## Answer

- Giảm compute
- Giảm noise
- Tập trung vào segment quan trọng

---

## Question
Bạn thiết kế fallback khi model fail?

## Answer

- Rule-based fallback
- Model đơn giản hơn
- Cache kết quả trước

---

## Question
Làm sao detect camera bị lỗi?

## Answer

- Check frame black/constant
- Check FPS drop
- Alert nếu bất thường

---

## Question
Bạn đánh giá độ ổn định của hệ thống video như thế nào?

## Answer

- Metric theo thời gian (temporal stability)
- Tracking consistency
- Frame-to-frame variance

---

# Interview Strategy

* Trả lời đơn giản trước
* Sau đó đào sâu
* Luôn nói về trade-off
* Luôn liên hệ production

---

# End of Document
