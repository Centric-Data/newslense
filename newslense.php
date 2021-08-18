<?php
/**
 * News-Lense
 *
 * @package     News-Lense
 * @author      Centric Data
 * @copyright   2021 Centric Data
 * @license     GPL-2.0-or-later
 *
*/
/*
Plugin Name: News-Lense
Plugin URI:  https://github.com/Centric-Data/newslense
Description: This is a custom newsletter plugin, it can be activated using a plugin shortcode. Its using a two column layout, with custom css.
Author: Centric Data
Version: 1.0.0
Author URI: https://github.com/Centric-Data
Text Domain: newslense
*/
/*
News-Lense is free software: you can redistribute it and/or modify it under the terms of GNU General Public License as published by the Free Software Foundation, either version 2 of the License, or any later version.

News-Lense is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Contact-Lense Form.
*/

/* Exit if directly accessed */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define variable for path to this plugin file.
define( 'NL_LOCATION', dirname( __FILE__ ) );
define( 'NL_LOCATION_URL' , plugins_url( '', __FILE__ ) );
define( 'NL_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 *
 */
class NewsLense
{

  public function __construct(){
    add_shortcode( 'newsletter-lense', array( $this, 'nl_load_shortcode') );

    add_action( 'wp_enqueue_scripts', array( $this, 'nl_load_assets' ) );

    add_action( 'init', array( $this, 'nl_custom_post_type' ) );

    add_filter( 'manage_centric_subscribe_posts_columns', array( $this, 'nl_subcribe_columns' ) );

    add_filter( 'enter_title_here', array( $this, 'nl_change_title_text' ) );

    add_action( 'manage_centric_subscribe_posts_custom_column', array( $this, 'nl_subscribe_custom_columns' ), 10, 2 );

    add_action( 'wp_footer', array( $this, 'nl_load_scripts' ) );

    add_action( 'rest_api_init', array( $this, 'nl_register_rest_api' ) );
  }

  // Enqueue clf_load_scripts
  public function nl_load_assets(){
    wp_enqueue_style( 'newslense-css', NL_PLUGIN_URL . 'css/newslense.css', [], time(), 'all' );
    wp_enqueue_script( 'newslense-js', NL_PLUGIN_URL . 'css/newslense.js', [], time(), 'all' );
  }

  // Shortcode Function for the homepage
  public function nl_load_shortcode(){
    ?>
    <div class="footer__newsletter footer--item">
      <h2 class="email__sub--title">Sign up for the monthly ZLC Newsletter</h2>
      <div class="email__sub--desc">
        <p>All the month's headlines and highlights from Zimbabwe Land Commission, direct to you monthly</p>
        <div class="email__form">
          <form id="nl_form">
            <div class="email__form--input">
              <input type="email" id="email_addr" name="email_addr" value="" placeholder="Email address">
              <button type="submit" id="email-button">Sign up</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <?php
  }

  /**
  * Run script on submit
  *
  */
  public function nl_load_scripts()
  {?>
    <script>

      let nl_nonce  = '<?php wp_create_nonce('nl_wp_rest');  ?>'

      ( function( $ ){
        $('#nl_form').on("submit", function( e ){
          e.preventDefault();
          let form = $( this ).serialize();
          console.log( form );

          $.ajax({
            method: 'post',
            url: '<?php echo get_rest_url( null, 'newslense/v1/subscribe' ); ?>',
            headers: { 'X-WP-Nounce': nl_nonce },
            data: form
          })

        });

      } )( jQuery )
    </script>
  <?php }

  /**
  * Register Rest API
  *
  */
  public function nl_register_rest_api(){
    register_rest_route( 'newslense/v1', 'subscribe', array(
      'methods' =>  'POST',
      'callback'  =>  array( $this, 'handle_subscription_form' )
    ) );
  }

  public function handle_subscription_form( $data ){
    $headers  = $data->get_headers();
    $params   = $data->get_params();

    $nonce    = $headers[ 'x_wp_nonce' ][0];

    // Verify nonce
    if( ! wp_verify_nonce( $nonce, 'nl_wp_rest' ) ){
      return new WP_REST_Response( 'Message not sent', 422 );
    }

    // Insert into post
    $post_id  = wp_insert_post( [
      'post_type'   => 'centric_subscribe',
      'post_title'  =>  wp_strip_all_tags( $params['email_addr'] ),
      'post_status' =>  'status'
      ] );

      add_post_meta( $post_id, 'email_addr', $params['email'] );

    // Success Message
    if( $post_id ){
      return new WP_REST_Response( $params['email_addr'], 200 );
    }

  }

  /**
  * Register a custom post type called 'centric_subscribe'
  *
  */
  public function nl_custom_post_type(){
    $labels = array(
      'name'          =>  _x( 'Subscriptions', 'newslense' ),
      'singular_name' =>  _x( 'Subscription', 'newslense' ),
      'menu_name'     =>  _x( 'Subscriptions', 'newslense' ),
      'add_new'       =>  __( 'Add New', 'newslense' ),
      'add_new_item'  =>  __( 'Add New Subscriber', 'newslense' ),
      'new_item'      =>  __( 'New Subscriber', 'newslense' ),
      'edit_item'     =>  __( 'Edit Subscriber', 'newslense' ),
      'view_item'     =>  __( 'View Subscriber', 'newslense' ),
      'all_items'     =>  __( 'All Subscribers', 'newslense' ),
      'search_items'  =>  __( 'Search Subscribers', 'newslense' ),
    );

    $args = array(
      'labels'        =>  $labels,
      'public'        =>  true,
      'has_archive'   =>  true,
      'hierarchical'  =>  false,
      'supports'      =>  array( 'title' ),
      'capability_type' =>  'post',
      'exclude_from_search' =>  true,
      'publicly_queryable'  =>  false,
      'menu_icon'           =>  'dashicons-universal-access-alt',
    );

    register_post_type( 'centric_subscribe', $args );
  }

  /**
  * Custom Subscription Columns
  *
  */
  public function nl_subcribe_columns( $columns ){

    $newColumns = array();
    $newColumns['title']  = 'Subscriber Email';
    $newColumns['date']   = 'Joined On';

    return $newColumns;
  }

  /**
  * Manage Custom Subscription Columns
  *
  */
  public function nl_subscribe_custom_columns( $columns, $post_id ){
    switch ( $columns ) {
      case 'title':
        $email = get_post_meta( $post_id, $_POST[ 'email_addr'] , true);
        echo $email;
        break;

      default:
        // code...
        break;
    }
  }

  /**
  * Change Input Title
  *
  */
  public function nl_change_title_text( $title ){
    $screen = get_current_screen();

    if( 'centric_subscribe' === $screen->post_type ){
      $title = 'Add subscriber email';
    }

    return $title;
  }

}
new NewsLense;
