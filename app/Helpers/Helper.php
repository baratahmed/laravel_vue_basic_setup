<?php

function send_msg($msg,$status,$code){
    $res = [
        'status' => $status,
        'message' => $msg,
    ];

    return response()->json($res,$code);
}

function laravel_date($date){
    $date = preg_replace('/\(.*\)$/','',$date);
    return date('Y-m-d',strtotime($date));
}


if (!function_exists('product_count_upto_zero')) {

    function product_count_upto_zero($data)
    {
        return $data->where('products_count', '>', 0);
    }
}
