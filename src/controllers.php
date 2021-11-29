<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * TASK 5: for paginating the todos list
 */
DEFINE('NUMBER_PER_PAGE', 2);

$app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
    $twig->addGlobal('user', $app['session']->get('user'));

    return $twig;
}));


$app->get('/', function () use ($app) {
    // fix README.md path issue
    return $app['twig']->render('index.html', [
        'readme' => file_get_contents('../README.md'),
    ]);
});


$app->match('/login', function (Request $request) use ($app) {
    $username = $request->get('username');
    $password = $request->get('password');

    if ($username) {
        $sql = "SELECT * FROM users WHERE username = '$username' and password = '$password'";
        $user = $app['db']->fetchAssoc($sql);

        if ($user){
            $app['session']->set('user', $user);
            return $app->redirect('/todo');
        }
    }

    return $app['twig']->render('login.html', array());
});


$app->get('/logout', function () use ($app) {
    $app['session']->set('user', null);
    return $app->redirect('/');
});


$app->get('/todo/{id}', function (Request $request, $id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    if ($id){
        $sql = "SELECT * FROM todos WHERE id = '$id'";
        $todo = $app['db']->fetchAssoc($sql);

        return $app['twig']->render('todo.html', [
            'todo' => $todo,
        ]);
    } else {
        /**
         * TASK 5: limit data return for each page
         * get current page and calculate the query record offset
         */
        $current_page = $request->get('page');
        // not using PHP7 feature here in case the program run under PHP5.3
        // default to page 1
        $current_page = ($current_page) ? $current_page : 1;
        $offset = ($current_page-1) * NUMBER_PER_PAGE ;
        
        $sql = "SELECT SQL_CALC_FOUND_ROWS * "
                . " FROM todos "
                . " WHERE user_id = '${user['id']}' "
                . " LIMIT " . NUMBER_PER_PAGE
                . " OFFSET " . $offset;
        $todos = $app['db']->fetchAll($sql);
        
        /**
         * TASK 5: get total number of rows for pagination generation
         */
        $total = $app['db']->fetchOne('SELECT FOUND_ROWS()');

        return $app['twig']->render('todos.html', [
            'todos' => $todos, 
            'current_page'=>$current_page,
            'pages'=> ceil($total/NUMBER_PER_PAGE)
        ]);
    }
})
->value('id', null);

/**
 * TASK 3: view todo in JSON format
 */
$app->get('/todo/json/{id}', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    if ($id){
        $sql = "SELECT * FROM todos WHERE id = '$id'";
        $todo = $app['db']->fetchAssoc($sql);

        // reuse the same template to output JSON format 
        return $app['twig']->render('todo.html', [
            'todo' => $todo,'json'=> json_encode($todo)
        ]);
    }
})
->value('id', null);


$app->post('/todo/add', function (Request $request) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $user_id = $user['id'];
    //SECURITY UPDATE: strip_tags for user entered string
    $description = strip_tags($request->get('description'));

    /**
     * Use parameter binding way to run SQL to avoid SQL injection.
     * Especially for the user input string field
     */
    $sql = "INSERT INTO todos (user_id, description) VALUES (?, ?)";
    $result = $app['db']->executeUpdate($sql, [$user_id, $description]);

    if ($result ){
        // TASK 4: show ADD confirmation message
        $app['session']->getFlashBag()->add('message', 'A new TODO <strong>"'.$description.'"</strong> has been added.');
    }else{
        // TASK 4: show ADD failed message
        $app['session']->getFlashBag()->add('error', 'The new TODO <strong>"'.$description.'"</strong> cannot be added.');
    }
    
    return $app->redirect('/todo');
});


$app->match('/todo/delete/{id}', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    /**
     * Use parameter binding way to run SQL to avoid SQL injection.
     * Always check that todo belongs to the current login user
     */
    $sql = "DELETE FROM todos "
            . " WHERE id = ? "
            . " AND user_id = ?";
    $result = $app['db']->executeUpdate($sql, [$id, $user['id']]);

    if ($result ){
        // TASK 4: show DELETE confirmation message
        $app['session']->getFlashBag()->add('message', '<strong>#'.$id .'</strong> TODO has been deleted.');
    }else{
        // TASK 4: show DELETE failed message
        $app['session']->getFlashBag()->add('error', '<strong>#'.$id .'</strong> TODO cannot be deleted.');
    }
    return $app->redirect('/todo');
});

/**
 * TASK 2: allow to set todo as completed
 */
$app->match('/todo/completed/{id}', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }
    
    /**
     * Use parameter binding way to run SQL to avoid SQL injection.
     * Always check that todo belongs to the current login user
     */
    $sql = "UPDATE todos "
            . " SET completed = 1 "
            . " WHERE id = ? "
            . " AND user_id = ?";
    $app['db']->executeUpdate($sql, [$id, $user['id']]);

    return $app->redirect('/todo');
});

