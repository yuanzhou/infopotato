<?php
final class Js_Manager extends Manager {
	public function get_index($params = array()) {
		// $js_files is an array created from $params[0]
		$js_files = count($params) > 0 ? explode(':', $params[0]) : NULL;
		
		if ($js_files != NULL) {
			$js_content = '';
			foreach ($js_files as $js_file) {
				$js_content .= $this->_render_template('js/'.$js_file);
			}
			
			$response_data = array(
				'content' => $js_content,
				'type' => 'text/javascript'
			);
			$this->_response($response_data);
		}
	}
}

/* End of file: ./application/managers/js_manager.php */
