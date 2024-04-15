<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Database connection details as constants
define('DB_SERVER', '10.147.17.233');
define('DB_USERNAME', 'IT490DB');
define('DB_PASSWORD', 'IT490DB');
define('DB_DATABASE', 'EARLYBIRD');


function getDatabaseConnection()
{
    static $conn = null; // Static variable to hold the connection

    if ($conn === null) {
        try {
            $conn = new PDO("mysql:host=" . DB_SERVER . ";dbname=" . DB_DATABASE, DB_USERNAME, DB_PASSWORD);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Error connecting to database: " . $e->getMessage());
        }
    }

    return $conn;
}

function createUser($name, $username, $email, $hash, $role)
{
    $response = array('status' => false, 'message' => '');

    try {
        $conn = getDatabaseConnection(); // Use the single connection function

        // SQL statement to insert a new user
        $sql = "INSERT INTO users (name, username, email, hash, role) VALUES (:name, :username, :email, :hash, :role)";

        // Prepare and bind parameters
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':hash', $hash);
        $stmt->bindParam(':role', $role);

        // Execute the statement
        $stmt->execute();

        // Update response status and message on success
        $response['status'] = true;
        $response['message'] = "New user created successfully";
    } catch (PDOException $e) {
        // Update response message on error
        $response['message'] = "Error: " . $e->getMessage();
    }

    // Return the response array
    return $response;
}

function login($username, $password)
{
    $response = array('status' => false, 'message' => '');

    try {
        $conn = getDatabaseConnection(); // Reuse the database connection function

        // SQL statement to select user by username
        $sql = "SELECT * FROM users WHERE username = :username LIMIT 1";

        // Prepare and bind parameters
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':username', $username);

        // Execute the statement
        $stmt->execute();

        // Check if user exists
        if ($stmt->rowCount() == 1) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verify the password
            if (password_verify($password, $user['hash'])) {
                // Password is correct
                $response['status'] = true;
                $response['message'] = "Login successful";
            } else {
                // Password is incorrect
                $response['message'] = "Invalid username or password";
            }
        } else {
            // Username does not exist
            $response['message'] = "Invalid username or password";
        }
    } catch (PDOException $e) {
        // Update response message on error
        $response['message'] = "Database error: " . $e->getMessage();
    }

    return $response;
}

function fetchFriendsByUsername($conn, $username) {
    if ($conn === null) {
        throw new Exception("Database connection is not established.");
    }

    try {
        $sql = "
            SELECT u.username, f.status
            FROM friends f
            JOIN users u ON u.id = f.user_id2
            WHERE f.user_id1 = (SELECT id FROM users WHERE username = :username)
              AND f.status = 'accepted'
            UNION
            SELECT u.username, f.status
            FROM friends f
            JOIN users u ON u.id = f.user_id1
            WHERE f.user_id2 = (SELECT id FROM users WHERE username = :username)
              AND f.status = 'accepted';
        ";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Failed to fetch friends by username: " . $e->getMessage());
        return [];
    }
}

