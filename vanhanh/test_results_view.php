<?php
require_once __DIR__ . '/../init.php';

// Giả sử bạn sẽ tạo Service này
use App\Services\TestResultService;

// --- BƯỚC 2: LẤY DỮ LIỆU ---
$test_id = isset($_GET['test_id']) ? (int)$_GET['test_id'] : 0;
if ($test_id <= 0) {
    die("Lỗi: ID bài kiểm tra không hợp lệ.");
}

$service = new TestResultService();
$data = $service->getPrintableViewData($test_id);
if (!$data) {
    die("Lỗi: Không tìm thấy dữ liệu cho bài kiểm tra này.");
}
$test_info = $data['test_info'];
$results = $data['results'];

// --- DỮ LIỆU DEMO ---
// $test_info = [
//     'id' => 1,
//     'date' => '2025-08-05',
//     'class_code' => 'TC9.0A',
//     'teachers' => 'Cô Nguyễn Thị Hồng - Cô Đào Hoa Mai',
//     'center_name' => 'VietElite Cầu Giấy',
//     'center_address' => 'Số 2, Ngõ 1, Trần Quốc Hoàn, Cầu Giấy, Hà Nội',
//     'center_phone' => '0981 234 567',
//     'center_email' => 'contact.cg@vietelite.edu.vn',
// ];
// $results = [
//     ['student_name' => 'Nguyễn Bảo An', 'score' => 9.75, 'comment' => "**Mức độ hoàn thành:** Tốt\n\n**Phần làm tốt:** Con hoàn thành tốt bài kiểm tra\n\n**Lỗi sai thường gặp:** Xét chưa đầy đủ điều kiện xác định dạng bất phương trình tích."],
//     ['student_name' => 'Hoàng An Bảo', 'score' => 9.75, 'comment' => "**Mức độ hoàn thành:** Tốt\n\n**Phần làm tốt:** Con hoàn thành tốt bài kiểm tra\n\n**Lỗi sai thường gặp:** Xét chưa đầy đủ điều kiện xác định dạng bất phương trình tích."],
//     ['student_name' => 'Chử Minh Chính', 'score' => 8.0, 'comment' => "**Mức độ hoàn thành:** Tốt\n\n**Phần làm tốt:**\n* Giải hệ phương trình\n* Biến đổi đại số\n\n**Lỗi sai thường gặp:**\n* Xác định thiếu điều kiện xác định\n* Trình bày bài chưa cẩn thận, một số chỗ trình bày tắt."],
//     ['student_name' => 'Phan Kim Ngân', 'score' => null, 'comment' => "**Mức độ hoàn thành:** Chưa đạt"],
// ];
// --- KẾT THÚC DỮ LIỆU DEMO ---

// Khởi tạo trình phân tích Markdown
$parsedown = new Parsedown();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thông báo Điểm Khảo sát - Lớp <?= htmlspecialchars($test_info['class_code']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif, 'Times New Roman', Times, serif; font-size: 13pt; color: #000; background-color: #f0f0f0; }
        .page { width: 21cm; min-height: 29.7cm; padding: 1cm 1cm; margin: 1cm auto; background: white; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
        .print-button { position: fixed; top: 20px; right: 20px; padding: 10px 20px; background-color: #16a34a; color: white; border: none; border-radius: 5px; cursor: pointer; font-family: 'Inter', sans-serif; }
        .comment-content ul { list-style-type: disc; padding-left: 20px; }
        .comment-content p { margin-bottom: 0.5rem; }
        @media print {
            body, .page { margin: 0; border: none; box-shadow: none; background-color: white; }
            .print-button { display: none; }
        }
    </style>
</head>
<body>

<button class="print-button" onclick="window.print()">In Báo cáo</button>

<div class="page">
    <table class="w-full border-b-2 border-black pb-2">
        <tr>
            <td style="width: 30%; vertical-align: top;">
                <img src="https://vietelite.edu.vn/wp-content/uploads/2022/06/logo-vietelite-xanh-768x364.png" alt="VietElite Logo" style="height: 80px;">
            </td>
            <td style="width: 70%; vertical-align: middle; text-align: right; font-size: 11pt; line-height: 1.5;">
                <span class="font-bold text-2xl text-green-700" style="text-transform: uppercase; "><?= nt($test_info['center_name']) ?></span><br>
                <!-- <strong><?= nt($test_info['center_name']) ?></strong><br> -->
                <strong>Địa chỉ:</strong> <?= nt($test_info['center_address']) ?><br>
                <strong>Điện thoại:</strong> <?= nt($test_info['center_phone']) ?><br>
            </td>
        </tr>
    </table>

    <h1 class="text-center text-2xl font-bold mt-8 mb-2">THÔNG BÁO ĐIỂM <br/><?= mb_strtoupper(nt($test_info['name']), 'UTF-8') ?></h1>
    <p class="text-center font-medium text-base mb-6">
        <span class="font-bold">Buổi học:</span> Ngày <?= nd($test_info['test_date']) ?>   &middot;    
        <span class="font-bold">Lớp:</span> <?= nt($test_info['class_code']) ?> <br/> 
        <span class="font-bold">Giáo viên phụ trách:</span> <?= nt($test_info['teachers']) ?>
    </p>

    <!-- <h2 class="text-center font-bold text-lg mb-4">ĐÁNH GIÁ BÀI KHẢO SÁT CHẤT LƯỢNG HỌC TẬP</h2> -->

    <table class="w-full border-collapse border border-black">
        <thead>
            <tr>
                <th class="border border-black p-2 w-12">STT</th>
                <th class="border border-black p-2">Họ và tên học sinh</th>
                <th class="border border-black p-2">Điểm<br>(Thang <?= nt($test_info['max_score']) ?>)</th>
                <th class="border border-black p-2">Nhận xét chi tiết</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($results as $index => $result): ?>
            <tr>
                <td class="border border-black p-2 text-center"><?= $index + 1 ?></td>
                <td class="border border-black p-2 font-bold">
                    <div class="text-md "><?= nt($result['full_name']) ?></div>
                    <div class="text-sm font-medium text-gray-500">(<?= nd($result['dob']) ?>)</div>
                </td>
                <td class="border border-black p-2 text-center font-bold text-lg">
                    <?= nt($result['score'], 'N/A') ?>
                </td>
                <td class="border border-black p-2 comment-content">
                    <?php
                    if (!empty($result['comments'])) {
                        // Chuyển đổi Markdown sang HTML để hiển thị
                        echo $parsedown->text($result['comments']);
                    }
                    ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="mt-8 text-sm">
        <p class="font-bold mb-2">Ghi chú:</p>
        <ul class="list-disc pl-5">
            <li>Điểm khảo sát phản ánh mức độ hiện tại của học sinh và giúp phụ huynh cùng trung tâm theo dõi tiến bộ của con.</li>
            <li>Phụ huynh khuyến khích con ôn tập theo lời khuyên của giáo viên để đạt kết quả tốt hơn.</li>
        </ul>
    </div>

    <div class="mt-12 flex justify-end">
        <div class="text-center">
            <p>Ngày thông báo: ....../....../2025</p>
            <p class="font-bold mt-2">Giáo viên phụ trách</p>
            <p class="italic">(Ký và ghi rõ họ tên)</p>
            <div class="mt-20"></div>
        </div>
    </div>
</div>

</body>
</html>
