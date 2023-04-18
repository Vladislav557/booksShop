<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/database/load_db.php';

$config = include __DIR__ . '/../config/database.php';

use App\Database;
use App\CommonManager;

use Slim\Factory\AppFactory;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

[
    "dsn" => $dsn,
    "user" => $user,
    "password" => $password,
    "options" => $options
] = $config;

$db = new Database($dsn, $user, $password, $options);
$connection = $db->getConnection();

$manager = new CommonManager($connection);

$app = AppFactory::create();
$app->addBodyParsingMiddleware();

$app->get('/authors', function (Request $request, Response $response) use ($manager): Response {
    $body = $manager->toJson($manager->authorsManager->getAllAuthors());
    $response->getBody()->write($body);
    return $response;
});

$app->post('/authors/new-add', function (Request $request, Response $response) use ($manager): Response {
    $fullname = $request->getParsedBody()['fullname'];
    $manager->authorsManager->addNewAuthor($fullname);
    return $response
        ->withHeader('Location', '/authors')
        ->withStatus(302);
});

$app->post('/authors/{id}/remove', function (Request $request, Response $response, array $args) use ($manager): Response {
    $id = $args['id'];
    $manager->authorsManager->removeAuthor($id);
    return $response
        ->withHeader('Location', '/authors')
        ->withStatus(302);
});

$app->post('/authors/{id}/update', function (Request $request, Response $response, array $args) use ($manager): Response {
    $id = $args['id'];
    $fullname = $request->getParsedBody()['fullname'];
    $manager->authorsManager->updateAuthor($id, $fullname);
    return $response
        ->withHeader('Location', '/authors')
        ->withStatus(302);
});

$app->get('/books', function (Request $request, Response $response) use ($manager): Response {
    $body = $manager->toJson($manager->booksManager->getAllBooks());
    $response->getBody()->write($body);
    return $response;
});

$app->get('/authors/{id}/books', function (Request $request, Response $response, array $args) use ($manager): Response {
    $id = $args['id'];
    $body = $manager->toJson($manager->booksManager->getBooksByAuthorId($id));
    $response->getBody()->write($body);
    return $response;
});

$app->get('/books/genres[/{params:.*}]', function (Request $request, Response $response, array $args) use ($manager): Response {
    $genres = explode('+', $args['params']);
    $books = $manager->booksManager->getBooksByGenres($genres);
    $response->getBody()->write($manager->toJson($books));
    return $response;
});

$app->get('/books/period[/start={start:.*}&end={end:.*}]', function (Request $request, Response $response, array $args) use ($manager): Response {
    $startYear = $args['start'];
    $endYear = $args['end'];
    $body = $manager->toJson($manager->booksManager->getBooksByPeriod($startYear, $endYear));
    $response->getBody()->write($body);
    return $response;
});

$app->get('/books/{id}/info', function (Request $request, Response $response, array $args) use ($manager): Response {
    $id = $args['id'];
    $info = $manager->booksManager->getInfo($id);
    $response->getBody()->write($manager->toJson($info));
    return $response;
});

$app->post('/books/new', function (Request $request, Response $response, array $args) use ($manager) {
    $data = $request->getParsedBody();
    [
        'name' => $name,
        'description' => $description,
        'created_year' => $createdYear,
        'author' => $author,
        'genres' => $genres
    ] = $data;

    $coAuthor = $data['coAuthor'] ?? null;

    $genresArray = array_map(fn($genre) => ucfirst(strtolower(trim($genre))), explode(',', $genres));

    $manager->addBook($name, $description, $createdYear, $author, $genresArray, $coAuthor);
    return $response
        ->withHeader('Location', '/books')
        ->withStatus(302);
});

$app->post('/books/{id}/edit', function (Request $request, Response $response, array $args) use ($manager) {
    $id = $args['id'];
    $data = $request->getParsedBody();
    [
        'name' => $name,
        'description' => $description,
        'created_year' => $createdYear,
        'author' => $author,
        'genres' => $genres
    ] = $data;

    $coAuthor = $data['coAuthor'] ?? null;

    $genresArray = array_map(fn($genre) => ucfirst(strtolower(trim($genre))), explode(',', $genres));

    $manager->updateBook($id, $name, $description, $createdYear, $author, $genresArray, $coAuthor);
    return $response
        ->withHeader('Location', '/books')
        ->withStatus(302);
});

$app->run();