<?php
/*****************************************************************************************
* ??document??
*****************************************************************************************/
class wv48fv_mail extends bv48fv_mail {
/*****************************************************************************************
* ??document??
*****************************************************************************************/
	protected function headercharset() {
		return "charset=\"" . get_option ( 'blog_charset' ) . "\"\n";
	}
/*****************************************************************************************
* ??document??
*****************************************************************************************/
	protected function sendit($to, $subject, $message, $headers = "") {
		wp_mail ( $to, $subject, $message, $headers );
	}
}