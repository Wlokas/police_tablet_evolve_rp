<?php
require 'config.php';

try {
    $pdo = new pdo('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=UTF8', DB_USER, DB_PASSWORD);
} catch (PDOException $e){
    echo 'Ошибка при подключении к базе данных';
}

$time = time();

$stmt = $pdo->query("SELECT `reason`, `time` FROM `bans_ip` WHERE `ip` = '{$_SERVER['REMOTE_ADDR']}' AND `time` >= $time OR `time`=0 LIMIT 1");

if ($stmt->rowCount() == 0) {
    if(isset($_POST['session_key'])) {
        $stmt = $pdo->query("SELECT * FROM `sessions` WHERE `session_key`='{$_POST['session_key']}' LIMIT 1");
        if($stmt->rowCount() != 0) {
            $time = time();
            $player_info = $stmt->fetch(PDO::FETCH_ASSOC);

            if(isset($_GET['create_db_criminal']) && isset($_POST['nickname']) && isset($_POST['age']) && isset($_POST['special_signs']) && isset($_POST['biography']) && isset($_POST['articles'])) {
                $stmt = $pdo->query("INSERT INTO `db_criminal`(`id_cop`, `nickname`, `age`, `special_signs`, `biography`, `articles`, `lastupdate`) VALUES ({$player_info['id']}, '{$_POST['nickname']}', {$_POST['age']}, '{$_POST['special_signs']}', '{$_POST['biography']}', '{$_POST['articles']}', $time)");
                if($stmt) {
                    $stmt = $pdo->query("SELECT `db_criminal`.id, `db_criminal`.`nickname`, `db_criminal`.`age`, `db_criminal`.`special_signs`, `db_criminal`.`biography`, `db_criminal`.`articles`, `db_criminal`.`lastupdate`, `sessions`.`nickname` as nickname_cop FROM `db_criminal` INNER JOIN `sessions` on `db_criminal`.`id_cop` = `sessions`.`id` ORDER BY `lastupdate` DESC LIMIT 50");
                    exit(json_encode(["response"=>$stmt->fetchAll(PDO::FETCH_ASSOC)]));
                }
                else exit(json_encode(["error"=>["message"=>"Произошла ошибка при добавлении записи в базу данных.\nПопробуйте позже.."]]));
            }

            if(isset($_GET['create_db_fine']) && isset($_POST['nickname']) && isset($_POST['age']) && isset($_POST['vehicle']) && isset($_POST['vehicle_number']) && isset($_POST['articles']) && isset($_POST['admitted'])) {
                $stmt = $pdo->query("INSERT INTO `db_fine` (`id_cop`, `nickname`, `age`, `vehicle`, `vehicle_number`, `articles`, `admitted`, `lastupdate`) VALUES ({$player_info['id']}, '{$_POST['nickname']}', {$_POST['age']}, '{$_POST['vehicle']}', '{$_POST['vehicle_number']}', '{$_POST['articles']}', {$_POST['admitted']}, $time)");
                if($stmt) {
                    $stmt = $pdo->query("SELECT `db_fine`.id, `db_fine`.`nickname`, `db_fine`.`age`, `db_fine`.`lastupdate`, `db_fine`.`vehicle`, `db_fine`.`vehicle_number`, `db_fine`.`articles`, `db_fine`.`admitted`, `sessions`.`nickname` as nickname_cop FROM `db_fine` INNER JOIN `sessions` on `db_fine`.`id_cop` = `sessions`.`id` ORDER BY `lastupdate` DESC LIMIT 50");
                    exit(json_encode(["response"=>$stmt->fetchAll(PDO::FETCH_ASSOC)]));
                }
                else exit(json_encode(["error"=>["message"=>"Произошла ошибка при добавлении записи в базу данных.\nПопробуйте позже.."]]));
            }

            if(isset($_GET['get_db_fine'])) {
                if(isset($_POST['id'])) {
                    $stmt = $pdo->query("SELECT * FROM `db_fine` WHERE `id`={$_POST['id']}");
                    exit(json_encode(["response"=>$stmt->fetch(PDO::FETCH_ASSOC)]));
                } elseif($_POST['search']) {
                    $nickname = strtolower($_POST['search']);
                    $stmt = $pdo->query("SELECT `db_fine`.id, `db_fine`.`nickname`, `db_fine`.`age`, `db_fine`.`lastupdate`, `db_fine`.`vehicle`, `db_fine`.`vehicle_number`, `db_fine`.`articles`, `db_fine`.`admitted`, `sessions`.`nickname` as nickname_cop FROM `db_fine` INNER JOIN `sessions` on `db_fine`.`id_cop` = `sessions`.`id` WHERE LOWER(`db_fine`.`nickname`) LIKE '%$nickname%' ORDER BY `lastupdate` DESC");
                    exit(json_encode(["response"=>$stmt->fetchAll(PDO::FETCH_ASSOC)]));
                } else {
                    $stmt = $pdo->query("SELECT `db_fine`.id, `db_fine`.`nickname`, `db_fine`.`age`, `db_fine`.`lastupdate`, `db_fine`.`vehicle`, `db_fine`.`vehicle_number`, `db_fine`.`articles`, `db_fine`.`admitted`, `sessions`.`nickname` as nickname_cop FROM `db_fine` INNER JOIN `sessions` on `db_fine`.`id_cop` = `sessions`.`id` ORDER BY `lastupdate` DESC LIMIT 50");
                    exit(json_encode(["response"=>$stmt->fetchAll(PDO::FETCH_ASSOC)]));
                }
            }

            if(isset($_GET['get_db_criminal'])) {
                if(isset($_POST['id'])) {
                    $stmt = $pdo->query("SELECT * FROM `db_criminal` WHERE `id`={$_POST['id']}");
                    exit(json_encode(["response"=>$stmt->fetch(PDO::FETCH_ASSOC)]));
                } elseif($_POST['search']) {
                    $nickname = strtolower($_POST['search']);
                    $stmt = $pdo->query("SELECT `db_criminal`.id, `db_criminal`.`nickname`, `db_criminal`.`age`, `db_criminal`.`special_signs`, `db_criminal`.`biography`, `db_criminal`.`articles`, `db_criminal`.`lastupdate`, `sessions`.`nickname` as nickname_cop FROM `db_criminal` INNER JOIN `sessions` on `db_criminal`.`id_cop` = `sessions`.`id` WHERE LOWER(`db_criminal`.`nickname`) LIKE '%$nickname%'");
                    exit(json_encode(["response"=>$stmt->fetchAll(PDO::FETCH_ASSOC)]));
                } else {
                    $stmt = $pdo->query("SELECT `db_criminal`.id, `db_criminal`.`nickname`, `db_criminal`.`age`, `db_criminal`.`special_signs`, `db_criminal`.`biography`, `db_criminal`.`articles`, `db_criminal`.`lastupdate`, `sessions`.`nickname` as nickname_cop FROM `db_criminal` INNER JOIN `sessions` on `db_criminal`.`id_cop` = `sessions`.`id` ORDER BY `lastupdate` DESC LIMIT 50");
                    exit(json_encode(["response"=>$stmt->fetchAll(PDO::FETCH_ASSOC)]));
                }
            }

            if(isset($_GET['get_calls_cords']) && isset($_POST['id_received_call'])) {
                $stmt = $pdo->query("SELECT `id_call` FROM `received_calls_cop` WHERE `id`={$_POST['id_received_call']}");
                if($stmt->rowCount() != 0) {
                    $pdo->exec("UPDATE `received_calls_cop` SET `lastupdate`=$time WHERE `id`={$_POST['id_received_call']}");
                    $id_call = $stmt->fetch(PDO::FETCH_ASSOC)['id_call'];
                    $stmt = $pdo->query("SELECT `x`, `y`, `z` FROM `calls_cop` WHERE `id`=$id_call AND $time-`lastupdate` < 120");
                    if($stmt->rowCount() != 0) {
                        $cords = $stmt->fetch(PDO::FETCH_ASSOC);
                        exit(json_encode(["response"=>["x"=>$cords['x'], "y"=>$cords['y'],"z"=>$cords['z']]]));
                    } else {
                        $pdo->exec("DELETE FROM `received_calls_cop` WHERE `nickname`='{$player_info['nickname']}'");
                        exit(json_encode(["response" => "not_found"]));
                    }
                } else exit(json_encode(["error"=>["message"=>"Не найден ID принятого вызова.\nПопробуйте позже.."]]));
            }

            if(isset($_GET['unreceived_call'])) {
                $pdo->exec("DELETE FROM `received_calls_cop` WHERE `nickname`='{$player_info['nickname']}'");
                exit(json_encode(["response"=>"success_delete"]));
            }

            if(isset($_GET['received_call']) && isset($_POST['id_call'])) {
                $stmt = $pdo->query("SELECT * FROM `calls_cop` WHERE `id`={$_POST['id_call']} AND $time-`lastupdate` < 120");
                if($stmt->rowCount() != 0) {
                    $pdo->exec("DELETE FROM `received_calls_cop` WHERE `nickname`='{$player_info['nickname']}'");
                    $stmt = $pdo->query("INSERT INTO `received_calls_cop`(`id_cop`, `nickname`, `id_call`, `lastupdate`) VALUES ({$player_info['id_server']},'{$player_info['nickname']}',{$_POST['id_call']},0)");

                    if ($stmt) exit(json_encode(["response" => ["id" => $pdo->lastInsertId()]]));
                    else exit(json_encode(["error" => ["message" => "Произошла во время принятия вызова.\nПопробуйте позже.."]]));
                } else exit(json_encode(["error" => ["message" => "Вызов не был найден или больше не действителен.\nПопробуйте позже.."]]));
            }

            if(isset($_GET['get_calls'])) {
                $calls = $pdo->query("SELECT * FROM `calls_cop` WHERE $time-`lastupdate` < 120")->fetchAll(PDO::FETCH_ASSOC);
                $received_calls = $pdo->query("SELECT * FROM `received_calls_cop` WHERE $time-`lastupdate` < 120")->fetchAll(PDO::FETCH_ASSOC);
                exit(json_encode(["response"=>["calls"=>$calls, "received_calls"=>$received_calls]]));
            }

            if(isset($_GET['delete_call'])) {
                $pdo->exec("DELETE FROM `calls_cop` WHERE `nickname`='{$player_info['nickname']}'");
                exit(json_encode(["response"=>"success_delete"]));
            }

            if(isset($_GET['broad_cords_call']) && isset($_POST['x']) && isset($_POST['y']) && isset($_POST['z']) && isset($_POST['id_call'])) {
                $stmt = $pdo->query("UPDATE `calls_cop` SET `x`={$_POST['x']},`y`={$_POST['y']},`z`={$_POST['z']}, `lastupdate`=$time WHERE `id`={$_POST['id_call']} AND `nickname`='{$player_info['nickname']}'");
                if($stmt) exit(json_encode(["response"=>["success_update"]]));
                else {
                    $pdo->exec("DELETE FROM `calls_cop` WHERE `nickname`='{$player_info['nickname']}'");
                    exit(json_encode(["error"=>["message"=>"Произошла ошибка при передаче данных о местоположении на сервер.\nПопробуйте позже.."]]));
                }
            }

            if(isset($_GET['create_call']) && isset($_POST['code'])) {

                $pdo->exec("DELETE FROM `calls_cop` WHERE `nickname`='{$player_info['nickname']}'");
                $stmt = $pdo->query("INSERT INTO `calls_cop`(`id_cop`, `nickname`, `code`, `x`, `y`, `z`, `lastupdate`) VALUES ({$player_info['id_server']},'{$player_info['nickname']}',{$_POST['code']},0.0,0.0,0.0,0)");
                if($stmt) exit(json_encode(["response"=>["id"=>$pdo->lastInsertId()]]));
                else exit(json_encode(["error"=>["message"=>"Произошла ошибка при создании вызова.\nПопробуйте позже.."]]));

            }

        } else exit(json_encode(["error"=>["message"=>"Произошла ошибка во время запроса на сервер.\nТокен сессии недействителен (invalid_token)\nПопробуйте позже.."]]));
    }
    elseif(isset($_POST['nickname']) && isset($_POST['serial']) && isset($_POST['id'])) {
        if(isset($_GET['get_session'])) {
            $session_key = generate_string(32);
            $stmt = $pdo->query("SELECT `id` FROM `sessions` WHERE `serial` = '{$_POST['serial']}' LIMIT 1");
            if($stmt->rowCount() == 0) {
                $stmt = $pdo->query("INSERT INTO `sessions`(`id_server`, `nickname`, `serial`, `ip`, `session_key`) VALUES ({$_POST['id']},'{$_POST['nickname']}','{$_POST['serial']}','{$_SERVER['REMOTE_ADDR']}','$session_key')");
                if($stmt) {
                    exit(json_encode(["response"=>["session_key"=>$session_key]]));
                } else exit(json_encode(["error"=>["message"=>"Произошла ошибка при создании сессии (#1).\nПопробуйте позже..\n".json_encode($pdo->errorInfo())]]));
            }
            else {
                $stmt = $pdo->query("UPDATE `sessions` SET `id_server`={$_POST['id']},`nickname`='{$_POST['nickname']}',`ip`='{$_SERVER['REMOTE_ADDR']}',`session_key`='$session_key' WHERE `serial` = '{$_POST['serial']}'");
                if($stmt) {
                    exit(json_encode(["response"=>["session_key"=>$session_key]]));
                } else exit(json_encode(["error"=>["message"=>"Произошла ошибка при создании сессии (#2).\nПопробуйте позже.."]]));
            }
        }
    }
    else exit(json_encode(["error"=>["message"=>"Произошла ошибка при передаче данных на сервер.\nПопробуйте позже.."]]));
}
else {
    $info_ban = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($info_ban['time'] == 0) $date = "Навсегда";
    else $date = date("d/m/y H:i:s", $info_ban['time']);
    exit(json_encode(["error"=>["message"=>"Вы были заблокированы в системе модератором.\nПричина: {$info_ban['reason']}\nДата разблокировки: $date"]]));
}

function generate_string($strength = 16) {
    $input = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $input_length = strlen($input);
    $random_string = '';
    for($i = 0; $i < $strength; $i++) {
        $random_character = $input[mt_rand(0, $input_length - 1)];
        $random_string .= $random_character;
    }

    return $random_string;
}

?>
