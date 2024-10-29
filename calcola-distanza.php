<?php

/**
 * Silence is golden, but I actually want people to view the website.
 */
if ( ! defined( 'ABSPATH' ) ) {
    // Call dirname() 4 times as this file is in '/wp-contents/plugins/plugin-dir/' and wp-config.php is in '/'
    require_once dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/wp-config.php';
    // Permanent redirect home.
    $response	= wp_remote_get("https://maps.googleapis.com/maps/api/distancematrix/".
				"json".
				"?units=metric"."&origins=place_id:".sanitize_text_field($_POST['a'])."&destinations=place_id:".sanitize_text_field($_POST['b']).
				"&key=".sanitize_text_field($_POST['k']));
	if ( is_array( $response ) ) {
	  $header = $response['headers']; // array of http header lines
	  $body = $response['body']; // use the content
	  echo $body;
	}
    die;
}

?>