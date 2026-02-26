<?php
// 取消身份验证要求，允许本地访问


// 设置响应头
header('Content-Type: application/json');

// 获取请求参数
$action = $_GET['action'] ?? '';
$path = $_GET['path'] ?? '';

// 基本安全检查
if (empty($action)) {
    echo json_encode(['error' => '缺少操作参数']);
    exit;
}

// 根据操作类型处理请求
switch ($action) {
    case 'read':
        // 读取文件内容
        if (empty($path)) {
            echo json_encode(['error' => '缺少文件路径']);
            exit;
        }
        
        // 安全检查：防止路径遍历攻击
        $realPath = realpath(dirname(__FILE__) . '/' . $path);
        $baseDir = realpath(dirname(__FILE__));
        
        if ($realPath === false || strpos($realPath, $baseDir) !== 0) {
            echo json_encode(['error' => '无效的文件路径']);
            exit;
        }
        
        // 检查文件是否存在
        if (!file_exists($realPath)) {
            echo json_encode(['error' => '文件不存在']);
            exit;
        }
        
        // 检查是否为文件
        if (!is_file($realPath)) {
            echo json_encode(['error' => '不是文件']);
            exit;
        }
        
        // 读取文件内容
        $content = file_get_contents($realPath);
        if ($content === false) {
            echo json_encode(['error' => '无法读取文件']);
            exit;
        }
        
        // 返回文件内容
        echo json_encode(['content' => $content]);
        break;
        
    case 'list':
        // 列出目录内容
        if (empty($path)) {
            $path = '';
        }
        
        // 调试信息
        $debugInfo = [
            'original_path' => $path,
            'file_dir' => dirname(__FILE__),
            'constructed_path' => dirname(__FILE__) . '/' . $path
        ];
        
        // 安全检查：防止路径遍历攻击
        $realPath = realpath(dirname(__FILE__) . '/' . $path);
        $baseDir = realpath(dirname(__FILE__));
        
        if ($realPath === false || strpos($realPath, $baseDir) !== 0) {
            echo json_encode([
                'error' => '无效的目录路径',
                'debug' => $debugInfo,
                'real_path' => $realPath,
                'base_dir' => $baseDir
            ]);
            exit;
        }
        
        // 检查目录是否存在
        if (!file_exists($realPath)) {
            echo json_encode(['error' => '目录不存在']);
            exit;
        }
        
        // 检查是否为目录
        if (!is_dir($realPath)) {
            echo json_encode(['error' => '不是目录']);
            exit;
        }
        
        // 获取目录内容
        $items = [];
        $dir = scandir($realPath);
        
        foreach ($dir as $item) {
            if ($item !== '.' && $item !== '..') {
                $itemPath = $realPath . '/' . $item;
                $isDir = is_dir($itemPath);
                $size = $isDir ? '<DIR>' : filesize($itemPath);
                $modified = date('Y-m-d H:i A', filemtime($itemPath));
                
                $items[] = [
                    'name' => $item,
                    'isDir' => $isDir,
                    'size' => $size,
                    'modified' => $modified
                ];
            }
        }
        
        // 返回目录内容
        echo json_encode(['items' => $items]);
        break;
        
    default:
        echo json_encode(['error' => '未知操作']);
        break;
}
?>