function fetchAllUsernames($currentUsername) {
    $conn = getDatabaseConnection(); // Reuse the existing database connection function

    try {
        $sql = "SELECT username FROM users WHERE username != :currentUsername";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':currentUsername', $currentUsername);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Fetches only the usernames
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage()); 
        return []; // Return an empty array on error
    }
}
function sendFriendRequest($conn, $username1, $username2) {
    try {
        $user_id1 = getUserIdByUsername($conn, $username1);
        $user_id2 = getUserIdByUsername($conn, $username2);

        if (!$user_id1 || !$user_id2) {
            return ['status' => false, 'message' => 'One or both users not found.'];
        }

        // Log initial user IDs for debugging
        error_log("Initial IDs: User1: $user_id1, User2: $user_id2, Action User: $username1");

        // Ensure user_id1 is always the smaller ID to maintain consistency
        if ($user_id1 > $user_id2) {
            $temp = $user_id1;
            $user_id1 = $user_id2;
            $user_id2 = $temp;
        }

        // Log swapped user IDs for debugging
        error_log("Swapped IDs if needed: User1: $user_id1, User2: $user_id2");

        // Check if any friend request exists regardless of status
        $stmt = $conn->prepare("SELECT * FROM friends WHERE (user_id1 = ? AND user_id2 = ?) OR (user_id1 = ? AND user_id2 = ?)");
        $stmt->execute([$user_id1, $user_id2, $user_id2, $user_id1]);
        if ($stmt->fetch()) {
            return ['status' => false, 'message' => 'A friend request already exists or has been processed.'];
        }

        // Insert the new friend request
        $stmt = $conn->prepare("INSERT INTO friends (user_id1, user_id2, status, action_user_id) VALUES (?, ?, 'pending', ?)");
        // Ensure the action_user_id is correctly set to the user who initiated the request
        $action_user_id = getUserIdByUsername($conn, $username1);  // This ensures the correct user is set as the initiator
        $stmt->execute([$user_id1, $user_id2, $action_user_id]);

        if ($stmt->rowCount() > 0) {
            return ['status' => true, 'message' => 'Friend request sent successfully.'];
        } else {
            return ['status' => false, 'message' => 'Failed to send friend request.'];
        }
    } catch (PDOException $e) {
        return ['status' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}



function fetchReceivedFriendRequests($conn, $username) {
    try {
        // Fetch user ID first
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $userId = $stmt->fetchColumn();

        // Fetch pending friend requests
        $stmt = $conn->prepare("SELECT u.username FROM friends f JOIN users u ON f.user_id1 = u.id WHERE f.user_id2 = ? AND f.status = 'pending'");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        return [];
    }
}

function getUserIdByUsername($conn, $username) {
    try {
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $userId = $stmt->fetchColumn();

        // Log what ID is fetched for which username
        error_log("Fetched user ID: {$userId} for username: '{$username}'");

        if ($userId) {
            return $userId;
        } else {
            // Log and handle the case where no user is found
            error_log("User not found for username: '{$username}'");
            return null; // Return null if no user ID found
        }
    } catch (PDOException $e) {
        // Log database errors
        error_log("Database error in getUserIdByUsername: " . $e->getMessage());
        throw $e; // Re-throw the exception to handle it in the calling script
    }
}

function updateFriendRequestStatus($conn, $requesterUsername, $receiverUsername, $status) {
    try {
        // Fetch user IDs
        $requester_id = getUserIdByUsername($conn, $requesterUsername);
        $receiver_id = getUserIdByUsername($conn, $receiverUsername);

        // Check if user IDs were found
        if (!$requester_id || !$receiver_id) {
            $missingUser = !$requester_id ? $requesterUsername : $receiverUsername;
            return [
                'success' => false,
                'message' => "User not found: {$missingUser}"
            ];
        }
	error_log("Updating status for requester ID: {$requester_id}, receiver ID: {$receiver_id}");

        // Update the status of the friend request
        $sql = "UPDATE friends SET status = ? WHERE (user_id1 = ? AND user_id2 = ?) OR (user_id1 = ? AND user_id2 = ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$status, $requester_id, $receiver_id, $receiver_id, $requester_id]);

        if ($stmt->rowCount() > 0) {
            return ['success' => true, 'message' => 'Friend request status updated successfully.'];
        } else {
            return ['success' => false, 'message' => 'No changes made. It is possible the request does not exist or it was already in the desired state.'];
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

function acceptFriendRequest($conn, $requester_id, $receiver_username) {
    try {
        $conn->beginTransaction(); // Start transaction

        // Get the receiver's user ID from the username
        $receiver_id = getUserIdByUsername($conn, $receiver_username);
        if (!$receiver_id) {
            $conn->rollback(); // Ensure no changes are made if user ID is not found
            return ['success' => false, 'message' => 'Receiver user not found.'];
        }

        // Prepare the SQL to update the friend request status
        $stmt = $conn->prepare("UPDATE friends SET status = 'accepted' WHERE user_id1 = :requester_id AND user_id2 = :receiver_id AND status = 'pending'");
        $stmt->execute([
            ':requester_id' => $requester_id,
            ':receiver_id' => $receiver_id
        ]);

        // Check if the update was successful
        if ($stmt->rowCount() > 0) {
            $conn->commit(); // Commit the transaction
            return ['success' => true, 'message' => 'Friend request accepted successfully.'];
        } else {
            $conn->rollback(); // Rollback the transaction if no rows were updated
            return ['success' => false, 'message' => 'No pending friend request to accept.'];
        }
    } catch (PDOException $e) {
        $conn->rollback(); // Ensure rollback on error
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}
function rejectFriendRequest($conn, $requesterUsername, $receiverUsername) {
    try {
        // Fetch user IDs
        $requester_id = getUserIdByUsername($conn, $requesterUsername);
        $receiver_id = getUserIdByUsername($conn, $receiverUsername);

        // Ensure both user IDs are found
        if (!$requester_id || !$receiver_id) {
            return ['success' => false, 'message' => 'One or both users not found.'];
        }

        // SQL to delete the friend request
        $sql = "DELETE FROM friends WHERE (user_id1 = ? AND user_id2 = ?) OR (user_id1 = ? AND user_id2 = ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$requester_id, $receiver_id, $receiver_id, $requester_id]);

        if ($stmt->rowCount() > 0) {
            return ['success' => true, 'message' => 'Friend request rejected successfully.'];
        } else {
            return ['success' => false, 'message' => 'No friend request to reject.'];
        }
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

function deleteFriend($conn, $user1_username, $user2_username) {
    try {
        $conn->beginTransaction(); // Start transaction

        // Retrieve IDs based on usernames
        $user1_id = getUserIdByUsername($conn, $user1_username);
        $user2_id = getUserIdByUsername($conn, $user2_username);

        if (!$user1_id || !$user2_id) {
            $conn->rollback(); // Rollback if any user ID is not found
            return ['success' => false, 'message' => 'User not found.'];
        }

        // Prepare the SQL statement to delete the friendship
        $sql = "DELETE FROM friends WHERE (user_id1 = ? AND user_id2 = ?) OR (user_id1 = ? AND user_id2 = ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$user1_id, $user2_id, $user2_id, $user1_id]);

        // Check if the deletion was successful
        if ($stmt->rowCount() > 0) {
            $conn->commit(); // Commit the transaction
            return ['success' => true, 'message' => 'Friendship successfully deleted.'];
        } else {
            $conn->rollback(); // Rollback the transaction if no rows were affected
            return ['success' => false, 'message' => 'No friendship was deleted.'];
        }
    } catch (PDOException $e) {
        $conn->rollback(); // Ensure rollback on error
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

function insertNewsArticle($title, $content, $source, $url = null) {
    $response = array('status' => false, 'message' => '');
    try {
        $conn = getDatabaseConnection(); // Reuse the database connection function
        $sql = "INSERT INTO articles (title, content, source, url) VALUES (:title, :content, :source, :url)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':content', $content);
        $stmt->bindParam(':source', $source);
        $stmt->bindParam(':url', $url);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $response['status'] = true;
            $response['message'] = "News article inserted successfully";
        } else {
            $response['message'] = "Failed to insert news article";
        }
    } catch (PDOException $e) {
        $response['message'] = "Error: " . $e->getMessage();
    }
    return $response;
}

function createArticle($title, $content, $author, $source = 'user', $url = null) {
    $response = ['status' => false, 'message' => ''];

    try {
        $conn = getDatabaseConnection(); // Use the database connection function

        // Get the author_id from the users table using the username
        $userSql = "SELECT id FROM users WHERE username = :username LIMIT 1";
        $userStmt = $conn->prepare($userSql);
        $userStmt->bindParam(':username', $author);
        $userStmt->execute();
        $user = $userStmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $author_id = $user['id']; // Fetched author ID

            // SQL statement to insert a new article with 'source' and optional 'url'
            $sql = "INSERT INTO articles (title, content, author_id, source, url) VALUES (:title, :content, :author_id, :source, :url)";

            // Prepare and bind parameters
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':content', $content);
            $stmt->bindParam(':author_id', $author_id);
            $stmt->bindParam(':source', $source);
            $stmt->bindParam(':url', $url);

            // Execute the statement
            $stmt->execute();

            // Update response status and message on success
            if ($stmt->rowCount() > 0) {
                $response['status'] = true;
                $response['message'] = "Article created successfully";
            } else {
                $response['message'] = "Failed to create article";
            }
        } else {
            $response['message'] = "Author username not found";
        }
    } catch (PDOException $e) {
        $response['message'] = "Error: " . $e->getMessage();
    }

    return $response;
}
function saveApiArticle($title, $content, $source, $url = null) {
    $response = array('status' => false, 'message' => '');

    try {
        $conn = getDatabaseConnection(); // Use the database connection function

        // SQL statement to insert a new article with 'source' and optional 'url'
        $sql = "INSERT INTO articles (title, content, author_id, is_private, publication_date, source, url) VALUES (:title, :content, NULL, 0, NOW(), :source, :url)";

        // Prepare and bind parameters
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':content', $content);
        $stmt->bindParam(':source', $source);
        $stmt->bindParam(':url', $url);

        // Execute the statement
        $stmt->execute();

        // Update response status and message on success
        if ($stmt->rowCount() > 0) {
            $response['status'] = true;
            $response['message'] = "Article from API inserted successfully.";
        } else {
            $response['message'] = "Failed to insert article from API.";
        }
    } catch (PDOException $e) {
        $response['message'] = "Database error while inserting article from API: " . $e->getMessage();
    }

    return $response;
}


function fetchArticles($limit = 15, $filter = 'public', $sourceFilter = 'user')
{
    $conn = getDatabaseConnection();

    // Adjust the SQL query based on the filter provided for article visibility and source
    $privacyClause = "";
    if ($filter === 'private') {
        $privacyClause = "AND is_private = 1";
    } elseif ($filter === 'public') {
        $privacyClause = "AND is_private = 0";
    }

    $sourceClause = "";
    if ($sourceFilter === 'user') {
        $sourceClause = "AND source = 'user'";
    } elseif ($sourceFilter === 'api') {
        $sourceClause = "AND source = 'api'";
    }

    $sql = "SELECT id, title, content, author_id, is_private, publication_date, source
            FROM articles
            WHERE 1=1 {$privacyClause} {$sourceClause}
            ORDER BY publication_date DESC
            LIMIT :limit";

    $stmt = $conn->prepare($sql);
    // Removed the user ID binding
    $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
    $stmt->execute();
    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return [
        'status' => !empty($articles),
        'articles' => $articles,
        'message' => !empty($articles) ? "Articles fetched successfully" : "No articles found"
    ];
}

function getArticleById($articleId) {
    $response = array('status' => false, 'message' => '', 'article' => null);

    try {
        $conn = getDatabaseConnection(); // Reuse the database connection function

        // Adjusted SQL statement to also select the 'source' column
        $sql = "SELECT a.id, a.title, a.content, a.publication_date, u.username AS author, a.source
                FROM articles a
                LEFT JOIN users u ON a.author_id = u.id  -- Use LEFT JOIN to handle articles without an associated user
                WHERE a.id = :articleId";

        // Prepare and bind parameters
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':articleId', $articleId, PDO::PARAM_INT);

        // Execute the statement
        $stmt->execute();

        // Check if the article exists
        if ($stmt->rowCount() == 1) {
            $article = $stmt->fetch(PDO::FETCH_ASSOC);
            $response['status'] = true;
            $response['message'] = "Article fetched successfully";
            $response['article'] = $article;
        } else {
            $response['message'] = "Article not found";
        }
    } catch (PDOException $e) {
        // Update response message on error
        $response['message'] = "Database error: " . $e->getMessage();
    }

    return $response;
}


function getCommentsByArticleId($articleId) {
    $response = array('status' => false, 'message' => '', 'comments' => array());

    try {
        $conn = getDatabaseConnection();
        // Adjusted SQL to also check the source of the article
        $sql = "SELECT c.id, c.comment, u.username
                FROM comments c
                JOIN users u ON c.user_id = u.id
                JOIN articles a ON c.article_id = a.id
                WHERE c.article_id = :articleId
                ORDER BY c.id DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':articleId', $articleId, PDO::PARAM_INT);
        $stmt->execute();

        $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($comments)) {
            $response['status'] = true;
            $response['message'] = "Comments fetched successfully";
            $response['comments'] = $comments;
        } else {
            $response['message'] = "No comments found for this article";
        }
    } catch (PDOException $e) {
        $response['message'] = "Database error: " . $e->getMessage();
    }

    return $response;
}


function getRatingsByArticleId($articleId)
{
    $response = array('status' => false, 'message' => '', 'ratings' => array());

    try {
        $conn = getDatabaseConnection();
        $sql = "SELECT r.rating, u.username
                FROM ratings r
                JOIN users u ON r.user_id = u.id
                WHERE r.article_id = :articleId";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':articleId', $articleId, PDO::PARAM_INT);
        $stmt->execute();

        $ratings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($ratings)) {
            $response['status'] = true;
            $response['message'] = "Ratings fetched successfully";
            $response['ratings'] = $ratings;
        } else {
            $response['message'] = "No ratings found for this article";
        }
    } catch (PDOException $e) {
        $response['message'] = "Database error: " . $e->getMessage();
    }

    return $response;
}

function getAverageRatingByArticleId($articleId)
{
    $response = array('status' => false, 'averageRating' => null, 'message' => '');

    try {
        $conn = getDatabaseConnection();
        $sql = "SELECT AVG(rating) AS averageRating FROM ratings WHERE article_id = :articleId";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':articleId', $articleId, PDO::PARAM_INT);
        $stmt->execute();

        // Check if the query was successful
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $averageRating = $row['averageRating'];

            if ($averageRating !== null) {
                $response['status'] = true;
                $response['averageRating'] = round($averageRating, 2); // Round to 2 decimal places
                $response['message'] = "Average rating fetched successfully";
            } else {
                $response['message'] = "No ratings found for this article";
            }
        } else {
            $response['message'] = "No ratings found for this article";
        }
    } catch (PDOException $e) {
        $response['message'] = "Database error: " . $e->getMessage();
    }

    return $response;
}
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function submitComment($articleId, $content, $commenterUsername) {
    $response = ['status' => false, 'message' => ''];

    try {
        $conn = getDatabaseConnection();

        // Insert the comment into the comments table
        $commentSql = "INSERT INTO comments (article_id, user_id, comment)
                       SELECT :article_id, users.id, :comment
                       FROM users
                       WHERE users.username = :commenterUsername";
        $commentStmt = $conn->prepare($commentSql);
        $commentStmt->bindParam(':article_id', $articleId, PDO::PARAM_INT);
        $commentStmt->bindParam(':comment', $content, PDO::PARAM_STR);
        $commentStmt->bindParam(':commenterUsername', $commenterUsername, PDO::PARAM_STR);
        $commentStmt->execute();

        if ($commentStmt->rowCount() > 0) {
            $response['status'] = true;
            $response['message'] = "Comment added successfully.";

            // Fetch the article author's email address and article title
            $emailSql = "SELECT users.email, articles.title
                         FROM articles
                         JOIN users ON articles.author_id = users.id
                         WHERE articles.id = :article_id";
            $emailStmt = $conn->prepare($emailSql);
            $emailStmt->bindParam(':article_id', $articleId, PDO::PARAM_INT);
            $emailStmt->execute();
            $authorInfo = $emailStmt->fetch(PDO::FETCH_ASSOC);

            if ($authorInfo) {
                $mail = new PHPMailer(true); // Passing `true` enables exceptions

                // Configure PHPMailer settings
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'earlybird6900@gmail.com';
                $mail->Password = 'mtxekiuhxgpllxqu';
                $mail->SMTPSecure = 'ssl';
                $mail->Port = 465;
                $mail->setFrom('earlybird6900@gmail.com', 'EarlyBird Platform');
                $mail->addAddress($authorInfo['email']);
                $mail->isHTML(true);
                $mail->Subject = "New comment on your article: " . $authorInfo['title'];
                $mail->Body = "Hi, a new comment has been posted on your article titled '" . $authorInfo['title'] . "':<br><br>" . nl2br(htmlspecialchars($content));

                if (!$mail->send()) {
                    $response['emailStatus'] = "Mailer Error: " . $mail->ErrorInfo;
                } else {
                    $response['emailStatus'] = "Email sent successfully to the author.";
                }
            }
        } else {
            $response['message'] = "Failed to add the comment.";
        }
    } catch (PDOException $e) {
        $response['message'] = "Database error: " . $e->getMessage();
    } catch (Exception $e) {
        $response['message'] = "Mailer error: " . $mail->ErrorInfo;
    }

    return $response;
}


