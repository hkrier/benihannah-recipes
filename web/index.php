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
    'twig.path' => __DIR__ . '/views',
));

$dbopts = parse_url(getenv('DATABASE_URL'));
$app->register(new Herrera\Pdo\PdoServiceProvider(),
    array(
        'pdo.dsn' => 'pgsql:dbname=' . ltrim($dbopts["path"], '/') . ';host=' . $dbopts["host"],
        'pdo.port' => $dbopts["port"],
        'pdo.username' => $dbopts["user"],
        'pdo.password' => $dbopts["pass"]
    )
);

// Our web handlers

$app->get('/', function () use ($app) {
    $sql = "SELECT * FROM recipes";
    $st = $app['pdo']->prepare($sql);
    $st->execute();

    $recipes = [];
    while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
        $row['rating_avg'] = round(($row['rating_ben'] + $row['rating_hannah']) / 2, 1);
        $recipes[] = $row;
    }

    usort($recipes, function($a, $b) {
        if ($a['rating_avg'] == $b['rating_avg']) {
            return 0;
        }
        return ($a['rating_avg'] > $b['rating_avg']) ? -1 : 1;
    });

    return $app['twig']->render('index.twig', [
        'recipes' => $recipes,
    ]);
});

$app->get('/view/{id}', function ($id) use ($app) {
    $sql = "SELECT * FROM recipes WHERE id = ?";
    $st = $app['pdo']->prepare($sql);
    $st->execute([$id]);

    $recipe = $st->fetch(PDO::FETCH_ASSOC);

    return $app['twig']->render('view.twig', [
        'recipe' => $recipe,
    ]);
});

$app->get('/edit/{id}', function ($id) use ($app) {
    $sql = "SELECT * FROM recipes WHERE id = ?";
    $st = $app['pdo']->prepare($sql);
    $st->execute([$id]);

    $recipe = $st->fetch(PDO::FETCH_ASSOC);

    return $app['twig']->render('form.twig', [
        'recipe' => $recipe,
    ]);
});

$app->get('/new', function () use ($app) {
    return $app['twig']->render('form.twig', [
        'recipe' => FALSE,
    ]);
});

$app->post('/create', function (Request $request) use ($app) {
    $sql = "INSERT INTO recipes (url, title, image_url, ingredients, directions, date, rating_ben, rating_hannah)
            VALUES (:url, :title, :image_url, :ingredients, :directions, :date, :rating_ben, :rating_hannah)";

    $variables = [
        ':url' => $request->get('url'),
        ':title' => $request->get('title'),
        ':image_url' => $request->get('image_url'),
        ':ingredients' => $request->get('ingredients'),
        ':directions' => $request->get('directions'),
        ':date' => strtotime($request->get('date')),
        ':rating_ben' => $request->get('rating_ben'),
        ':rating_hannah' => $request->get('rating_hannah'),
    ];

    $st = $app['pdo']->prepare($sql);

    if ($st->execute($variables)) {
        // Redirect on success
        $sql = "SELECT MAX(id) FROM recipes";
        $st = $app['pdo']->prepare($sql);
        $st->execute();
        $id = $st->fetch(PDO::FETCH_NUM);

        return $app->redirect('/view/' . $id[0]);
    }
});

$app->post('/update', function (Request $request) use ($app) {
    $id = $request->get('id');
    $sql = "UPDATE recipes SET (url, title, image_url, ingredients, directions, date, rating_ben, rating_hannah)
            = (:url, :title, :image_url, :ingredients, :directions, :date, :rating_ben, :rating_hannah)
          WHERE id = :id";

    $variables = [
        ':url' => $request->get('url'),
        ':title' => $request->get('title'),
        ':image_url' => $request->get('image_url'),
        ':ingredients' => $request->get('ingredients'),
        ':directions' => $request->get('directions'),
        ':date' => strtotime($request->get('date')),
        ':rating_ben' => $request->get('rating_ben'),
        ':rating_hannah' => $request->get('rating_hannah'),
        ':id' => $id,
    ];

    $st = $app['pdo']->prepare($sql);
    $st->execute($variables);

    return $app->redirect('/view/' . $id);
});

$app->run();
