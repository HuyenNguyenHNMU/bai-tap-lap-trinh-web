<?php
declare(strict_types=1); // Mục 2.1: Hạn chế lỗi chuyển kiểu không mong muốn[cite: 21]

// 1. Thực hành nhanh (Trang 14): Kiểm tra điểm hợp lệ từ 0 đến 10[cite: 20]
$diem = 8.5;
$hopLe = is_numeric($diem) && $diem >= 0 && $diem <= 10; //[cite: 20]

// 2. Câu hỏi Mục 2.1 (Trang 15): Dự đoán kiểu và kết quả[cite: 21]
$x = "5"; // string[cite: 21]
$y = 5;   // int[cite: 21]
$tong = $x + $y; // Kết quả: 10 (PHP tự chuyển chuỗi số "5" thành int 5)[cite: 21]

// 3. Bài tập Mục 2.2 (Trang 17): So sánh lỏng (==) và so sánh chặt (===)[cite: 20, 23]
// 0 == "0"  -> true  (vì chuyển kiểu)[cite: 23]
// 0 === "0" -> false (khác kiểu int và string)[cite: 23]
// false == 0 -> true[cite: 23]

// 4. Bài tập Mục 2.3 (Trang 18): Điều kiện xếp loại từ cao xuống thấp[cite: 24]
function xepLoai(float $score): string {
    if ($score >= 8.0) {
        return "Giỏi"; //[cite: 24]
    } elseif ($score >= 6.5) {
        return "Khá"; //[cite: 24]
    } elseif ($score >= 5.0) {
        return "Trung bình"; //[cite: 24]
    } else {
        return "Chưa đạt"; //[cite: 24]
    }
}

// 5. Bài tập Mục 2.4 (Trang 19): Mảng kết hợp 3 sinh viên có name và score[cite: 25]
$students = [
    ['name' => 'An',   'score' => 8.0], //[cite: 25]
    ['name' => 'Bình', 'score' => 6.5], //[cite: 25]
    ['name' => 'Chi',  'score' => 4.5], //[cite: 25]
];

// 6. Bài tập Mục 2.5 (Trang 20): Hàm tính điểm tổng kết[cite: 26]
function finalScore(float $cc, float $mid, float $final): float { //[cite: 26]
    return $cc * 0.10 + $mid * 0.30 + $final * 0.60; //[cite: 26]
}

// Hiển thị kết quả ra màn hình
echo "<h3>1. Kiểm tra điểm $diem: " . ($hopLe ? 'Hợp lệ' : 'Không hợp lệ') . "</h3>";
echo "<h3>2. Xếp loại điểm $diem: " . xepLoai($diem) . "</h3>";
echo "<h3>3. Danh sách 3 sinh viên (Mục 2.4):</h3>";
foreach ($students as $student) { //[cite: 25]
    echo 'Tên: ' . $student['name'] . ' - Điểm: ' . $student['score'] . '<br>'; //[cite: 25]
}
echo "<h3>4. Điểm tổng kết (CC: 9, GK: 8, CK: 8.5): " . finalScore(9, 8, 8.5) . "</h3>";
?>