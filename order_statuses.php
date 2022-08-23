<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

$APPLICATION->SetTitle("Тест");
$APPLICATION->RestartBuffer();

use Bitrix\Sale;
\Bitrix\Main\Loader::includeModule('sale');

$input = filter_input_array(INPUT_GET);

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

$query = \Bitrix\Sale\Internals\StatusTable::query();
$query->setSelect([
	'ID', 'SORT', 'TYPE', 'NOTIFY', 'LID' => 'STATUS_LANG.LID',
	'COLOR' ,'NAME' => 'STATUS_LANG.NAME', 'DESCRIPTION' => 'STATUS_LANG.DESCRIPTION'
]);
$query->where(
	\Bitrix\Main\ORM\Query\Query::filter()
		->logic('OR')
		->where('STATUS_LANG.LID', '=', LANGUAGE_ID)
		->where('STATUS_LANG.LID', NULL)
);

$dbResultList = $query->exec();

$counter = 0;
$statuses = [];
while($ob = $dbResultList->Fetch()){
	// var_dump($counter);
	// var_dump($ob);
	$statuses[] = $ob;
	$counter++;
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($statuses, JSON_UNESCAPED_UNICODE);

// while ($ob1 = $res1->GetNext()) {
// 	print_r($ob1);
// }


// http://kukoba1.av.fvds.ru/wmsexchange/order_statuses.php?login=kukoba&password=AuOZUxCwAu

die();
?>


<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>