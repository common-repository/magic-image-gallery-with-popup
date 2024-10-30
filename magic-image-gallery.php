<?php
/*
Plugin name: Magic Image Gallery With Popup
Description: plugin for create excellent image gallery.
Version: 0.1.0
Author: Webgensis Team
*/

// Add Script and Style
function mig_enqueue_script()
	{
	    wp_enqueue_style('pala-styles', plugin_dir_url(__FILE__) . 'css/photopile.css');
		wp_enqueue_script('pal-script', plugin_dir_url(__FILE__) . 'js/photopile.js', array('jquery'), null, true);
	}
add_action('wp_enqueue_scripts', 'mig_enqueue_script');

/*********** Register Post Type for Gallery ***********/
add_action('init', 'mig_init');
function mig_init() {
	   $labels = array(
		'name'               => _x( 'Magic Galleries'),
		'singular_name'      => _x( 'Magic Gallery'),
		'menu_name'          => _x( 'Magic Galleries'),
		'name_admin_bar'     => _x( 'Magic Gallery'),
		'add_new'            => _x( 'Add New', 'Gallery'),
		'add_new_item'       => __( 'Add New Gallery'),
		'new_item'           => __( 'New Gallery'),
		'edit_item'          => __( 'Edit Gallery'),
		'all_items'          => __( 'All Galleries'),
		'search_items'       => __( 'Search Gallery'),
		'not_found'          => __( 'No Gallery found.'),
		'not_found_in_trash' => __( 'No Gallery found in Trash.')
	);

    $args = array(
		'labels'             => $labels,
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => true,
		'rewrite'            => array( 'slug' => 'magic_gallery' ),
		'capability_type'    => 'post',
		'has_archive'        => true,
		'hierarchical'       => false,
		'menu_position'      => null,
		'supports'           => array( 'title')
	);
    
	register_post_type( 'magic_gallery', $args );
  }
/********************* Integrate CMB2 For Meta Box ********************/
 if (file_exists( dirname( __FILE__ ) . '/cmb2/init.php' ) ) {
	require_once dirname( __FILE__ ) . '/cmb2/init.php';
} elseif ( file_exists( dirname( __FILE__ ) . '/CMB2/init.php' ) ) {
	require_once dirname( __FILE__ ) . '/CMB2/init.php';
}
add_action( 'cmb2_admin_init', 'mig_metaboxes');
function mig_metaboxes() { 
   
   $cmb = new_cmb2_box( array(
       'id'            => 'mig_gallery_metabox',
       'title'         => __( 'Gallery Items', 'cmb2' ),
       'object_types'  => array( 'magic_gallery', ),
       'context'       => 'normal',
       'priority'      => 'high',
       'show_names'    => true, 
      ) );	
   
   $group_field_id = $cmb->add_field( array(
    'id'          => 'mig_repeat',
    'type'        => 'group',
    'options'     => array(
        'group_title'   => __( 'Item {#}', 'cmb2' ), 
        'add_button'    => __( 'Add Another Item', 'cmb2' ),
        'remove_button' => __( 'Remove Item', 'cmb2' ),
        'sortable'      => true,   
     ),
    ) );
	
	$cmb->add_group_field( $group_field_id, array(
    'name' => 'Title',
    'id'   => 'mig_title',
    'type' => 'text',
    ) );
	
    $cmb->add_group_field( $group_field_id, array(
    'name' => 'Image',
    'id'   => 'mig_image',
    'type' => 'file',
    ) );	 	   	
 }

/********************* Add Shortcode *******************/
function mig_shortcode($atts)
 { 
  extract(shortcode_atts(array(
  'gallery_id' => '',
  'post_type' => 'magic_gallery',
   ) , $atts));
  $args = array(
  'post_type' =>'magic_gallery',
   'p' => $gallery_id,
  );
  $query = new WP_Query($args);
  if ($query->have_posts()):
  while ($query->have_posts()):
  $query->the_post();
  $mig_repeat = get_post_meta(get_the_ID(),'mig_repeat',true);
  ?>
  <div class="photopile-wrapper">
  <ul class="photopile">
   <?php if(!empty($mig_repeat)){
    foreach($mig_repeat as $key => $mig_repeats){
     $mig_full_image = $mig_repeats['mig_image'];
     $mig_title = $mig_repeats['mig_title'];
    ?>
    <li>
      <a href="<?php echo esc_html($mig_full_image); ?>">
        <img src="<?php echo esc_html($mig_full_image); ?>" alt="<?php echo esc_html($mig_title);  ?>" />
      </a>
    </li>
    <?php }} ?>
  </ul>
</div>
  <?php
  endwhile;
  wp_reset_postdata();
  endif;
  }
 add_shortcode('mig', 'mig_shortcode');

/*********************** Show Short code ******************/
add_filter('manage_magic_gallery_posts_columns', 'mic_head_magic_gallery', 10);
add_action('manage_magic_gallery_posts_custom_column', 'mic_only_magic_gallery', 10, 2);
function mic_head_magic_gallery($defaults) {
    $defaults['shortcode_name'] = 'Shortcode';
    return $defaults;
	}
function mic_only_magic_gallery($column_name, $post_ID) {
    if ($column_name == 'shortcode_name') {
        echo esc_html("[mig gallery_id=". $post_ID. "]");
    }
}