function fetchAllTopics()
{
    $conn = getDatabaseConnection();
    $sql = "SELECT * FROM topics";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function saveUserPreference($username, $topicId)
{
    $conn = getDatabaseConnection();

    // Check if the preference already exists to avoid duplicates
    $checkSql = "SELECT * FROM user_preferences WHERE username = :username AND topic_id = :topicId LIMIT 1";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bindParam(':username', $username);
    $checkStmt->bindParam(':topicId', $topicId);
    $checkStmt->execute();

    if ($checkStmt->rowCount() == 0) {
        // If the preference does not exist, insert it
        $sql = "INSERT INTO user_preferences (username, topic_id) VALUES (:username, :topicId)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':topicId', $topicId);
        $stmt->execute();
    }
}

function clearUserPreferences($username)
{
    $conn = getDatabaseConnection();

    // Delete preferences directly by username
    $sql = "DELETE FROM user_preferences WHERE username = :username";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':username', $username);
    $stmt->execute();

    return "Your preferences have been cleared.";
}

function fetchUserPreferences($username)
{
    $conn = getDatabaseConnection();

    // Fetch preferences based directly on the username
    $sql = "SELECT topic_id FROM user_preferences WHERE username = :username";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_COLUMN, 0); // Fetching as a simple array of topic IDs
}


