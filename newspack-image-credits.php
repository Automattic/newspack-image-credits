<?php
/**
 * Plugin Name: Newspack Image Credits
 * Description: Add photo credit info to images. A modernization of Navis Media Credit.
 * Version: 1.0.1
 * Author: Automattic, INN Labs, Project Argo
 * Author URI: https://newspack.blog/
 * License: GPL2
 * Text Domain: newspack-image-credits
 * Domain Path: /languages/
 */

defined( 'ABSPATH' ) || exit;

/**
 * Runs the whole show.
 */
class Newspack_Image_Credits {

	const MEDIA_CREDIT_META = '_media_credit';

	const MEDIA_CREDIT_URL_META = '_media_credit_url';

	const MEDIA_CREDIT_ORG_META = '_navis_media_credit_org';

	const MEDIA_CREDIT_CAN_DISTRIBUTE_META = '_navis_media_can_distribute';

	/**
	 * Hook actions and filters.
	 */
	public static function init() {
		add_filter( 'attachment_fields_to_save', [ __CLASS__, 'save_media_credit' ], 10, 2 );
		add_filter( 'attachment_fields_to_edit', [ __CLASS__, 'add_media_credit' ], 10, 2 );
		add_filter( 'get_the_excerpt', [ __CLASS__, 'add_credit_to_attachment_excerpts' ], 10, 2 );
		add_filter( 'render_block', [ __CLASS__, 'add_credit_to_image_block' ], 10, 2 );
	}

	/**
	 * Get media credit info for an attachment.
	 *
	 * @param int $attachment_id Post ID of the attachment.
	 * @return array Credit info. See $output at the top of this method.
	 */
	public static function get_media_credit( $attachment_id ) {
		$output = [
			'id'             => $attachment_id,
			'credit'         => '',
			'credit_url'     => '',
			'organization'   => '',
			'can_distribute' => false,
		];

		$credit = get_post_meta( $attachment_id, self::MEDIA_CREDIT_META, true );
		if ( $credit ) {
			$output['credit'] = esc_attr( $credit );
		}

		$credit_url = get_post_meta( $attachment_id, self::MEDIA_CREDIT_URL_META, true );
		if ( $credit_url ) {
			$output['credit_url'] = esc_attr( $credit_url );
		}

		$organization = get_post_meta( $attachment_id, self::MEDIA_CREDIT_ORG_META, true );
		if ( $organization ) {
			$output['organization'] = esc_attr( $organization );
		}

		$can_distribute = get_post_meta( $attachment_id, self::MEDIA_CREDIT_CAN_DISTRIBUTE_META, true );
		if ( $can_distribute ) {
			$output['can_distribute'] = true;
		}

		return $output;
	}

	/**
	 * Get credit info as an HTML string.
	 *
	 * @param int $attachment_id Attachment post ID.
	 * @return string The credit ready for output.
	 */
	public static function get_media_credit_string( $attachment_id ) {
		$credit_info = self::get_media_credit( $attachment_id );
		if ( ! $credit_info['credit'] ) {
			return '';
		}

		$credit = $credit_info['credit'];
		if ( $credit_info['organization'] ) {
			$credit .= ' / ' . $credit_info['organization'];
		}

		if ( $credit_info['credit_url'] ) {
			$credit = '<a href="' . $credit_info['credit_url'] . '" target="_blank">' . $credit . '</a>';
		}

		$credit = '<span class="image-credit">' . sprintf( __( 'Credit: %s', 'newspack-image-credits' ), $credit ) . '</span>';

		return wp_kses_post( $credit );
	}

