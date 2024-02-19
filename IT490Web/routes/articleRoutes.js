const express = require('express');
const axios = require('axios');
const authenticateToken = require('../utils/authMiddleware'); // Assume you have implemented this middleware
const Article = require('../models/Article'); // Assume you have an Article model

const router = express.Router();

// Middleware to verify JWT token
router.use(authenticateToken);

router.post('/fetch', async (req, res) => {
    const { keyword } = req.body;
    try {
        const response = await axios.get(`https://newsapi.org/v2/everything?q=${keyword}&apiKey=${process.env.NEWSAPI_KEY}`);
        const articles = response.data.articles;
        for (let article of articles) {
            const existingArticle = await Article.findOne({ url: article.url });
            if (!existingArticle) {
                const newArticle = new Article({ ...article, keyword });
                await newArticle.save();
            }
        }
        res.status(200).send('Articles fetched and stored successfully');
    } catch (error) {
        res.status(400).send(error.message);
    }
});

// Example route to get articles for a user
router.get('/myarticles', async (req, res) => {
    // Implementation depends on how you link articles to users
    // This is just a placeholder function
    try {
        const articles = await Article.find({}); // Modify this to filter articles based on user
        res.status(200).json(articles);
    } catch (error) {
        res.status(400).send(error.message);
    }
});

module.exports = router;
