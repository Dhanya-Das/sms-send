<?php
/**
* Plugin Name: SMS Send 2.0
* Plugin URI: https://abacies.com
* Description: Customized plugin for sms sending.
* Version: 1.05
* Author: Abacies
* Author URI: https://abacies.com
**/
define( 'SMS_SEND',     plugin_dir_url( __FILE__ )  );
define( 'SMS_SEND_PATH',    plugin_dir_path( __FILE__ ) );
require_once(ABSPATH.'/wp-content/plugins/wpcharitable-textmessage/twilio-php-main/src/Twilio/autoload.php');
use Twilio\Rest\Client;

add_action('wp_enqueue_scripts', 'scripts_for_sms_send_js');
function scripts_for_sms_send_js() {
	wp_enqueue_script('sms_sendjs', SMS_SEND.'assets/js/bootstrap.js', array('jquery'), '1.1.0', true );
	wp_enqueue_script('smssendjs', SMS_SEND.'assets/js/sms-sending.js', array('jquery'), '1.1.1', true );
}

add_action('wp_enqueue_scripts', 'scripts_for_sms_send_css');
add_action('admin_enqueue_scripts', 'scripts_for_sms_send_css');
function scripts_for_sms_send_css() {
	wp_enqueue_style('sms_sendcss', SMS_SEND.'assets/css/bootstrap.css');
	wp_enqueue_style('smssending1_css', SMS_SEND.'assets/css/sms-sending.css');
}

