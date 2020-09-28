<!DOCTYPE html>
<html>
<head>
  <title>my rabbitMQ tester</title>
  <style>
    body{
      font-family: "lucida console";
      background-color: black;
      color: lightgreen;
    }
  </style>
</head>
<body>

</body>
</html>

<?php 
require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

define("RABBITMQ_HOST", $_ENV['RABBITMQ_HOST']);
define("RABBITMQ_PORT", $_ENV['RABBITMQ_PORT']);
define("RABBITMQ_USERNAME", $_ENV['RABBITMQ_USERNAME']);
define("RABBITMQ_PASSWORD", $_ENV['RABBITMQ_PASSWORD']);
define("RABBITMQ_QUEUE_NAME", $_ENV['RABBITMQ_QUEUE_NAME']);

$connection = new AMQPStreamConnection(
  RABBITMQ_HOST, 
  RABBITMQ_PORT, 
  RABBITMQ_USERNAME, 
  RABBITMQ_PASSWORD);
$channel = $connection->channel();

# Create the queue if it does not already exist.
$channel->queue_declare(
    $queue = RABBITMQ_QUEUE_NAME,
    $passive = false,
    $durable = true,
    $exclusive = false,
    $auto_delete = false,
    $nowait = false,
    $arguments = null,
    $ticket = null
);

for ($i=0; $i < 1; $i++) { 
  $jobArray = array(
      'id' => "job-".$i,
      'task' => 'sleep',
      'sleep_period' => rand(0, 3)
  );

  $msg = new \PhpAmqpLib\Message\AMQPMessage(
      json_encode($jobArray, JSON_UNESCAPED_SLASHES),
      array('delivery_mode' => 2) # make message persistent
  );

  $channel->basic_publish($msg, '', RABBITMQ_QUEUE_NAME);
  print 'Job created' . PHP_EOL;
  sleep(1);
}


echo "hello worl";