function setArticlePrivate($articleId, $username)
{
    $response = ['status' => false, 'message' => ''];
    try {
        $conn = getDatabaseConnection();
        $sql = "UPDATE articles SET is_private = 1 WHERE id = :article_id AND author_id = (SELECT id FROM users WHERE username = :username)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':article_id', $articleId, PDO::PARAM_INT);
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $response['status'] = true;
            $response['message'] = "Article set to private successfully.";
        } else {
            $response['message'] = "Failed to update article privacy or article not found.";
        }
    } catch (PDOException $e) {
        $response['message'] = "Database error: " . $e->getMessage();
    }
    return $response;
}

function setArticlePublic($articleId, $username)
{
    $response = ['status' => false, 'message' => ''];
    try {
        $conn = getDatabaseConnection();
        $sql = "UPDATE articles SET is_private = 0 WHERE id = :article_id AND author_id = (SELECT id FROM users WHERE username = :username)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':article_id', $articleId, PDO::PARAM_INT);
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $response['status'] = true;
            $response['message'] = "Article set to public successfully.";
        } else {
            $response['message'] = "Failed to update article privacy or article not found.";
        }
    } catch (PDOException $e) {
        $response['message'] = "Database error: " . $e->getMessage();
    }
    return $response;
}

function SendArticle($recipientEmail, $articleTitle, $articleContent, $articleUrl = null)
{
    $response = ['status' => false, 'message' => ''];

    try {
        $mail = new PHPMailer(true); // Create instance with exceptions enabled

        // The existing PHPMailer settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'earlybird6900@gmail.com'; // The SMTP username
        $mail->Password = 'mtxekiuhxgpllxqu'; // The SMTP password
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;
        $mail->setFrom('earlybird6900@gmail.com', 'EarlyBird Platform');

        // Add recipient
        $mail->addAddress($recipientEmail);

        // Set email format to HTML
        $mail->isHTML(true);

        // Email subject
        $mail->Subject = 'Interesting Article for You!';

        // Email body content
        $bodyContent = '<h1>' . htmlspecialchars($articleTitle) . '</h1>';
        $bodyContent .= '<p>' . nl2br(htmlspecialchars($articleContent)) . '</p>';
        if ($articleUrl) {
            $bodyContent .= 'Read more at: <a href="' . htmlspecialchars($articleUrl) . '">' . htmlspecialchars($articleUrl) . '</a>';
        }
        $mail->Body = $bodyContent;

        // Attempt to send the email
        $mail->send();
        $response['status'] = true;
        $response['message'] = 'Article has been sent successfully.';
    } catch (Exception $e) {
        $response['message'] = 'Failed to send the article. Mailer Error: ' . $mail->ErrorInfo;
    }

    return $response;
}

