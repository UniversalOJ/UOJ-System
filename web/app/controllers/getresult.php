<?php
// requirePHPLib('init');

// 安全性检查: 在真实场景中，你需要检查当前用户是否有权限查看这个提交记录。
// 比如，检查用户是否为提交者或管理员。
// if (!Auth::check() || !canViewSubmission(Auth::user(), $_GET['submission_id'])) {
//     become403Page();
// }

// 1. 验证请求方法，必须是 GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(405); // 405 Method Not Allowed
    echo json_encode([
        "status" => "error",
        "message" => "请使用 GET 方法获取结果。"
    ]);
    exit;
}

// 2. 验证必要的参数 'submission_id' 是否存在
if (!isset($_GET['submission_id']) || empty($_GET['submission_id'])) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(400); // 400 Bad Request
    echo json_encode([
        "status" => "error",
        "message" => "请求缺少必要的参数：submission_id。"
    ]);
    exit;
}

// 3. 获取并安全地处理参数
// 使用 escapeshellarg() 来防止任何命令注入的风险
$submissionId = escapeshellarg($_GET['submission_id']);

// 定义后端的 Python 脚本路径
$pythonScriptPath = escapeshellarg("/opt/uoj/web/api/getresult.py");

// 4. 构建并执行 shell 命令
$command = "python3 $pythonScriptPath $submissionId";

// 执行命令，获取输出和返回码
exec($command, $output, $retcode);

// 5. 处理并返回结果

// 总是返回 JSON 格式
header('Content-Type: application/json; charset=utf-8');

if ($retcode !== 0) {
    // 如果 Python 脚本返回非零值，说明发生了错误（例如：提交ID不存在）
    // 返回由 PHP 生成的统一错误信息
    http_response_code(404); // 404 Not Found 可能是个合适的错误码
    echo json_encode([
        "status" => "error",
        "message" => "获取结果失败。",
        "details" => implode("\n", $output) // 从脚本获取具体的错误细节
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
} else {
    // 如果 Python 脚本成功执行（返回码为0）
    // 我们假定 Python 脚本的输出本身就是一个完整的 JSON 字符串。
    // 直接将这个 JSON 字符串作为响应体返回，这是最高效的方式。
    echo implode("\n", $output);
}
?>