<?php

use Phalcon\Loader;
use Phalcon\Mvc\Micro;
use Phalcon\Di\FactoryDefault;
use Phalcon\Http\Response;

$loader = new Loader();
$loader->registerNamespaces(
    [
        'MyApp\Models' => __DIR__ . '/models/',
    ]
);

require_once  __DIR__ . "/vendor/autoload.php";

$loader->register();

$container = new FactoryDefault();

$container->set(
    'manager',
    function () {
        return new Phalcon\Mvc\Collection\Manager();
    }
);
$container->set(
    'mongo',
    function () {
        $mongo = new MongoDB\Client(
            "mongodb+srv://root:Password123@mycluster.qjf75n3.mongodb.net/?retryWrites=true&w=majority"
        );

        return $mongo->api;
    },
    true
);

$app = new Micro($container);

$app->get(
    '/api/product',
    function () {
        $movies = $this->mongo->data->find();

        $data = [];

        foreach ($movies as $movie) {
            $data[] = [
                'id'   => $movie->_id,
                'name' => $movie->name,
                'type' => $movie->type,
                'year' => $movie->year,
            ];
        }

        echo json_encode($data);
    }
);

$app->get(
    '/api/product/search/{name}',
    function ($name) {
        $movies = $this->mongo->data->find(array('name' => $name));
        $data = [];
        foreach ($movies as $movie) {
            $data[] = [
                'id'   => $movie->_id,
                'name' => $movie->name,
                'type' => $movie->type,
                'year' => $movie->year,
            ];
        }

        echo json_encode($data);
    }
);

$app->get(
    '/api/product/{id}',
    function ($id) {
        $movies = $this->mongo->data->findOne(array('_id' => new MongoDB\BSON\ObjectId($id)));
        print_r(json_encode($movies));
    }
);

$app->post(
    '/api/product',
    function () use ($app) {
        $robot = $app->request->getJsonRawBody();
        $this->mongo->data->insertOne($robot);
        echo "Inserted Successfully";
    }
);


$app->put(
    '/api/product/{id}',
    function ($id) use ($app) {
        $robot = $app->request->getJsonRawBody();
        $this->mongo->data->updateOne(array("_id" => new MongoDB\BSON\ObjectId($id)), array('$set' => $robot));
        echo "Updation Successful";
    }
);

$app->delete(
    '/api/product/{id}',
    function ($id) {
        $this->mongo->data->deleteOne(array('_id' => new MongoDB\BSON\ObjectId($id)));
        echo "Deletion Successful";
    }
);

$app->handle(
    $_SERVER["REQUEST_URI"]
);
