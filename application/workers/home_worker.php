<?php
class Home_Worker extends Worker {
	public function process() {
		$layout_data = array(
			'page_title' => 'Home',
			'content' => $this->load_template('pages/home'),
		);
		
		$response_data = array(
			'content' => $this->load_template('layouts/default_layout', $layout_data),
			'type' => 'text/html',
		);
		$this->response($response_data);
	}
}

// End of file: ./application/presenters/home_worker.php
