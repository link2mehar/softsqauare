<?php
/**
 * Plugin Name:       SoftSquare Plugin
 * Version:           1.0.0
 * Author:            Irfan Razzaq
 */


//Styles & Scripts
function softsquare_scripts(){

  wp_enqueue_style( 'softsqaure-product', get_template_directory_uri() . '/assets/styles.css',false,'1.1','all');


} 
add_action('wp_enqueue_scripts','softsquare_scripts' );

//Register post 
function softsquare_custom_post_product() {
  $labels = array(
    'name'               => _x( 'Products', 'post type general name' ),
    'singular_name'      => _x( 'Product', 'post type singular name' ),
    'add_new'            => _x( 'Add New', 'book' ),
    'add_new_item'       => __( 'Add New Product' ),
    'edit_item'          => __( 'Edit Product' ),
    'new_item'           => __( 'New Product' ),
    'all_items'          => __( 'All Products' ),
    'view_item'          => __( 'View Product' ),
    'search_items'       => __( 'Search Products' ),
    'not_found'          => __( 'No products found' ),
    'not_found_in_trash' => __( 'No products found in the Trash' ), 
    'menu_name'          => 'Products'
  );
  $args = array(
    'labels'        => $labels,
    'description'   => 'Holds our products and product specific data',
    'public'        => true,
    'menu_position' => 5,
    'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt', 'comments' ),
    'has_archive'   => true,
  );
  register_post_type( 'product', $args ); 
}
add_action( 'init', 'softsquare_custom_post_product' );

//register taxonomy
function softsquare_product_taxonomy() {

  //labels array
  
  $labels = array(
  'name' => _x( 'Product Categories', 'taxonomy general name' ),
  'singular_name' => _x( 'Product Category', 'taxonomy singular name' ),
  'search_items' => __( 'Search Product Categories' ),
  'all_items' => __( 'All Product Categories' ),
  'parent_item' => __( 'Parent Product Category' ),
  'parent_item_colon' => __( 'Parent Product Category:' ),
  'edit_item' => __( 'Edit Product Category' ),
  'update_item' => __( 'Update Product Category' ),
  'add_new_item' => __( 'Add New Product Category' ),
  'new_item_name' => __( 'New Product Category' ),
  'menu_name' => __( 'Product Categories' ),
  );
  
  //args array
  
  $args = array(
  'labels' => $labels,
  'hierarchical' => true,
  );
  
  register_taxonomy( 'product_category', 'product', $args );
  }


  add_action( 'init', 'softsquare_product_taxonomy');


  //link custom meta box with cpt
  function softsquare_product_meta_box() {

    add_meta_box(
        'product-bg',
        __( 'Product background', 'textdomain' ),
        'softsquare_product_meta_box_callback',
        'product',
        'side',
        'high'
    );
}

add_action( 'add_meta_boxes', 'softsquare_product_meta_box' );

//show custom fields
function softsquare_product_meta_box_callback( $post ) {

  wp_nonce_field( 'product_bg_nonce', 'product_bg_nonce' );
  $value = get_post_meta( $post->ID, '_product_bg', true );

  echo '<input type="color"  id="product_bg" name="product-bg" value="'.esc_attr( $value ).'"/>';
}

//save custom fields
function softsquare_produc_meta_box_data( $post_id ) {

  // Check if our nonce is set.
  if ( ! isset( $_POST['product_bg_nonce'] ) ) {
      return;
  }

  // Verify that the nonce is valid.
  if ( ! wp_verify_nonce( $_POST['product_bg_nonce'], 'product_bg_nonce' ) ) {
      return;
  }

  // If this is an autosave, our form has not been submitted, so we don't want to do anything.
  if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
      return;
  }

  // Check the user's permissions.
  if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {

      if ( ! current_user_can( 'edit_page', $post_id ) ) {
          return;
      }

  }
  else {

      if ( ! current_user_can( 'edit_post', $post_id ) ) {
          return;
      }
  }

  /* OK, it's safe for us to save the data now. */

  // Make sure that it is set.
  if ( ! isset( $_POST['product-bg'] ) ) {
      return;
  }

  // Sanitize user input.
  $data = sanitize_text_field( $_POST['product-bg'] );

  // Update the meta field in the database.
  update_post_meta( $post_id, '_product_bg', $data );
}

add_action( 'save_post', 'softsquare_produc_meta_box_data' );



//add cpt to main query
add_action( 'pre_get_posts', 'add_product_types_to_query' );
 
function add_product_types_to_query( $query ) {
    if ( is_home() && $query->is_main_query() )
        $query->set( 'post_type', array( 'product' ) );
    return $query;
}



//shotcode for showing the cpt
function softsquare_product_shortcode() { 
 
  $args = array(  
        'post_type' => 'product',
        'post_status' => 'publish',
        'posts_per_page' => 8, 
        'orderby' => 'title', 
        'order' => 'ASC', 
    );

    $loop = new WP_Query( $args ); 
    echo '<div class="container flex">';    
    while ( $loop->have_posts() ) : $loop->the_post();
      $bg = get_post_meta( get_the_ID(), '_product_bg', true )
    ?>
        
        <div style="background-color:<?php echo $bg ?>" class="p-3 w-4">
          <div class="content">
            <h1><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h1>
          </div>
          <div class="thumb">
            <?php the_post_thumbnail(); ?>
          </div>
          
        </div>
       
    <?php endwhile;
    echo '</div>';  
    wp_reset_postdata(); 
  } 

  add_shortcode('softsquare_products', 'softsquare_product_shortcode'); 




