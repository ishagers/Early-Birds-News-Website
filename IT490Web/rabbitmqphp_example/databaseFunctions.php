<?php

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

function fetchRecentArticles($limit = 10)
{
    $response = array('status' => false, 'articles' => array(), 'message' => '');

    try {
        $conn = getDatabaseConnection();
        // Include the `id` in the SELECT statement
        $sql = "SELECT id, title, content, author_id, publication_date FROM articles ORDER BY publication_date DESC LIMIT :limit";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($articles)) {
            $response['status'] = true;
            $response['articles'] = $articles;
            $response['message'] = "Articles fetched successfully";
        } else {
            $response['message'] = "No articles found";
        }
    } catch (PDOException $e) {
        $response['message'] = "Database error: " . $e->getMessage();
    }

    return $response;
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
        $sql = "SELECT c.id, c.comment, u.username, c.created_at
                FROM comments c
                JOIN users u ON c.user_id = u.id
                WHERE c.article_id = :articleId
                ORDER BY c.created_at DESC";
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
