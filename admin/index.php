<?php
session_start();

// 检查是否已登录
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no">
    <title>管理面板 - 原始大陆</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Microsoft YaHei', sans-serif;
        }

        body {
            background: #f5f7fa;
            color: #333;
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        header {
            background: #2c3e50;
            color: white;
            padding: 20px 0;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logout-btn {
            padding: 8px 15px;
            background: #e74c3c;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            transition: background 0.3s;
            text-decoration: none;
        }

        .logout-btn:hover {
            background: #c0392b;
        }

        .nav {
            display: flex;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            margin-bottom: 30px;
            overflow: hidden;
        }

        .nav-item {
            flex: 1;
            text-align: center;
            padding: 15px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s;
            border-bottom: 3px solid transparent;
            text-decoration: none;
            color: inherit;
        }

        .nav-item.active {
            color: #3498db;
            border-bottom: 3px solid #3498db;
            background: #f8f9fa;
        }

        .nav-item:hover:not(.active) {
            background: #f8f9fa;
        }

        .card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            margin-bottom: 30px;
            text-align: center;
        }

        .card-title {
            font-size: 24px;
            color: #2c3e50;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .btn {
            padding: 12px 25px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            font-size: 16px;
            transition: background 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .btn:hover {
            background: #2980b9;
        }

        footer {
            text-align: center;
            margin-top: 30px;
            padding: 20px;
            color: #7f8c8d;
            font-size: 14px;
        }

        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 15px;
            }
            
            .user-info {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <div class="logo">
                <i class="fas fa-cogs"></i> 管理面板
            </div>
            <div class="user-info">
                <span>欢迎, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> 登出
                </a>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="nav">
            <a href="music.php" class="nav-item">
                <i class="fas fa-music"></i> 音乐管理
            </a>
            <a href="resource.php" class="nav-item">
                <i class="fas fa-file-download"></i> 资源包管理
            </a>
            <a href="server.php" class="nav-item">
                <i class="fas fa-server"></i> 服务器设置
            </a>
            <a href="video.php" class="nav-item">
                <i class="fas fa-video"></i> 视频背景
            </a>
            <a href="join.php" class="nav-item">
                <i class="fas fa-qrcode"></i> 加入我们
            </a>
            <a href="settings.php" class="nav-item">
                <i class="fas fa-cog"></i> 系统设置
            </a>
        </div>

        <div class="card">
            <h2 class="card-title">
                <i class="fas fa-home"></i> 欢迎使用管理面板
            </h2>
            <p>请选择上方的导航项来管理相应的内容。</p>

        </div>
    </div>

    <footer>
        <div class="container">
            &copy; 2025 原始大陆管理系统 | 数据存储于 SQLite
        </div>
    </footer>
</body>
</html>