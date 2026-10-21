<?php
declare(strict_types=1);

// Hàm định dạng tiền tệ Việt Nam (ví dụ: 2.000.000 ₫)
function money(float $value): string {
    return number_format($value, 0, ',', '.') . ' ₫';
}

// Hàm mã hóa chống XSS
function e(string $val): string {
    return htmlspecialchars($val, ENT_QUOTES, 'UTF-8');
}

$name  = trim($_POST['name'] ?? '');
$qty   = $_POST['qty'] ?? '';
$price = $_POST['price'] ?? '';
$error = '';
$resultList = [];

// Sử dụng Session để lưu danh sách hóa đơn hiển thị dạng bảng kết quả sau khi POST
session_start();
if (!isset($_SESSION['invoices'])) {
    $_SESSION['invoices'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($name === '') {
        $error = 'Vui lòng nhập tên tài liệu.';
    } elseif ($qty === '' || !is_numeric($qty) || (int)$qty <= 0) {
        $error = 'Số lượng phải là số nguyên lớn hơn 0.';
    } elseif ($price === '' || !is_numeric($price) || (float)$price < 0) {
        $error = 'Đơn giá phải là số lớn hơn hoặc bằng 0.';
    } else {
        $quantity = (int)$qty;
        $unitPrice = (float)$price;
        $total = $quantity * $unitPrice;

        // Thêm vào mảng lưu trữ tạm thời trong session
        $_SESSION['invoices'][] = [
            'name'  => $name,
            'qty'   => $quantity,
            'price' => $unitPrice,
            'total' => $total
        ];

        // Reset dữ liệu form sau khi submit thành công
        $name = '';
        $qty = '';
        $price = '';
    }
}
$resultList = $_SESSION['invoices'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Hóa đơn học liệu - Buổi 2</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: linear-gradient(135deg, #fdf2f8 0%, #fae8ff 100%); 
            margin: 0;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            box-sizing: border-box;
        }

        /* Khung lớn chứa 2 bảng song song */
        .main-container { 
            background: rgba(255, 255, 255, 0.96); 
            width: 100%;
            max-width: 1050px; 
            padding: 35px; 
            border-radius: 16px; 
            box-shadow: 0 20px 40px rgba(219, 39, 119, 0.15);
            box-sizing: border-box;
            backdrop-filter: blur(10px);
        }

        /* Tiêu đề chung */
        .page-title {
            background: linear-gradient(135deg, #db2777 0%, #be185d 100%);
            color: #ffffff;
            margin: -35px -35px 30px -35px;
            padding: 22px;
            text-align: center;
            border-top-left-radius: 16px;
            border-top-right-radius: 16px;
            font-size: 24px;
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        /* Chia bố cục thành 2 cột: Trái (Nhập liệu) - Phải (Kết quả) */
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1.3fr;
            gap: 25px;
        }

        @media (max-width: 850px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
        }

        .panel {
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            border: 1px solid #fbcfe8;
            box-shadow: 0 4px 12px rgba(219, 39, 119, 0.05);
        }

        .panel h3 {
            margin-top: 0;
            color: #9d174d;
            font-size: 18px;
            border-bottom: 2px solid #fdf2f8;
            padding-bottom: 10px;
        }

        .form-group { 
            margin-bottom: 15px; 
        }

        label { 
            display: block; 
            font-weight: 600; 
            margin-bottom: 6px; 
            color: #500724;
            font-size: 14px;
        }

        input[type="text"] { 
            width: 100%; 
            padding: 11px 14px; 
            box-sizing: border-box; 
            border: 2px solid #fbcfe8; 
            border-radius: 8px; 
            font-size: 15px;
            background-color: #fdf2f8;
            transition: all 0.3s ease;
        }

        input[type="text"]:focus {
            border-color: #db2777;
            background-color: #fff;
            outline: none;
            box-shadow: 0 0 0 3px rgba(219, 39, 119, 0.15);
        }

        .error { 
            color: #e11d48; 
            font-size: 0.85em; 
            margin-top: 10px; 
            font-weight: 500;
            padding: 8px;
            background: #fff1f2;
            border-left: 4px solid #e11d48;
            border-radius: 4px;
        }

        button { 
            background: linear-gradient(135deg, #f472b6 0%, #db2777 100%); 
            color: #fff; 
            border: none; 
            padding: 12px 20px; 
            border-radius: 8px; 
            cursor: pointer; 
            font-weight: bold; 
            font-size: 16px;
            width: 100%;
            transition: all 0.3s ease;
            margin-top: 10px;
            box-shadow: 0 4px 10px rgba(219, 39, 119, 0.2);
        }

        button:hover { 
            filter: brightness(1.05);
            transform: translateY(-1px);
        }

        /* Định dạng bảng kết quả bên phải */
        table { 
            border-collapse: collapse; 
            width: 100%; 
            margin-top: 10px;
            border-radius: 8px;
            overflow: hidden;
        }

        th, td { 
            border: 1px solid #fbcfe8; 
            padding: 10px 8px; 
            text-align: center; 
            font-size: 14px;
        }

        th { 
            background: #fdf2f8; 
            color: #9d174d; 
            font-weight: 600; 
        }

        td.name-cell {
            text-align: left;
            font-weight: 500;
            color: #831843;
        }

        .total-row {
            font-weight: bold;
            background: #fdf2f8;
            color: #be185d;
        }
    </style>
</head>
<body>
<div class="main-container">
    <div class="page-title">🌸 Quản Lý Hóa Đơn Học Liệu</div>

    <div class="content-grid">
        <!-- BẢNG 1: NHẬP LIỆU (Bên trái) -->
        <div class="panel">
            <h3>📝 Bảng Nhập Liệu</h3>
            <form method="post" action="">
                <div class="form-group">
                    <label for="name">📚 Tên tài liệu:</label>
                    <input type="text" id="name" name="name" value="<?= e($name) ?>" placeholder="Nhập tên tài liệu...">
                </div>

                <div class="form-group">
                    <label for="qty">📦 Số lượng:</label>
                    <input type="text" id="qty" name="qty" value="<?= e((string)$qty) ?>" placeholder="Nhập số lượng...">
                </div>

                <div class="form-group">
                    <label for="price">💰 Đơn giá (VNĐ):</label>
                    <input type="text" id="price" name="price" value="<?= e((string)$price) ?>" placeholder="Nhập đơn giá...">
                </div>

                <button type="submit">🧮 Thêm & Tính Tiền</button>
            </form>

            <?php if ($error !== ''): ?>
                <div class="error"><?= e($error) ?></div>
            <?php endif; ?>
        </div>

        <!-- BẢNG 2: IN RA KẾT QUẢ (Bên phải) -->
        <div class="panel">
            <h3>📊 Bảng Kết Quả Hóa Đơn</h3>
            <?php if (empty($resultList)): ?>
                <p style="color: #71717a; text-align: center; margin-top: 40px; font-style: italic;">Chưa có tài liệu nào được nhập. Hãy nhập thông tin bên trái để hiển thị kết quả!</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>STT</th>
                            <th>Tên tài liệu</th>
                            <th>SL</th>
                            <th>Đơn giá</th>
                            <th>Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $grandTotal = 0;
                        foreach ($resultList as $index => $item): 
                            $grandTotal += $item['total'];
                        ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td class="name-cell"><?= e($item['name']) ?></td>
                            <td><?= $item['qty'] ?></td>
                            <td><?= number_format($item['price'], 0, ',', '.') ?> ₫</td>
                            <td><strong><?= money($item['total']) ?></strong></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="total-row">
                            <td colspan="4" style="text-align: right;">Tổng cộng thanh toán:</td>
                            <td><?= money($grandTotal) ?></td>
                        </tr>
                    </tfoot>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>