<?php
/*
Plugin Name: Advanced LIKE SYSTEM BY SHIRSHAK BAJGAIN.
Description: Best Facebook Like Plugin
Author: Shirshak Bajgain
Version: 1.0
Text Domain: shirshak
License: MIT
*/
defined('ABSPATH') or die("Cannot access pages directly."); 

// Register the Script
add_action( 'wp_enqueue_scripts', function () {

	wp_register_script(
		'shirshak_like_plugin',
		plugins_url( 'like_system.js', __FILE__ )
	);
});
add_action( 'wp_enqueue_scripts', function() {
	wp_enqueue_script(
		'shirshak_like_plugin',
		plugins_url( 'like_system.js', __FILE__ ),
		array( 'jquery' ),
		false,
		true
	);

	wp_localize_script(
		'shirshak_like_plugin',
		'like_ajax',
		array(
			'url'   => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'shirshak-likes-nounce' )
		)
	);
});
/*Things to do in ajax*/
function like_ajax_hook(){
	$get_nounce=esc_attr( $_POST['nonce'] );

	if( false == wp_verify_nonce( $get_nounce, 'shirshak-likes-nounce' ) ) {
		$errors=["data"=>_e( 'OMG You should not have done this... :(', 'shirshak' )];
		die(json_encode($errors));
	}
	if(isset($_POST['what_to_do']) AND isset( $_POST['postID'] )){
		$what_to_do=$_POST['what_to_do'];

		if(function_exists('get_client_ip')){$user_ip = get_client_ip();}else{$user_ip = $_SERVER['REMOTE_ADDR'];}

		$post_id = esc_attr( $_POST['postID'] );
		$likers_ip=likers_ip( $post_id);
		$dislikers_ip=dislikers_ip( $post_id );
		$likes_count=likes_count( $post_id );
		$dislikes_count=dislikes_count( $post_id );
		$data=[];

		if($what_to_do=="like"):
			if( already_liked( $post_id )==="No"){
				if(already_disliked( $post_id)=="Yes"){
					remove_dislikers_ip($dislikers_ip,$user_ip,$post_id);
					remove_dislike_count($post_id,$dislikes_count);
				}
				if(already_disliked( $post_id)=="No"){
					add_likers_ip($likers_ip,$user_ip,$post_id);
					add_like_count($post_id,$likes_count);
				}
			}elseif(already_liked( $post_id )==="Yes"){
				remove_likers_ip($likers_ip,$user_ip,$post_id);
				remove_like_count($post_id,$likes_count);
			}
		endif;

		if($what_to_do=="dislike"):
			if( already_disliked( $post_id )==="No"){
				if(already_liked( $post_id)=="Yes"){
					remove_likers_ip($likers_ip,$user_ip,$post_id);
					remove_like_count($post_id,$likes_count);
				}
				if(already_liked( $post_id)=="No"){
					add_dislikers_ip($dislikers_ip,$user_ip,$post_id);
					add_dislike_count($post_id,$dislikes_count);
				}
			}elseif(already_disliked( $post_id )==="Yes"){
				remove_dislikers_ip($dislikers_ip,$user_ip,$post_id);
				remove_dislike_count($post_id,$dislikes_count);
			}
		endif;


	$data["likes_count"]=likes_count( $post_id );
	$data["dislikes_count"]=dislikes_count( $post_id );
	if(already_liked( $post_id )=="Yes"){
		$data["allow_dislike"]="No";
	}elseif(already_liked( $post_id )=="No"){
		$data["allow_dislike"]="Yes";
	}

	if(already_disliked( $post_id )=="Yes"){
		$data["allow_dislike"]="No";
	}elseif(already_disliked( $post_id )=="No"){
		$data["allow_dislike"]="Yes";
	}
	echo json_encode($data);
	}
	exit;
}
add_action( 'wp_ajax_nopriv_like-post', 'like_ajax_hook' );
add_action( 'wp_ajax_like-post', 'like_ajax_hook' );

function likers_ip($post_id){
	$likers_ip  = get_post_meta( $post_id, 'likers_ip',true);
	if( "No" == is_array( $likers_ip ) ) {
			$likers_ip = array();
	}
	return $likers_ip;
}
function likes_count($post_id){
	if(get_post_meta( $post_id, 'likes_count', true )){
		$likes_count = get_post_meta( $post_id, 'likes_count', true );
	}else{
		$likes_count=0;
	}
	return $likes_count;
}
function add_likers_ip($likers_ip,$user_ip,$post_id){
	$likers_ip[$user_ip] = time();
	update_post_meta( $post_id, 'likers_ip', $likers_ip );
	return $likers_ip;
}
function  add_like_count($post_id,$likes_count){
	update_post_meta( $post_id, 'likes_count', ++$likes_count );
	return $likes_count;
}
function remove_likers_ip($likers_ip,$user_ip,$post_id){
	unset($likers_ip[$user_ip]);
	update_post_meta( $post_id, 'likers_ip', $likers_ip );
	return $likers_ip;
}
function remove_like_count($post_id,$likes_count){
	update_post_meta( $post_id, 'likes_count', --$likes_count );
	return $likes_count;
}

