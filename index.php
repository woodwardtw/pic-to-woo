<?php 
/*
Plugin Name: pic to woo
Plugin URI:  https://github.com/
Description: Takes images added to the media library and transforms them into products in woocommerce
Version:     1.0
Author:      Your dad
Author URI:  http://altlab.vcu.edu
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Domain Path: /languages
Text Domain: my-toolset

*/
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );


add_action('wp_enqueue_scripts', 'prefix_load_scripts');

function prefix_load_scripts() {                           
    $deps = array('jquery');
    $version= '1.0'; 
    $in_footer = true;    
    wp_enqueue_script('prefix-main-js', plugin_dir_url( __FILE__) . 'js/prefix-main.js', $deps, $version, $in_footer); 
    wp_enqueue_style( 'prefix-main-css', plugin_dir_url( __FILE__) . 'css/prefix-main.css');
}


function pic_to_woo_maker( $attachment_id ) {
       // Create post object
        $my_post = array(
          'post_title'    => wp_strip_all_tags( 'image ' . $attachment_id ),
          'post_content'  => '',
          'post_status'   => 'publish',
          'post_author'   => 1,
          'post_type'     => 'product',
        );
         
        // Insert the post into the database
        $new_prod = wp_insert_post( $my_post );
        set_post_thumbnail( $new_prod, $attachment_id);
        //_regular_price
        update_post_meta( $new_prod, '_price', '15.00' );
        update_post_meta( $new_prod, '_regular_price', '15.00' );
        update_post_meta( $new_prod, '_downloadable' , 'yes');
        update_post_meta( $new_prod, '_virtual' , 'yes');   
        

      //download file portion 

      $file_name = 'image ' . $attachment_id;
      $file_url  = wp_get_attachment_url( $attachment_id);
      $download_id = md5( $file_url );

      // Creating an empty instance of a WC_Product_Download object
      $pd_object = new WC_Product_Download();

      // Set the data in the WC_Product_Download object
      $pd_object->set_id( $download_id );
      $pd_object->set_name( $file_name );
      $pd_object->set_file( $file_url );

      // Get an instance of the WC_Product object (from a defined product ID)
      $product = wc_get_product( $new_prod ); // <=== Be sure it's the product ID

      // Get existing downloads (if they exist)
      $downloads = $product->get_downloads();

      // Add the new WC_Product_Download object to the array
      $downloads[$download_id] = $pd_object;

      // Set the complete downloads array in the product
      $product->set_downloads($downloads);
      $product->save(); // Save the data in database
  }
add_action( 'add_attachment', 'pic_to_woo_maker' );



//LOGGER -- like frogger but more useful

if ( ! function_exists('write_log')) {
   function write_log ( $log )  {
      if ( is_array( $log ) || is_object( $log ) ) {
         error_log( print_r( $log, true ) );
      } else {
         error_log( $log );
      }
   }
}