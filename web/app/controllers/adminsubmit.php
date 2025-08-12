<?php
// It's good practice to include necessary setup files.
// requirePHPLib('init');

// Security Check: Ensure the user is an authorized administrator.
// if (!Auth::check() || !isSuperUser(Auth::user())) {
//     // Returns a 403 Forbidden page if the user is not authorized.
//     become403Page();
// }

// 1. Validate the request method.
// The script only accepts POST requests.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // If not POST, return an error message and terminate.
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(405); // Method Not Allowed
    echo json_encode([
        "status" => "error",
        "message" => "请使用 POST 方法提交代码。"
    ]);
    exit;
}

// 2. Validate incoming parameters.
// Check if 'problem_id', 'language', and 'code' are set and not empty.
if (!isset($_POST['problem_id']) || !isset($_POST['language']) || !isset($_POST['code'])) {
    // If any parameter is missing, return a client error.
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(400); // Bad Request
    echo json_encode([
        "status" => "error",
        "message" => "请求缺少必要的参数：problem_id, language, 或 code。"
    ]);
    exit;
}

// 3. Retrieve and prepare the parameters for the shell command.
$problemId = escapeshellarg($_POST['problem_id']);
$language = escapeshellarg($_POST['language']);
$code = escapeshellarg($_POST['code']);

// Define the path to the Python submission script.
$pythonScriptPath = escapeshellarg("/opt/uoj/web/api/adminsubmit.py");

// 4. Construct and execute the shell command.
// The command passes problem_id, language, and the code as arguments to the Python script.
$command = "python3 $pythonScriptPath $problemId $language $code";

// 'exec' runs the command, populates the $output array with its output lines,
// and sets the $retcode with the script's exit code.
exec($command, $output, $retcode);

// 5. Handle the response from the Python script.

// Set the HTTP header to indicate a JSON response.
header('Content-Type: application/json; charset=utf-8');

if ($retcode !== 0) {
    // If the Python script returned a non-zero exit code, it indicates an error.
    http_response_code(500); // Internal Server Error
    echo json_encode([
        "status" => "error",
        "message" => "代码提交处理失败。",
        "details" => implode("\n", $output) // Provide detailed output from the script.
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
} else {
    // If the script executed successfully (exit code 0).
    // The Python script's standard output is expected to be the primary result.
    echo json_encode([
        "status" => "success",
        "output" => implode("\n", $output) // Combine output lines into a single string.
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}
?>