<?php
/**
 * Plugin Name: Test WP REST API
 * Description: This is the test play ground plugin with JSON-based REST API for WordPress.
 * Author: Gaeasys Team
 * Author URI: https://github.com/LytayTOUCH
 * Version: 1.0
 * Plugin URI: https://github.com/LytayTOUCH/test-wp-rest-api
 * License: GPL2+
 */

 /**
  * WP_REST_Controller class.
  */
 if ( ! class_exists( 'WP_Rest_API_Product' ) ) {
 	require_once dirname( __FILE__ ) . '/class-wp-rest-api-product.php';
 }




 /**
 * Grab latest post title by an author!
 *
 * @param array $data Options for the function.
 * @return string|null Post title for the latest,  * or null if none.
 */
function my_awesome_func( $data ) {
  $posts = get_posts( array(
    'author' => $data['id'],
  ) );

  if ( empty( $posts ) ) {
    return new WP_Error( 'awesome_no_author', 'Invalid author', array( 'status' => 404 ) );
  }

  return "Number of Posts: ".count($posts);
  // [5]->post_title;
}

function get_all_posts(WP_REST_Request $request){
 //  global $wpdb;
 //  // $param = $request['some_param'];
 //
 //  // Or via the helper method:
 // $param = $request->get_param( 'some_param' );
 // //
 // //  // You can get the combined, merged set of parameters:
 // $parameters = $request->get_params();
 // //
 // //  // The individual sets of parameters are also available, if needed:
 // // $parameters = $request->get_url_params();
 //  // $parameters = $request->get_query_params();
 //  // $parameters = $request->get_body_params();
 //  // $parameters = $request->get_json_params();
 // //  $parameters = $request->get_default_params();
 // //
 // //  // Uploads aren't merged in, but can be accessed separately:
 // // $parameters = $request->get_file_params();
 // // return "function get_all_posts activated !";
 // // return $parameters;
 // return $wpdb;

	// global $jal_db_version;
  //
	// $table_name = $wpdb->prefix . 'liveshoutbox';
  //
	// $charset_collate = $wpdb->get_charset_collate();
  //
	// $sql = "CREATE TABLE $table_name (
	// 	id mediumint(9) NOT NULL AUTO_INCREMENT,
	// 	time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
	// 	name tinytext NOT NULL,
	// 	text text NOT NULL,
	// 	url varchar(55) DEFAULT '' NOT NULL,
	// 	PRIMARY KEY  (id)
	// ) $charset_collate;";
  //
	// require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	// dbDelta( $sql );
  //
	// add_option( 'jal_db_version', $jal_db_version );
  //
  // return "Done created table...";
}



// add_action( 'rest_api_init', function () {
//   register_rest_route( 'myplugin/v1', '/list_col', array(
//     'methods' => 'GET',
//     'callback' => 'get_list_table_name',
//   ) );
//   register_rest_route( 'myplugin/v1', '/create_table/(?P<tbl_name>\S+)', array(
//     'methods' => WP_REST_Server::CREATABLE,
//     'callback' => 'create_table',
//   ) );
//
// } );


class My_REST_Posts_Controller {

    // Here initialize our namespace and resource name.
    public function __construct() {
        $this->namespace     = 'my-namespace/v1';
        $this->resource_name = 'posts';
    }

