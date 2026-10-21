<?php
declare(strict_types=1);
session_start();

$errors = [];
$data = ['msv' => '', 'name' => '', 'email' => '', 'khoa' => 'Công nghệ thông tin'];
// Đã viết rõ tên các khoa đầy đủ, không viết tắt
$allowedKhoa = ['Công nghệ thông tin', 'Kỹ thuật phần mềm', 'Hệ thống thông tin', 'An toàn thông tin'];

// Lấy thông tin sinh viên vừa nhập thành công để hiển thị ở bảng bên phải (PRG Pattern)
$latestStudent = $_SESSION['success_sv'] ?? null;
unset($_SESSION['success_sv']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Làm sạch dữ liệu đầu vào (Sanitization)
    $data['msv']   = trim($_POST['msv'] ?? '');
    $data['name']  = trim($_POST['name'] ?? '');
    $data['email'] = strtolower(trim($_POST['email'] ?? ''));
    $data['khoa']  = trim($_POST['khoa'] ?? '');

    // 2. Validation phía Server (Kiểm tra bắt buộc, định dạng)
    if ($data['msv'] === '') {
        $errors['msv'] = 'Vui lòng nhập mã sinh viên.';
    } elseif (mb_strlen($data['msv']) < 4) {
        $errors['msv'] = 'Mã sinh viên phải có ít nhất 4 ký tự.';
    }

    if ($data['name'] === '') {
        $errors['name'] = 'Vui lòng nhập họ và tên sinh viên.';
    } elseif (mb_strlen($data['name']) < 2) {
        $errors['name'] = 'Họ tên phải có ít nhất 2 ký tự.';
    }

    if ($data['email'] === '') {
        $errors['email'] = 'Vui lòng nhập địa chỉ email.';
    } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Email không đúng định dạng (ví dụ: sv@edu.vn).';
    }

    if (!in_array($data['khoa'], $allowedKhoa, true)) {
        $errors['khoa'] = 'Khoa đào tạo không hợp lệ.';
    }

    // 3. Nếu không có lỗi -> Lưu vào session kèm đầy đủ khóa 'time' và chuyển hướng
    if (empty($errors)) {
        $_SESSION['success_sv'] = [
            'msv'   => $data['msv'],
            'name'  => $data['name'],
            'email' => $data['email'],
            'khoa'  => $data['khoa'],
            'time'  => date('H:i:s - d/m/Y')
        ];
        header('Location: student_create.php');
        exit;
    }
}

