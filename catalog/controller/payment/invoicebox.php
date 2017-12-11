<?php
class ControllerPaymentInvoicebox extends Controller {
	  
  public function index() {
    
    $this->language->load('payment/invoicebox');
	$this->document->setTitle('Invoicebox Method Configuration');
    $this->data['button_confirm'] = $this->language->get('pay_button');
    $this->data['invoicebox_participant_id'] = $this->config->get('invoicebox_participant_id');
	$this->data['invoicebox_participant_ident'] = $this->config->get('invoicebox_participant_ident');
    $this->data['currency'] = $this->config->get('currency');
	$this->data['testmode'] = $this->config->get('invoicebox_testmode');
	$this->data['action'] = 'https://go.invoicebox.ru/module_inbox_auto.u';
    $this->data['text_testmode'] = $this->language->get('text_testmode');    
    $this->load->model('checkout/order');
    $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
  
    if ($order_info) {
	$total_data = array();
			$total = 0;
			$taxes = $this->cart->getTaxes();

			$this->load->model('setting/extension');

			$sort_order = array(); 

			$results = $this->model_setting_extension->getExtensions('total');

			foreach ($results as $key => $value) {
				$sort_order[$key] = $this->config->get($value['code'] . '_sort_order');
			}

			array_multisort($sort_order, SORT_ASC, $results);

			foreach ($results as $result) {
				if ($this->config->get($result['code'] . '_status')) {
					$this->load->model('total/' . $result['code']);

					$this->{'model_total_' . $result['code']}->getTotal($total_data, $total, $taxes);
				}
			}

			$sort_order = array(); 

			foreach ($total_data as $key => $value) {
				$sort_order[$key] = $value['sort_order'];
			}

			array_multisort($sort_order, SORT_ASC, $total_data);
	// echo '<pre>';
	// print_r($total_data);		
	// echo '</pre>';
			$totalshipping = 0;
			foreach ($total_data as $total_d){
				if($total_d['code'] == 'shipping'){
					$totalshipping	= $total_d['value'];
				}
			}
			$subtotal = $this->cart->getSubTotal();
			$ko = ($subtotal/($order_info['total'] - $totalshipping));
			$this->data['products'] = array();
			$quantity = 0;
			foreach ($this->cart->getProducts() as $product) {
				
				$this->data['products'][] = array(
					'name'     => htmlspecialchars($product['name']),
					'price'    => $this->currency->format(round((($product['price']*$product['quantity'])/$ko)/$product['quantity'],2), $order_info['currency_code'], false, false),
					'quantity' => $product['quantity'],
					'vatrate' => $this->tax->getTax( $product['price'], $product['tax_class_id'])
				);
				$quantity +=$product['quantity'];
			}
			
			if( $totalshipping > 0) {
				$this->data['products'][] = array(
					'name'     => htmlspecialchars($order_info['shipping_method']),
					'price'    => $this->currency->format($totalshipping, $order_info['currency_code'], false, false),
					'quantity' => 1,
					'vatrate' => 0
				);
			}
			
			$this->data['quantity'] = $quantity;
			$this->data['total'] = $this->currency->format($order_info['total'], $order_info['currency_code'], false, false);
			$this->data['currency_code'] = $order_info['currency_code'];
			$this->data['first_name'] = html_entity_decode($order_info['payment_firstname'], ENT_QUOTES, 'UTF-8');
			$this->data['last_name'] = html_entity_decode($order_info['payment_lastname'], ENT_QUOTES, 'UTF-8');
			$this->data['phone'] = html_entity_decode($order_info['telephone'], ENT_QUOTES, 'UTF-8');
			
			$this->data['email'] = $order_info['email'];
			$this->data['invoice'] = $this->session->data['order_id'] . ' - ' . html_entity_decode($order_info['payment_firstname'], ENT_QUOTES, 'UTF-8') . ' ' . html_entity_decode($order_info['payment_lastname'], ENT_QUOTES, 'UTF-8');
			
			$this->data['return'] = $this->url->link('checkout/failure');
			$this->data['returnsuccess'] = $this->url->link('checkout/success');
			$this->data['notify_url'] = $this->url->link('payment/invoicebox/callback', '', true);
			//$this->data['cancel_return'] = $this->url->link('checkout/checkout', '', true);

			
			$signatureValue = md5(
			$this->config->get('invoicebox_participant_id').
			$this->session->data['order_id'].
			$order_info['total'].
			$order_info['currency_code'].
			$this->config->get('invoicebox_api_key')
			); 
			$this->data['invoicebox_sign'] = $signatureValue;
			$this->data['order_id'] = $this->session->data['order_id'];
			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/invoicebox_checkout.tpl')) {
            $this->template = $this->config->get('config_template') . '/template/payment/invoicebox_checkout.tpl';
        } else {
            $this->template = 'default/template/payment/invoicebox_checkout.tpl';
        }
			$this->render();
		}
      
  }
  
  public function fail() {
        $this->createLog(__METHOD__, '', 'Платеж не выполнен');
        $this->response->redirect($this->url->link('checkout/checkout', '', 'SSL'));
        return true;
    }

    public function success() {
        $order_id = $this->request->post["participantOrderId"];
        $this->load->model('checkout/order');
        $order_info = $this->model_checkout_order->getOrder($order_id);

        if ((int)$order_info["order_status_id"] == (int)$this->config->get('invoicebox_order_status_completed')) {
            $this->createLog(__METHOD__, $this->request->post, 'Платеж успешно завершон');
            $this->redirect($this->url->link('checkout/success', '', 'SSL'));
            return true;
        }
        return $this->fail();
    }
  
  public function callback() {
  
	if (isset($this->request->post['participantId'])) {
		$participantId 		= IntVal($this->request->post["participantId"]);
	}
	if (isset($this->request->post['participantOrderId'])) {
		$participantOrderId 	= IntVal($this->request->post["participantOrderId"]);
	}

	if ( !($participantId && $participantOrderId )){
		die( "Данные запроса не переданы" );
    }
	$order_id = trim($participantOrderId);
	$this->load->model('checkout/order');
	$order_info = $this->model_checkout_order->getOrder($order_id);
  
	if (!$order_info) {	
		die( "Указанный номер заказа не найден в системе: " . $participantOrderId );
	}
	
	$ucode 		= trim($this->request->post["ucode"]);
	$timetype 	= trim($this->request->post["timetype"]);
	$time 		= str_replace(' ','+',trim($this->request->post["time"]));
	$amount 	= trim($this->request->post["amount"]);
	$currency 	= trim($this->request->post["currency"]);
	$agentName 	= trim(html_entity_decode($this->request->post["agentName"], ENT_QUOTES, 'UTF-8'));
	$agentPointName = trim(html_entity_decode($this->request->post["agentPointName"], ENT_QUOTES, 'UTF-8'));
	$testMode 	= trim($this->request->post["testMode"]);
	$sign	 	= trim($this->request->post["sign"]);
	$participant_apikey 	=  $this->config->get('invoicebox_api_key');
		$sign_strA = 
			$participantId .
			$participantOrderId .
			$ucode .
			$timetype .
			$time .
			$amount .
			$currency .
			$agentName .
			$agentPointName .
			$testMode .
			$participant_apikey;
		$sign_crcA = md5( $sign_strA ); 
	if ( strtolower($sign_crcA) != strtolower($sign) )
		{
			die( "Подпись запроса неверна" );
		}; 
		$amount 	= number_format($amount, 2, '.', '');
		$total = number_format($order_info['total'], 2, '.', '');
		
		if ($total == $amount){
		  $this->model_checkout_order->confirm($order_id, $this->config->get('invoicebox_order_status_completed'));
		  die('OK'); 
		 }else{
			die ("Сумма оплаты не совпадает с суммой заказа");
		 }
  }
}