    // Register our routes.
    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->resource_name, array(
            // Here we register the readable endpoint for collections.
            array(
                'methods'   => 'GET',
                'callback'  => array( $this, 'get_items' ),
                'permission_callback' => array( $this, 'get_items_permissions_check' ),
            ),
            // Register our schema callback.
            'schema' => array( $this, 'get_item_schema' ),
        ) );
        register_rest_route( $this->namespace, '/' . $this->resource_name . '/(?P<id>[\d]+)', array(
            // Notice how we are registering multiple endpoints the 'schema' equates to an OPTIONS request.
            array(
                'methods'   => 'GET',
                'callback'  => array( $this, 'get_item' ),
                'permission_callback' => array( $this, 'get_item_permissions_check' ),
            ),
            // Register our schema callback.
            'schema' => array( $this, 'get_item_schema' ),
        ) );
    }

    /**
     * Check permissions for the posts.
     *
     * @param WP_REST_Request $request Current request.
     */
    public function get_items_permissions_check( $request ) {
        if ( ! current_user_can( 'read' ) ) {
            return new WP_Error( 'rest_forbidden', esc_html__( 'You cannot view the post resource.' ), array( 'status' => $this->authorization_status_code() ) );
        }
        return true;
    }

    /**
     * Grabs the five most recent posts and outputs them as a rest response.
     *
     * @param WP_REST_Request $request Current request.
     */
    public function get_items( $request ) {
        $args = array(
            'post_per_page' => 5,
        );
        $posts = get_posts( $args );

        $data = array();

        if ( empty( $posts ) ) {
            return rest_ensure_response( $data );
        }

        foreach ( $posts as $post ) {
            $response = $this->prepare_item_for_response( $post, $request );
            $data[] = $this->prepare_response_for_collection( $response );
        }

        // Return all of our comment response data.
        return rest_ensure_response( $data );
    }

    /**
     * Check permissions for the posts.
     *
     * @param WP_REST_Request $request Current request.
     */
    public function get_item_permissions_check( $request ) {
        if ( ! current_user_can( 'read' ) ) {
            return new WP_Error( 'rest_forbidden', esc_html__( 'You cannot view the post resource.' ), array( 'status' => $this->authorization_status_code() ) );
        }
        return true;
    }

    /**
     * Grabs the five most recent posts and outputs them as a rest response.
     *
     * @param WP_REST_Request $request Current request.
     */
    public function get_item( $request ) {
        $id = (int) $request['id'];
        $post = get_post( $id );

        if ( empty( $post ) ) {
            return rest_ensure_response( array() );
        }

        $response = prepare_item_for_response( $post );

        // Return all of our post response data.
        return $response;
    }

    /**
     * Matches the post data to the schema we want.
     *
     * @param WP_Post $post The comment object whose response is being prepared.
     */
    public function prepare_item_for_response( $post, $request ) {
        $post_data = array();

        $schema = $this->get_item_schema( $request );

        // We are also renaming the fields to more understandable names.
        if ( isset( $schema['properties']['id'] ) ) {
            $post_data['id'] = (int) $post->ID;
        }

        if ( isset( $schema['properties']['content'] ) ) {
            $post_data['content'] = apply_filters( 'the_content', $post->post_content, $post );
        }

        return rest_ensure_response( $post_data );
    }

    /**
     * Prepare a response for inserting into a collection of responses.
     *
     * This is copied from WP_REST_Controller class in the WP REST API v2 plugin.
     *
     * @param WP_REST_Response $response Response object.
     * @return array Response data, ready for insertion into collection data.
     */
    public function prepare_response_for_collection( $response ) {
        if ( ! ( $response instanceof WP_REST_Response ) ) {
            return $response;
        }

        $data = (array) $response->get_data();
        $server = rest_get_server();

        if ( method_exists( $server, 'get_compact_response_links' ) ) {
            $links = call_user_func( array( $server, 'get_compact_response_links' ), $response );
        } else {
            $links = call_user_func( array( $server, 'get_response_links' ), $response );
        }

        if ( ! empty( $links ) ) {
            $data['_links'] = $links;
        }

        return $data;
    }

    /**
     * Get our sample schema for a post.
     *
     * @param WP_REST_Request $request Current request.
     */
    public function get_item_schema( $request ) {
        $schema = array(
            // This tells the spec of JSON Schema we are using which is draft 4.
            '$schema'              => 'http://json-schema.org/draft-04/schema#',
            // The title property marks the identity of the resource.
            'title'                => 'post',
            'type'                 => 'object',
            // In JSON Schema you can specify object properties in the properties attribute.
            'properties'           => array(
                'id' => array(
                    'description'  => esc_html__( 'Unique identifier for the object.', 'my-textdomain' ),
                    'type'         => 'integer',
                    'context'      => array( 'view', 'edit', 'embed' ),
                    'readonly'     => true,
                ),
                'content' => array(
                    'description'  => esc_html__( 'The content for the object.', 'my-textdomain' ),
                    'type'         => 'string',
                ),
            ),
        );

        return $schema;
    }

    // Sets up the proper HTTP status code for authorization.
    public function authorization_status_code() {

        $status = 401;

        if ( is_user_logged_in() ) {
            $status = 403;
        }

        return $status;
    }
}

