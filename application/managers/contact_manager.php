<?php
final class Contact_Manager extends Manager {
	public function __construct() {
		parent::__construct();
        session_start();
	}

	public function get_index() {
		$_SESSION['form_token'] = uniqid();
		
		$data = array(
			'form_token' => $_SESSION['form_token'], 
		);
		
		$layout_data = array(
			'page_title' => 'Contact',
			'content' => $this->render_template('pages/contact', $data),
		);
		
		$response_data = array(
			'content' => $this->render_template('layouts/default_layout', $layout_data),
			'type' => 'text/html',
		);
		$this->response($response_data);
	}

	public function post_index() {
		$form_token = isset($_SESSION['form_token']) ? $_SESSION['form_token'] : NULL;

		// Load Form Validation library and assign post data
		$this->load_library('SYS', 'form_validation/form_validation_library', 'fv', array('post' => $this->post));

		$this->fv->set_rules('form_token', 'Form Token', 'form_token['.$form_token.']');
		$this->fv->set_rules('contact_title', 'Title', 'trim|max_length[0]'); // Anti-spam
		$this->fv->set_rules('contact_subject', 'Subject', 'trim|required');
		$this->fv->set_rules('contact_message', 'Message', 'trim|required');
		$this->fv->set_rules('contact_name', 'Name', 'trim|required');
		$this->fv->set_rules('contact_email', 'Email', 'trim|required|valid_email');
		
		$result = $this->fv->run();
		
		$this->load_function('SYS', 'htmlawed/htmlawed_function');
		
		// Further process the input data with htmlawed function
		$contact_subject = htmlawed_function($this->fv->set_value('contact_subject'), array('safe'=>1, 'deny_attribute'=>'style, on*', 'elements'=>'* -a'));
		$contact_message = htmlawed_function($this->fv->set_value('contact_message'), array('safe'=>1, 'deny_attribute'=>'style, on*', 'elements'=>'* -a'));
		$contact_name = htmlawed_function($this->fv->set_value('contact_name'), array('safe'=>1, 'deny_attribute'=>'style, on*', 'elements'=>'* -a'));
		$contact_email = $this->fv->set_value('contact_email');

		if ($result == FALSE) {
			$errors = $this->fv->form_errors();
			
			// Errors and submitted data to be displayed in view
			$data = array(
				'form_token' => $form_token, 
				'errors' => empty($errors) ? NULL : $errors, 
				'contact_subject' => $contact_subject,
				'contact_message' => $contact_message,
				'contact_name' => $contact_name,
				'contact_email' => $contact_email,
			);
		} else {
			// Unset the old form token and create new form token
			unset( $_SESSION['form_token']);
			$_SESSION['form_token'] = uniqid();
			
			// Load and instantiate Email library
			$config = array(
				'protocol' => 'sendmail',
				//'protocol' => 'smtp',
				//'smtp_host' => 'ssl://smtp.pitt.edu',
				//'smtp_port' => 465,
				//'smtp_user' => 'ifl',
				//'smtp_pass' => 'jan2001',
			);
			
			$this->load_library('email/email_library', 'email', $config);		

			$this->email->set_newline("\r\n");
			$this->email->from('zhy19@pitt.edu', 'InfoPotato Contact');
			$this->email->reply_to($contact_email, $contact_name);
			//$this->email->to('zhy19@pitt.edu'); 
			//$this->email->cc('another@another-example.com'); 
			$this->email->bcc('yuanzhou19@gmail.com'); 

			$this->email->subject('[InfoPotato Contact Form] '.$contact_subject);
			$this->email->message($contact_message);	

			// Data to be displayed in view
			$data = array(
				'sent' => $this->email->send(), 
			);	
			//echo $this->email->print_debugger();
		}

		$layout_data = array(
			'page_title' => 'Contact',
			'content' => $this->render_template('pages/contact', $data),
		);

		$response_data = array(
			'content' => $this->render_template('layouts/default_layout', $layout_data),
			'type' => 'text/html',
		);
		$this->response($response_data);
	}
	
}

// End of file: ./application/managers/contact_manager.php 