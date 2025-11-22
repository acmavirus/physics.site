## Mục Tiêu
- Chuẩn hóa cấu trúc view "cong-thuc" để hiển thị mindmap ổn định, nhất quán.
- Chuẩn hóa luồng dữ liệu từ các file JSON theo slug công thức.
- Loại bỏ việc tải file trong thư mục `application` qua HTTP; chuyển sang đọc server-side an toàn.

## Quan Sát Hiện Trạng
- View hiện đọc một JSON cố định (`formula_mindmap_emc2.json`) và có logic layout/toggle nhánh tốt.
- Controller `Formula::index($slug)` truyền biến `slug` vào view nhưng view chưa dùng để quyết định dữ liệu.
- Có nhiều file JSON mindmap khác trong `application/data/` (dao_dong, newton2, quang_hoc...).
- JS đang có đoạn dùng AJAX tới `/application/data/...` (không chuẩn trong CodeIgniter, dễ lỗi quyền truy cập).

## Chuẩn Hóa Luồng Dữ Liệu
- Tại server (PHP), dùng `APPPATH.'data/formula_mindmap_'.$slug.'.json'` để đọc dữ liệu theo slug.
- Nếu file không tồn tại, fallback sang `APPPATH.'data/formula_mindmap_emc2.json'`.
- Inject thẳng đối tượng `mmConfig` vào JS bằng `json_encode` (giữ `JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES`).
- Loại bỏ toàn bộ AJAX tải JSON từ thư mục `application`.

## Chuẩn Hóa View
- Tách bạch các phần: controls (selector, search), canvas physics-bg, container mindmap, script KaTeX, script mindmap.
- Dropdown công thức: thay vì load qua AJAX, chuyển hướng tới route `cong-thuc/<slug>` để controller cấp đúng JSON.
- Giữ nguyên các hàm đã có (wrapCenterLatex, layoutMindMap, toggleBranchGroup) nhưng:
  - Dùng `mmConfig.colors`, `mmConfig.sectors`, `mmConfig.groups` đồng bộ.
  - Thêm hỗ trợ `description` cho tooltip.
  - Search lọc theo giá trị `latex/text/name/description` từ `mmConfig` thay vì dựa vị trí DOM.
- Chuẩn hóa CSS: giữ khối style trong view hiện tại; nếu cần sẽ trích sang asset sau (không bắt buộc).

## Cập Nhật Controller
- `Formula::index($slug)`: kiểm tra hợp lệ slug; tải JSON theo slug như trên; truyền `mmConfig` và danh sách công thức có sẵn vào view.
- Tạo danh sách công thức có sẵn bằng `glob(APPPATH.'data/formula_mindmap_*.json')` → suy ra `slug` và nhãn hiển thị.

## Chuẩn Hóa Schema JSON
- centerLatex: string
- colors: map key→color (ví dụ E/m/c)
- sectors: map key→{start, end, base}
- groups: map key→array các item: {latex|text, angle, radius, description?}
- Tất cả file `formula_mindmap_*.json` tuân thủ schema trên; mô tả (`description`) tùy chọn.

## Trải Nghiệm Người Dùng
- Dropdown chuyển slug → server render đúng mindmap ngay (không nhấp nháy).
- Tooltip hiển thị mô tả khi hover node/biểu tượng trung tâm.
- Tìm kiếm realtime ẩn/hiện node phù hợp.
- Animation mở/đóng nhánh mượt, tránh chồng chập bằng layout hiện có.

## Kiểm Thử & Xác Minh
- Mở `cong-thuc/emc2`, `cong-thuc/newton2`, `cong-thuc/dao_dong` để kiểm tra render, màu, layout, tooltip, search.
- Kiểm tra fallback khi slug không có file JSON (hiển thị emc2).
- Dò các trường hợp JSON lỗi cú pháp → vẫn fallback an toàn.

## Thay Đổi Dự Kiến
- Sửa `application/controllers/Formula.php` để đọc JSON theo slug và truyền `mmConfig`, `availableFormulas` vào view.
- Sửa `application/views/public/formula/index.php` để:
  - Nhận `mmConfig` từ PHP; bỏ AJAX.
  - Dropdown điều hướng tới `cong-thuc/<slug>` và set selected theo `$slug`.
  - Chuẩn hóa các hàm JS theo `mmConfig`.
- Không tạo file mới bắt buộc; chỉ hiệu chỉnh nếu JSON nào sai schema.

Vui lòng xác nhận để tôi tiến hành cập nhật theo kế hoạch.