<?php
/**
 * post-process.php
 * make sure to include post-process.php in your functions.php. Use this in functions.php:
 *
 * get_template_part('post-process');
 *
 */
function do_insert() {
	if( 'POST' == $_SERVER['REQUEST_METHOD']
		&& !empty( $_POST['action'] )
		&& $_POST['post_type'] == 'centric_subscribe' ) { // Check what the post type is here instead

		// Setting the 'post_type' => $_POST['post_type'] in the $post array below causes 404
		// Just set it based on what is set in the above IF $_POST type == 'book'.
		// and below do 'post_type' => 'book'

		// Do some minor form validation to make sure there is content
		if (isset ($_POST['email_addr'])) { $title =  $_POST['email_addr']; } else { echo 'Please enter email'; }


		// Add the content of the form to $post as an array
		$post = array(
			'post_title'	=> $title,
			'post_status'	=> 'publish', // Choose: publish, preview, future, etc.
			'post_type'		=> 'centric_subscribe' // Set the post type based on the IF is post_type X
		);
		wp_insert_post($post);  // Pass  the value of $post to WordPress the insert function
								// http://codex.wordpress.org/Function_Reference/wp_insert_post
	} // end IF
}

// Do the wp_insert_post action to insert it
do_action('wp_insert_post', 'do_insert');
?>
