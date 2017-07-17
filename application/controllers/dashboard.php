<?php
class blognetworking_dashboard extends wv48fv_action {
	public function blognetworkingWPmenuMeta($return) {
		$return ['title'] = $this->application ()->name;
		$return ['menu'] = 'Settings';
		return $return;
	}
	public function settingsActionMeta($return) {
		$return ['link_name'] = $return ['title'];
		$return ['priority'] = - 1;
		$return ['classes'] [] = 'v48fv_16x16_settings';
		return $return;
	}
	public function settingsAction() {
		$this->view->network=$this->application ()->data ()->post ('network');
		$this->view->title = $this->help('settings')->render('Settings');
		$this->view->column_count=2;
		$this->view->rows[] = $this->render_script('blognetworking/row1.phtml');
		$this->view->rows[] = $this->render_script('blognetworking/row2.phtml');
		$this->view->next = 'never';
		$scheduled = wp_next_scheduled('blognetworking_sync');
		if($scheduled!==false)
		{
			$this->view->next = gmdate(get_option('date_format').' '.get_option('time_format'),$scheduled);
		}		
		$this->view->rows[] = $this->render_script('blognetworking/row5.phtml');
		$this->view->rows[] = $this->render_script('blognetworking/row3.phtml');
		$this->view->rows[] = $this->render_script('blognetworking/row4.phtml');
		$this->view->apply="Update Settings &amp; Network";
		return $this->render_table();
	}
}