<?php
/**
 * Pulisce le opzioni e i metadati creati da StillGood alla disinstallazione.
 *
 * @package StillGood
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'stillgood_options' );
delete_post_meta_by_key( '_stillgood_exclude' );
