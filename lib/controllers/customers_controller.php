<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly.
}


if ( ! class_exists( 'OsCustomersController' ) ) :


  class OsCustomersController extends OsController {

    function __construct(){
      parent::__construct();


      $this->views_folder = LATEPOINT_VIEWS_ABSPATH . 'customers/';
      $this->vars['page_header'] = OsMenuHelper::get_menu_items_by_id('customers');
      $this->vars['breadcrumbs'][] = array('label' => __('Customers', 'latepoint'), 'link' => OsRouterHelper::build_link(OsRouterHelper::build_route_name('customers', 'index') ) );
    }

    public function destroy(){
      if(filter_var($this->params['id'], FILTER_VALIDATE_INT)){
	      $this->check_nonce('destroy_customer_'.$this->params['id']);
        $customer = new OsCustomerModel($this->params['id']);
        if($customer->delete()){
          $status = LATEPOINT_STATUS_SUCCESS;
          $response_html = __('Customer Removed', 'latepoint');
        }else{
          $status = LATEPOINT_STATUS_ERROR;
          $response_html = __('Error Removing Customer', 'latepoint');
        }
      }else{
        $status = LATEPOINT_STATUS_ERROR;
        $response_html = __('Error Removing Customer', 'latepoint');
      }

      if($this->get_return_format() == 'json'){
        $this->send_json(array('status' => $status, 'message' => $response_html));
      }
    }


		public function view_customer_log(){

			$activities = new OsActivityModel();
			$activities = $activities->where(['customer_id' => absint($this->params['customer_id'])])->order_by('id desc')->get_results_as_models();

			$customer = new OsCustomerModel($this->params['customer_id']);

			$this->vars['customer'] = $customer;
			$this->vars['activities'] = $activities;

      $this->format_render(__FUNCTION__);
		}


		public function quick_edit(){
			$customer = new OsCustomerModel($this->params['customer_id']);

			$this->vars['customer'] = $customer;

			$this->format_render(__FUNCTION__);
		}


    public function inline_edit_form(){
      $selected_customer = new OsCustomerModel();
      if(isset($this->params['customer_id'])){
        $selected_customer->load_by_id($this->params['customer_id']);
      }
      $this->vars['default_fields_for_customer'] = OsSettingsHelper::get_default_fields_for_customer();
      $this->vars['selected_customer'] = $selected_customer;
      $this->format_render(__FUNCTION__);
    }

		public function set_as_guest(){
      if(filter_var($this->params['id'], FILTER_VALIDATE_INT)){
        $customer = new OsCustomerModel($this->params['id']);
        if($customer->update_attributes(['is_guest' => true])){
          $status = LATEPOINT_STATUS_SUCCESS;
          $response_html = __('Customer is now allowed to book without password', 'latepoint');
        }else{
          $status = LATEPOINT_STATUS_ERROR;
          $response_html = $customer->get_error_messages();
        }
      }else{
        $status = LATEPOINT_STATUS_ERROR;
        $response_html = __('Error setting customer as guest', 'latepoint');
      }

      if($this->get_return_format() == 'json'){
        $this->send_json(array('status' => $status, 'message' => $response_html));
      }
		}

    /*
      Edit customer
    */

    public function edit_form(){
      $this->vars['page_header'] = __('Edit Customer', 'latepoint');
      $this->vars['breadcrumbs'][] = array('label' => __('Edit Customer', 'latepoint'), 'link' => false );

      if(filter_var($this->params['id'], FILTER_VALIDATE_INT)){
				// check if allowed to access
				$customer = new OsCustomerModel();
				$customer = $customer->where([LATEPOINT_TABLE_CUSTOMERS.'.id' => absint($this->params['id'])])->filter_allowed_records()->set_limit(1)->get_results_as_models();
	      $this->vars['customer'] = $customer;
	      $this->vars['wp_users_for_select'] = OsWpUserHelper::get_wp_users_for_select();
      }

      $this->format_render(__FUNCTION__);
    }


    public function query_for_booking_form(){
      $query = trim($this->params['query']);
      $sql_query = '%'.$query.'%';
      $query = $this->params['query'];
      $customers = new OsCustomerModel();
      $this->vars['query'] = $query;
      $this->vars['customers'] = $customers->where(array('OR' => array('CONCAT (first_name, " ", last_name) LIKE ' => $sql_query, 'email LIKE' => $sql_query, 'phone LIKE' => $sql_query)))->set_limit(20)->order_by('first_name asc, last_name asc')->get_results_as_models();

      $this->format_render(__FUNCTION__);
    }


    /*
      Create customer
    */

    public function create(){
      $this->check_nonce('new_customer');
      $customer = new OsCustomerModel();
      $customer->set_data($this->params['customer']);
      if($customer->save()){
	      // translators: %s is the html of a customer edit link
				$response_html = sprintf(__('Customer Created ID: %s', 'latepoint'), '<span class="os-notification-link" '.OsCustomerHelper::quick_customer_btn_html($customer->id).'>'.$customer->id.'</span>');
        $status = LATEPOINT_STATUS_SUCCESS;
        do_action('latepoint_customer_created', $customer);
      }else{
        $response_html = $customer->get_error_messages();
        $status = LATEPOINT_STATUS_ERROR;
      }
      if($this->get_return_format() == 'json'){
        $this->send_json(array('status' => $status, 'message' => $response_html));
      }
    }


    /*
      Update customer
    */

    public function update(){
      if(isset($this->params['customer']['id']) && filter_var($this->params['customer']['id'], FILTER_VALIDATE_INT)){
	      $this->check_nonce('edit_customer_'.$this->params['customer']['id']);
	      $customer = new OsCustomerModel($this->params['customer']['id']);
				if(!$customer || !OsRolesHelper::can_user_make_action_on_model_record($customer, 'edit')){
	        $response_html = __('Access Restricted', 'latepoint');
	        $status = LATEPOINT_STATUS_ERROR;
				}else{
					$old_customer_data = $customer->get_data_vars();
		      $customer->set_data($this->params['customer']);
		      if($customer->save()){
				  // translators: %s is the html of a customer edit link
						$response_html = sprintf(__('Customer Updated ID: %s', 'latepoint'), '<span class="os-notification-link" '.OsCustomerHelper::quick_customer_btn_html($customer->id).'>'.$customer->id.'</span>');
		        $status = LATEPOINT_STATUS_SUCCESS;
		        do_action('latepoint_customer_updated', $customer, $old_customer_data);
		      }else{
		        $response_html = $customer->get_error_messages();
		        $status = LATEPOINT_STATUS_ERROR;
		      }
				}
      }else{
        $response_html = __('Invalid customer ID', 'latepoint');
        $status = LATEPOINT_STATUS_ERROR;
      }
      if($this->get_return_format() == 'json'){
        $this->send_json(array('status' => $status, 'message' => $response_html));
      }
    }

    public function mini_profile(){
      if(filter_var($this->params['customer_id'], FILTER_VALIDATE_INT)){
        $customer = new OsCustomerModel($this->params['customer_id']);
        $this->vars['upcoming_booking'] = $customer->get_future_bookings(1, true);
        $this->vars['customer'] = $customer;


        $pie_labels = [];
        $pie_colors = [];
        $pie_values = [];
        $pie_chart_data = OsBookingHelper::get_stat('bookings', ['group_by' => 'status', 'customer_id' => $customer->id]);
        $colors = ['#2752E4', '#C066F1', '#26B7DD', '#E8C634', '#19CED6', '#2FEAA3', '#252a3e', '#8d87a5', '#b9b784'];
        $status_colors = [
          LATEPOINT_BOOKING_STATUS_APPROVED => '#35d893',
          LATEPOINT_BOOKING_STATUS_PENDING => '#e6b935',
          LATEPOINT_BOOKING_STATUS_PAYMENT_PENDING => '#4ca4ef',
          LATEPOINT_BOOKING_STATUS_CANCELLED => '#f1585d'
        ];
        $i = 0;
        foreach($pie_chart_data as $pie_data){
          $pie_labels[] = $pie_data['status'];
          $pie_colors[] = isset($status_colors[$pie_data['status']]) ? $status_colors[$pie_data['status']] : $colors[$i];
          $pie_values[] = $pie_data['stat'];
          $i++;
        }

        $this->vars['pie_chart_data'] = ['labels' => $pie_labels, 'colors' => $pie_colors, 'values' => $pie_values];



        $this->set_layout('none');
        $response_html = $this->format_render_return(__FUNCTION__);
      }else{
        $status = LATEPOINT_STATUS_ERROR;
        $response_html = __('Error Accessing Customer', 'latepoint');
      }

      if($this->get_return_format() == 'json'){
        $this->send_json(array('status' => $status, 'message' => $response_html));
      }
    }

    public function connect_all_to_wp_users(){
      $customers = new OsCustomerModel();
      $customers = $customers->where(['wordpress_user_id' => ['OR' => [0, 'IS NULL']]])->get_results_as_models();
      if($customers){
        foreach($customers as $customer){
          $wp_user_id = OsCustomerHelper::create_wp_user_for_customer($customer);
          if($wp_user_id) $customer->update_attributes(['wordpress_user_id' => $wp_user_id]);
        }
      }
      if($this->get_return_format() == 'json'){
        $this->send_json(array('status' => LATEPOINT_STATUS_SUCCESS, 'message' => __('Customers Connected', 'latepoint')));
      }
    }

    public function disconnect_from_wp_user(){
      $customer_id = $this->params['customer_id'];
      $customer = new OsCustomerModel();
      $customer = $customer->where(['id' => $customer_id])->set_limit(1)->get_results_as_models();
      if($customer){
        $customer->update_attributes(['wordpress_user_id' => NULL]);
      }
      if($this->get_return_format() == 'json'){
        $this->send_json(array('status' => LATEPOINT_STATUS_SUCCESS, 'message' => __('Customer Disconnected', 'latepoint')));
      }
    }

    public function connect_to_wp_user(){
      $customer_id = $this->params['customer_id'];
      $customer = new OsCustomerModel();
      $customer = $customer->where(['id' => $customer_id])->set_limit(1)->get_results_as_models();
      if($customer && !$customer->wordpress_user_id){
        $wp_user = OsCustomerHelper::create_wp_user_for_customer($customer);
      }
      if($this->get_return_format() == 'json'){
        $this->send_json(array('status' => LATEPOINT_STATUS_SUCCESS, 'message' => __('Customer Connected', 'latepoint')));
      }
    }


    public function index(){

			$this->vars['page_header'] = false;
      $page_number = isset($this->params['page_number']) ? $this->params['page_number'] : 1;
      $per_page = OsSettingsHelper::get_number_of_records_per_page();
      $offset = ($page_number > 1) ? (($page_number - 1) * $per_page) : 0;


      $customers = new OsCustomerModel();
      $query_args = [];

      $filter = isset($this->params['filter']) ? $this->params['filter'] : false;

      // TABLE SEARCH FILTERS
      if($filter){
        if($filter['id']) $query_args['id'] = $filter['id'];
        if($filter['registration_date_from'] && $filter['registration_date_to']){
          $query_args[LATEPOINT_TABLE_CUSTOMERS.'.created_at >='] = $filter['registration_date_from'];
          $query_args[LATEPOINT_TABLE_CUSTOMERS.'.created_at <='] = $filter['registration_date_to'];
        }
        if($filter['customer']){
          $query_args['concat_ws(" ", '.LATEPOINT_TABLE_CUSTOMERS.'.first_name,'.LATEPOINT_TABLE_CUSTOMERS.'.last_name) LIKE'] = '%'.$filter['customer'].'%';
          $this->vars['customer_name_query'] = $filter['customer'];
        }
        if($filter['phone']){
          $query_args['phone LIKE'] = '%'.$filter['phone'].'%';
          $this->vars['phone_query'] = $filter['phone'];
        }
        if($filter['email']){
          $query_args['email LIKE'] = '%'.$filter['email'].'%';
          $this->vars['email_query'] = $filter['email'];
        }
      }


      // OUTPUT CSV IF REQUESTED
      if(isset($this->params['download']) && $this->params['download'] == 'csv'){
        $csv_filename = 'customers_'.OsUtilHelper::random_text();
        
        header("Content-Type: text/csv");
        header("Content-Disposition: attachment; filename={$csv_filename}.csv");

        $labels_row = [ __('ID', 'latepoint'), 
                        __('Name', 'latepoint'), 
                        __('Phone', 'latepoint'), 
                        __('Email', 'latepoint'), 
                        __('Total Appointments', 'latepoint'), 
                        __('Registered On', 'latepoint') ];


        $customers_data = [];
        $customers_data[] = $labels_row;


        $customers_arr = $customers->where($query_args)->filter_allowed_records()->order_by('id desc')->get_results_as_models();
        if($customers_arr){
          foreach($customers_arr as $customer){
            $values_row = [ $customer->id, 
                            $customer->full_name, 
                            $customer->phone, 
                            $customer->email, 
                            $customer->total_bookings_count,
                            $customer->formatted_created_date()];
            $values_row = apply_filters('latepoint_customer_row_for_csv_export', $values_row, $customer, $this->params);
            $customers_data[] = $values_row;
          }
        }
        $customers_data = apply_filters('latepoint_customers_data_for_csv_export', $customers_data, $this->params);
        OsCSVHelper::array_to_csv($customers_data);
        return;
      }

			$customers->where($query_args)->filter_allowed_records();
      $count_total_customers = clone $customers;

      $total_customers = $count_total_customers->count();
      $total_pages = ceil($total_customers / $per_page);

      $this->vars['customers'] = $customers->set_limit($per_page)->set_offset($offset)->order_by('id desc')->get_results_as_models();
      $this->vars['total_customers'] = $total_customers;

      $this->vars['total_pages'] = ceil($total_customers / $per_page);
      $this->vars['per_page'] = $per_page;
      $this->vars['current_page_number'] = $page_number;
      
      $this->vars['showing_from'] = (($page_number - 1) * $per_page) ? (($page_number - 1) * $per_page) : 1;
      $this->vars['showing_to'] = min($page_number * $per_page, $this->vars['total_customers']);

      $this->format_render(['json_view_name' => '_table_body', 'html_view_name' => __FUNCTION__], [], ['total_pages' => $total_pages, 'showing_from' => $this->vars['showing_from'], 'showing_to' => $this->vars['showing_to'], 'total_records' => $total_customers]);
    }



  }


endif;