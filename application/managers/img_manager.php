<?php
final class Img_Manager extends Manager {
	public function get_index() {
 
        $config['image_library_to_use'] = 'gd2';
		$config['source_image']	= APP_DIR.'t.jpg';
		//$config['new_image']	= APP_DIR.'t2.jpg';
		$config['x_axis'] = 500;
        $config['y_axis'] = 200;
		$config['width']	 = 75;
        $config['height']	= 50;
		
		$this->load_library('SYS', 'image/image_library', 'img', $config);

		if ( ! $this->img->crop()) {
			echo $this->img->display_errors();
		}
	}

}

// End of file: ./application/managers/home_manager.php
