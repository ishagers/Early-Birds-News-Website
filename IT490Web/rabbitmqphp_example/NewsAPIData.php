<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Latest News</title>
    <style>
         body {

            font-family: Arial, sans-serif;

            margin: 0;

            padding: 20px;

            background-color: #f4f4f4;

        }

        .news-container {

            max-width: 800px;

            margin: auto;

            background-color: #fff;

            padding: 20px;

            box-shadow: 0 2px 4px rgba(0,0,0,0.1);

        }

        .news-article {

            margin-bottom: 20px;

            padding-bottom: 20px;

            border-bottom: 1px solid #eee;

        }

        .news-article:last-child {

            border: none;

        }

        .news-article h2 {

            font-size: 20px;

            margin: 0 0 10px;

        }

        .news-article p {

            font-size: 16px;

        }

        .news-article a {

            display: inline-block;

            margin-top: 10px;

            color: #333;

            text-decoration: none;

        }

        .news-article a:hover {

            text-decoration: underline;

        }

        .news-image {

            max-width: 100%;

            height: auto;

        }
    </style>
</head>
<body>

<div class="news-container" id="newsContainer">
    <h1>Latest News</h1>
    <!-- News articles will be loaded here --> 
   
</div>

<script>
function fetchNews() {
    fetch('fetch_news.php')
        .then(response => response.text())
        .then(html => {
            const newsContainer = document.getElementById('newsContainer');
            newsContainer.innerHTML = html;
        })
        .catch(error => {
            console.error('Error fetching news:', error);
        });
}

// Fetch news immediately on page load
fetchNews();

// Fetch news every 5 minutes
setInterval(fetchNews, 300000); // 300000 milliseconds = 5 minutes
</script>

</body>
</html>

