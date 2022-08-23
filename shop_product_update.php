<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

$APPLICATION->SetTitle("Тест");
$APPLICATION->RestartBuffer();

use Bitrix\Sale;
\Bitrix\Main\Loader::includeModule('sale');
\Bitrix\Main\Loader::includeModule('iblock');




$json = file_get_contents('php://input');
$input = json_decode($json, true);

// var_dump($input);
// echo $json;
// die();


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

if(!empty($input) && is_array($input)){
    foreach($input as $inp){
        if( ($ID = $inp['article']) != false  && $inp['count'] !== ''){
            $res = ciblockelement::getlist(array(), array( 'PROPERTY_ARTNUMBER' => $ID , 'IBLOCK_ID' => 4), false, false, ['*'] );
            if($res != false && $ob = $res->GetNext()){
                $arFields = ['QUANTITY' => $inp['count']];
//////////////// габариты вес
                //в wms вес брутто в кг, поэтому умножаем на 1000
				if($inp['weight'] != '')
					$arFields['WEIGHT'] = $inp['weight']*1000;
                if($inp['width'] != '')
                    $arFields['WIDTH'] = $inp['width'];
                if($inp['length'] != '')
                    $arFields['LENGTH'] = $inp['length'];
                if($inp['height'] != '')
                    $arFields['HEIGHT'] = $inp['height'];
                // update
                    \Bitrix\Catalog\ProductTable::update($ob['ID'], $arFields);
                    echo "$ID update success!".PHP_EOL;
            }else{
                    echo "$ID not found!".PHP_EOL;
            }
        }else{
            echo 'empty article or count!';
        }
    }
}else{
	echo "empty required params!";
}


die();

// curl --header "Content-Type: application/json"   --request POST   --data '[{"login": "kukoba", "password": "AuOZUxCwAu"}, {"article": "30104641","count": 10},{"article": "30088075","count": 5},{"article": "30107248","count": 3}]'   http://kukoba1.av.fvds.ru/wmsexchange/shop_product_update.php

?>


<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>