function dislikers_ip($post_id){
	$dislikers_ip  = get_post_meta( $post_id, 'dislikers_ip',true);
	if( false == is_array( $dislikers_ip ) ) {
			$likers_ip = array();
	}
	return $dislikers_ip;
}
function dislikes_count($post_id){
	if(get_post_meta( $post_id, 'dislikes_count', true )){
		$dislikes_count = get_post_meta( $post_id, 'dislikes_count', true );
	}else{
		$dislikes_count=0;
	}
	return $dislikes_count;
}

function add_dislikers_ip($dislikers_ip,$user_ip,$post_id){
	$dislikers_ip[$user_ip] = time();
	update_post_meta( $post_id, 'dislikers_ip', $dislikers_ip );
	return $dislikers_ip;
}
function  add_dislike_count($post_id,$dislikes_count){
	update_post_meta( $post_id, 'dislikes_count', ++$dislikes_count );
	return $dislikes_count;
}
function remove_dislikers_ip($dislikers_ip,$user_ip,$post_id){
	unset($dislikers_ip[$user_ip]);
	update_post_meta( $post_id, 'dislikers_ip', $dislikers_ip );
	return $dislikers_ip;
}
function remove_dislike_count($post_id,$dislikes_count){
	update_post_meta( $post_id, 'dislikes_count', --$dislikes_count );
	return $dislikes_count;
}

function already_liked( $post_id ) {
	$database_likers_ip  = get_post_meta( $post_id, 'likers_ip',true );

	if( false == is_array( $database_likers_ip ) ) {
		$database_likers_ip = array();
	}

	if(function_exists('get_client_ip')){$user_ip = get_client_ip();}else{$user_ip = $_SERVER['REMOTE_ADDR'];}

	if( in_array( $user_ip, array_keys( $database_likers_ip ) ) ) {
		return "Yes";
	} else {
		return "No";
	}
	die();
}
function already_disliked( $post_id ) {
	$database_dislikers_ip  = get_post_meta( $post_id, 'dislikers_ip',true );

	if( false == is_array( $database_dislikers_ip ) ) {
		$database_dislikers_ip = array();
	}

	if(function_exists('get_client_ip')){$user_ip = get_client_ip();}else{$user_ip = $_SERVER['REMOTE_ADDR'];}

	if( in_array( $user_ip, array_keys( $database_dislikers_ip ) ) ) {
		return "Yes";
	} else {
		return "No";
	}
	die();
}

function show_likes_dislikes( $post_id ) {
	$likes_count = get_post_meta( $post_id, 'likes_count', true );
	$dislikes_count = get_post_meta( $post_id, 'dislikes_count', true );
	if(already_liked($post_id)=="Yes"){
		$likeclass="liked";
	}else{
		$likeclass="";
	}
	if(already_disliked($post_id)=="Yes"){
		$dislikeclass="disliked";
	}else{
		$dislikeclass='';
	}
	if(function_exists('get_client_ip')){$user_ip = get_client_ip();}else{$user_ip = $_SERVER['REMOTE_ADDR'];}

	$output = '
		<p class="post-like"> Do you like this article ? If yes then like otherwise dislike : 
			<span class="like_system">
				<a  data-what_to_do="like" class="like_button ' . $likeclass . '" data-postid="' . $post_id . '" href="#">
				</a>	
				<span class="likes_count">'.$likes_count.'</span>
			</span>
			<span class="dislike_system">
				<a  data-what_to_do="dislike" class="dislike_button ' . $dislikeclass . '" data-postid="' . $post_id . '" href="#">
				</a>	
				<span class="dislikes_count">'.$dislikes_count.'</span>
			</span>
		</p>
	';

	echo $output;
}
function show_likes_count($post_id){
    $like_count = get_post_meta($post_id, "likes_count", true);
    $like_count = !empty( $like_count)? $like_count:"0";
    return $like_count;
}
function show_dislikes_count($post_id){
    $dislike_count = get_post_meta($post_id, "dislikes_count", true);
    $dislike_count = !empty( $dislike_count)? $dislike_count:"0";
    return $dislike_count;
}