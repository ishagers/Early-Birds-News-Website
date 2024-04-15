const nodemailer = require('nodemailer');

async function sendEmailNotification(userEmail, articles) {
    const transporter = nodemailer.createTransport({
        service: 'gmail',
        auth: {
            user: 'your@gmail.com',
            pass: 'password',
        },
    });

    const mailOptions = {
        from: 'your@gmail.com',
        to: userEmail,
        subject: 'Latest Articles',
        text: `Here are the latest articles: ${articles.map(a => a.title).join(', ')}`,
    };

    await transporter.sendMail(mailOptions);
}
