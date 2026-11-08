<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Lỗi máy chủ | PhongTroQN</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background: #f8fafc;
            color: #334155;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            text-align: center;
        }
        .container {
            max-width: 520px;
            padding: 40px 30px;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            margin: 20px;
        }
        .error-code {
            font-size: 80px;
            font-weight: 800;
            color: #ef4444;
            line-height: 1;
            margin-bottom: 10px;
        }
        h1 {
            font-size: 24px;
            margin-bottom: 15px;
            color: #1e293b;
        }
        p {
            font-size: 15px;
            color: #64748b;
            line-height: 1.6;
            margin-bottom: 25px;
        }
        .btn {
            display: inline-block;
            padding: 12px 28px;
            background: #2563eb;
            color: #ffffff;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 15px;
            transition: background 0.2s;
        }
        .btn:hover {
            background: #1d4ed8;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-code">500</div>
        <h1>Đã xảy ra sự cố kỹ thuật</h1>
        <p>Hệ thống đang gặp lỗi xử lý nội bộ. Đội ngũ kỹ thuật đã được thông báo để khắc phục. Xin lỗi bạn vì sự bất tiện này!</p>
        <a href="<?= defined('BASE_URL') ? BASE_URL : '/' ?>" class="btn">Quay về trang chủ</a>
    </div>
</body>
</html>
