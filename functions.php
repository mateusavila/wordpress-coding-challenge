<?php 
add_theme_support( 'title-tag' );
add_theme_support( 'post-thumbnails' );
function add_cors_http_header(){
    header("Access-Control-Allow-Origin: *");
}
add_action('init','add_cors_http_header');

add_action('rest_api_init', function () {
  register_rest_route( '/api', '/home', array(
    'methods' => 'GET',
    'callback' => 'home_route_api')
  );
  register_rest_route( '/api', '/blog', array(
    'methods' => 'GET',
    'callback' => 'get_blog_posts')
  );
});

function validate_get_field ($field, $error_message) {
  if(empty($field)) {
    return wp_send_json(array(
      'error' =>  $error_message
    ), 404); 
  }
}

function get_loop_single_data ($wpquery_object) {
  $data = array();
  $data['page'] = array();
  while ($wpquery_object->have_posts()) : $wpquery_object->the_post();
    global $post;
    $authorID = get_post_field('post_author', get_the_ID());
    $author = get_userdata($authorID);
    $data['page'] = array();
    $data['page']['id']           = get_the_ID();
    $data['page']['title']        = get_the_title();
    $data['page']['slug']         = $post->post_name;
    $data['page']['category']     = get_the_category();
    $data['page']['content']      = wpautop(apply_filters('the_content', get_the_content()));
    $data['page']['category']     = get_the_category();
    $data['page']['date']         = get_the_date('d/m/Y');
    $data['page']['pretty']       = get_the_date('d/m/Y');
    $data['page']['updated_date'] = get_the_modified_time('d/m/Y');
    $data['page']['views']        = (int) get_post_meta(get_the_ID(), 'views', true) ?? false;
    $data['page']['author']       = [];

    // gambiarra para resolver a interna de noticias
    if($author) {
      $data['page']['author'] =  array(
        'name'        => $author->user_nicename,
        'email'       => $author->user_email,
        'photo'       => get_avatar_url($author->user_email),
        'description' => get_the_author_meta('description', $author->id),
        'first_name'  => $author->first_name,
        'last_name'   => $author->last_name
      );
    }
  endwhile;
  wp_reset_query(); 
  return $data;
}

function add_value_to_options ($id, $option_name) {
  $current = (int) get_post_meta( $id, $option_name, true ) ?? 0;
  return update_post_meta( $id, $option_name,  $current + 1);
}

function create_and_return ($data) {
  return wp_send_json(array(
    'data' => $data
  ), 200);
}

function get_loop_array_data ($wpquery_object, $keys) {
  $i = 0;
  $data = array();
  $data[$keys] = array();
  while ($wpquery_object->have_posts()) : $wpquery_object->the_post();
    global $post;
    $authorID = get_post_field('post_author', get_the_ID());
    $author = get_userdata($authorID);
    $sizes = wp_get_attachment_image_src(get_post_thumbnail_id(get_the_ID()), "full");
    $data[$keys][$i]['id']           = get_the_ID();
    $data[$keys][$i]['title']        = get_the_title();
    $data[$keys][$i]['content']      = wpautop(get_the_content());
    $data[$keys][$i]['excerpt']      = wpautop(get_the_excerpt());
    $data[$keys][$i]['category']     = get_the_category();
    $data[$keys][$i]['thumbnail']    = get_the_post_thumbnail_url();
    $data[$keys][$i]['width']        = $sizes[1];
    $data[$keys][$i]['height']       = $sizes[2];
    $data[$keys][$i]['slug']         = $post->post_name;
    $data[$keys][$i]['date']         = get_the_date('F j, Y');
    $data[$keys][$i]['updated_date'] = get_the_modified_time('F j, Y');
    $data[$keys][$i]['views']        = (int) get_post_meta(get_the_ID(), 'views', true) ?? false;
    $data[$keys][$i]['author']       = [];
    if($author) {
      $data[$keys][$i]['author'] =  array(
        'name'        => $author->user_nicename,
        'email'       => $author->user_email,
        'photo'       => get_avatar_url($author->user_email),
        'description' => get_the_author_meta('description', $author->id),
        'first_name'  => $author->first_name,
        'last_name'   => $author->last_name
      );
    }
    $i++;
  endwhile;
  wp_reset_query(); 
  return $data;
}

