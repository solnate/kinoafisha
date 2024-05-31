<?php

use Lib\Database;

require_once $_SERVER['DOCUMENT_ROOT'] . '/core/database.php';
try {
    $auth = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/core/data/auth');
    $auth = json_decode($auth, true);
    $DB = new Database($auth['host'], $auth['user'], $auth['password'], $auth['database']);
} catch (Exception $e) {
    http_response_code(502);
    print_r($e->getMessage());
}
if(!empty($DB)):
    $table = $DB->read(USER_TABLE);
    $roles = $DB->read(ROLE_TABLE);
    $cross_table = array();
    if(!empty($table['data'])){
        foreach ($table['data'] as $user_id => $user) {
            $cross_user_data = $DB->read(
                CROSS_TABLE, '*', CROSS_TABLE_USER_NAME, $user_id
            );
            if(!empty($cross_user_data['data']))
                foreach ($cross_user_data['data'] as $users_roles_data)
                    $cross_table[$user_id][] = $users_roles_data[CROSS_TABLE_ROLE_NAME];
        }
    }
    $DB->disconnect();
endif;
