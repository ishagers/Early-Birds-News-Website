<?php
// Assume $article is an associative array representing the article, fetched from our database
if ($article['source'] == 'internal') {
    // Generate a local URL for internal articles
    $articleUrl = "http://localhost/viewArticle.php?id=" . $article['id'];
} else {
    // Use the external URL for articles from NewsAPI
    $articleUrl = $article['external_url'];
}

echo "Share this article: <a href='" . htmlspecialchars($articleUrl) . "'>" . htmlspecialchars($articleUrl) . "</a>";
?>
