const amqp = require('amqplib');

async function connectRabbitMQ() {
  try {
    const connection = await amqp.connect('amqp://10.147.17.178:15672'); // Adjust the URL as needed
    const channel = await connection.createChannel();
    return channel;
  } catch (error) {
    console.error('Error connecting to RabbitMQ:', error);
  }
}

async function publishMessage(queue, message) {
  const channel = await connectRabbitMQ();
  await channel.assertQueue(queue, {
    durable: false
  });
  channel.sendToQueue(queue, Buffer.from(message));
  console.log(`Message sent to ${queue}: ${message}`);
}

module.exports = {
  publishMessage
};
