<?php
require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use DI\Container;

//подключение flash
session_start();
$container = new Container();
$container->set('renderer', function () {
    return new \Slim\Views\PhpRenderer(__DIR__ . '/../templates');
});
$container->set('flash', function () {
    return new \Slim\Flash\Messages();
});
AppFactory::setContainer($container);
$app = AppFactory::create();
//завершение подключения flash

//Подключение контейнера для шаблонов:
$container = new Container();
$container->set('renderer', function () {
    // Параметром передается базовая директория, в которой будут храниться шаблоны
    return new \Slim\Views\PhpRenderer(__DIR__ . '/../templates');
});
$app = AppFactory::createFromContainer($container);
$app->addErrorMiddleware(true, true, true);

//Подключение переопределения метода:
use Slim\Middleware\MethodOverrideMiddleware;

$app = AppFactory::create();
$app->add(MethodOverrideMiddleware::class);


//Первая страница:
$app->get('/', function ($request, $response) {
    $response->getBody()->write('Welcome to Slim!');
    return $response;
})->setName('main');


//Список учеников(index). Надо бы использовать пагинацию, но я пока без нее:
$app->get('/students', callable: function ($request, $response) {
    $students = array(
        array('id' => 1, 'name' => 'Sophia'),
        array('id' => 2, 'name' => 'Anna'),
        array('id' => 3, 'name' => 'Vika'),
        array('id' => 4, 'name' => 'Mark')
    );
    $params= ['students'=>$students];
    return $this->get('renderer')->render($response, '/users/index_students.phtml', $params);
})->setName('students');


//Отображение отдельного ученика(show):
$app->get('/students/{id}', function($request, $response, array $args) {
    $students = array(
        array('id' => 1, 'name' => 'Sophia'),
        array('id' => 2, 'name' => 'Anna'),
        array('id' => 3, 'name' => 'Vika'),
        array('id' => 4, 'name' => 'Mark')
    );
    $id=(int)$args['id'];
    foreach ($students as $student){
        if ($id === $student['id']) {
            $params=['student'=>$student, 'id'=>$id];
            return $this->get('renderer')->render($response, 'users/show_students.phtml',$params);
        }
    }
    return $response->withStatus(404) ->write('Page not found');
})->setName('student');

//Регистрация нового ученика(форма)(new). Требуются два обработчика и форма. Но так как нужен репозиторий
//или база данных, я не делала. Код смотри в тетради.

//Обновление данных. Требуются два обработчика и форма. Но так как нужен репозиторий
//или база данных, я не делала. Код смотри в тетради.









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

$users = ['mike', 'mishel', 'adel', 'kamila', 'karina', 'mickael','adelaida'];
$app->get('/users', function ($request, $response) use ($users) {
    $name = $request->getQueryParam('name');
    $filtered_array = [];
    foreach ($users as $user) {
        if (str_contains($user, (string)$name)) {
           $filtered_array[] = $user;
        }
    }
    $params = ['name' => $name, 'filtered_array' => $filtered_array];
    return $this->get('renderer')->render($response, "users/index.phtml", $params);
});

$app->get('/users/new', function($request, $response) {//обработчик выводит пустую форму
    $params = [];
    return $this->get('renderer')->render($response, 'users/user_form.phtml', $params);
});


$app->get('/users/{id}', function ($request, $response, $args) {
    $params = [
        'id' => $args['id'],
        'nickname' => 'user-' . $args['id']
    ];
    // Указанный путь считается относительно базовой директории для шаблонов, заданной на этапе конфигурации
    // $this доступен внутри анонимной функции благодаря https://php.net/manual/ru/closure.bindto.php
    // $this в Slim это контейнер зависимостей
    return $this->get('renderer')->render($response, 'users/show.phtml', $params);
});




$app->run();