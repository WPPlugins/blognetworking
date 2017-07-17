<?php
class blognetworking_widget extends wv48fv_widget {
	public static $application;
	public function blognetworking_widget() {
		$this->init(self::$application);
		$widget_ops = array ('description' => __ ( "Display posts from your community. See the settings screen to configure your community." ) );
		$this->WP_Widget ( 'blognet', 'Recent Community Posts', $widget_ops );
	}
	public function widget($args, $instance) {
		$title = '';
		if(isset($instance['title']))
		{
			$title=$instance['title'];
		}
		do_action ( 'showNetwork', $title );
	}
	public function update($new_instance, $instance) {
		$instance ['title'] = $new_instance ['title'];
		return $instance;
	}
	public function form($instance) {
		$this->view->title_id = $this->get_field_id ( 'title' );
		$this->view->title_name = $this->get_field_name ( 'title' );
		$this->view->title = '';
		if(isset($instance ['title']))
		{
			$this->view->title = $instance ['title'];
		}
		echo $this->render_script('blognetworking/widget_settings.phtml');
	}

}