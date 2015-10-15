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

$app->get('/', function() use ($app) {
  $sql = "SELECT * FROM recipes";
  $conn = $app['pdo']->prepare($sql);
  $conn->execute();

  $recipes = [];
  while ($row = $conn->fetch(PDO::FETCH_ASSOC)) {
    $row['rating_avg'] = round(($row['rating_ben'] + $row['rating_hannah']) / 2, 1);
    $recipes[] = $row;
  }

  return $app['twig']->render('index.twig', [
    'recipes' => $recipes,
  ]);
});

$app->get('/view/{id}', function($id) use ($app) {
  $sql = "SELECT * FROM recipes WHERE id = ?";
  $conn = $app['pdo']->prepare($sql);
  $conn->execute([$id]);

  $recipe = $conn->fetch(PDO::FETCH_ASSOC);

  return $app['twig']->render('view.twig', [
      'recipe' => $recipe,
  ]);
});

$app->get('/edit/{id}', function($id) use ($app) {
  $sql = "SELECT * FROM recipes WHERE id = ?";
  $conn = $app['pdo']->prepare($sql);
  $conn->execute([$id]);

  $recipe = $conn->fetch(PDO::FETCH_ASSOC);

  return $app['twig']->render('form.twig', [
      'recipe' => $recipe,
  ]);
});

$app->get('/new', function() use($app) {
  return $app['twig']->render('form.twig');
});

$app->post('/save', function(Request $request) use($app) {
  $sql = "INSERT INTO recipes (url, title, image_url, ingredients, directions, date, rating_ben, rating_hannah)
            VALUES (:url, :title, :image_url, :ingredients, :directions, :date, :rating_ben, :rating_hannah)";

  $variables = [
    ':url' => $request->get('url'),
    ':title' => $request->get('title'),
    ':image_url' => $request->get('image_url'),
    ':ingredients' => $request->get('ingredients'),
    ':directions' => $request->get('directions'),
    ':date' => $request->get('date'),
    ':rating_ben' => $request->get('rating_ben'),
    ':rating_hannah' => $request->get('rating_hannah'),
  ];

  $app['monolog']->addDebug('!!!!! ' . $request->get('url'));

  $conn = $app['pdo']->prepare($sql);
  $conn->execute($variables);

  return $app->redirect('/');
});

$app->run();
