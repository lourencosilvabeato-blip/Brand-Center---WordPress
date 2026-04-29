<?php
/**
 * C01 — Channel Page: ACF field group + asset enqueue.
 *
 * Registers the `group_channel_page` ACF field group on pages assigned the
 * "Channel Page" template (page-channel.php). Fields:
 *  - cp_description  — optional wysiwyg intro (right-column of page header)
 *  - buttons         — optional repeater of CTA buttons (reuses abc_buttons_repeater_field)
 *  - cp_cards        — repeater of child-page cards (title, image, subtitle, url)
 *
 * Enqueues channel.css only on channel-page templates.
 *
 * @package ascendum-brand-center
 */

defined( 'ABSPATH' ) || exit;

// ---------------------------------------------------------------------------
// ACF field group
// ---------------------------------------------------------------------------

add_action( 'acf/init', 'abc_register_channel_page_acf_fields' );
function abc_register_channel_page_acf_fields() {
    if ( ! function_exists( 'acf_add_local_field_group' ) ) {
        return;
    }

    acf_add_local_field_group( array(
        'key'    => 'group_channel_page',
        'title'  => 'Channel Page Content',
        'fields' => array(

            // Optional introductory text — right column of page header.
            array(
                'key'          => 'field_cp_description',
                'label'        => 'Description',
                'name'         => 'cp_description',
                'type'         => 'wysiwyg',
                'required'     => 0,
                'toolbar'      => 'basic',
                'media_upload' => 0,
                'tabs'         => 'visual',
                'instructions' => 'Optional. Introductory text displayed to the right of the page title.',
            ),

            // CTA buttons — reuses the shared repeater helper from inc/blocks.php.
            abc_buttons_repeater_field( 'cp' ),

            // Card grid.
            array(
                'key'          => 'field_cp_cards',
                'label'        => 'Cards',
                'name'         => 'cp_cards',
                'type'         => 'repeater',
                'required'     => 0,
                'min'          => 0,
                'max'          => 0,
                'layout'       => 'block',
                'button_label' => 'Add card',
                'instructions' => 'Each card links to a sub-page or destination. The entire card is clickable.',
                'sub_fields'   => array(
                    array(
                        'key'         => 'field_cp_card_title',
                        'label'       => 'Title',
                        'name'        => 'card_title',
                        'type'        => 'text',
                        'required'    => 1,
                        'placeholder' => 'Card title',
                    ),
                    array(
                        'key'           => 'field_cp_card_image',
                        'label'         => 'Image',
                        'name'          => 'card_image',
                        'type'          => 'image',
                        'required'      => 1,
                        'return_format' => 'array',
                        'preview_size'  => 'medium',
                    ),
                    array(
                        'key'          => 'field_cp_card_subtitle',
                        'label'        => 'Subtitle',
                        'name'         => 'card_subtitle',
                        'type'         => 'wysiwyg',
                        'required'     => 0,
                        'toolbar'      => 'basic',
                        'media_upload' => 0,
                        'tabs'         => 'visual',
                        'instructions' => 'Optional. Short descriptive text displayed below the title.',
                    ),
                    array(
                        'key'          => 'field_cp_card_url',
                        'label'        => 'Destination URL',
                        'name'         => 'card_url',
                        'type'         => 'url',
                        'required'     => 1,
                        'placeholder'  => 'https://…',
                        'instructions' => 'The entire card is clickable and links to this URL. Multiple cards may point to the same URL.',
                    ),
                ),
            ),

        ),
        'location' => array(
            array(
                array(
                    'param'    => 'page_template',
                    'operator' => '==',
                    'value'    => 'page-channel.php',
                ),
            ),
        ),
        'menu_order'      => 0,
        'position'        => 'normal',
        'style'           => 'default',
        'label_placement' => 'top',
        'hide_on_screen'  => array( 'the_content' ),
    ) );
}

// ---------------------------------------------------------------------------
// Enqueue channel CSS only on channel-page templates
// ---------------------------------------------------------------------------

add_action( 'wp_enqueue_scripts', 'abc_enqueue_channel_assets' );
function abc_enqueue_channel_assets() {
    if ( ! is_page_template( 'page-channel.php' ) ) {
        return;
    }

    wp_enqueue_style(
        'abc-channel',
        get_template_directory_uri() . '/assets/css/channel.css',
        array( 'abc-style' ),
        wp_get_theme()->get( 'Version' )
    );
}
