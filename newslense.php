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

  // Enqueue clf_load_scripts
  public function nl_load_assets(){
    wp_enqueue_style( 'newslense-css', NL_PLUGIN_URL . '/css/newslense.css', [], time(), 'all' );
    wp_enqueue_script( 'newslense-js', NL_PLUGIN_URL . '/css/newslense.js', [], time(), 'all' );
  }

  public function __construct(){
    add_shortcode( 'newsletter-lense', array( $this, 'nl_load_shortcode') );
  }

  // Shortcode Function for the homepage
  public function nl_load_shortcode(){
    ?>
    <div class="footer__newsletter footer--item">
      <h2 class="email__sub--title">Sign up for the monthly ZLC Newsletter</h2>
      <div class="email__sub--desc">
        <p>All the month's headlines and highlights from Zimbabwe Land Commission, direct to you monthly</p>
        <div class="email__form">
          <form action="#">
            <div class="email__form--input">
              <input type="email" id="email-addr" placeholder="Email address">
              <button id="email-button">Sign up</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <?php
  }
}
new NewsLense;
