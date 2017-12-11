<?php
class ControllerPaymentInvoicebox extends Controller
{
    private $error = array();

    
    public function index()
    {
        $data = array();
        $this->language->load('payment/invoicebox');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');
        $this->load->model('setting/store');
        $this->load->model('payment/invoicebox');

        $this->data['text_enabled'] = $this->language->get('text_enabled');
        $this->data['text_disabled'] = $this->language->get('text_disabled');
        $this->error = array();



        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            //unset($this->request->post['invoicebox_module']);

            $this->model_setting_setting->editSetting('invoicebox', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');
			
			$this->redirect($this->url->link('extension/payment','token='.$this->session->data['token'], 'SSL'));
        } else {
            if (!empty($this->error)) {
                $this->data['error_warning'] = array_shift($this->error);
            }
        }

        $this->data['heading_title'] = $this->language->get('heading_title');

        $this->data['invoicebox_participant_id_label'] = $this->language->get('invoicebox_participant_id');
        $this->data['invoicebox_participant_ident_label'] = $this->language->get('invoicebox_participant_ident');
        $this->data['invoicebox_api_key_label'] = $this->language->get('invoicebox_api_key');
		$this->data['invoicebox_testmode_label'] = $this->language->get('invoicebox_testmode');
		
        $this->data['description_label'] = $this->language->get('description');
        $this->data['status_label'] = $this->language->get('status');
        $this->data['status_success_label'] = $this->language->get('status_success');
        $this->data['status_failed_label'] = $this->language->get('status_failed');

        $this->data['button_save'] = $this->language->get('button_save');
        $this->data['button_cancel'] = $this->language->get('button_cancel');

        $this->data['entry_sort_order'] = $this->language->get('entry_sort_order');
        $this->data['status_completed'] = $this->language->get('status_completed');
        $this->data['status_canceled'] = $this->language->get('status_canceled');
        
        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_payment'),
            'href' => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('payment/invoicebox', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );

        //button actions
        $this->data['action'] = $this->url->link('payment/invoicebox', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');

        $this->data['invoicebox_participant_id'] = isset($this->request->post['invoicebox_participant_id']) ? $this->request->post['invoicebox_participant_id'] : $this->config->get('invoicebox_participant_id');
        $this->data['invoicebox_participant_ident'] = isset($this->request->post['invoicebox_participant_ident']) ? $this->request->post['invoicebox_participant_ident'] : $this->config->get('invoicebox_participant_ident');
        $this->data['invoicebox_api_key'] = isset($this->request->post['invoicebox_api_key']) ? $this->request->post['invoicebox_api_key'] : $this->config->get('invoicebox_api_key');
        $this->data['invoicebox_sort_order'] = isset($this->request->post['invoicebox_sort_order']) ? $this->request->post['invoicebox_sort_order'] : $this->config->get('invoicebox_sort_order');
		$this->data['invoicebox_testmode'] = isset($this->request->post['invoicebox_testmode']) ? $this->request->post['invoicebox_testmode'] : $this->config->get('invoicebox_testmode');
        $this->data['description'] = isset($this->request->post['description']) ? $this->request->post['description'] : $this->config->get('description');
        $this->data['invoicebox_status'] = isset($this->request->post['invoicebox_status']) ? $this->request->post['invoicebox_status'] : $this->config->get('invoicebox_status');
        $this->data['order_status_success_id'] = isset($this->request->post['order_status_success_id']) ? $this->request->post['order_status_success_id'] : $this->config->get('order_status_success_id');
        $this->data['order_status_failed_id'] = isset($this->request->post['order_status_failed_id']) ? $this->request->post['order_status_failed_id'] : $this->config->get('order_status_failed_id');

        $this->data['invoicebox_order_status_completed'] = isset($this->request->post['invoicebox_order_status_completed']) ? $this->request->post['invoicebox_order_status_completed'] : $this->config->get('invoicebox_order_status_completed');
        $this->data['invoicebox_order_status_canceled'] = isset($this->request->post['invoicebox_order_status_canceled']) ? $this->request->post['invoicebox_order_status_canceled'] : $this->config->get('invoicebox_order_status_canceled');
        
		if (isset($this->request->post['invoicebox_sort_order'])) {
			$this->data['invoicebox_sort_order'] = $this->request->post['invoicebox_sort_order'];
		} else {
			$this->data['invoicebox_sort_order'] = $this->config->get('invoicebox_sort_order');
		}

        $this->load->model('localisation/order_status');

        $this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        $this->data['token'] = $this->session->data['token'];

        $this->template = 'payment/invoicebox.tpl';
        $this->children = array(
            'common/header',
            'common/footer'
        );
        // $this->data['header'] = $this->load->controller('common/header');
        // $this->data['column_left'] = $this->load->controller('common/column_left');
        // $this->data['footer'] = $this->load->controller('common/footer');
       // $this->response->setOutput($this->load->view('extension/payment/invoicebox.tpl', $data));
		$this->response->setOutput($this->render());
    }

    protected function validate()
    {
        if (!$this->user->hasPermission('modify', 'payment/invoicebox')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (empty($this->request->post['invoicebox_participant_id'])) {
            $this->error['invoicebox_participant_id'] = $this->language->get('error_invoicebox_participant_id');
        }

        if (empty($this->request->post['invoicebox_participant_ident'])) {
            $this->error['invoicebox_participant_ident'] = $this->language->get('error_invoicebox_participant_ident');
        }

        if (empty($this->request->post['invoicebox_api_key'])) {
            $this->error['invoicebox_api_key'] = $this->language->get('error_invoicebox_api_key');
        }

       

        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }



}