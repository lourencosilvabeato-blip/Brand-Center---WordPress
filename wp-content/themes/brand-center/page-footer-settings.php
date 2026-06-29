<?php
/**
 * Template Name: Footer Settings
 *
 * Internal settings-only page — holds ACF footer fields.
 * Has no public-facing content; redirects all visitors to the homepage.
 *
 * @package brand-center
 */

wp_safe_redirect( home_url( '/' ), 301 );
exit;
