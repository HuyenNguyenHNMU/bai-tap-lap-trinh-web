<?php
declare(strict_types=1);

// 1. Khai báo mảng sinh viên có mã sinh viên (MSV)
$students = [
    ['msv' => '224001722', 'name' => 'Nguyễn Thị Thu Huyền', 'midterm' => 8.5, 'final' => 9.0],
    ['msv' => '224001723', 'name' => 'Trần Văn Bình',        'midterm' => 5.5, 'final' => 6.0],
    ['msv' => '224001724', 'name' => 'Lê Hoàng Chi',         'midterm' => 4.0, 'final' => 4.5],
];

// 2. Hàm tính điểm trung bình
function calculateAverage(float $midterm, float $final): float {
    return ($midterm + $final) / 2;
}

// 3. Biến đếm thống kê
$totalStudents = count($students);
$passCount = 0;
$failCount = 0;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thực hành 2.8: Bảng điểm sinh viên</title>
    <style>
        /* Căn giữa toàn màn hình với nền hồng pastel nhẹ nhàng */
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            margin: 0; 
            min-height: 100vh;
            background: linear-gradient(135deg, #fdf2f8 0%, #fae8ff 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            box-sizing: border-box;
        }

        .wrapper {
            width: 100%;
            max-width: 950px; /* Tăng độ rộng tổng thể để bảng thoải mái không bị chật */
        }

        /* Tiêu đề tông màu hồng pastel/hồng trà sang trọng */
        .page-title {
            background: linear-gradient(135deg, #db2777 0%, #be185d 100%);
            color: #ffffff;
            text-align: center;
            padding: 20px;
            border-radius: 14px;
            font-size: 24px;
            font-weight: bold;
            letter-spacing: 0.5px;
            margin-bottom: 25px;
            box-shadow: 0 8px 20px rgba(219, 39, 119, 0.2);
        }

        /* Bảng hiển thị dữ liệu */
        table { 
            border-collapse: collapse; 
            width: 100%; 
            background: #fff; 
            box-shadow: 0 10px 25px rgba(219, 39, 119, 0.1); 
            border-radius: 14px; 
            overflow: hidden; 
            margin-bottom: 20px;
        }

        th, td { 
            border: 1px solid #fbcfe8; 
            padding: 14px 16px; 
            text-align: center; 
            font-size: 15px;
        }

        th { 
            background: linear-gradient(135deg, #f472b6 0%, #ec4899 100%); 
            color: white; 
            font-weight: 600; 
            text-transform: uppercase;
            font-size: 13px;
            letter-spacing: 0.5px;
        }

        /* Cố định kích thước các cột phụ */
        th.col-stt, td.col-stt { width: 55px; }
        th.col-msv, td.col-msv { width: 120px; }
        th.col-diem, td.col-diem { width: 95px; }
        th.col-ketqua, td.col-ketqua { width: 110px; }
        
        /* Cột Họ và tên: Ngăn không cho xuống dòng bằng white-space: nowrap */
        td.name-col { 
            text-align: left; 
            font-weight: 600; 
            color: #831843; 
            font-size: 16px; 
            white-space: nowrap; 
        }
        
        tr:hover {
            background-color: #fdf2f8;
        }

        .dat { color: #059669; font-weight: bold; }
        .chua-dat { color: #e11d48; font-weight: bold; }

        /* Khung thống kê đồng bộ tông hồng pastel */
        .summary-box { 
            background: #ffffff; 
            padding: 20px 25px; 
            border-radius: 14px; 
            box-shadow: 0 10px 25px rgba(219, 39, 119, 0.1); 
            border-left: 6px solid #db2777; 
        }

        .summary-box h3 {
            margin-top: 0;
            color: #9d174d;
            font-size: 18px;
            border-bottom: 2px solid #fdf2f8;
            padding-bottom: 10px;
        }

        .summary-box p { 
            margin: 10px 0; 
            font-size: 15px; 
            color: #500724; 
            display: flex;
            justify-content: space-between;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="page-title">🌸 Bảng Điểm Chi Tiết Sinh Viên</div>

        <table>
            <thead>
                <tr>
                    <th class="col-stt">STT</th>
                    <th class="col-msv">Mã SV</th>
                    <th>Họ và tên</th>
                    <th class="col-diem">Giữa kỳ</th>
                    <th class="col-diem">Cuối kỳ</th>
                    <th class="col-diem">Điểm TB</th>
                    <th class="col-ketqua">Kết quả</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($students as $index => $student): 
                    $avg = calculateAverage((float)$student['midterm'], (float)$student['final']);
                    $isPass = ($avg >= 5.0);
                    
                    if ($isPass) {
                        $passCount++;
                    } else {
                        $failCount++;
                    }
                ?>
                <tr>
                    <td class="col-stt"><?= $index + 1 ?></td>
                    <td class="col-msv"><strong><?= htmlspecialchars($student['msv'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                    <td class="name-col"><?= htmlspecialchars($student['name'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="col-diem"><?= number_format((float)$student['midterm'], 1) ?></td>
                    <td class="col-diem"><?= number_format((float)$student['final'], 1) ?></td>
                    <td class="col-diem"><strong><?= number_format($avg, 2) ?></strong></td>
                    <td class="col-ketqua <?= $isPass ? 'dat' : 'chua-dat' ?>">
                        <?= $isPass ? 'Đạt' : 'Chưa đạt' ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="summary-box">
            <h3>📋 Bảng Thống Kê Kết Quả Học Tập</h3>
            <p><span>📌 Tổng số sinh viên:</span> <strong><?= $totalStudents ?></strong></p>
            <p><span>✅ Tổng số sinh viên đạt (Điểm TB >= 5.0):</span> <strong style="color: #059669;"><?= $passCount ?></strong></p>
            <p><span>❌ Tổng số sinh viên chưa đạt (Điểm TB < 5.0):</span> <strong style="color: #e11d48;"><?= $failCount ?></strong></p>
        </div>
    </div>
</body>
</html>