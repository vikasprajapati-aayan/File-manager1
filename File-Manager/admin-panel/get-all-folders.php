<?php

header('Content-Type: application/json');
require '../database/database.php';


if(!isset($_SESSION['user_id'])){
    http_response_code(401);
    echo json_encode(["error" => "Not logged in"]);
    exit;
}


if($_SERVER['REQUEST_METHOD'] === 'GET'){

$stmt = $conn->query("
    SELECT folder_id, folders.name, folders.parent_id, folders.user_id,
           folders.updated_at, users.name AS owner_name
    FROM folders JOIN users ON folders.user_id = users.user_id WHERE folders.is_deleted = '0'
    ORDER BY folders.name ASC ");
$folders = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $conn->query("
    SELECT files.id, files.name, files.folder_id, files.user_id,
           files.size, files.extension, files.updated_at, files.storage_path, users.name AS owner_name
    FROM files JOIN users ON files.user_id = users.user_id  WHERE files.is_deleted = '0'
    ORDER BY files.name ASC ");

$files = $stmt->fetchAll(PDO::FETCH_ASSOC);

$folders = array_map(function($f){
    $f['id'] = (int)$f['folder_id'];
    $f['parent_id'] = $f['parent_id'] !== null ? (int)$f['parent_id'] : null;
    $f['user_id'] = (int)$f['user_id'];
    return $f;
}, $folders);

$files = array_map(function($f){
    $f['id'] = (int)$f['id'];
    $f['folder_id'] = $f['folder_id'] !== null ? (int)$f['folder_id'] : null;
    $f['user_id'] = (int)$f['user_id'];
    return $f;
}, $files);

echo json_encode([
    "folders" => $folders,
    "files" => $files
]);
exit;
}