add_action('wp_head', 'myplugin_ajaxurl_sms_send');
function myplugin_ajaxurl_sms_send() {

   echo '<script type="text/javascript">
           var ajaxurl = "' . admin_url('admin-ajax.php') . '";
         </script>';
}
/*
* Get All Form Entries
* */
function get_all_form_entries(){
	global $wpdb;
    $row_data_ = $wpdb->get_results( "SELECT wp_frm_fields.id,
    wp_frm_items.created_at,
    wp_frm_item_metas.meta_value,
    wp_frm_fields.name, 
    wp_frm_fields.form_id FROM wp_frm_fields
    INNER JOIN wp_frm_item_metas ON wp_frm_fields.id=wp_frm_item_metas.field_id 
    INNER JOIN wp_frm_items ON wp_frm_items.form_id=wp_frm_fields.form_id" );
   
   return $row_data_;
}
/*
* Shortcode for send msg button
* */
add_shortcode('text_message_list','text_message_list');
function text_message_list(){
	$user_id = get_current_user_id();
    $organization_data = get_organization_data();
	$html = "";
    if($user_id){
		
		$cmp_ary = array (
			'post_type' => 'campaign',
			'posts_per_page' => -1,
			'author' => $user_id,
			'post_status' =>'publish',
			'order'=> 'ASC');
		$campaign_ary = get_posts($cmp_ary);
		global $wpdb;
    	$table_name = $wpdb->prefix . "formidable_copy"; 
		$result = $wpdb->get_results ( "SELECT * FROM $table_name");
		foreach($result as $student_entries){
			foreach($campaign_ary as $data_){
				if($data->post_title == $studentEntries->campagin_url){
					$is_value = '1';
				}else {
					$is_value = '0';
				}
			}
		}
		$get_list =get_all_form_entries();
		$field_id_settings = get_option('field_id_settings');
		$formID = $field_id_settings['std_formID'];
		$message1ID = $field_id_settings['message1'];
		$message2ID = $field_id_settings['message2'];
		$message3ID = $field_id_settings['message3'];
		$message4ID = $field_id_settings['message4'];

		if($is_value == '1'){
			$html .= "<div class='sms_send'>
						<table class='table' id='form_results10_sms'>
						<thead>
							<tr>
								<th scope='col' style='width: 5%;'>#</th>
								<th scope='col' style='width: 15%;'>Name</th>
								<th scope='col' style='width: 15%;'>Phone</th>
								<th scope='col' style='width: 15%;'>Send SMS</th>
							</tr>
						</thead>
						<tbody>"; 
		}
				$i = 0;
				foreach($result as $studentEntries){
					$i++;
					foreach($campaign_ary as $data_){
						$args = array("post_type" => "campaign", 'posts_per_page' => -1);

						$query = get_posts( $args );
						foreach($query as $data){
							if($data->post_title == $studentEntries->campagin_url){
								$postID = $data->ID;
								
							}
						}
						
						if($data_->post_title == $studentEntries->campagin_url){
								$is_value = true;
								$args_value = array("post_type" => "campaign", 'posts_per_page' => -1);

								$querys = get_posts( $args_value );
								foreach($querys as $datas){
									if($data->post_title == $studentEntries->campagin_url){
										$post_ID = $datas->ID;
										$donation = charitable_get_donation($post_ID);
										$campaign_id = current($donation->get_campaign_donations())->campaign_id;
										if ($campaign_id == $post_ID) {
											$amtRaised = $donation->get_total_donation_amount();
											if($amtRaised){
												$amtraised = number_format((float)$amtRaised, 2, '.', '');
												$amt_raised = "$$amtraised";
											}else{
												$amt_raised = "$0";
											}
										}else {
											$amt_raised = "$0"; 
										}
										
										
									}
								}
								$camp_goal = get_post_meta($post_ID, '_campaign_goal', true);
								if($camp_goal) {
									$campGgoal = "$$camp_goal";
								}else {
									$campGgoal = "$0";
								}
								$author_id = get_post_field( 'post_author', $postID );
								$camp_title = get_the_title($postID);
								$author_name = get_the_author_meta( 'display_name', $author_id );
								$page_link = get_permalink($postID);
								foreach ($get_list as $value) {
									if($value->form_id = $formID){
										if($value->id == $message4ID ) {
											$message4 = $value->meta_value;
											$keywords1 = ["{Campaign Owner}", "{Campaign Goal}", "{Organization}", "{Campaign Title}", "{Campaign URL}", '{Amount Raised}'];
											$values1   = [$author_name, $campGgoal, $organization_data['name'], $camp_title, $page_link, $amt_raised];
											$content1 = str_replace($keywords1, $values1, $message4);
										}
									}
								}
								$phoneNumber = str_replace(array( '(', ')', ' ', '+1', '–' ), '', $studentEntries->phone);
								$html .="<tr>
									<td scope='row'>$i</td>
									<td>$studentEntries->name</td>
									<td><a href='tel:$studentEntries->phone'>$studentEntries->phone</a></td>
									<td> <div class='d-flex'>
												<a class='show_mobile_sms btn btn-dark' id='show_mobilesms' style='margin-left: 1rem; background-color:black; color:#ffffff; text-decoration: none; border-radius: 5px;' id='mobile_submitsms_btn' data-type='sms:?body=' href='sms:+1$phoneNumber?body=$content1'>Send</a>
									 			<a class='show_mobile_ios_sms btn btn-dark' id='show_mobile_iossms' style='margin-left: 1rem; background-color:black; color:#ffffff; text-decoration: none; border-radius: 5px;' id='mobile_submitsms_btn_ios' data-type='sms://?&body=' href='sms:+1$phoneNumber//?&body=$content1'>Send</a>	
												";
								// $html .="<tr>
								// 	<td scope='row'>$i</td>
								// 	<td>$studentEntries->name</td>
								// 	<td><a href='tel:$studentEntries->phone'>$studentEntries->phone</a></td>
								// 	<td> <div class='d-flex'>
								// 			<form action='' class='d-flex sms-submit-form' id='sms-submit-form' method='post'> 
								// 				<select class='sms-form-select' id='sms-form-select' name='sms-form-select'>
								// 				<option value='$content1 $page_link'>MSG1</option>
								// 				<option value='$content2 $page_link'>MSG2</option>
								// 				<option value='$content3 $page_link'>MSG3</option>
								// 				</select>
								// 				<a class='show_mobile_sms btn btn-dark' id='show_mobilesms' style='margin-left: 1rem; background-color:black; color:#ffffff; text-decoration: none; border-radius: 5px;' id='mobile_submitsms_btn' data-type='sms:?body=' href='sms:?body=$content1 $page_link'>Send</a>
								// 	 			<a class='show_mobile_ios_sms btn btn-dark' id='show_mobile_iossms' style='margin-left: 1rem; background-color:black; color:#ffffff; text-decoration: none; border-radius: 5px;' id='mobile_submitsms_btn_ios' data-type='sms://?&body=' href='sms://?&body=$content1 $page_link'>Send</a>	
								// 				</form>";
									$html .="</div>
									</td>";
							}
					}
				}
				$html .= "</tbody>
				</table>";
				$html .= "<div class='loader d-none' id ='message-sent-sms'>
							<div class='d-flex justify-content-center' >
								<div class='spinner-border' role='status'>
								<span class='sr-only'>Loading...</span>
								</div>
							</div>
						</div>";
			
			return $html;
	}
}
