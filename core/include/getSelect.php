<?php

use Lib\Database;

require_once $_SERVER['DOCUMENT_ROOT'] . '/core/database.php';
try {
    $auth = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/core/data/auth');
    $auth = json_decode($auth, true);
    $DB = new Database($auth['host'], $auth['user'], $auth['password'], $auth['database']);
} catch (Exception $e) {
    http_response_code(502);
    die($e->getMessage());
}
$roles = $DB->read(ROLE_TABLE);
$DB->disconnect();
?>
<select class="form-control" name="role" multiple>
    <?php foreach ($roles['data'] as $id => $row):?>
        <option name="role" value="<?=$row[0]?>" selected>
            <?=$row['role']?>
        </option>
    <?php endforeach;?>
</select>