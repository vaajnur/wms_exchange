<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

$APPLICATION->SetTitle("Тест");
$APPLICATION->RestartBuffer();

use Bitrix\Sale;
\Bitrix\Main\Loader::includeModule('sale');

$input = filter_input_array(INPUT_POST);

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

// var_dump($input);

if(!empty($input['order_id']) && !empty($input['status_id'])){
	$ORDER_ID = $input['order_id'];
	$STATUS_ID = $input['status_id'];
	// получаем объект существующего заказа
	$order = Sale\Order::load($ORDER_ID);
	if(!empty($order)){
	// задаем значение для поля STATUS_ID - N (статус: принят)
		$query = \Bitrix\Sale\Internals\StatusTable::query();
		$query->setSelect([
			'ID', 'SORT', 'TYPE', 'NOTIFY', 'LID' => 'STATUS_LANG.LID',
			'COLOR' ,'NAME' => 'STATUS_LANG.NAME', 'DESCRIPTION' => 'STATUS_LANG.DESCRIPTION'
		]);
		$query->where(
			\Bitrix\Main\ORM\Query\Query::filter()
				->where('ID', '=', $STATUS_ID)
		);

		$dbResultList = $query->exec();
// извлекаем статус дабы проверить существует ли он
		if($ob = $dbResultList->Fetch()){
			$order->setField('STATUS_ID', $STATUS_ID);
			// сохраняем изменения
			$order->save();
			echo "success";
		}else{
			echo "order status not exist!";
		}
	}else{
		echo "order id not exist!";
	}
}else{
	echo "empty required ids";
}


die();

// curl -d 'login=kukoba&password=AuOZUxCwAu&order_id=7&status_id=EE'   -X   POST    http://kukoba1.av.fvds.ru/wmsexchange/set_order_status.php

?>


<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>