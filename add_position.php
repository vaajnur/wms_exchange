<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

$APPLICATION->SetTitle("Тест");
$APPLICATION->RestartBuffer();

use Bitrix\Sale;
\Bitrix\Main\Loader::includeModule('sale');
\Bitrix\Main\Loader::includeModule('catalog');

$json = file_get_contents('php://input');
$input = json_decode($json, true);

//var_dump($input);
//die();

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
        if( ($ID = $inp['article']) != false && $inp['name'] !== ''){
            $res1 = ciblockelement::getlist(array(), array('IBLOCK_ID' => 4, 'IBLOCK_TYPE' => 'catalog', 'PROPERTY_ARTNUMBER' => $ID), false, false, array('ID', 'IBLOCK_ID', 'NAME'));
            $obElement = new CIBlockElement();
            if($ob = $res1->GetNext()){
                /*if(isset($inp['name']) && $inp['name'] != ''){
                    $result1 = $obElement->Update($ob['ID'], ['NAME' => $inp['name'] ]);
                    if($result1 == true)
                        echo "sucsess $ID updated!" . PHP_EOL;
                    else
                        echo "Error:  $ID not updated" . PHP_EOL;
                }*/

                // UPDATE FIELDS
                $res_1 = null;
                $arFieldsUpdate = [];
                if($inp['quantity'] != '')
                    $arFieldsUpdate['QUANTITY'] = $inp['quantity'];
                //в wms вес брутто в кг, поэтому умножаем на 1000
				if($inp['weight'] != '')
					$arFields['WEIGHT'] = $inp['weight']*1000;
                if($inp['width'] != '')
                    $arFieldsUpdate['WIDTH'] = $inp['width'];
                if($inp['length'] != '')
                    $arFieldsUpdate['LENGTH'] = $inp['length'];
                if($inp['height'] != '')
                    $arFieldsUpdate['HEIGHT'] = $inp['height'];
                if(!empty($arFieldsUpdate))
                    $res_1 = CCatalogProduct::update($ob['ID'], $arFieldsUpdate);
                if($res_1 === true)
                    echo implode(', ', array_keys($arFieldsUpdate))." обновлены для $ID";
                else 
					if($res_1 === false)
                    	echo "Error: LID not updated for $ID!";
					else
						echo "Error: LID not updated for $ID (nothing to update)!";

            }else{
                $params = array(
				  "max_len" => "100", 
				  "change_case" => "L", 
				  "replace_space" => "_", 
				  "replace_other" => "_", 
				  "delete_repeat_replace" => "true", 
				  "use_google" => "false", 
				);

				$inp_code = "";
				if($inp['name'] != "") {
					$inp_code = Cutil::translit($inp['name'], "ru", $params);
				}

				$rsSections = CIBlockSection::GetList(array(),array('IBLOCK_ID' => 4, '=CODE' => '_temp', 'DEPTH_LEVEL' => 1));
				if ($arSection = $rsSections->Fetch())
				{
					$arFields = array(
						'NAME' => $inp['name'],
						'IBLOCK_ID' => 4,
						'ACTIVE' => 'N',
						'IBLOCK_SECTION' => $arSection['ID'], //для теста поставил временно 68, а так необходимо определять в какой раздел добавлять
						'CODE' => $inp_code,
						'PROPERTY_VALUES' => array('26' => $inp['article'])
					 );
					 if($elemId = $obElement->Add($arFields)){
						   $arFields = array(
							"ID" => $elemId, 
							// "VAT_ID" => 1, //выставляем тип ндс (задается в админке)  
							// "VAT_INCLUDED" => "Y", //НДС входит в стоимость
							"QUANTITY" => $inp['quantity'] != '' ? $inp['quantity'] : '0'   
							);
							//в wms вес брутто в кг, поэтому умножаем на 1000
							if($inp['weight'] != '')
								$arFields['WEIGHT'] = $inp['weight']*1000;
							if($inp['width'] != '')
								$arFields['WIDTH'] = $inp['width'];
							if($inp['length'] != '')
								$arFields['LENGTH'] = $inp['length'];
							if($inp['height'] != '')
								$arFields['HEIGHT'] = $inp['height'];
							if(CCatalogProduct::Add($arFields)){
								echo "Товарная позиция $ID добавлена!" .PHP_EOL;
							}else{
								echo "Ошибка: товарная позиция $elemId не добавлена!".PHP_EOL;
							}
					 } else{
						 echo 'Ошибка: '.$inp['name'].' не добавлен'.PHP_EOL;
					 }
					//echo 'ID раздела: '.$arSection['ID'];
				}
				else
				{
 					echo 'Раздел с символьным кодом _temp не найден!';
				}
            }
        }else{
            echo 'Пустой article или name!';
        }
        global $APPLICATION;
        if($ex = $APPLICATION->getexception())
            echo $ex->getstring();
        
    }
}else{
	echo "Не заполнены обязательные параметры article или name!";
}

$USER->Logout();

//   curl --header "Content-Type: application/json"   --request POST   --data '[{"login": "kukoba", "password": "AuOZUxCwAu"}, {"article": "11111111111111111","name": "новый товар"},{"article": "22222222222","name": "еще один товар"},{"article": "333333333","name": "+ 1 товар"}]'   http://kukoba1.av.fvds.ru/wmsexchange/add_position.php

die();
