const mysql = require('mysql');
require('dotenv').config();
const { publishMessage } = require('./messaging'); // Ensure you've set up messaging.js as described

// Database connection setup using environment variables
const connection = mysql.createConnection({
  host: process.env.localhost,
  user: process.env.IT490DB,
  password: process.env.IT490DB,
  database: process.env.EARLYBIRD
});

// Connect to the database
connection.connect(error => {
  if (error) {
    console.error('Error connecting to the database: ' + error.stack);
    return;
  }
  console.log('Successfully connected to the database.');
});

// Function to insert a new user into the database and publish a message to RabbitMQ
function insertUser({ name, email, username, hash, role }, callback) {
  const query = `INSERT INTO users (name, email, username, hash, role) VALUES (?, ?, ?, ?, ?);`;
  connection.query(query, [name, email, username, hash, role], (error, results) => {
    if (error) {
      callback(error, null);
    } else {
      // Publish a message to RabbitMQ after successful insertion
      const message = `New user added: Username: ${username}, Email: ${email}`;
      publishMessage('userQueue', message).catch(console.error); // Error handling for RabbitMQ
      callback(null, results);
    }
  });
}

// Function to retrieve all users from the database
function getAllUsers(callback) {
  const query = `SELECT * FROM users;`;
  connection.query(query, (error, results) => {
    if (error) {
      callback(error, null);
    } else {
      callback(null, results);
    }
  });
}

module.exports = {
  insertUser,
  getAllUsers
};
