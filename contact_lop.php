<?php
declare(strict_types=1);
session_start();

$allowedTopics = ['Hỗ trợ kỹ thuật', 'Tư vấn học vụ', 'Góp ý hệ thống', 'Khác'];
$errors = [];
$data = ['name' => '', 'email' => '', 'topic' => 'Hỗ trợ kỹ thuật', 'content' => ''];

// Lưu kết quả gần nhất vào Session để hiển thị ở bảng bên phải
$latestResult = $_SESSION['latest_contact'] ?? null;
unset($_SESSION['latest_contact']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Làm sạch dữ liệu đầu vào (Sanitization)
    $data['name']    = trim($_POST['name'] ?? '');
    $data['email']   = strtolower(trim($_POST['email'] ?? ''));
    $data['topic']   = trim($_POST['topic'] ?? '');
    $data['content'] = trim($_POST['content'] ?? '');

    // 2. Kiểm tra bắt buộc & Định dạng (Validation phía Server)
    if ($data['name'] === '') {
        $errors['name'] = 'Vui lòng nhập họ và tên.';
    } elseif (mb_strlen($data['name']) < 2) {
        $errors['name'] = 'Họ tên phải có ít nhất 2 ký tự.';
    }

    if ($data['email'] === '') {
        $errors['email'] = 'Vui lòng nhập địa chỉ email.';
    } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Email không đúng định dạng (ví dụ: haovanle@edu.vn).';
    }

    if (!in_array($data['topic'], $allowedTopics, true)) {
        $errors['topic'] = 'Chủ đề không hợp lệ.';
    }

    $contentLen = mb_strlen($data['content']);
    if ($data['content'] === '') {
        $errors['content'] = 'Vui lòng nhập nội dung liên hệ.';
    } elseif ($contentLen < 10 || $contentLen > 500) {
        $errors['content'] = 'Nội dung phải từ 10 đến 500 ký tự (hiện tại: ' . $contentLen . ' ký tự).';
    }

    // 3. Kiểm tra Upload ảnh đại diện (Đủ 3 kiểm tra bắt buộc theo Slide 3.8)
    $savedImage = null;
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['avatar'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors['avatar'] = 'Có lỗi xảy ra khi tải tệp lên.';
        } else {
            // Kiểm tra 1: Kích thước tối đa <= 2MB
            if ($file['size'] > 2 * 1024 * 1024) {
                $errors['avatar'] = 'Dung lượng ảnh vượt quá mức tối đa 2MB.';
            }

            // Kiểm tra 2: Phần mở rộng và MIME type thực tế
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if (!in_array($ext, $allowedExts, true) || !str_starts_with($mime, 'image/')) {
                $errors['avatar'] = 'Chỉ chấp nhận định dạng ảnh hợp lệ (JPG, PNG, WEBP).';
            }

            // Kiểm tra 3: Đổi tên file mới an toàn trước khi lưu
            if (empty($errors['avatar']) && empty($errors)) {
                $uploadDir = __DIR__ . '/uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $savedImage = 'avatar_' . bin2hex(random_bytes(8)) . '.' . $ext;
                move_uploaded_file($file['tmp_name'], $uploadDir . $savedImage);
            }
        }
    }

    // 4. Lưu kết quả gần nhất và chuyển hướng PRG khi thành công
    if (empty($errors)) {
        $_SESSION['latest_contact'] = [
            'name'    => $data['name'],
            'email'   => $data['email'],
            'topic'   => $data['topic'],
            'content' => $data['content'],
            'image'   => $savedImage,
            'time'    => date('H:i:s - d/m/Y')
        ];
        header('Location: contact_lop.php');
        exit;
    }
}

$displayResult = $latestResult;

