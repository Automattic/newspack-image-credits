<?php
/**
 * Plugin Name: Newspack Image Credits
 * Description: Add photo credit info to images. A modernization of Navis Media Credit.
 * Version: 1.0.0
 * Author: Automattic, INN Labs, Project Argo
 * Author URI: https://newspack.blog/
 * License: GPL2
 * Text Domain: newspack-image-credits
 * Domain Path: /languages/
 */

defined( 'ABSPATH' ) || exit;

class Newspack_Image_Credits {

	const MEDIA_CREDIT_META = '_media_credit';

	const MEDIA_CREDIT_URL_META = '_media_credit_url';

	const MEDIA_CREDIT_ORG_META = '_navis_media_credit_org';

	const MEDIA_CREDIT_CAN_DISTRIBUTE_META = '_navis_media_can_distribute';

	public static function init() {
		add_filter( 'attachment_fields_to_save', [ __CLASS__, 'save_media_credit' ], 10, 2 );
		add_filter( 'attachment_fields_to_edit', [ __CLASS__, 'add_media_credit' ], 10, 2 );
	}

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

	public static function get_media_credit_string( $attachment_id ) {

	}

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

}
Newspack_Image_Credits::init();