function home_route_api ( WP_REST_Request $request ) {

  // retrive the first sticky post only
  $sticky = get_option('sticky_posts');
  $loop_sticky = new WP_Query(array(
    'p'         => $sticky[0],
    'post_type' => 'post',
  ));
  $loop_sticky_posts = get_loop_array_data($loop_sticky, 'sticky');
  
  // retrive first featured posts
  $loop_featured = new WP_Query(array(
    'post_type'      => 'post',
    'posts_per_page' => 12,
    'meta_key'       => 'featured_post',
    'meta_value'     => '1',
    'post__not_in'   => array($sticky[0])
  ));
  $loop_featured_posts = get_loop_array_data($loop_featured, 'featured');

  // retrive first featured posts
  $sticky_featured_ids = [$sticky[0]];
  $size = sizeof($loop_featured_posts['featured']);
  for ($i=0; $i < $size; $i++) { 
    array_push($sticky_featured_ids, $loop_featured_posts['featured'][$i]['id']);
  }

  $loop_newest = new WP_Query(array(
    'post_type'      => 'post',
    'posts_per_page' => 4,
    'orderby'        => 'date',
    'order'          => 'DESC',
    'post__not_in'   => $sticky_featured_ids
  ));
  $loop_newest_posts = get_loop_array_data($loop_newest, 'newest');
  $data = array_merge($loop_featured_posts, $loop_sticky_posts, $loop_newest_posts);
  create_and_return($data);
}

function get_blog_posts ( WP_REST_Request $request ) {

  validate_get_field($_GET['page'], 'The page is required');
  $paginated = isset($_GET['page']) ? (int) $_GET['page'] : 1;
  $sticky = get_option('sticky_posts');

   // retrive first featured posts
   $loop_featured = new WP_Query(array(
    'post_type'      => 'post',
    'posts_per_page' => 4,
    'meta_key'       => 'featured_post',
    'meta_value'     => '1',
    'post__not_in'   => array($sticky[0])
  ));
  $loop_featured_posts = get_loop_array_data($loop_featured, 'featured');

  // retrive first featured posts
  $sticky_featured_ids = [$sticky[0]];
  $size = sizeof($loop_featured_posts['featured']);
  for ($i=0; $i < $size; $i++) { 
    array_push($sticky_featured_ids, $loop_featured_posts['featured'][$i]['id']);
  }

  // workaround to remove from the search the first page (4 registries)
  $loop_first_page = new WP_Query(array(
    'post_type'      => 'post',
    'posts_per_page' => 4,
    'orderby'        => 'date',
    'order'          => 'DESC',
    'post__not_in'   => $sticky_featured_ids
  ));
  $loop_first_page_posts = get_loop_array_data($loop_first_page, 'first');

  $size = sizeof($loop_first_page_posts['first']);
  for ($i=0; $i < $size; $i++) { 
    array_push($sticky_featured_ids, $loop_first_page_posts['first'][$i]['id']);
  }


  $loop_newest = new WP_Query(array(
    'post_type'      => 'post',
    'posts_per_page' => 10,
    'paged'          => $paginated,
    'orderby'        => 'date',
    'order'          => 'DESC',
    'post__not_in'   => $sticky_featured_ids
  ));
  $loop_newest_posts = get_loop_array_data($loop_newest, 'newest');
  $data = array_merge($loop_newest_posts);
  create_and_return($data);
}



// https://metabox.io/how-to-create-custom-meta-boxes-custom-fields-in-wordpress/

function metabox_featured_post() {
  add_meta_box( 'hcf-1', __( 'Sticky Post', 'hcf' ), 'hcf_display_callback', 'post' );
}
add_action( 'add_meta_boxes', 'metabox_featured_post' );
function hcf_display_callback( $post ) { 
  $checked = (int) esc_attr(get_post_meta( get_the_ID(), 'featured_post', true)) === 1 ? 'checked' : '';
?>
  <style scoped>
    .metabox-options-box {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
    }
  </style>
  <p class="meta-options metabox-options-box">
    <label for="featured_post_label"> <input id="featured_post_label" type="checkbox" name="featured_post" value="1" <?php echo $checked ?>><span>This is a Featured Post</span></label>
  </p>
<?php
}

function metabox_save_featured_post( $post_id ) {
  if (defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE) return;
  if ($parent_id = wp_is_post_revision( $post_id)) {
    $post_id = $parent_id;
  }
  update_post_meta($post_id, 'featured_post', sanitize_text_field( isset($_POST['featured_post']) ? 1 : 0 ));
}
add_action( 'save_post', 'metabox_save_featured_post' );