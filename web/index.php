<?php

require('../vendor/autoload.php');

use Symfony\Component\HttpFoundation\Request;

$app = new Silex\Application();
$app['debug'] = true;

// Register the monolog logging service
$app->register(new Silex\Provider\MonologServiceProvider(), array(
  'monolog.logfile' => 'php://stderr',
));

// Register view rendering
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/views',
));

$dbopts = parse_url(getenv('DATABASE_URL'));
$app->register(new Herrera\Pdo\PdoServiceProvider(),
  array(
    'pdo.dsn' => 'pgsql:dbname='.ltrim($dbopts["path"],'/').';host='.$dbopts["host"],
    'pdo.port' => $dbopts["port"],
    'pdo.username' => $dbopts["user"],
    'pdo.password' => $dbopts["pass"]
  )
);

// Our web handlers

$app->get('/', function() use($app) {
  return $app['twig']->render('index.twig');
});

$app->get('/new', function() use($app) {
  return $app['twig']->render('new.twig');
});

$app->post('/save', function(Request $request) use($app) {
  $sql = "INSERT INTO recipes (url, title, image_url ingredients, description, date, rating_ben, rating_hannah)
            VALUES (:url, :title, :image_url, :ingredients, :description, :date, :rating_ben, :rating_hannah)";

  $variables = [
    ':url' => $request->get('url'),
    ':title' => $request->get('title'),
    ':image_url' => $request->get('image_url'),
    ':ingredients' => $request->get('ingredients'),
    ':description' => $request->get('description'),
    ':date' => $request->get('date'),
    ':rating_ben' => $request->get('rating_ben'),
    ':rating_hannah' => $request->get('rating_hannah'),
  ];
  
  $conn = $app['pdo']->prepare($sql);
  $conn->execute($variables);

  return $app->redirect('/');
});

$app->run();
