<?php
// 获取请求的文件名
if (isset($_GET['file'])) {
    $file = basename($_GET['file']); // 使用basename防止路径遍历攻击
    $filePath = "content/" . $file;
    
    // 检查文件是否存在且可读取
    if (is_file($filePath) && is_readable($filePath)) {
        $content = file_get_contents($filePath);
        $lines = explode("\n", $content);
        
        $title = "";
        $date = "";
        $body = "";
        
        // 解析公告内容
        foreach ($lines as $line) {
            if (strpos($line, "标题:") === 0) {
                $title = trim(substr($line, 3));
            } elseif (strpos($line, "日期:") === 0) {
                $date = trim(substr($line, 3));
            } else {
                $body .= $line . "\n";
            }
        }
        
        // 移除正文末尾的空行
        $body = trim($body);
        
        // 返回JSON格式的公告内容
        echo json_encode(array(
            "title" => $title,
            "date" => $date,
            "content" => $body
        ));
    } else {
        // 文件不存在或不可读取
        echo json_encode(array(
            "error" => "公告文件不存在或无法读取。"
        ));
    }
} else {
    // 未提供文件名
    echo json_encode(array(
        "error" => "未指定要加载的公告文件。"
    ));
}
?>