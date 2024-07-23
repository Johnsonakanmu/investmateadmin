<?php
// Database connection
$servername = "localhost";
$username = "investimate";
$password = "Admin.4****";
$dbname = "investmate_admin";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Directory for file uploads
define('UPLOAD_DIR', 'uploads/');


// File Upload Function
function uploadFile($file) {
    $uploadDir = UPLOAD_DIR;
    
    // Ensure the uploads directory exists
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Generate a unique name for the file
    $uniqueName = uniqid() . '-' . basename($file['name']);
    $uploadFile = $uploadDir . $uniqueName;

    // Move the file to the upload directory
    if (move_uploaded_file($file['tmp_name'], $uploadFile)) {
        return $uniqueName; // Return the unique name of the file
    } else {
        throw new Exception("File upload failed.");
    }
}

// File Retrieval Function
function getFileUrl($fileName) {
    // URL of the file based on the upload directory
    $uploadUrl = '/uploads/' . $fileName;
    return $uploadUrl;
}


// Read File Function
function readFileContent($fileName) {
    $uploadDir = UPLOAD_DIR;
    $filePath = $uploadDir . $fileName;

    if (file_exists($filePath)) {
        return file_get_contents($filePath); // Returns the file content as bytes
    } else {
        throw new Exception("File not found.");
    }
}
function validateUser($username, $password) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            return $user;
        }
    }
    return false;
}

function createBlogPost($title, $caption, $category, $content, $tags, $file, $user_id) {
    global $conn;

    // Handle file upload if a file is provided
    $fileUrl = null;
    if (!empty($file['name'])) {
        $fileName = uploadFile($file);
        $fileUrl = getFileUrl($fileName);
    }

    // Prepare the SQL statement
    $stmt = $conn->prepare("INSERT INTO posts (title, caption, content, category, tags, featured_image_url, user_id) VALUES (?, ?, ?, ?, ?, ?, ?)");

    // Check if the statement was prepared successfully
    if ($stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }

    // Bind the parameters
    $stmt->bind_param("ssssssi", $title,$caption, $content, $category, $tags, $fileUrl, $user_id);

    // Execute the statement
    if (!$stmt->execute()) {
        die('Execute failed: ' . htmlspecialchars($stmt->error));
    }

    // Close the statement
    $stmt->close();
}

// List Posts
function listPosts() {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM posts");
    $stmt->execute();
    $result = $stmt->get_result();
    $posts = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $posts;
}

// Update Post
function updatePost($post_id, $title, $caption, $content, $category, $tags, $file = null) {
    global $conn;
    
    // Handle file upload if a new file is provided
    if ($file && !empty($file['name'])) {
        $fileName = uploadFile($file);
        $fileUrl = getFileUrl($fileName);

        // Update post with new file URL
        $stmt = $conn->prepare("UPDATE posts SET title = ?, caption = ?, content = ?, category = ?, tags = ?, featured_image_url = ? WHERE post_id = ?");
        $stmt->bind_param("ssssssi", $title, $caption, $content, $category, $tags, $fileUrl, $post_id);
    } else {
        // Update post without changing the file URL
        $stmt = $conn->prepare("UPDATE Post SET title = ?, caption = ?, content = ?, category = ?, tags = ? WHERE post_id = ?");
        $stmt->bind_param("sssssi", $title, $caption, $content, $category, $tags, $post_id);
    }

    $stmt->execute();
    $stmt->close();
}


// Delete Post
function deletePost($post_id) {
    global $conn;
    $stmt = $conn->prepare("DELETE FROM posts WHERE post_id = ?");
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $stmt->close();
}




// Create User
function createUser($username, $password, $email) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO users (username, password, email) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $password, $email);
    $stmt->execute();
    $stmt->close();
}


// List Users
function listUsers() {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM users");
    $stmt->execute();
    $result = $stmt->get_result();
    $users = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $users;
}

// Update User
function updateUser($user_id, $username, $password, $email) {
    global $conn;
    $stmt = $conn->prepare("UPDATE User SET username = ?, password = ?, email = ? WHERE user_id = ?");
    $stmt->bind_param("sssi", $username, $password, $email, $user_id);
    $stmt->execute();
    $stmt->close();
}


// Delete User
function deleteUser($user_id) {
    global $conn;
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();
}
// Get the count of Posts
function getPostCount() {
    global $conn;
    $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM Posts");
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_assoc()['count'];
    $stmt->close();
    return $count;
}

// Get the count of Users
function getUserCount() {
    global $conn;
    $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM Users");
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_assoc()['count'];
    $stmt->close();
    return $count;
}
function getPostById($post_id) {
    global $conn;

    // Prepare the SQL statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM Posts WHERE post_id = ?");
    $stmt->bind_param("i", $post_id);

    // Execute the statement
    $stmt->execute();

    // Get the result
    $result = $stmt->get_result();

    // Fetch the post data
    $post = $result->fetch_assoc();

    // Close the statement
    $stmt->close();

    return $post;
}
// Close connection
function closeConnection() {
    global $conn;
    $conn->close();
}

?>