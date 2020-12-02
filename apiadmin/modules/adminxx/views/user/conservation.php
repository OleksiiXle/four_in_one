<?php
function printTree($data, $level = 0){
    $r=1;
    foreach($data as $k => $v){
        $qqq = $v;
        if (is_array($v)){
            $isArray = true;
            $isJSON = false;
        } elseif (is_string($v)){
            if (!empty($v)){
                $bufJSON = json_decode($v, true);
                $isArray = is_array($bufJSON);
                $isJSON = (json_last_error() == JSON_ERROR_NONE && is_array($bufJSON));
            } else {
                $isArray = false;
                $isJSON = false;
            }
        } else {
            $isArray = false;
            $isJSON = false;
        }
        echo str_pad('', $level*2).($isArray ? $k : $k . '=' . $v)."\n";
        if($isArray){
            if ($isJSON){
                $buf = $bufJSON;
            } else {
                $buf = $v;
            }
            printTree($buf, $level + 1);
        }
    }
}




ini_set('xdebug.var_display_max_depth', 5);
ini_set('xdebug.var_display_max_children', 256);
ini_set('xdebug.var_display_max_data', 1024);
echo '<pre>';
//var_dump($conservation);
printTree($conservation);
echo '</pre>';
?>