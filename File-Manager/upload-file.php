<?php
require_once('database/database.php');
header('Content-Type: application/json');

if($_SERVER['REQUEST_METHOD'] === 'POST'){

$user_id = $_SESSION['user_id']; // real app mein session se lena
$folder_id = !empty($_POST['folder_id']) ? $_POST['folder_id'] : null;

if(empty($_FILES['files'])){
    echo json_encode(["error" => "No files received"]);
    exit;
}

// allowed types aur max size (security ke liye)
$allowedExtensions = ['pdf','doc','docx','jpg','jpeg','png','txt','xlsx','zip'];
$maxSizeBytes = 10 * 1024 * 1024; // 10 MB

function formatSize($bytes){
    if($bytes >= 1048576) return round($bytes / 1048576, 1) . ' MB';
    if($bytes >= 1024) return round($bytes / 1024, 1) . ' KB';
    return $bytes . ' B';
}

$uploadedFiles = [];
$failedFiles = [];


$fileCount = count($_FILES['files']['name']);

for($i = 0; $i < $fileCount; $i++){

    $originalName = $_FILES['files']['name'][$i];
    $tmpPath      = $_FILES['files']['tmp_name'][$i];
    $errorCode    = $_FILES['files']['error'][$i];
    $sizeBytes    = $_FILES['files']['size'][$i];
    $type    = $_FILES['files']['type'][$i];


    // 1. Upload error check
    if($errorCode !== UPLOAD_ERR_OK){
        $failedFiles[] = $originalName;
        continue;
    }

    // 2. Size check
    if($sizeBytes > $maxSizeBytes){
        $failedFiles[] = $originalName . ' (too large)';
        continue;
    }

    // 3. Extension check
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    if(!in_array($ext, $allowedExtensions)){
        $failedFiles[] = $originalName . ' (type not allowed)';
        continue;
    }

    // 4. Save with a unique name
    $uniqueName = uniqid('file_', true) . '.' . $ext;
    $destination =  __DIR__ .'/uploads/' . $uniqueName;

    if(!move_uploaded_file($tmpPath, $destination)){
        $failedFiles[] = $originalName . ' (save failed)';
        continue;
    }

    // 5. DB mein entry daalo
    $stmt = $conn->prepare("INSERT INTO files (name, folder_id, user_id, size, storage_path, extension) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$originalName, $folder_id, $user_id, $sizeBytes,  $uniqueName, $ext]);

    $uploadedFiles[] = [
        "id" => (int)$conn->lastInsertId(),
        "name" => $originalName,
        "folder_id" => $folder_id ? (int)$folder_id : null,
        "size" => formatSize($sizeBytes),
        "storage_path" => $uniqueName
    ];
}

echo json_encode([
    "files" => $uploadedFiles,
    "failed" => $failedFiles
]);
exit;
}