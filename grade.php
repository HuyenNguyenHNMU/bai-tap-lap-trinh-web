<?php
declare(strict_types=1);

// 1. Hàm kiểm tra điểm hợp lệ
function isValidScore(string $rawScore): bool {
    if ($rawScore === '' || !is_numeric($rawScore)) {
        return false;
    }
    $val = (float)$rawScore;
    return $val >= 0.0 && $val <= 10.0;
}

// 2. Hàm tính điểm tổng kết: 10% chuyên cần, 30% giữa kỳ, 60% cuối kỳ
function finalScore(float $cc, float $mid, float $final): float {
    return ($cc * 0.10) + ($mid * 0.30) + ($final * 0.60);
}

// 3. Hàm xếp loại học lực
function classifyGrade(float $total): string {
    if ($total >= 8.0) return 'Giỏi';
    if ($total >= 6.5) return 'Khá';
    if ($total >= 5.0) return 'Trung bình';
    return 'Chưa đạt';
}

// Hàm hỗ trợ mã hóa đầu ra chống XSS
function e(string $val): string {
    return htmlspecialchars($val, ENT_QUOTES, 'UTF-8');
}

$errors = [];
$data = [
    'name'  => '',
    'cc'    => '',
    'mid'   => '',
    'final' => ''
];
$result = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['name']  = trim($_POST['name'] ?? '');
    $data['cc']    = trim($_POST['cc'] ?? '');
    $data['mid']   = trim($_POST['mid'] ?? '');
    $data['final'] = trim($_POST['final'] ?? '');

    if ($data['name'] === '') {
        $errors['name'] = 'Vui lòng nhập họ tên sinh viên.';
    }
    if (!isValidScore($data['cc'])) {
        $errors['cc'] = 'Điểm chuyên cần phải là số trong khoảng từ 0 đến 10.';
    }
    if (!isValidScore($data['mid'])) {
        $errors['mid'] = 'Điểm giữa kỳ phải là số trong khoảng từ 0 đến 10.';
    }
    if (!isValidScore($data['final'])) {
        $errors['final'] = 'Điểm cuối kỳ phải là số trong khoảng từ 0 đến 10.';
    }

    if (empty($errors)) {
        $ccFloat    = (float)$data['cc'];
        $midFloat   = (float)$data['mid'];
        $finalFloat = (float)$data['final'];

        $totalScore = finalScore($ccFloat, $midFloat, $finalFloat);
        $result = [
            'total' => $totalScore,
            'rank'  => classifyGrade($totalScore)
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Máy tính điểm học phần - BTVN Buổi 2</title>
    <style>
        /* Căn giữa màn hình và tạo nền gradient hiện đại */
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            margin: 0;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        /* Khung thẻ chính to bản, hiệu ứng kính mờ và đổ bóng sắc nét */
        .card { 
            background: rgba(255, 255, 255, 0.96); 
            width: 100%;
            max-width: 580px; 
            padding: 40px; 
            border-radius: 16px; 
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            box-sizing: border-box;
            backdrop-filter: blur(10px);
        }

        /* Tiêu đề có màu nền gradient nổi bật kết hợp icon */
        .card h2 {
            background: linear-gradient(135deg, #2b5876 0%, #4e4376 100%);
            color: #ffffff;
            margin: -40px -40px 30px -40px;
            padding: 24px;
            text-align: center;
            border-top-left-radius: 16px;
            border-top-right-radius: 16px;
            font-size: 26px;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .form-group { 
            margin-bottom: 22px; 
        }

        label { 
            display: block; 
            font-weight: 600; 
            margin-bottom: 8px; 
            color: #2d3748;
            font-size: 15px;
        }

        /* Ô nhập liệu linh hoạt: thêm hiệu ứng đổi màu nền nhẹ khi rê chuột vào */
        input[type="text"] { 
            width: 100%; 
            padding: 13px 16px; 
            box-sizing: border-box; 
            border: 2px solid #e2e8f0; 
            border-radius: 8px; 
            font-size: 16px;
            background-color: #f8fafc;
            transition: all 0.3s ease;
        }

        input[type="text"]:hover {
            border-color: #cbd5e0;
            background-color: #fff;
        }

        input[type="text"]:focus {
            border-color: #4e4376;
            background-color: #fff;
            outline: none;
            box-shadow: 0 0 0 4px rgba(78, 67, 118, 0.12);
        }

        .error { 
            color: #e53e3e; 
            font-size: 0.9em; 
            margin-top: 6px; 
            font-weight: 500;
            display: flex;
            align-items: center;
        }
        
        .error::before {
            content: "⚠️ ";
            margin-right: 4px;
        }

        /* Nút ấn cách điệu linh hoạt, có icon mũi tên trượt khi hover */
        button { 
            background: linear-gradient(135deg, #4e4376 0%, #2b5876 100%); 
            color: #fff; 
            border: none; 
            padding: 15px 24px; 
            border-radius: 8px; 
            cursor: pointer; 
            font-weight: bold; 
            font-size: 17px;
            width: 100%;
            transition: all 0.3s ease;
            margin-top: 10px;
            box-shadow: 0 4px 12px rgba(43, 88, 118, 0.3);
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
        }

        button:hover { 
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(43, 88, 118, 0.4);
            filter: brightness(1.1);
        }

        button:active {
            transform: translateY(0);
        }

        /* Khung kết quả hiển thị sinh động */
        .result { 
            margin-top: 25px; 
            padding: 20px; 
            background: #f0fff4; 
            border-left: 6px solid #38a169; 
            border-radius: 8px;
            font-size: 16px;
            box-shadow: 0 2px 8px rgba(56, 161, 105, 0.1);
        }

        .result p {
            margin: 8px 0;
            color: #22543d;
        }
    </style>
</head>
<body>
<div class="card">
    <h2>📊 Máy tính điểm học phần</h2>
    <form method="post" action="">
        <div class="form-group">
            <label for="name">👤 Họ và tên sinh viên:</label>
            <input type="text" id="name" name="name" value="<?= e($data['name']) ?>" placeholder="Nhập họ tên đầy đủ...">
            <?php if (isset($errors['name'])): ?><div class="error"><?= e($errors['name']) ?></div><?php endif; ?>
        </div>

        <div class="form-group">
            <label for="cc">🕒 Điểm chuyên cần (10%):</label>
            <input type="text" id="cc" name="cc" value="<?= e($data['cc']) ?>" placeholder="Nhập điểm từ 0 - 10">
            <?php if (isset($errors['cc'])): ?><div class="error"><?= e($errors['cc']) ?></div><?php endif; ?>
        </div>

        <div class="form-group">
            <label for="mid">📝 Điểm giữa kỳ (30%):</label>
            <input type="text" id="mid" name="mid" value="<?= e($data['mid']) ?>" placeholder="Nhập điểm từ 0 - 10">
            <?php if (isset($errors['mid'])): ?><div class="error"><?= e($errors['mid']) ?></div><?php endif; ?>
        </div>

        <div class="form-group">
            <label for="final">🏆 Điểm cuối kỳ (60%):</label>
            <input type="text" id="final" name="final" value="<?= e($data['final']) ?>" placeholder="Nhập điểm từ 0 - 10">
            <?php if (isset($errors['final'])): ?><div class="error"><?= e($errors['final']) ?></div><?php endif; ?>
        </div>

        <button type="submit">
            <span>🧮 Tính điểm tổng kết</span>
        </button>
    </form>

    <?php if ($result !== null): ?>
        <div class="result">
            <p><strong>👤 Họ tên:</strong> <?= e($data['name']) ?></p>
            <p><strong>📈 Điểm tổng kết:</strong> <?= number_format($result['total'], 2) ?></p>
            <p><strong>🎖️ Xếp loại:</strong> <strong><?= e($result['rank']) ?></strong></p>
        </div>
    <?php endif; ?>
</div>
</body>
</html>