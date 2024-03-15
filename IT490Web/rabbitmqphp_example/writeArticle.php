<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require('session.php'); // Adjust the path as necessary
checkLogin(); // Call the checkLogin function to ensure the user is logged in

if (!empty($_POST['title']) && !empty($_POST['content'])) {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $author = $_SESSION['username'];

    //Setting array and its values to send to RabbitMQ
    $queryValues = array();

    $queryValues['type'] = 'create_article';
    $queryValues['title'] = $title;
    $queryValues['content'] = $content;
    $queryValues['author'] = $author;

    //Printing Array and executing SQL Publisher function
    //print_r($queryValues);
    $result = publisher($queryValues);

    //If returned 0, it means it was pushed to the database. Otherwise, echo error
    if ($result == 0) {
        // Use JavaScript for redirect to ensure the alert is shown before redirecting
        echo "<script>alert('Article Successfully Saved'); window.location.href = 'mainMenu.php';</script>";
        exit();
    } else {
        echo "<script>alert('Error'); window.location.href='../index.php';</script>";
        exit();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Early Bird Articles - Create Article</title>
    <link rel="stylesheet" href="../routes/menuStyles.css" />
</head>
<body>
    <div class="header">
        <h1>Early Bird Articles</h1>
        <div class="user-info">
            Logged in as: <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>
        </div>
    </div>
    <div class="nav-bar">
        <ul>
            <li><a href="article-history.php">Article History</a></li>
            <li><a href="keyword-settings.php">Keyword Settings</a></li>
            <li><a href="account-settings.php">Account Settings</a></li>
            <li><a href="mainMenu.php">Home</a></li>
        </ul>
    </div>

    <div class="article-form">
        <form method="post">
            <label for="title">Article Title:</label><br />
            <input type="text" id="title" name="title" required /><br />
            <label for="content">Content:</label><br />
            <textarea id="content" name="content" rows="10" cols="50" required></textarea><br />
            <input type="submit" value="Submit Article" />
        </form>
    </div>

    <div class="logout-button">
            <a href="logout.php">Logout</a>
    </div>
</body>
</html>
