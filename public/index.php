<?php
require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use DI\Container;

$container = new Container();
$container->set('renderer', function () {
    // Параметром передается базовая директория, в которой будут храниться шаблоны
    return new \Slim\Views\PhpRenderer(__DIR__ . '/../templates');
});

$app = AppFactory::createFromContainer($container);
$app->addErrorMiddleware(true, true, true);

$app->get('/', function ($request, $response) {
    $response->getBody()->write('Welcome to Slim!');
    return $response;
    // Благодаря пакету slim/http этот же код можно записать короче
    // return $response->write('Welcome to Slim!');
});

$app->post('/users', function ($request, $response) {
    return $response->withStatus(302);
});

$app->get('/courses/{id}', function ($request, $response, array $args) {
    $id = $args['id'];
    return $response->write("Course id: {$id}");
});
$app->get('/companies/{id}', callable: function($request, $response, array $args){
    $id = (int) $args['id'];
    $companies = [
        ['id' => 4, 'name'=> 'IMA'],
        ['id' => 2, 'name'=> 'Severstal'],
        ['id' => 8, 'name'=> 'Fosagro']
    ];
    foreach ($companies as $elem){
        if ($id === $elem['id']) {
            return $response->write(json_encode($elem));
        }
    }

    return $response->withStatus(404) ->write('Page not found');

});

$app->get('/users/{id}', function ($request, $response, $args) {
    $params = [
        'id' => $args['id'],
        'nickname' => 'user-' . $args['id'],
        'message' => 'Здравствуй, любовь моя -'. $args['id'],
    ];
    // Указанный путь считается относительно базовой директории для шаблонов, заданной на этапе конфигурации
    // $this доступен внутри анонимной функции благодаря https://php.net/manual/ru/closure.bindto.php
    // $this в Slim это контейнер зависимостей
    return $this->get('renderer')->render($response, 'users/show.phtml', $params);
});

$app->run();