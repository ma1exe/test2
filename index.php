<?php 
    set_time_limit(10);

    $mysqli = new mysqli('localhost', 'root', '', 'test');

    if ($mysqli->connect_errno) {
        printf('Не удалось подключиться к БД: %s\n', $mysqli->connect_error);
        exit();
    }

    $query_create_table = "CREATE TABLE IF NOT EXISTS `test` (
        `hash` char(40) NOT NULL UNIQUE,
        `text` text NOT NULL
    )";

    if (!$mysqli->query($query_create_table)) {
        printf('При создании БД произошла ошибка!');
        exit();
    }

    $str_source = '{Пожалуйста,|Просто|Если сможете,} сделайте так, чтобы это {удивительное|крутое|простое|важное|бесполезное} тестовое предложение {изменялось случайным образом|менялось каждый раз}.';
    $str_done_pre = [];
    $str_done = [];

    preg_match_all('(\{[^\}]+\}[^\{]+)', $str_source, $preg);

    function getArray($haystack, $arr_tmp) {
        $pos_start = strrpos($haystack, '{') + 1;
        $pos_end = strpos($haystack, '}') - $pos_start;

        $str_tmp = substr($haystack, $pos_start, $pos_end);

        $random = explode('|', $str_tmp);

        foreach ($random as $rand) {
            $str = str_replace('{' . $str_tmp . '}', $rand, $haystack);
            $arr_tmp[] = $str;
            /*if (substr_count($str, '{')) { // Не работает
                $arr_tmp = getArray($str, $arr_tmp);
            } else {
                $hash = sha1(str_replace('{' . $str_tmp . '}', $rand, $haystack));
                $arr_tmp[] = $str;
            }*/
        }

        return $arr_tmp;
    }

    for ($i = 0; $i < count($preg[0]); $i++) {
        $str_done_pre[$i] = getArray($preg[0][$i], []);
    }

    $counter = 1;

    foreach ($str_done_pre as $c) {
        $counter *= count($c);
    }

    for ($i = count($str_done_pre); $i >= 0; $i--) {
        $k = 0;
        for ($j = 0; $j < $counter; $j++) {
            if ($k + 1 < count($str_done_pre[$i])) { $k++; } 
            else { $k = 0; }
            $str_done[$j] = $str_done_pre[$i][$k] . $str_done[$j];
        }
    }

    foreach ($str_done as $str) {
        $hash = sha1($str);
        $query_select = "SELECT * FROM `test` WHERE `hash` = '" . $hash . "'";
        $query_insert = "INSERT INTO `test` (`hash`, `text`) VALUES ('" . $hash . "', '" . $str ."')";
        $result = $mysqli->query($query_select);
        if ($result->num_rows) {
            echo '<p>Строка "'.$str.'" уже есть в таблице!</p>';
        } elseif ($result = $mysqli->query($query_insert)) {
            echo '<p><b>Строка "'.$str.'" добавлена в таблицу.</b></p>';
        } else {
            echo '<p>При работе со строкой "'.$str.'" произошла ошибка.</p>';
        }
    }

?>