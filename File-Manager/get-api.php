<?php
require_once('database/database.php');
header("Content-Type: application/json; charset=UTF-8");


if(!isset($_SESSION['user_id'])){
    return;
}

$data = json_decode(file_get_contents('php://input'), 1);

$user_id = $_SESSION['user_id'];


// GET FOLDER AND FILES for logged user
if($_SERVER['REQUEST_METHOD'] === 'GET'){

        $stmt1 = $conn->prepare("SELECT * FROM folders WHERE user_id = ? and is_deleted = '0' ");
        $stmt2 = $conn->prepare("SELECT * FROM files WHERE user_id = ? and is_deleted = '0'");
        $stmt1->execute([$user_id]);
        $stmt2->execute([$user_id]);

        $res1 = $stmt1->fetchAll(PDO::FETCH_ASSOC);
        $res2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
           "folders" => $res1,
           "files"   => $res2  ]);
        exit;
      
}

// GET DELETED FOLDER AND FILES (TRASH)
if($_SERVER['REQUEST_METHOD'] === 'POST' && ($data['get_trash'] ?? null)){
        $stmt1 = $conn->prepare("SELECT * FROM folders WHERE user_id = ? and is_deleted = '1' ");
        $stmt2 = $conn->prepare("SELECT * FROM files WHERE user_id = ? and is_deleted = '1'");
        $stmt1->execute([$user_id]);
        $stmt2->execute([$user_id]);

        $res1 = $stmt1->fetchAll(PDO::FETCH_ASSOC);
        $res2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
           "folders" => $res1,
           "files"   => $res2  ]);
        exit;
}

// create new folder
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    if($data['new_folder'] ?? null ){
           

           $finalName = getUniqueFolderName($conn, $data['new_folder'] , $data['parent_id'], $user_id);

          $stmt = $conn->prepare("INSERT INTO folders (name, user_id, parent_id) VALUES (?,?, ?)");
          $stmt->execute([$finalName  ,$user_id, $data['parent_id']]);

          $newId = $conn->lastInsertId(); 
          echo json_encode([
                    "folder_id" => (int)$newId,
                    "name" => $finalName ,
                    "parent_id" =>  $data['parent_id']
                ]);
                exit();
      }


      if($data['logout'] ?? null)
        {
          session_destroy();
          unset($conn);
          echo json_encode(["success" => true]);
          exit;
      }



}



function getUniqueFolderName($conn, $desiredName, $parentId, $userId){
    $desiredName = trim($desiredName);

    // check karo ye naam already exist karta hai kya isi parent ke andar
    $stmt = $conn->prepare("SELECT name FROM folders WHERE parent_id <=> ? AND user_id = ?");
    $stmt->execute([$parentId, $userId]);
    $existingNames = array_map(fn($row) => strtolower($row['name']), $stmt->fetchAll(PDO::FETCH_ASSOC));

    if(!in_array(strtolower($desiredName), $existingNames)){
        return $desiredName;
    }

    $counter = 1;
    do {
        $newName = "$desiredName ($counter)";
        $counter++;
    } while(in_array(strtolower($newName), $existingNames));

    return $newName;
}

?>
