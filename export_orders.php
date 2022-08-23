<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

$APPLICATION->SetTitle("Тест");
$APPLICATION->RestartBuffer();

use Bitrix\Sale;
\Bitrix\Main\Loader::includeModule('sale');
\Bitrix\Main\Loader::includeModule('iblock');


	// echo "<pre>";

//$input_vars = filter_input_array(INPUT_POST);
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

$filter = Array();
if(!empty($input_vars['id'])){
    $filter['ID'] = $input_vars['id'];
}
if(!empty($input_vars['date_from'])){
    try {
        $date_from = new datetime($input_vars['date_from']);
        $filter['>=DATE_INSERT'] = $date_from->format('d.m.Y H:i:s');   
    } catch (Exception $ex) {
        echo $ex->getMessage();
    }
}
if(!empty($input_vars['date_to'])){
    try {
        $date_to = new datetime($input_vars['date_to']);
        $filter['<DATE_INSERT'] = $date_to->format('d.m.Y H:i:s');           
    } catch (Exception $ex) {
        echo $ex->getMessage();
    }
}


$orders = []; 

$parameters = [
    'filter' => [
        $filter
        ],
    'order' => ["ID" => "DESC"],
    'limit' => 50
];

$dbRes = \Bitrix\Sale\Order::getList($parameters);
while ($order_arr = $dbRes->fetch()){
    	$items = [];
	 // pr($order_arr);

	$order = Sale\Order::load($order_arr['ID']);	

		$dbRes1 = \Bitrix\Sale\PropertyValueCollection::getList([
		    'select' => ['*'],
		    'filter' => [
		        '=ORDER_ID' => $order_arr['ID'], 
		    ]
		]);

		while ($item = $dbRes1->fetch())
		{
			$order_props[$item['CODE']] = $item['VALUE'];
		    // pr($item);
		}

	$basket = $order->getBasket();

        $shipmentCollection = $order->getShipmentCollection()->getNotSystemItems();
        $shipment_arr = '';
        foreach ($shipmentCollection as $shipment)
        {
//            echo '123';
            $shipment_arr = $shipment->getFieldValues();
            if(is_object($shipment_arr['DATE_INSERT']))
                $shipment_arr['DATE_INSERT'] = $shipment_arr['DATE_INSERT']->format('Y-m-d H:i:s');
            if(is_object($shipment_arr['DATE_ALLOW_DELIVERY']))
                $shipment_arr['DATE_ALLOW_DELIVERY'] = $shipment_arr['DATE_ALLOW_DELIVERY']->format('Y-m-d H:i:s');
//            pr($shipment->getAvailableFields());
//            pr($shipment_arr);
        }
        
        $paymentCollection = $order->getPaymentCollection();
        $payment_arr = '';
        foreach ($paymentCollection as $payment){
            $payment_arr = $payment->getFieldValues();
            if(is_object($payment_arr['DATE_PAID']))
                $payment_arr['DATE_PAID'] = $payment_arr['DATE_PAID']->format('Y-m-d H:i:s');
            if(is_object($payment_arr['DATE_PAID']))
                $payment_arr['PAY_VOUCHER_DATE'] = $payment_arr['PAY_VOUCHER_DATE']->format('Y-m-d H:i:s');
            if(is_object($payment_arr['DATE_PAID']))
                $payment_arr['DATE_BILL'] = $payment_arr['DATE_BILL']->format('Y-m-d H:i:s');
//            pr($payment_arr);
        }
        
//        die();
        

        
//	die();


	// print_r($basket);
	foreach ($basket as $product){
		// var_dump($product->getField('NAME'));
		// var_dump($product->getField('PRODUCT_ID'));
			$res1 = ciblockelement::getbyid($product->getProductId());
			if($elem1 = $res1->Getnextelement()){
				$elem_props = $elem1->getproperties();
			}
			$items[] = array(
				'id' => $product->getProductId(),
				'order_id' => $order_arr['ID'],
				'name' => $product->getField('NAME'),
				'product_id' => $product->getField('PRODUCT_ID'),
				'tov_id' => $elem_props['ARTNUMBER']['VALUE'],
				// 'sku_id' => '',
				// 'sku_code' => '',
				// 'PropertyCollection' => $PropertyCollection,
				// 'PropertyCollection' => $elem_props,
				'type' => 'product',
				'service_id' => '',
				// 'service_variant_id' => '',
				'price' => $product->getPrice(),
				'quantity' => $product->getQuantity(),
				'parent_id' => '',
				'stock_id' => '',
				'virtualstock_id' => '',
				'purchase_price' => '',
				'total_discount' => '',
				'tax_percent' => '',
				'tax_included' => $product->getField('VAT_INCLUDED'),
				'goodsItemIndex' => '',
				'sort' => 0,
			);	
	}

	$rsUser = CUser::GetByID($order->getUserId());
        if($arUser = $rsUser->getnext(true, false)){
           unset($arUser['PASSWORD']);
           unset($arUser['CHECKWORD']);
        }else{
            $arUser = ''; 
        }

        // var_dump($order_arr['DATE_INSERT']);

        if(is_object($order_arr['DATE_INSERT']))
        	$order_arr['DATE_INSERT'] = $order_arr['DATE_INSERT']->format('Y-m-d H:i:s');
        if(is_object($order_arr['DATE_UPDATE']))
        	$order_arr['DATE_UPDATE'] = $order_arr['DATE_UPDATE']->format('Y-m-d H:i:s');
        // $order_arr['DATE_INSERT'] = $order_arr['DATE_INSERT']->format('Y-m-d H:i:s');
        $order_arr['properties'] = $order_props;
        $order_arr['shipping'] = $shipment_arr;
        $order_arr['contragent'] = $arUser;
        $order_arr['payment'] = $payment_arr;
        $order_arr['items'] = $items;
        $orders[] = $order_arr;

}

header('Content-type: application/json; charset=utf-8');
echo json_encode($orders, JSON_UNESCAPED_UNICODE);
die();



?>



<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>