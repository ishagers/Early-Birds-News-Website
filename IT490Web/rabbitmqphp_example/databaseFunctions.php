<?php
require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'path/to/PHPMailer-master/src/Exception.php';
require 'path/to/PHPMailer-master/src/PHPMailer.php';
require 'path/to/PHPMailer-master/src/SMTP.php';

$mail = new PHPMailer(true); // Passing `true` enables exceptions
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
                WHERE c.article_id = :articleId AND a.source = 'user'
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
                // Prepare and send the email notification
                $mail = new PHPMailer\PHPMailer\PHPMailer();
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'earlybird6900@gmail.com';
                $mail->Password = 'mtxekiuhxgpllxqu'; // Consider using environment variables or another secure method to store credentials
                $mail->SMTPSecure = 'ssl';
                $mail->Port = 465; // Corrected port number
                $mail->setFrom('earlybird6900@gmail.com', 'EarlyBird Platform'); // Optional: Add a second parameter as the name
                $mail->addAddress($authorInfo['email']); // Add recipient
                $mail->isHTML(true); // Set email format to HTML
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
