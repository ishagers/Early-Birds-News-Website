<?php


require('session.php'); // Adjust the path as necessary
require('SQLPublish.php');
checkLogin(); // Call the checkLogin function to ensure the user is logged in

ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!empty($_POST['title']) && !empty($_POST['content'])) {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $author = $_SESSION['username'];

    // Setting array and its values to send to RabbitMQ
    $queryValues = array();
    $queryValues['type'] = 'create_article';
    $queryValues['title'] = $title;
    $queryValues['content'] = $content;
    $queryValues['author'] = $author;

    // Executing SQL Publisher function
    $result = publisher($queryValues);

    // Check if 'returnCode' in the result is "0"
    if (isset($result['returnCode']) && $result['returnCode'] === "0") {
        echo "<script>alert('Article Successfully Saved'); window.location.href = 'mainMenu.php';</script>";
        exit();
    } else {
        // You might want to use $result['message'] for a more specific error alert if available
        echo "<script>alert('Error Saving Article'); window.location.href='../index.php';</script>";
        exit();
    }
}

$userSettings = fetchUserSettings($username);  // Ensure this function is implemented to fetch settings
$themeStylePath = '../routes/menuStyles.css';
if ($userSettings['has_dark_mode']) {
    $themeStylePath = 'css/darkModeStyles.css'; // Dark mode style
} elseif ($userSettings['has_alternative_theme']) {
    $themeStylePath = 'css/alternativeThemeStyles.css'; // Alternative theme style
}
if ($userSettings['has_custom_cursor']) {
    // Specify the path to the custom cursor image file
    $customCursorPath = "css/custom-cursor/sharingan-cursor.png";
    echo "<style>body { cursor: url('$customCursorPath'), auto; }</style>";
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Early Bird Articles - Create Article</title>
    <link id="themeStyle" rel="stylesheet" href="<?php echo $themeStylePath; ?>" />
    <?php if ($userSettings['has_custom_cursor']): ?>
        <style>
            body {
                cursor: url('<?php echo $customCursorPath; ?>'), auto;
            }

        </style>
    <?php endif; ?>
</head>
<body class="create-article-page">

    <?php require('nav.php'); ?>

    <div class="main-container">
        <!-- Ensure this div covers all content you want centered -->
        <div class="article-form">
            <form method="post">
                <label for="title">Article Title:</label><br />
                <input type="text" id="title" name="title" required /><br />
                <label for="content">Content:</label><br />
                <textarea id="content" name="content" rows="10" cols="50" required></textarea><br />
                <input type="submit" value="Submit Article" />
            </form>
        </div>
    </div>

    <div class="logout-button">
        <a href="logout.php">Logout</a>
    </div>
</body>
</html>
