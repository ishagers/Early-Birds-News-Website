<?php
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

function createArticle($title, $content, $author)
{
    $response = array('status' => false, 'message' => '');

    try {
        $conn = getDatabaseConnection(); // Reuse the database connection function

        // First, get the author_id from the users table using the username
        $userSql = "SELECT id FROM users WHERE username = :username LIMIT 1";
        $userStmt = $conn->prepare($userSql);
        $userStmt->bindParam(':username', $author);
        $userStmt->execute();
        $user = $userStmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $author_id = $user['id']; // Use the fetched author ID

            // SQL statement to insert a new article
            $sql = "INSERT INTO articles (title, content, author_id) VALUES (:title, :content, :author_id)";

            // Prepare and bind parameters
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':content', $content);
            $stmt->bindParam(':author_id', $author_id);

            // Execute the statement
            $stmt->execute();

            // Update response status and message on success
            if ($stmt->rowCount() > 0) {
                $response['status'] = true;
                $response['message'] = "Article created successfully";
            } else {
                // No rows affected
                $response['message'] = "Failed to create article";
            }
        } else {
            $response['message'] = "Author username not found";
        }
    } catch (PDOException $e) {
        // Update response message on error
        $response['message'] = "Error: " . $e->getMessage();
    }

    // Return the response array
    return $response;
}

function fetchUserArticles($username, $limit = 15, $filter = 'all')
{
    $conn = getDatabaseConnection();

    // Start by getting the user ID from the username to query articles by author_id
    $userSql = "SELECT id FROM users WHERE username = :username LIMIT 1";
    $userStmt = $conn->prepare($userSql);
    $userStmt->bindParam(':username', $username);
    $userStmt->execute();
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        return ['status' => false, 'message' => "User not found", 'articles' => []];
    }

    $userId = $user['id'];

    // Adjust the SQL query based on the filter provided for article visibility
    $privacyClause = "";
    if ($filter === 'private') {
        $privacyClause = "AND is_private = 1";
    } elseif ($filter === 'public') {
        $privacyClause = "AND is_private = 0";
    }
    // No clause needed for 'all', as it will fetch both private and public

    $sql = "SELECT id, title, content, author_id, is_private, publication_date
FROM articles
WHERE author_id = :userId {$privacyClause}
ORDER BY publication_date DESC
LIMIT :limit";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return [
        'status' => !empty($articles),
        'articles' => $articles,
        'message' => !empty($articles) ? "Articles fetched successfully" : "No articles found"
    ];
}

function getArticleById($articleId)
{
    $response = array('status' => false, 'message' => '', 'article' => null);

    try {
        $conn = getDatabaseConnection(); // Reuse the database connection function

        // SQL statement to select an article by ID
        $sql = "SELECT a.id, a.title, a.content, a.publication_date, u.username AS author
                FROM articles a
                JOIN users u ON a.author_id = u.id
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

function getCommentsByArticleId($articleId)
{
    $response = array('status' => false, 'message' => '', 'comments' => array());

    try {
        $conn = getDatabaseConnection();
        $sql = "SELECT c.id, c.comment, u.username
            FROM comments c
            JOIN users u ON c.user_id = u.id
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

function submitComment($articleId, $content, $commenterUsername)
{
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
                $to = $authorInfo['email'];
                $subject = "New message from '" . $commenterUsername . "' on your article titled '" . $authorInfo['title'] . "'";
                $message = "Hi, a new comment has been posted on your article titled '" . $authorInfo['title'] . "'. \n\nComment: " . $content;
                // Here, use a valid "From" email address
                $headers = "From: noreply@example.com";

                if (mail($to, $subject, $message, $headers)) {
                    $response['emailStatus'] = "Email sent successfully to the author.";
                } else {
                    $response['emailStatus'] = "Failed to send email to the author.";
                }
            }

            $response['status'] = true;
            $response['message'] = "Comment added successfully.";
        } else {
            $response['message'] = "Failed to add the comment.";
        }
    } catch (PDOException $e) {
        $response['message'] = "Database error: " . $e->getMessage();
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