	/**
	 * Save the media credit info.
	 *
	 * @param array $post Array of post info.
	 * @param array $attachment Array of media field input info.
	 * @return array $post Unmodified post info.
	 */
	public static function save_media_credit( $post, $attachment ) {
		if ( isset( $attachment['media_credit'] ) ) {
			update_post_meta( $post['ID'], self::MEDIA_CREDIT_META, sanitize_text_field( $attachment['media_credit'] ) );
		}

		if ( isset( $attachment['media_credit_url'] ) ) {
			update_post_meta( $post['ID'], self::MEDIA_CREDIT_URL_META, sanitize_text_field( $attachment['media_credit_url'] ) );
		}

		if ( isset( $attachment['media_credit_org'] ) ) {
			update_post_meta( $post['ID'], self::MEDIA_CREDIT_ORG_META, sanitize_text_field( $attachment['media_credit_org'] ) );
		}

		if ( isset( $attachment['media_can_distribute'] ) ) {
			update_post_meta( $post['ID'], self::MEDIA_CREDIT_CAN_DISTRIBUTE_META, (bool) $attachment['media_can_distribute'] );
		}

		return $post;
	}

	/**
	 * Add media credit fields to the media editor.
	 *
	 * @param array $fields Array of media editor field info.
	 * @param WP_Post $post Post object for current attachment.
	 * @return array Modified $fields.
	 */
	public static function add_media_credit( $fields, $post ) {
		$credit_info = self::get_media_credit( $post->ID );
        $fields['media_credit'] = [
        	'label' => __( 'Credit', 'newspack-image-credits' ),
        	'input' => 'text',
        	'value' => $credit_info['credit'],
        ];

        $fields['media_credit_url'] = [
        	'label' => __( 'Credit URL', 'newspack-image-credits' ),
        	'input' => 'text',
        	'value' => $credit_info['credit_url'],
        ];

        $fields['media_credit_org'] = [
        	'label' => __( 'Organization', 'newspack-image-credits' ),
        	'input' => 'text',
        	'value' => $credit_info['organization'],
        ];

		$distfield = 'attachments[' . $post->ID . '][media_can_distribute]';
        $fields['media_can_distribute'] = [
        	'label' => __( 'Can distribute?', 'newspack-image-credits' ),
        	'input' => 'html',
        	'html' => '<input id="' . $distfield . '" name="' . $distfield . '" type="hidden" value="0" /><input id="' . $distfield . '" name="' . $distfield . '" type="checkbox" value="1" ' . checked( $credit_info['can_distribute'], true, false ) . ' />',
        ];

        return $fields;
	}

	/**
	 * Add media credit to attachment excerpts (captions) when possible.
	 *
	 * @param string $excerpt Attachment excerpt/caption.
	 * @param WP_Post $post Post object.
	 * @return string Modified $excerpt.
	 */
	public static function add_credit_to_attachment_excerpts( $excerpt, $post ) {
		if ( 'attachment' !== $post->post_type ) {
			return $excerpt;
		}

		$credit_string = self::get_media_credit_string( $post->ID );
		if ( $excerpt && $credit_string ) {
			return $excerpt . ' ' . $credit_string;
		} elseif ( $credit_string ) {
			return $credit_string;
		} else {
			return $excerpt;
		}
	}

	/**
	 * Add media credit to image blocks when possible.
	 *
	 * @param string $block_output HTML block output.
	 * @param array $block Raw block info.
	 * @return string Modified $block_output.
	 */
	public static function add_credit_to_image_block( $block_output, $block ) {
		if ( 'core/image' !== $block['blockName'] || empty( $block['attrs']['id'] ) ) {
			return $block_output;
		}

		$credit_string = self::get_media_credit_string( $block['attrs']['id'] );
		if ( ! $credit_string ) {
			return $block_output;
		}

		if ( strpos( $block_output, '</figcaption>' ) ) {
			// If an image caption exists, add the credit to it.
			$block_output = str_replace( '</figcaption>', ' ' . $credit_string . '</figcaption>', $block_output );
		} else {
			// If an image caption doesn't exist, make the credit the caption.
			$block_output = str_replace( '</figure>', '<figcaption>' . $credit_string . '</figcaption></figure>', $block_output );
		}

		return $block_output;
	}
}
Newspack_Image_Credits::init();