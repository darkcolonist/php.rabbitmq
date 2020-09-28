<?php 
require_once __DIR__ . '/vendor/autoload.php';
use Dotenv\Dotenv;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$dotenv = Dotenv::createImmutable(__DIR__, ".env");
$dotenv->load();

define("RABBITMQ_HOST", getenv('RABBITMQ_HOST'));
define("RABBITMQ_PORT", getenv('RABBITMQ_PORT'));
define("RABBITMQ_USERNAME", getenv('RABBITMQ_USERNAME'));
define("RABBITMQ_PASSWORD", getenv('RABBITMQ_PASSWORD'));
define("RABBITMQ_QUEUE_NAME", getenv('RABBITMQ_QUEUE_NAME'));

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

$i = 0;
while(true){
  $jobArray = array(
      'id' => $i++,
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