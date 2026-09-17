<?php
require_once('database/database.php');
header("Content-Type: application/json; charset=UTF-8");

// Function to recursively get all descendant folder IDs
function getAllDescendantFolderIds($folderId, $conn) {
    $descendants = [$folderId]; // Include the folder itself
    
    $stmt = $conn->prepare("SELECT folder_id FROM folders WHERE parent_id = ?");
    $stmt->execute([$folderId]);
    $children = $stmt->fetchAll($conn::FETCH_ASSOC);
    
    foreach($children as $child) {
        $descendants = array_merge($descendants, getAllDescendantFolderIds($child['folder_id'], $conn));
    }
    
    return $descendants;
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){

  $user_id = $_SESSION['user_id'];

  $data = json_decode(file_get_contents('php://input'), 1);


   if($data['action'] === 'delete'){

      if($data['type'] === 'folder'){
            // Get all descendant folder IDs (including the folder itself)
            $allFolderIds = getAllDescendantFolderIds($data['id'], $conn);
            
            // Delete all descendants folders
            $placeholders = implode(',', array_fill(0, count($allFolderIds), '?'));
            $stmt = $conn->prepare("UPDATE folders SET is_deleted = '1' WHERE folder_id IN ($placeholders)");
            $stmt->execute($allFolderIds);
            
            // Delete all files in these folders
            $stmt1 = $conn->prepare("UPDATE files SET is_deleted = '1' WHERE folder_id IN ($placeholders)");
            $stmt1->execute($allFolderIds);

            echo json_encode(['type' => $data['type'], 'id' => $data['id'], 'action' => $data['action'] ]);
            exit;
      }  

      if($data['type'] === 'file'){
            $stmt = $conn->prepare("UPDATE files SET is_deleted = '1' WHERE id = ?");
            $stmt->execute([$data['id']]);

            echo json_encode(['type' => $data['type'], 'id' => $data['id'], 'action' => $data['action'] ]);
            exit;
      }

   }

   if($data['action'] === 'rename'){

      if($data['type'] === 'folder'){
            $stmt = $conn->prepare("UPDATE folders SET name = ? WHERE folder_id = ?");
            $stmt->execute([$data['name'], $data['id']]);
            echo json_encode(['type' => $data['type'], 'id' => $data['id'], 'newName' => $data['name'], 'action' => $data['action'] ]);
           exit;
        }
        if($data['type'] === 'file'){
            $stmt = $conn->prepare("UPDATE files SET name = ? WHERE id = ?");
            $stmt->execute([$data['name'], $data['id']]);
            echo json_encode(['type' => $data['type'], 'id' => $data['id'], 'newName' => $data['name'], 'action' => $data['action']]);
           exit;
        }

   }


    if($data['action'] === 'paste'){

        $fileId = $data['id'];
        $targetFolderId = $data['target_folder_id'];

        $stmt = $conn->prepare("SELECT * FROM files WHERE id = ? AND user_id = ?");
        $stmt->execute([$fileId, $user_id]);
        $original = $stmt->fetch($conn::FETCH_ASSOC);

        if(!$original){
            echo json_encode(["error" => "File not found"]);
            exit;
        }

        // sirf physical file ka naam naya (taaki disk pe clash na ho)
        $newStoredName = uniqid('file_', true) . '.' . $original['extension'];
        $sourcePath = __DIR__ . '/uploads/' . basename($original['storage_path']);
        $destPath   = __DIR__ . '/uploads/' . $newStoredName;

        copy($sourcePath, $destPath);

        // baaki SAB same rakha — name, size, extension, sab original jaisa hi
        $stmt = $conn->prepare("INSERT INTO files (name, folder_id, user_id, size, storage_path, extension) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $original['name'],          // ✅ same naam (display ke liye) — "resume.pdf" hi rahega
            $targetFolderId,            // 👈 sirf yahi cheez badli
            $user_id,
            $original['size'],          // ✅ same
            $newStoredName, // ❌ ye naya honi chahiye (physical file ka path)
            $original['extension']      // ✅ same
        ]);

        echo json_encode([
            "success" => true,
            'action' => $data['action'],
            "newFile" => [
                "id" => (int)$conn->lastInsertId(),
                "name" => $original['name'],
                "folder_id" => (int)$targetFolderId,
                "size" => $original['size'],
                "extension" =>  $original['extension'] 
            ]
        ]);
        exit;
    }


   if($data['action'] === 'moveto'){

        $type = $data['type'];
        $id = $data['id'];
        $targetFolderId = $data['target_folder_id']; // null ho sakta hai (root)
        //  echo json_encode(['type' => $data['type'], 'id' =>  $data['id'], 'target_folder_id' =>  $data['target_folder_id'] ]);
        //  exit;
            
        if($type === 'file'){
            $stmt = $conn->prepare("UPDATE files SET folder_id = ? WHERE id = ? AND user_id = ?");
            $stmt->execute([$targetFolderId, $id, $user_id]);
        } else {
            
            $stmt = $conn->prepare("UPDATE folders SET parent_id = ? WHERE folder_id = ? AND user_id = ?");
            $stmt->execute([$targetFolderId, $id, $user_id]);
        }

        echo json_encode(["success" => true, "action" => $data['action'] ]);
        exit;
   }

   // PERMANENT DELETE from trash
   // PERMANENT DELETE from trash
   if($data['action'] === 'permanent_delete'){
        $type = $data['type'];
        $id = $data['id'];

        if($type === 'folder'){
            // Get all descendant folder IDs (including the folder itself)
            $allFolderIds = getAllDescendantFolderIds($id, $conn);
            
            // Delete all files in trash folders
            $placeholders = implode(',', array_fill(0, count($allFolderIds), '?'));
            $stmt1 = $conn->prepare("DELETE FROM files WHERE folder_id IN ($placeholders) AND user_id = ?");
            $stmt1->execute(array_merge($allFolderIds, [$user_id]));
            
            // Delete all trash folders
            $stmt = $conn->prepare("DELETE FROM folders WHERE folder_id IN ($placeholders) AND user_id = ?");
            $stmt->execute(array_merge($allFolderIds, [$user_id]));

            echo json_encode(['type' => $type, 'id' => $id, 'action' => $data['action'], 'success' => true]);
            exit;
        }  

        if($type === 'file'){
            // Delete physical file from disk
            $stmt = $conn->prepare("SELECT storage_path FROM files WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $user_id]);
            $file = $stmt->fetch($conn::FETCH_ASSOC);
            
            if($file){
                $filePath = __DIR__ . '/uploads/' . basename($file['storage_path']);
                if(file_exists($filePath)){
                    unlink($filePath);
                }
            }

            // Delete from database
            $stmt = $conn->prepare("DELETE FROM files WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $user_id]);

            echo json_encode(['type' => $type, 'id' => $id, 'action' => $data['action'], 'success' => true]);
            exit;
        }
   }

   // RESTORE from trash
   if($data['action'] === 'restore'){
        $type = $data['type'];
        $id = $data['id'];

        if($type === 'folder'){
            // Restore folder and all its descendants
            $allFolderIds = getAllDescendantFolderIds($id, $conn);
            $placeholders = implode(',', array_fill(0, count($allFolderIds), '?'));
            
            $stmt = $conn->prepare("UPDATE folders SET is_deleted = '0' WHERE folder_id IN ($placeholders) AND user_id = ?");
            $stmt->execute(array_merge($allFolderIds, [$user_id]));

            echo json_encode(['type' => $type, 'id' => $id, 'action' => $data['action'], 'success' => true]);
            exit;
        }

        if($type === 'file'){
            $stmt = $conn->prepare("UPDATE files SET is_deleted = '0' WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $user_id]);

            echo json_encode(['type' => $type, 'id' => $id, 'action' => $data['action'], 'success' => true]);
            exit;
        }
   }


}







?>