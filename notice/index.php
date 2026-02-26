<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>天天科技公告栏</title>
    <style>
        /* 全局样式 */
        @import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: #000;
            color: #e0e0e0;
            font-family: 'Roboto', sans-serif;
            line-height: 1.6;
            background-image: 
                radial-gradient(circle at 20% 50%, rgba(0, 255, 204, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(0, 204, 255, 0.1) 0%, transparent 50%);
        }
        
        /* 页面容器 */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        /* 页面头部 */
        .page-header {
            text-align: center;
            padding: 40px 0;
            margin-bottom: 40px;
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.9), rgba(20, 0, 40, 0.8));
            border-radius: 20px;
            border: 1px solid #00ffcc;
            box-shadow: 0 0 30px rgba(0, 255, 204, 0.3);
        }
        
        .page-title {
            font-family: 'Orbitron', sans-serif;
            font-size: 3rem;
            color: #00ffcc;
            margin-bottom: 10px;
            text-shadow: 0 0 10px rgba(0, 255, 204, 0.5);
        }
        
        .page-subtitle {
            font-size: 1.2rem;
            color: #a0a0a0;
        }
        
        /* 返回首页按钮 */
        .back-btn {
            display: inline-block;
            background: linear-gradient(135deg, #00ffcc, #00ccff);
            color: #000;
            text-decoration: none;
            padding: 10px 25px;
            border-radius: 25px;
            font-weight: 600;
            margin-bottom: 20px;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0, 204, 255, 0.4);
        }
        
        .back-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 204, 255, 0.6);
        }
        
        /* 公告列表 */
        .notices-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 30px;
            margin-bottom: 50px;
        }
        
        /* 公告卡片 */
        .notice-card {
            background: rgba(20, 0, 40, 0.6);
            border-radius: 15px;
            padding: 25px;
            border: 1px solid rgba(0, 255, 204, 0.2);
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .notice-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 255, 204, 0.3);
            border-color: #00ffcc;
        }
        
        .notice-date {
            font-size: 0.9rem;
            color: #00ffcc;
            margin-bottom: 10px;
            font-weight: 500;
        }
        
        .notice-title {
            font-size: 1.3rem;
            color: #fff;
            margin-bottom: 15px;
            font-weight: 600;
        }
        
        .notice-excerpt {
            font-size: 0.95rem;
            color: #a0a0a0;
            line-height: 1.5;
        }
        
        /* 公告详情模态框 */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(5px);
        }
        
        .modal-content {
            background: linear-gradient(135deg, rgba(20, 0, 40, 0.95), rgba(30, 0, 60, 0.95));
            margin: 5% auto;
            padding: 40px;
            border: 1px solid #00ffcc;
            width: 80%;
            max-width: 800px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 255, 204, 0.3);
            position: relative;
        }
        
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            position: absolute;
            top: 20px;
            right: 25px;
        }
        
        .close:hover {
            color: #00ffcc;
        }
        
        .modal-title {
            font-family: 'Orbitron', sans-serif;
            font-size: 2rem;
            color: #00ffcc;
            margin-bottom: 20px;
            text-shadow: 0 0 10px rgba(0, 255, 204, 0.5);
        }
        
        .modal-date {
            font-size: 1rem;
            color: #00ffcc;
            margin-bottom: 30px;
            font-weight: 500;
        }
        
        .modal-body {
            font-size: 1.1rem;
            line-height: 1.8;
            color: #e0e0e0;
        }
        
        /* 页面底部 */
        .page-footer {
            text-align: center;
            padding: 30px 0;
            color: #666;
            font-size: 0.9rem;
            border-top: 1px solid rgba(0, 255, 204, 0.2);
        }
        
        /* 搜索框样式 */
        .search-container {
            margin-top: 20px;
            display: flex;
            justify-content: center;
            gap: 10px;
        }
        
        .search-container input {
            padding: 10px 15px;
            border: 1px solid #00ffcc;
            border-radius: 25px;
            background: rgba(20, 0, 40, 0.6);
            color: #fff;
            font-size: 1rem;
            width: 300px;
            outline: none;
        }
        
        .search-container input:focus {
            box-shadow: 0 0 10px rgba(0, 255, 204, 0.5);
        }
        
        .search-container button {
            padding: 10px 20px;
            border: none;
            border-radius: 25px;
            background: linear-gradient(135deg, #00ffcc, #00ccff);
            color: #000;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .search-container button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 204, 255, 0.4);
        }
        
        /* 搜索结果样式 */
        .search-results {
            margin-bottom: 40px;
        }
        
        .search-results h3 {
            color: #00ffcc;
            margin-bottom: 20px;
            font-family: 'Orbitron', sans-serif;
        }
        
        /* 移动端适配 */
        @media (max-width: 768px) {
            .page-title {
                font-size: 2rem;
            }
            
            .notices-list {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .modal-content {
                width: 95%;
                padding: 20px;
                margin: 10% auto;
            }
            
            .modal-title {
                font-size: 1.5rem;
            }
            
            .search-container {
                flex-direction: column;
                align-items: center;
            }
            
            .search-container input {
                width: 100%;
                max-width: 300px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- 页面头部 -->
        <header class="page-header">
            
            <h1 class="page-title">天天科技公告栏</h1>
            <p class="page-subtitle">最新公司动态与重要通知</p>
            <a href="../index.html" class="back-btn">返回首页</a>
            <div class="search-container">
                <form method="GET" action="index.php">
                    <input type="text" name="search" placeholder="搜索公告内容..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    <button type="submit">搜索</button>
                </form>
            </div>
        </header>
        
        <!-- 搜索结果 -->
        <?php
        // 搜索功能实现
        function searchAnnouncements($query) {
            $results = array();
            $contentDir = "content/";
            
            if (is_dir($contentDir)) {
                $files = scandir($contentDir);
                foreach ($files as $file) {
                    // 只处理.html文件
                    if ($file !== "." && $file !== ".." && pathinfo($file, PATHINFO_EXTENSION) === "html") {
                        $filePath = $contentDir . $file;
                        if (is_file($filePath)) {
                            $content = file_get_contents($filePath);
                            if (strpos(strtolower($content), strtolower($query)) !== false) {
                                // 解析HTML内容
                                $title = parseTitleFromHTML($content);
                                $date = parseDateFromHTML($content);
                                $body = parseBodyFromHTML($content);
                                
                                $results[] = array(
                                    "file" => $file,
                                    "title" => $title,
                                    "date" => $date,
                                    "content" => $body,
                                    "excerpt" => substr($body, 0, 100) . "..."
                                );
                            }
                        }
                    }
                }
            }
            
            return $results;
        }
        
        // 获取所有公告
        function getAllAnnouncements() {
            $announcements = array();
            $contentDir = "content/";
            
            if (is_dir($contentDir)) {
                $files = scandir($contentDir);
                
                foreach ($files as $file) {
                    // 只处理.html文件
                    if ($file !== "." && $file !== ".." && pathinfo($file, PATHINFO_EXTENSION) === "html") {
                        $filePath = $contentDir . $file;
                        if (is_file($filePath)) {
                            $content = file_get_contents($filePath);
                            // 解析HTML内容
                            $title = parseTitleFromHTML($content);
                            $date = parseDateFromHTML($content);
                            $body = parseBodyFromHTML($content);
                            
                            $announcements[] = array(
                                "file" => $file,
                                "title" => $title,
                                "date" => $date,
                                "content" => $body,
                                "excerpt" => substr($body, 0, 100) . "..."
                            );
                        }
                    }
                }
            }
            
            // 按日期排序（最新的在前）
            usort($announcements, function($a, $b) {
                return strtotime($b['date']) - strtotime($a['date']);
            });
            
            return $announcements;
        }
        
        // 从HTML内容中解析标题
        function parseTitleFromHTML($content) {
            preg_match('/<h2[^>]*>(.*?)<\/h2>/s', $content, $matches);
            return isset($matches[1]) ? trim(strip_tags($matches[1])) : "";
        }
        
        // 从HTML内容中解析日期
        function parseDateFromHTML($content) {
            preg_match('/<div class="notice-date">(.*?)<\/div>/s', $content, $matches);
            return isset($matches[1]) ? trim($matches[1]) : "";
        }
        
        // 从HTML内容中解析正文
        function parseBodyFromHTML($content) {
            preg_match('/<div class="notice-body">(.*?)<\/div>/s', $content, $matches);
            if (isset($matches[1])) {
                return trim(strip_tags($matches[1]));
            }
            return "";
        }
        
        // 处理搜索请求
        $searchQuery = isset($_GET['search']) ? $_GET['search'] : '';
        $announcements = array();
        
        if (!empty($searchQuery)) {
            $announcements = searchAnnouncements($searchQuery);
            echo "<div class='search-results'>";
            echo "<h3>搜索结果: '{$searchQuery}' (共 " . count($announcements) . " 条)</h3>";
        } else {
            $announcements = getAllAnnouncements();
        }
        
        // 显示公告列表
        echo "<main class='notices-list'>";
        
        if (count($announcements) > 0) {
            foreach ($announcements as $announcement) {
                // 将.txt扩展名改为.html
                $htmlFile = str_replace('.txt', '.html', $announcement['file']);
                echo '<article class="notice-card" onclick="window.location.href=\'content/' . $htmlFile . '\'">';
                echo '<div class="notice-date">' . $announcement['date'] . '</div>';
                echo '<h3 class="notice-title">' . $announcement['title'] . '</h3>';
                echo '<p class="notice-excerpt">' . $announcement['excerpt'] . '</p>';
                echo '</article>';
            }
        } else {
            echo "<p style='text-align: center; color: #a0a0a0; font-size: 1.2rem;'>没有找到相关公告。</p>";
        }
        
        if (!empty($searchQuery)) {
            echo "</div>";
        }
        
        echo "</main>";
        ?>
        
        
        <!-- 页面底部 -->
        <footer class="page-footer">
            <p>&copy; 2024 天天科技. 版权所有.</p>
        </footer>
    </div>
    
    <!-- 公告详情模态框 -->
    <div id="notice-modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <div id="modal-content"></div>
        </div>
    </div>
    
    <script>
        // 打开模态框
        function openModal(file) {
            const modal = document.getElementById("notice-modal");
            const modalContent = document.getElementById("modal-content");
            
            // 显示加载状态
            modalContent.innerHTML = `
                <div style="text-align: center; padding: 50px;">
                    <p>加载公告内容中...</p>
                </div>
            `;
            modal.style.display = "block";
            
            // 发送AJAX请求获取公告内容
            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'get_notice.php?file=' + encodeURIComponent(file), true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    if (xhr.status === 200) {
                        const notice = JSON.parse(xhr.responseText);
                        if (notice.error) {
                            modalContent.innerHTML = `
                                <div style="text-align: center; padding: 50px;">
                                    <p>加载公告失败: ${notice.error}</p>
                                </div>
                            `;
                        } else {
                            modalContent.innerHTML = `
                                <h2 class="modal-title">${notice.title}</h2>
                                <div class="modal-date">${notice.date}</div>
                                <div class="modal-body">${notice.content.replace(/\n/g, '<br>')}</div>
                            `;
                        }
                    } else {
                        modalContent.innerHTML = `
                            <div style="text-align: center; padding: 50px;">
                                <p>加载公告失败，请稍后重试。</p>
                            </div>
                        `;
                    }
                }
            };
            xhr.send();
        }
        
        // 关闭模态框
        function closeModal() {
            const modal = document.getElementById("notice-modal");
            modal.style.display = "none";
        }
        
        // 点击模态框外部关闭
        window.onclick = function(event) {
            const modal = document.getElementById("notice-modal");
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>