// Hàm mã hóa chống XSS khi in ra màn hình
function e($v) { 
    return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); 
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản Lý Hồ Sơ Sinh Viên - Bài Tập Lớn</title>
    <style>
        body { 
            font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, sans-serif; 
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); 
            margin: 0; 
            min-height: 100vh; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            padding: 30px 20px; 
            box-sizing: border-box; 
        }

        .main-wrapper {
            width: 100%;
            max-width: 1150px;
            background: #f8fafc;
            border-radius: 18px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            overflow: hidden;
        }

        .top-banner {
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
            color: #ffffff;
            padding: 26px 36px;
            text-align: center;
        }

        .top-banner h2 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        /* Đã bỏ dòng chữ phụ dưới tiêu đề theo yêu cầu */

        .grid-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 28px;
            padding: 30px;
        }

        @media (max-width: 900px) {
            .grid-container {
                grid-template-columns: 1fr;
            }
        }

        .panel {
            background: #ffffff;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            padding: 28px;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.05);
            display: flex;
            flex-direction: column;
        }

        .panel-header {
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 14px;
            margin-bottom: 20px;
        }

        .panel-header h3 {
            margin: 0;
            color: #0f172a;
            font-size: 18px;
            font-weight: 700;
        }

        .form-group { margin-bottom: 18px; }

        label { 
            display: block; 
            font-weight: 600; 
            margin-bottom: 6px; 
            color: #1e293b; 
            font-size: 13.5px; 
        }

        input[type="text"], select { 
            width: 100%; 
            padding: 11px 14px; 
            box-sizing: border-box; 
            border: 1.5px solid #cbd5e1; 
            border-radius: 8px; 
            font-size: 14.5px; 
            color: #0f172a;
            background: #f8fafc; 
            transition: all 0.2s ease; 
        }

        input:focus, select:focus { 
            border-color: #2563eb; 
            background: #ffffff; 
            outline: none; 
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12); 
        }

        .input-invalid {
            border-color: #dc2626 !important;
            background: #fef2f2 !important;
        }

        .error { color: #dc2626; font-size: 12.5px; margin-top: 5px; font-weight: 500; }

        button { 
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); 
            color: #ffffff; 
            border: none; 
            padding: 13px; 
            border-radius: 8px; 
            font-weight: 600; 
            font-size: 15px; 
            width: 100%; 
            cursor: pointer; 
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25); 
            transition: all 0.2s ease; 
            margin-top: 8px;
        }

        button:hover { 
            background: linear-gradient(135deg, #1d4ed8 100%, #1e40af 100%);
            transform: translateY(-1px); 
        }

        .result-card {
            background: #f0fdf4;
            border: 1.5px solid #10b981;
            border-radius: 10px;
            padding: 22px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .result-item {
            background: #ffffff;
            padding: 12px 16px;
            border-radius: 8px;
            border: 1px solid #d1fae5;
        }

        .result-label {
            font-size: 12px;
            font-weight: 700;
            color: #047857;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 3px;
        }

        .result-value {
            font-size: 15px;
            color: #0f172a;
            font-weight: 500;
        }

        .empty-state {
            text-align: center;
            padding: 80px 20px;
            color: #64748b;
            background: #f8fafc;
            border-radius: 8px;
            border: 1px dashed #cbd5e1;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
    </style>
</head>
<body>

<div class="main-wrapper">
    <div class="top-banner">
        <h2>QUẢN LÝ HỒ SƠ SINH VIÊN - BÀI TẬP LỚN</h2>
    </div>

    <div class="grid-container">
        <!-- BẢNG 1 (BÊN TRÁI): FORM NHẬP LIỆU -->
        <div class="panel">
            <div class="panel-header">
                <h3>1. Thêm Mới Hồ Sơ Sinh Viên</h3>
            </div>

            <form method="post" action="" novalidate>
                <div class="form-group">
                    <label for="msv">Mã sinh viên *</label>
                    <input type="text" id="msv" name="msv" 
                           class="<?= isset($errors['msv']) ? 'input-invalid' : '' ?>"
                           value="<?= e($data['msv']) ?>" placeholder="Ví dụ: B22DCCON01">
                    <?php if (isset($errors['msv'])): ?><div class="error"><?= e($errors['msv']) ?></div><?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="name">Họ và tên sinh viên *</label>
                    <input type="text" id="name" name="name" 
                           class="<?= isset($errors['name']) ? 'input-invalid' : '' ?>"
                           value="<?= e($data['name']) ?>" placeholder="Nguyễn Văn A">
                    <?php if (isset($errors['name'])): ?><div class="error"><?= e($errors['name']) ?></div><?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="email">Địa chỉ Email *</label>
                    <input type="text" id="email" name="email" 
                           class="<?= isset($errors['email']) ? 'input-invalid' : '' ?>"
                           value="<?= e($data['email']) ?>" placeholder="sinhvien@edu.vn">
                    <?php if (isset($errors['email'])): ?><div class="error"><?= e($errors['email']) ?></div><?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="khoa">Khoa đào tạo</label>
                    <select id="khoa" name="khoa">
                        <?php foreach ($allowedKhoa as $k): ?>
                            <option value="<?= e($k) ?>" <?= $data['khoa'] === $k ? 'selected' : '' ?>><?= e($k) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit">Lưu Hồ Sơ & Hiển Thị</button>
            </form>
        </div>

        <!-- BẢNG 2 (BÊN PHẢI): KẾT QUẢ HIỂN THỊ TO RÕ -->
        <div class="panel">
            <div class="panel-header">
                <h3>2. Kết Quả Hồ Sơ Vừa Nhập</h3>
            </div>

            <?php if (!$latestStudent): ?>
                <div class="empty-state">
                    <strong style="font-size: 16px; color: #334155;">Chưa có hồ sơ sinh viên nào!</strong>
                    <p style="margin-top: 6px; font-size: 14px;">Nhập thông tin bên trái và bấm lưu để hiển thị kết quả chi tiết tại đây.</p>
                </div>
            <?php else: ?>
                <div class="result-card">
                    <div style="background: #d1fae5; color: #065f46; padding: 10px 14px; border-radius: 6px; font-weight: 600; font-size: 13.5px; text-align: center; margin-bottom: 4px;">
                        ✓ Lưu hồ sơ thành công lúc <?= e($latestStudent['time']) ?>
                    </div>

                    <div class="result-item">
                        <div class="result-label">🆔 Mã sinh viên:</div>
                        <div class="result-value"><span style="background: #e0f2fe; color: #0369a1; padding: 3px 8px; border-radius: 4px; font-weight: bold;"><?= e($latestStudent['msv']) ?></span></div>
                    </div>

                    <div class="result-item">
                        <div class="result-label">👤 Họ và tên:</div>
                        <div class="result-value" style="font-size: 17px; font-weight: 600; color: #047857;"><?= e($latestStudent['name']) ?></div>
                    </div>

                    <div class="result-item">
                        <div class="result-label">📧 Địa chỉ Email:</div>
                        <div class="result-value"><?= e($latestStudent['email']) ?></div>
                    </div>

                    <div class="result-item">
                        <div class="result-label">🏛️ Khoa đào tạo:</div>
                        <div class="result-value"><?= e($latestStudent['khoa']) ?></div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>