function addCurrencyToUserByUsername($username, $amount)
{
    $response = array('status' => false, 'message' => '');

    try {
        $conn = getDatabaseConnection(); // Reuse the database connection function

        // Begin transaction
        $conn->beginTransaction();

        // First, get the user's id
        $userSql = "SELECT id FROM users WHERE username = :username LIMIT 1";
        $userStmt = $conn->prepare($userSql);
        $userStmt->bindParam(':username', $username);
        $userStmt->execute();
        $user = $userStmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // SQL statement to add currency to the user's EBP
            $sql = "UPDATE users SET EBP = EBP + :amount WHERE id = :id";

            // Prepare and bind parameters
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':amount', $amount, PDO::PARAM_INT);
            $stmt->bindParam(':id', $user['id'], PDO::PARAM_INT);

            // Execute the statement
            $stmt->execute();

            // Check if the update was successful
            if ($stmt->rowCount() > 0) {
                $response['status'] = true;
                $response['message'] = "EBP added successfully";
                $conn->commit(); // Commit the transaction
            } else {
                $conn->rollBack(); // Rollback the transaction on failure
                $response['message'] = "No EBP was added. User not found or amount is zero.";
            }
        } else {
            $conn->rollBack(); // Rollback the transaction if user not found
            $response['message'] = "User not found.";
        }
    } catch (PDOException $e) {
        $conn->rollBack(); // Rollback the transaction on error
        $response['message'] = "Database error: " . $e->getMessage();
    }

    return $response;
}

function fetchUserEBP($username)
{
    $conn = getDatabaseConnection();
    $sql = "SELECT EBP FROM users WHERE username = :username LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    return $user ? $user['EBP'] : 0;
}
