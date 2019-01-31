<?php
/**
 * @package snow-monkey-bbpress-support
 * @author inc2734
 * @license GPL-2.0+
 */

namespace Snow_Monkey\Plugin\bbPressSupport\App;

class Notice {

	public function __construct() {
		add_action( 'wp_insert_post', [ $this, '_send_new_topic' ], 10, 3 );
		add_action( 'wpg_connect', [ $this, '_send_new_user' ], 10, 4 );
	}

	/**
	 * Send mail when adding new topic
	 *
	 * @param  [int]     $post_id
	 * @param  [WP_Post] $post
	 * @param  [boolean] $update
	 * @return [int]
	 */
	public function _send_new_topic( $post_id, $post, $update ) {
		if ( $update ) {
			return;
		}

		if ( 'topic' !== get_post_type( $post_id ) ) {
			return;
		}

		$post = get_post( $post_id );
		if ( empty( $post->ID ) ) {
			return;
		}

		$body  = sprintf( __( 'Title: %1$s', 'snow-monkey-bbpress-support' ), get_the_title( $post_id ) );
		$body .= "\n";
		$body .= sprintf( __( 'Owner: %1$s', 'snow-monkey-bbpress-support' ), get_the_author_meta( 'display_name', $post->post_author ) );
		$body .= "\n\n";
		$body .= strip_tags( $post->post_content );
		$body .= "\n\n";
		$body .= get_permalink( $post_id );

		$subject = sprintf(
			__( '【%1$s】Added new topic - %2$s', 'snow-monkey-bbpress-support' ),
			get_bloginfo( 'name' ),
			get_the_title( $post_id )
		);

		$this->_mail( $subject, $body );
	}

	/**
	 * Send mail when adding new user
	 *
	 * @param  [int]     $user_id
	 * @param  [mixed]   $data
	 * @param  [string]  $service_name
	 * @param  [boolean] $on_creation
	 * @return [int]
	 */
	public function _send_new_user( $user_id, $data, $service_name, $on_creation ) {
		$first_name   = get_the_author_meta( 'first_name', $user_id );
		$last_name    = get_the_author_meta( 'last_name', $user_id );
		$nickname     = get_the_author_meta( 'nickname', $user_id );
		$display_name = get_the_author_meta( 'display_name', $user_id );

		$name = $first_name . ' ' . $last_name;
		if ( ! $name ) {
			$name = $nickname;
		}
		if ( ! $name ) {
			$name = $display_name;
		}

		$body  = sprintf( __( 'Name: %1$s', 'snow-monkey-bbpress-support' ), $name );
		$body .= "\n";
		$body .= sprintf( __( 'E-mail: %1$s', 'snow-monkey-bbpress-support' ), get_the_author_meta( 'user_email', $user_id ) );
		$body .= "\n\n";
		$body .= admin_url( 'users.php' );

		$subject = sprintf(
			__( '【%1$s】Added new user using %2$s.', 'snow-monkey-bbpress-support' ),
			get_bloginfo( 'name' ),
			$service_name
		);

		$this->_mail( $subject, $body );
	}

	protected function _mail( $subject, $body ) {
		wp_mail(
			get_bloginfo( 'admin_email' ),
			$subject,
			$body
		);
	}
}
