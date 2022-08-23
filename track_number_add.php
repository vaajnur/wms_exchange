<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

$APPLICATION->SetTitle("Тест");
$APPLICATION->RestartBuffer();

use Bitrix\Sale;
\Bitrix\Main\Loader::includeModule('sale');

$input_vars = filter_input_array(INPUT_GET);

if($_SERVER['PHP_AUTH_USER'] != '' && $_SERVER['PHP_AUTH_PW'] != ''){
    global $USER;
    if (!is_object($USER)) $USER = new CUser;
    $arAuthResult = $USER->Login($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'], "Y");
    if($arAuthResult === true){
    }else{
        header('HTTP/1.0 401 Unauthorized');
        echo "login or password incorrect!";
        die();
    }
}else{
    header('HTTP/1.0 401 Unauthorized');
    echo "login or password incorrect!";
    die();
}

if(!empty($input_vars['tracking_number'])  && !empty($input_vars['order_id'])){
    $order = Sale\Order::load($input_vars['order_id']);
    if($order != false){
        $shipmentCollection = $order->getShipmentCollection()->getNotSystemItems();
        $shipment_arr = '';
        foreach ($shipmentCollection as $shipment)
        {
            $shipment->setField('TRACKING_NUMBER', $input_vars['tracking_number']);
            $res = $order->save();
            if($res->isSuccess()){
                echo 'трек номер '.$input_vars['tracking_number'].' для заказа '.$input_vars['order_id'].' установлен!';
            }else{
                echo 'Ошибка! трек номер '.$input_vars['tracking_number'].' для заказа '.$input_vars['order_id'].' не установлен!';
            }
        }
    }else{
        echo 'order '.$input_vars['order_id'].' not finded!';
    }
}else{
    echo 'empty required fields!';
}