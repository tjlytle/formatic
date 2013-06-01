<?php
//some path stuff
$templates = realpath(__DIR__ . '/../templates');
$event = json_decode(file_get_contents($templates . '/event.json'), true);

$names = array('buddy', 'champ', 'chief', 'boss', 'bud', 'dude');
$name = $names[array_rand($names)];

require_once '../vendor/autoload.php';
$app = new \Slim\Slim();

$app->get('/form/event/:id', function ($id = null) use ($app) {
    if(!file_exists('/tmp/'.$id)){
        $app->response()->status(404);
        return;
    }
    
    $event = json_decode(file_get_contents('/tmp/'.$id));
    
    //spit out data
    $app->response()->body(json_encode($event));
});

$app->post('/form/event/', function () use ($app, $event) {
    //merge data
    $post = $app->request()->post('specialEventApplication');
    if($post AND is_array($post)){
        $event['specialEventApplication'] = array_merge($event['specialEventApplication'], $post);
    }

    //save data (what?)
    $id = md5(uniqid(time(), true));
    $event['id'] = $id;
    
    file_put_contents('/tmp/'.$id, json_encode($event));
    
    //spit out data
    $app->response()->body(json_encode($event));
});

$app->put('/form/event/:id', function ($id) use ($app, $name, $event) {
    if(!file_exists('/tmp/'.$id)){
        $app->response()->status(404);
        return;
    }    
    
    $event['id'] = $id;
    
    //merge data
    $post = $app->request()->post('specialEventApplication');
    if($post AND is_array($post)){
        $event['specialEventApplication'] = array_merge($event['specialEventApplication'], $post);
    }
    
    file_put_contents('/tmp/'.$id, json_encode($event));
    
    //spit out data
    $app->response()->body(json_encode($event));
});


$app->run();