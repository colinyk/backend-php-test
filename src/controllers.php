<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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


$app->get('/todo/{id}', function ($id) use ($app) {
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
        $sql = "SELECT * FROM todos WHERE user_id = '${user['id']}'";
        $todos = $app['db']->fetchAll($sql);

        return $app['twig']->render('todos.html', [
            'todos' => $todos,
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
    $description = $request->get('description');

    /**
     * Use parameter binding way to run SQL to avoid SQL injection.
     * Especially for the user input string field
     */
    $sql = "INSERT INTO todos (user_id, description) VALUES (?, ?)";
    $app['db']->executeUpdate($sql, [$user_id, $description]);

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
    $app['db']->executeUpdate($sql, [$id, $user['id']]);

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

