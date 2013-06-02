<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: X-Requested-With, Content-Type');

// respond to preflights
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    //header('Access-Control-Allow-Headers: X-Requested-With');
    exit;
}

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
    
    $event = json_decode(file_get_contents('/tmp/'.$id), true);

    $format = $app->request()->get('format');
    if('application/pdf' == $app->request()->headers('Accept')){
        $format = 'pdf';        
    }
    
    switch($format){
        case 'pdf':
            $model = new \OpenForm\Model\Event($event);
            file_put_contents('/tmp/'.$id.'.fdf', $model->genFDF());
            
            exec('pdftk ./../test.pdf fill_form /tmp/'.$id.'.fdf output /tmp/'.$id.'.pdf');
            $app->response()->header('Content-Type', 'application/pdf');
            $app->response()->body(file_get_contents('/tmp/'.$id.'.pdf'));
            return;
        default:
            //spit out data
            $app->response()->body(json_encode($event));
    }
});

$app->post('/form/event/', function () use ($app, $event) {
    //merge data
    $post = $app->request()->post('specialEventApplication');
    if($post AND is_array($post)){
        $event['specialEventApplication'] = array_merge_recursive_distinct($event['specialEventApplication'], $post);
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
        $event['specialEventApplication'] = array_merge_recursive_distinct($event['specialEventApplication'], $post);
    }
    
    file_put_contents('/tmp/'.$id, json_encode($event));
    
    //spit out data
    $app->response()->body(json_encode($event));
});

function array_merge_recursive_distinct ( array &$array1, array &$array2 )
{
  $merged = $array1;

  foreach ( $array2 as $key => &$value )
  {
    if ( is_array ( $value ) && isset ( $merged [$key] ) && is_array ( $merged [$key] ) )
    {
      $merged [$key] = array_merge_recursive_distinct ( $merged [$key], $value );
    }
    else
    {
      $merged [$key] = $value;
    }
  }

  return $merged;
}
$app->run();