// Hàm mã hóa đầu ra chống XSS
function e($v) { 
    return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); 
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>3.8 & 3.9: Hệ Thống Liên Hệ & Kết Quả Chi Tiết</title>
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
            padding: 24px 36px;
            text-align: center;
        }

        .top-banner h2 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .top-banner p {
            margin: 6px 0 0 0;
            font-size: 14px;
            color: #dbeafe;
        }

        /* Chia 2 cột song song: Trái (Form nhập) - Phải (Kết quả chi tiết rõ nét) */
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

        .form-group { margin-bottom: 16px; }

        label { 
            display: block; 
            font-weight: 600; 
            margin-bottom: 6px; 
            color: #1e293b; 
            font-size: 13.5px; 
        }

        input[type="text"], select, textarea, input[type="file"] { 
            width: 100%; 
            padding: 10px 13px; 
            box-sizing: border-box; 
            border: 1.5px solid #cbd5e1; 
            border-radius: 8px; 
            font-size: 14px; 
            color: #0f172a;
            background: #f8fafc; 
            transition: all 0.2s ease; 
        }

        input:focus, select:focus, textarea:focus { 
            border-color: #2563eb; 
            background: #ffffff; 
            outline: none; 
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12); 
        }

        .input-invalid {
            border-color: #dc2626 !important;
            background: #fef2f2 !important;
        }

        .hint { font-size: 12px; color: #64748b; margin-top: 4px; }
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
            margin-top: 6px;
        }

        button:hover { 
            background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
            transform: translateY(-1px); 
        }

        /* Bảng hiển thị kết quả chi tiết bên phải */
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
            word-break: break-word;
        }

        .result-avatar {
            max-width: 130px;
            max-height: 130px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #10b981;
            margin-top: 6px;
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
        <h2>HỆ THỐNG TIẾP NHẬN & XỬ LÝ LIÊN HỆ AN TOÀN</h2>
        
    </div>

    <div class="grid-container">
        <!-- BẢNG 1 (BÊN TRÁI): FORM NHẬP LIỆU -->
        <div class="panel">
            <div class="panel-header">
                <h3>1. Nhập Thông Tin Liên Hệ</h3>
            </div>

            <form method="post" action="" enctype="multipart/form-data" novalidate>
                <div class="form-group">
                    <label for="name">Họ và tên *</label>
                    <input type="text" id="name" name="name" 
                           class="<?= isset($errors['name']) ? 'input-invalid' : '' ?>"
                           value="<?= e($data['name']) ?>" placeholder="Lê Văn Hào">
                    <?php if (isset($errors['name'])): ?><div class="error"><?= e($errors['name']) ?></div><?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="email">Địa chỉ Email *</label>
                    <input type="text" id="email" name="email" 
                           class="<?= isset($errors['email']) ? 'input-invalid' : '' ?>"
                           value="<?= e($data['email']) ?>" placeholder="haovanle@edu.vn">
                    <?php if (isset($errors['email'])): ?><div class="error"><?= e($errors['email']) ?></div><?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="topic">Chủ đề liên hệ</label>
                    <select id="topic" name="topic">
                        <?php foreach ($allowedTopics as $t): ?>
                            <option value="<?= e($t) ?>" <?= $data['topic'] === $t ? 'selected' : '' ?>><?= e($t) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="content">Nội dung chi tiết *</label>
                    <textarea id="content" name="content" rows="4" 
                              class="<?= isset($errors['content']) ? 'input-invalid' : '' ?>"
                              placeholder="Nhập nội dung từ 10 đến 500 ký tự..."><?= e($data['content']) ?></textarea>
                    <div class="hint">Yêu cầu: Từ 10 đến 500 ký tự.</div>
                    <?php if (isset($errors['content'])): ?><div class="error"><?= e($errors['content']) ?></div><?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="avatar">Ảnh đại diện / Minh chứng</label>
                    <input type="file" id="avatar" name="avatar" accept=".jpg,.jpeg,.png,.webp">
                    <div class="hint">Định dạng JPG, PNG, WEBP (Tối đa 2MB).</div>
                    <?php if (isset($errors['avatar'])): ?><div class="error"><?= e($errors['avatar']) ?></div><?php endif; ?>
                </div>

                <button type="submit">Gửi & Hiển Thị Kết Quả</button>
            </form>
        </div>

        <!-- BẢNG 2 (BÊN PHẢI): KẾT QUẢ HIỂN THỊ CHI TIẾT VÀ RÕ RÉT -->
        <div class="panel">
            <div class="panel-header">
                <h3>2. Kết Quả Thông Tin Đã Gửi</h3>
            </div>

            <?php if (!$displayResult): ?>
                <div class="empty-state">
                    <strong style="font-size: 16px; color: #334155;">Chưa có dữ liệu hiển thị!</strong>
                    <p style="margin-top: 6px; font-size: 14px;">Hãy điền thông tin vào form bên trái và bấm gửi để kết quả hiển thị to rõ tại đây.</p>
                </div>
            <?php else: ?>
                <div class="result-card">
                    <div class="result-item">
                        <div class="result-label">👤 Người gửi:</div>
                        <div class="result-value"><?= e($displayResult['name']) ?> (<?= e($displayResult['email']) ?>)</div>
                    </div>

                    <div class="result-item">
                        <div class="result-label">📌 Chủ đề yêu cầu:</div>
                        <div class="result-value"><span style="background: #dbeafe; color: #1e40af; padding: 3px 8px; border-radius: 4px; font-weight: 600; font-size: 13.5px;"><?= e($displayResult['topic']) ?></span></div>
                    </div>

                    <div class="result-item">
                        <div class="result-label">📝 Nội dung chi tiết:</div>
                        <div class="result-value" style="white-space: pre-line;"><?= e($displayResult['content']) ?></div>
                    </div>

                    <div class="result-item">
                        <div class="result-label">🖼️ Ảnh đại diện đính kèm:</div>
                        <div class="result-value">
                            <?php if (!empty($displayResult['image'])): ?>
                                <img class="result-avatar" src="uploads/<?= e($displayResult['image']) ?>" alt="Avatar">
                            <?php else: ?>
                                <span style="color: #64748b; font-style: italic;">Không có tệp ảnh nào được tải lên.</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>