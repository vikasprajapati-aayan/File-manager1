<?php
header('Content-Type: application/json');
require '../database/database.php';

//real app mein: agar session hi nahi hai to yahin rok do
if(!isset($_SESSION['user_id'])){
    http_response_code(401);
    echo json_encode(["error" => "Not logged in"]);
    exit;
}

if($_SERVER['REQUEST_METHOD'] === 'GET'){


   $currentUserId   = $_SESSION['user_id'];
$currentUserRole = $_SESSION['role']; // 1=admin, 2=subadmin, 3=normal user

// current logged-in user ki apni details (header mein dikhane ke liye)
$stmt = $conn->prepare("SELECT user_id, name, email, role, status FROM users WHERE user_id = ?");
$stmt->execute([$currentUserId]);
$currentUser = $stmt->fetch(PDO::FETCH_ASSOC);

// sare users ki list (password kabhi bhi frontend ko mat bhejna)
$stmt = $conn->query("SELECT user_id, name, email, role, status FROM users ORDER BY user_id DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// integers me convert karo (PDO kabhi kabhi strings deta hai)
$users = array_map(function($u){
    $u['user_id'] = (int)$u['user_id'];
    $u['role'] = (int)$u['role'];
    $u['status'] = (int)$u['status'];
    return $u;
}, $users);

echo json_encode([
    "currentUser" => [
        "user_id" => (int)$currentUser['user_id'],
        "name" => $currentUser['name'],
        "email" => $currentUser['email'],
        "role" => (int)$currentUser['role']
    ],
    "users" => $users
]);
exit;
}




if($_SERVER['REQUEST_METHOD'] === 'POST'){

// if($_SESSION['role'] != 1){
//     http_response_code(403);
//     echo json_encode(["error" => "Only admins can perform this action"]);
//     exit;
// }

$data = json_decode(file_get_contents("php://input"), true);
$action = $data['action'] ?? null;

if(!$action){
    echo json_encode(["error" => "Invalid request"]);
    exit;
}

try {

    if($action === 'add'){

        $name = trim($data['name'] ?? '');
        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';
        $role = (int)($data['role'] ?? 3);
        $status = (int)($data['status'] ?? 1);

        if(!$name || !$email || strlen($password) < 5){
            echo json_encode(["error" => "Name, email, and a password (5+ chars) are required"]);
            exit;
        }

        // email pehle se exist to nahi karta
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if($stmt->fetch()){
            echo json_encode(["error" => "A user with this email already exists"]);
            exit;
        }


        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $email, $password, $role, $status]);

        echo json_encode([
            "success" => true,
            "user" => [
                "user_id" => (int)$conn->lastInsertId(),
                "name" => $name,
                "email" => $email,
                "role" => $role,
                "status" => $status
            ]
        ]);

    } elseif($action === 'edit'){

        $id = (int)($data['id'] ?? 0);
        $name = trim($data['name'] ?? '');
        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';
        $role = (int)($data['role'] ?? 3);
        $status = (int)($data['status'] ?? 1);

        if(!$id || !$name || !$email){
            echo json_encode(["error" => "Invalid data"]);
            exit;
        }

        // email kisi doosre user ke paas to nahi hai
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
        $stmt->execute([$email, $id]);
        if($stmt->fetch()){
            echo json_encode(["error" => "A user with this email already exists"]);
            exit;
        }

        // agar password field khaali chhoda hai to purana hi rehne do
        if(!empty($password)){
            if(strlen($password) < 6){
                echo json_encode(["error" => "Password must be at least 6 characters"]);
                exit;
            }
            $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, password = ?, role = ?, status = ? WHERE user_id = ?");
            $stmt->execute([$name, $email, $password, $role, $status, $id]);
        } else {
            $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, role = ?, status = ? WHERE user_id = ?");
            $stmt->execute([$name, $email, $role, $status, $id]);
        }

        echo json_encode([
            "success" => true,
            "user" => [
                "user_id" => $id,
                "name" => $name,
                "email" => $email,
                "role" => $role,
                "status" => $status
            ]
        ]);

    } elseif($action === 'delete'){

        $id = (int)($data['id'] ?? 0);

        if(!$id){
            echo json_encode(["error" => "Invalid user id"]);
            exit;
        }

        // apna khud ka account delete na kar sake
        if($id === (int)$_SESSION['user_id']){
            echo json_encode(["error" => "You can't delete your own account"]);
            exit;
        }

        $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->execute([$id]);

        echo json_encode(["success" => true]);

    } else {
        echo json_encode(["error" => "Invalid action"]);
    }

} catch(Exception $e){
    echo json_encode(["error" => "Something went wrong. Please try again."]);
}

}
