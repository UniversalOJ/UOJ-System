<?php
//requirePHPLib('init');

// if (!Auth::check() || !isSuperUser(Auth::user())) {
//     become403Page();
// }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    becomeMsgPage("请使用 POST 方法上传 zip 文件");
}

if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    becomeMsgPage("文件上传失败！");
}

// 保存 zip 文件
$tmpZipPath = "/tmp/batchsetup_" . uniqid() . ".zip";
move_uploaded_file($_FILES['file']['tmp_name'], $tmpZipPath);

// 解压缩 zip 文件到临时目录
$tmpExtractDir = "/tmp/batchsetup_dir_" . uniqid();
mkdir($tmpExtractDir, 0777, true);
$zip = new ZipArchive();
if ($zip->open($tmpZipPath) === TRUE) {
    $zip->extractTo($tmpExtractDir);
    $zip->close();
} else {
    becomeMsgPage("zip 文件解压失败！");
}

$pythonScriptPath = escapeshellarg("/opt/uoj/web/api/batchsetup.py");
$targetDir = escapeshellarg($tmpExtractDir);
$command = "python3 $pythonScriptPath $targetDir";
$json_output = shell_exec($command);
exec("python3 $pythonScriptPath $targetDir", $output, $retcode);

if ($retcode !== 0) {
    // 如果 Python 脚本出错，返回错误信息
    becomeMsgPage("处理失败：\n" . implode("\n", $output));
}

// ==================【核心修改部分】==================


if ($retcode !== 0) {
    becomeMsgPage("处理失败：\n" . implode("\n", $output));
}

// 1. 设置正确的 HTTP 响应头，告诉浏览器返回的是 JSON
header('Content-Type: application/json; charset=utf-8');

// 2. 将 Python 输出的行数组合并成一个字符串，并直接输出
// 这就是 Python 脚本产生的 JSON 内容
echo implode("\n", $output);

echo json_encode([
    "status" => "success",
    "output" => $output
], JSON_PRETTY_PRINT);
?>