// Function to register our new routes from the controller.
function prefix_register_my_rest_routes() {
    $controller = new My_REST_Posts_Controller();
    $controller->register_routes();

    register_rest_route( 'myplugin/v1', '/list_fields_table', array(
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'get_list_table_name',
        'permission_callback' => array( $controller, 'get_items_permissions_check')
      )
    );

    register_rest_route( 'myplugin/v1', '/create_table', array(
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => 'create_table',
        'permission_callback' => array( $controller, 'get_items_permissions_check')
      )
    );

    register_rest_route( 'myplugin/v1', '/get_metadata', array(
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'get_custom_metadata',
        // 'permission_callback' => array( $controller, 'get_items_permissions_check')
      )
    );

    register_rest_route('gaeasys/v1', '/product', array(
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'startproduct',
        // 'permission_callback' => 'permission'
      )
    );


}

function startproduct(){
    $product = new WP_Rest_API_Product();
    return $product->test();
  }

  function permission(){

    }

function get_list_table_name(){

  global $wpdb;
  $table = $wpdb->prefix.'liveshoutbox';
  $get_table_sql = "DESC $table";
  $results = $wpdb->get_results($get_table_sql);

  return $wpdb->dbh;

  // // $wp_rest_server = new WP_REST_Server();
  // //
  //
  // $new_wp_query = new WP_Query();
  // //
  // // $sql = "SHOW TABLES LIKE '".$wpdb->prefix."%'";
  // //
  //
  // //
  // // // return $results;
  // // return $wp_rest_server-;
  // //
  // // $server = rest_get_server();
  // // return $server;
  //
  // return $wpdb;

  // $data = array('a'=>1, 'b'=>2, 'c'=>3, 'd'=>4);
  //
  // return rest_ensure_response( $data );

}

function get_custom_metadata(){
  return "get_custom_metadata";
}

function create_table(WP_REST_Request $request){
  $table_name = $request['table_name'];

  // return "Successfully created ...";
  return $table_name;
}


// function create_table(){
//   // if ( ! empty( $request['table_name'] ) ) {
// 	// 		return new WP_Error( 'rest_post_exists', __( 'Cannot create '.$request['table_name'].' table.' ), array( 'status' => 400 ) );
// 	// }
//   return "create table...";
//
// }
//


add_action( 'rest_api_init', 'prefix_register_my_rest_routes' );

function install_table(){

}

// add_action('activate_plugin', 'install_talbe');

function uninstall_table(){

}

// add_action('deactivate_plugin', 'uninstall_talbe');


global $jal_db_version;
$jal_db_version = '1.0';

function jal_install() {
	global $wpdb;
	global $jal_db_version;

	$table_name = $wpdb->prefix . 'liveshoutbox';

	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		name tinytext NOT NULL,
		text text NOT NULL,
		url varchar(55) DEFAULT '' NOT NULL,
		PRIMARY KEY  (id)
	) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );

	add_option( 'jal_db_version', $jal_db_version );
}

function jal_install_data() {
	global $wpdb;

	$welcome_name = 'Mr. WordPress';
	$welcome_text = 'Congratulations, you just completed the installation!';

	$table_name = $wpdb->prefix . 'liveshoutbox';

	$wpdb->insert(
		$table_name,
		array(
			'time' => current_time( 'mysql' ),
			'name' => $welcome_name,
			'text' => $welcome_text,
		)
	);
}

register_activation_hook( __FILE__, 'jal_install' );
register_activation_hook( __FILE__, 'jal_install_data' );

register_deactivation_hook(__FILE__, 'cleanTable');
register_uninstall_hook(__FILE__, 'dropTable');

function cleanTable(){
  global $wpdb;
  $table_name = $wpdb->prefix . 'liveshoutbox';
  $wpdb->query("TRUNCATE TABLE $table_name");
}

function dropTable(){
  global $wpdb;
  $table_name = $wpdb->prefix . 'liveshoutbox';
  $wpdb->query("DROP TABLE IF EXISTS $table_name");
}
