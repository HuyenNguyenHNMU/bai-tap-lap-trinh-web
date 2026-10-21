<?php
declare(strict_types=1);
session_start();

$errors = [];
$data = ['name' => '', 'email' => '', 'birthday' => ''];

// Lấy thông báo thành công sau khi redirect (PRG Pattern)
$successMsg = $_SESSION['success_msg'] ?? '';
unset($_SESSION['success_msg']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Làm sạch dữ liệu
    $data['name']     = trim($_POST['name'] ?? '');
    $data['email']    = trim($_POST['email'] ?? '');
    $data['birthday'] = trim($_POST['birthday'] ?? '');

    // 2. Kiểm tra bắt buộc và email hợp lệ (Chuẩn Slide 3.7)
    if ($data['name'] === '') {
        $errors['name'] = 'Vui lòng nhập họ và tên sinh viên.';
    }
    
    if ($data['email'] === '') {
        $errors['email'] = 'Vui lòng nhập địa chỉ email.';
    } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Định dạng email không hợp lệ (ví dụ: ten@domain.com).';
    }
    
    if ($data['birthday'] === '') {
        $errors['birthday'] = 'Vui lòng chọn ngày tháng năm sinh.';
    }

    // 3. Nếu hợp lệ -> Chuyển hướng PRG
    if (empty($errors)) {
        $_SESSION['success_msg'] = 'Đã đăng ký hồ sơ thành công cho: ' . $data['name'];
        header('Location: dangky.php');
        exit;
    }
}

function e($v) { 
    return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); 
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>3.7 Demo: Form đăng ký sinh viên</title>
    <style>
        body { 
            font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, sans-serif; 
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); 
            margin: 0; 
            min-height: 100vh; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            padding: 24px; 
            box-sizing: border-box; 
        }

        .card { 
            background: #ffffff; 
            width: 100%; 
            max-width: 500px; 
            border-radius: 16px; 
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); 
            overflow: hidden; 
        }

        .card-header {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            color: #ffffff;
            padding: 28px 36px;
            text-align: center;
        }

        .card-header h2 { 
            margin: 0; 
            font-size: 22px; 
            font-weight: 700; 
            letter-spacing: 0.3px;
        }

        .card-header p {
            margin: 6px 0 0 0;
            font-size: 13.5px;
            color: #dbeafe;
        }

        .card-body {
            padding: 32px 36px;
        }

        .form-group { margin-bottom: 20px; }

        label { 
            display: block; 
            font-weight: 600; 
            margin-bottom: 8px; 
            color: #1e293b; 
            font-size: 14px; 
        }

        input { 
            width: 100%; 
            padding: 12px 14px; 
            box-sizing: border-box; 
            border: 1.5px solid #cbd5e1; 
            border-radius: 8px; 
            font-size: 15px; 
            color: #0f172a;
            background: #f8fafc; 
            transition: all 0.25s ease; 
        }

        input:hover {
            border-color: #94a3b8;
        }

        input:focus { 
            border-color: #2563eb; 
            background: #ffffff; 
            outline: none; 
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12); 
        }

        .input-invalid {
            border-color: #dc2626 !important;
            background: #fef2f2 !important;
        }

        .error { 
            color: #dc2626; 
            font-size: 13px; 
            margin-top: 6px; 
            font-weight: 500; 
        }

        .success { 
            background: #ecfdf5; 
            border-left: 4px solid #10b981; 
            color: #065f46; 
            padding: 14px 16px; 
            border-radius: 6px; 
            margin-bottom: 22px; 
            font-weight: 600; 
            font-size: 14px;
        }

        button { 
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); 
            color: #ffffff; 
            border: none; 
            padding: 14px; 
            border-radius: 8px; 
            font-weight: 600; 
            font-size: 15.5px; 
            width: 100%; 
            cursor: pointer; 
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25); 
            transition: all 0.25s ease; 
            margin-top: 8px;
        }

        button:hover { 
            background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
            transform: translateY(-1px); 
            box-shadow: 0 6px 16px rgba(37, 99, 235, 0.35);
        }
    </style>
</head>
<body>
<div class="card">
    <div class="card-header">
        <h2>HỆ THỐNG ĐĂNG KÝ SINH VIÊN</h2>
    </div>

    <div class="card-body">
        <?php if ($successMsg !== ''): ?>
            <div class="success">✓ <?= e($successMsg) ?></div>
        <?php endif; ?>

        <form method="post" action="" novalidate>
            <div class="form-group">
                <label for="name">Họ và tên sinh viên *</label>
                <input type="text" id="name" name="name" 
                       class="<?= isset($errors['name']) ? 'input-invalid' : '' ?>"
                       value="<?= e($data['name']) ?>" placeholder="Nhập đầy đủ họ và tên...">
                <?php if (isset($errors['name'])): ?><div class="error"><?= e($errors['name']) ?></div><?php endif; ?>
            </div>

            <div class="form-group">
                <label for="email">Địa chỉ Email *</label>
                <input type="text" id="email" name="email" 
                       class="<?= isset($errors['email']) ? 'input-invalid' : '' ?>"
                       value="<?= e($data['email']) ?>" placeholder="vidu@edu.vn">
                <?php if (isset($errors['email'])): ?><div class="error"><?= e($errors['email']) ?></div><?php endif; ?>
            </div>

            <div class="form-group">
                <label for="birthday">Ngày sinh *</label>
                <input type="date" id="birthday" name="birthday" 
                       class="<?= isset($errors['birthday']) ? 'input-invalid' : '' ?>"
                       value="<?= e($data['birthday']) ?>">
                <?php if (isset($errors['birthday'])): ?><div class="error"><?= e($errors['birthday']) ?></div><?php endif; ?>
            </div>

            <button type="submit">Xác Nhận Đăng Ký</button>
        </form>
    </div>
</div>
</body>
</html>