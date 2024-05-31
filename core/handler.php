<?php
use Lib\Database;
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/database.php';
$request = json_decode(file_get_contents('php://input'), true);
if(empty($request)) header('Location: /kinoafisha');

try {
    $auth = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/core/data/auth');
    $auth = json_decode($auth, true);
    $DB = new Database($auth['host'], $auth['user'], $auth['password'], $auth['database']);
} catch (Exception $e) {
    http_response_code(502);
    die($e->getMessage());
}

$data = json_decode($request['data'], true);
if ($request['action'] == 'create') {
    if($request['db'] == 'users') {
        $result = $DB->create(USER_TABLE, [USER_NAME => $data['name']]);
        foreach ($data['role'] as $role_id)
            $DB->create(
                CROSS_TABLE,
                [CROSS_TABLE_USER_NAME => $result['id'], CROSS_TABLE_ROLE_NAME => $role_id]);
    }
    else{
        $result = $DB->create(ROLE_TABLE, $data);
    }
}
else if ($request['action'] == 'delete') {
    if($request['db'] == 'users') {
        $DB->delete(CROSS_TABLE, CROSS_TABLE_USER_NAME, $data['id']);
        $DB->delete(USER_TABLE, 'id', $data['id']);
    }
    else {
        $DB->delete(CROSS_TABLE, CROSS_TABLE_ROLE_NAME, $data['id']);
        $DB->delete(ROLE_TABLE, 'id', $data['id']);
    }
}
else if ($request['action'] == 'update') {
    if($request['db'] == 'users') {
        if($data['name'])
            $DB->update(
                USER_TABLE,
                [USER_NAME => $data['name']],
                ['id' => $data['id']]
            );
        $DB->update_cross(
            CROSS_TABLE,
            $data,
            $data['id']
        );
    }
    else {
        $DB->update(
            ROLE_TABLE,
            [ROLE_NAME => $data['ROLE']],
            ['id' => $data['id']]
        );
    }

}
$DB->disconnect();
