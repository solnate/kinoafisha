<?php
$request = json_decode(file_get_contents('php://input'), true);
if(empty($request)) header('Location: /kinoafisha/login');
file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/core/data/auth', $request);
$auth = json_decode($request['data'], true);
$connection = mysqli_connect($auth['host'], $auth['user'], $auth['password'], $auth['database']);
$query = 'create table roles
(
    id   int auto_increment
        primary key,
    role varchar(50) not null
);

create table users
(
    id   int auto_increment
        primary key,
    name varchar(50) not null
);

create table users_roles
(
    id       int auto_increment
        primary key,
    id_users int null,
    id_roles int null,
    constraint id_roles
        foreign key (id_roles) references roles (id),
    constraint id_users
        foreign key (id_users) references users (id)
);';
$result = $connection->multi_query($query);


