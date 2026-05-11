<?php
/**
 * C02/E02 — Page content blocks: ACF field registration and block helpers.
 *
 * Registers the `page_content_blocks` flexible-content ACF field group used on
 * all pages assigned the "Generic Content" template. Also provides:
 *  - abc_render_collection_detail() — collection detail view from ?collection=N
 *  - abc_render_sibling_nav_buttons() — prev/next page siblings
 *  - TinyMCE custom styles filter (Check List, Red Cross List)
 *
 * ⚠️  Requires ACF Pro 6.x — flexible_content and repeater field types are Pro-only.
 *
 * @package ascendum-brand-center
 */

defined( 'ABSPATH' ) || exit;

// ---------------------------------------------------------------------------
// Shared sub-field: Buttons repeater
// Used by Page Header and Section Top.
// ---------------------------------------------------------------------------

/**
 * Returns the ACF repeater sub-field definition for a generic Buttons list.
 *
 * @param string $key_prefix  Unique prefix for field keys (e.g. 'ph' or 'st').
 * @return array
 */
function abc_buttons_repeater_field( string $key_prefix ): array {
    return array(
        'key'          => 'field_' . $key_prefix . '_buttons',
        'label'        => 'Buttons',
        'name'         => 'buttons',
        'type'         => 'repeater',
        'required'     => 0,
        'min'          => 0,
        'max'          => 0,
        'layout'       => 'table',
        'button_label' => 'Add button',
        'sub_fields'   => array(
            array(
                'key'         => 'field_' . $key_prefix . '_btn_label',
                'label'       => 'Label',
                'name'        => 'button_label',
                'type'        => 'text',
                'required'    => 1,
                'placeholder' => 'Button text',
            ),
            array(
                'key'          => 'field_' . $key_prefix . '_btn_url',
                'label'        => 'URL',
                'name'         => 'button_url',
                'type'         => 'url',
                'required'     => 0,
                'placeholder'  => 'https://…',
                'instructions' => 'Use this OR the file field below, not both.',
            ),
            array(
                'key'           => 'field_' . $key_prefix . '_btn_file',
                'label'         => 'File (download)',
                'name'          => 'button_file',
                'type'          => 'file',
                'required'      => 0,
                'return_format' => 'array',
                'library'       => 'all',
                'instructions'  => 'Use this OR the URL field above, not both.',
            ),
            array(
                'key'           => 'field_' . $key_prefix . '_btn_new_tab',
                'label'         => 'Open in new tab',
                'name'          => 'button_new_tab',
                'type'          => 'true_false',
                'required'      => 0,
                'default_value' => 0,
                'ui'            => 1,
            ),
        ),
    );
}

// ---------------------------------------------------------------------------
// ACF field group — page content blocks (flexible content)
// ---------------------------------------------------------------------------

add_action( 'acf/init', 'abc_register_content_blocks_acf_fields' );
function abc_register_content_blocks_acf_fields() {
    if ( ! function_exists( 'acf_add_local_field_group' ) ) {
        return;
    }

    // -------------------------------------------------------------------------
    // Layout 1: Page Introduction (optional intro text + buttons)
    // The H1 title is auto-generated from the WordPress page title — no need
    // to enter it here. Use this block only for an introductory paragraph
    // and/or CTA buttons below the auto-rendered title.
    // -------------------------------------------------------------------------
    $layout_page_header = array(
        'key'        => 'layout_page_header',
        'name'       => 'page_header',
        'label'      => 'Page Introduction',
        'display'    => 'block',
        'min'        => 0,
        'max'        => 1,
        'sub_fields' => array(
            array(
                'key'          => 'field_ph_intro',
                'label'        => 'Introduction',
                'name'         => 'page_intro',
                'type'         => 'wysiwyg',
                'required'     => 0,
                'toolbar'      => 'basic',
                'media_upload' => 0,
                'tabs'         => 'visual',
                'instructions' => 'Optional introductory text displayed below the page title.',
            ),
            abc_buttons_repeater_field( 'ph' ),
        ),
    );

    // -------------------------------------------------------------------------
    // Layout 2: Divider (Separador)
    // -------------------------------------------------------------------------
    $layout_divider = array(
        'key'        => 'layout_divider',
        'name'       => 'divider',
        'label'      => 'Divider',
        'display'    => 'row',
        'min'        => 0,
        'max'        => '',
        'sub_fields' => array(
            array(
                'key'           => 'field_div_type',
                'label'         => 'Type',
                'name'          => 'divider_type',
                'type'          => 'radio',
                'required'      => 1,
                'choices'       => array(
                    'short' => 'Short',
                    'long'  => 'Long',
                ),
                'default_value' => 'short',
                'layout'        => 'horizontal',
            ),
        ),
    );

    // -------------------------------------------------------------------------
    // Layout 3: Section Top (Topo de Secção)
    // Generates anchor bar entries. Anchor ID defaults to title if field empty.
    // -------------------------------------------------------------------------
    $layout_section_top = array(
        'key'        => 'layout_section_top',
        'name'       => 'section_top',
        'label'      => 'Section Top',
        'display'    => 'block',
        'min'        => 0,
        'max'        => '',
        'sub_fields' => array(
            array(
                'key'         => 'field_st_title',
                'label'       => 'Section Title (H2)',
                'name'        => 'section_title',
                'type'        => 'text',
                'required'    => 1,
                'placeholder' => 'Section title',
            ),
            array(
                'key'          => 'field_st_label',
                'label'        => 'Label (above title)',
                'name'         => 'section_label',
                'type'         => 'text',
                'required'     => 0,
                'placeholder'  => 'e.g. Guidelines',
                'instructions' => 'Optional. Small text rendered above the H2 title.',
            ),
            array(
                'key'          => 'field_st_anchor',
                'label'        => 'Anchor ID',
                'name'         => 'section_anchor',
                'type'         => 'text',
                'required'     => 0,
                'placeholder'  => 'e.g. usage-guidelines',
                'instructions' => 'Optional. URL-safe anchor ID (no spaces). If empty, the Section Title is used.',
            ),
            array(
                'key'          => 'field_st_body',
                'label'        => 'Body Text',
                'name'         => 'section_body',
                'type'         => 'wysiwyg',
                'required'     => 0,
                'toolbar'      => 'full',
                'media_upload' => 0,
                'tabs'         => 'visual',
            ),
            abc_buttons_repeater_field( 'st' ),
        ),
    );

    // -------------------------------------------------------------------------
    // Layout 4: Paragraph (Parágrafo)
    // -------------------------------------------------------------------------
    $layout_paragraph = array(
        'key'        => 'layout_paragraph',
        'name'       => 'paragraph',
        'label'      => 'Paragraph',
        'display'    => 'block',
        'min'        => 0,
        'max'        => '',
        'sub_fields' => array(
            array(
                'key'          => 'field_par_content',
                'label'        => 'Content',
                'name'         => 'paragraph_content',
                'type'         => 'wysiwyg',
                'required'     => 1,
                'toolbar'      => 'full',
                'media_upload' => 1,
                'tabs'         => 'visual',
            ),
        ),
    );

    // -------------------------------------------------------------------------
    // Layout 5: Quote (Citação)
    // -------------------------------------------------------------------------
    $layout_quote = array(
        'key'        => 'layout_quote',
        'name'       => 'quote',
        'label'      => 'Quote',
        'display'    => 'block',
        'min'        => 0,
        'max'        => '',
        'sub_fields' => array(
            array(
                'key'         => 'field_qt_text',
                'label'       => 'Quote Text',
                'name'        => 'quote_text',
                'type'        => 'textarea',
                'required'    => 1,
                'rows'        => 3,
                'placeholder' => 'Enter the quote…',
            ),
            array(
                'key'         => 'field_qt_author',
                'label'       => 'Author / Source',
                'name'        => 'quote_author',
                'type'        => 'text',
                'required'    => 0,
                'placeholder' => 'Name, Title',
                'instructions' => 'Optional. If empty, the attribution line does not render.',
            ),
        ),
    );

    // -------------------------------------------------------------------------
    // Layout 6: Table (Tabela)
    // Column count (1–5) + row repeater. Cells 2–5 are conditionally shown based
    // on the column count, but all 5 are always present to support the template.
    // The template renders only cells 1..N.
    // -------------------------------------------------------------------------
    $layout_table = array(
        'key'        => 'layout_table_block',
        'name'       => 'table_block',
        'label'      => 'Table',
        'display'    => 'block',
        'min'        => 0,
        'max'        => '',
        'sub_fields' => array(
            array(
                'key'           => 'field_tb_columns',
                'label'         => 'Number of columns',
                'name'          => 'table_columns',
                'type'          => 'select',
                'required'      => 1,
                'choices'       => array(
                    '1' => '1 column',
                    '2' => '2 columns',
                    '3' => '3 columns',
                    '4' => '4 columns',
                    '5' => '5 columns',
                ),
                'default_value' => '3',
                'ui'            => 0,
            ),
            array(
                'key'           => 'field_tb_has_header',
                'label'         => 'First row is a header',
                'name'          => 'table_has_header',
                'type'          => 'true_false',
                'required'      => 0,
                'default_value' => 1,
                'ui'            => 1,
            ),
            array(
                'key'          => 'field_tb_rows',
                'label'        => 'Rows',
                'name'         => 'table_rows',
                'type'         => 'repeater',
                'required'     => 1,
                'min'          => 1,
                'max'          => 0,
                'layout'       => 'block',
                'button_label' => 'Add row',
                'instructions' => 'Fill only the cells that match your chosen column count.',
                'sub_fields'   => array(
                    array(
                        'key'      => 'field_tb_cell_1',
                        'label'    => 'Cell 1',
                        'name'     => 'cell_1',
                        'type'     => 'wysiwyg',
                        'required' => 0,
                        'toolbar'  => 'basic',
                        'media_upload' => 0,
                        'tabs'     => 'visual',
                    ),
                    array(
                        'key'      => 'field_tb_cell_2',
                        'label'    => 'Cell 2',
                        'name'     => 'cell_2',
                        'type'     => 'wysiwyg',
                        'required' => 0,
                        'toolbar'  => 'basic',
                        'media_upload' => 0,
                        'tabs'     => 'visual',
                        'conditional_logic' => array(
                            array(
                                array(
                                    'field'    => 'field_tb_columns',
                                    'operator' => '>',
                                    'value'    => '1',
                                ),
                            ),
                        ),
                    ),
                    array(
                        'key'      => 'field_tb_cell_3',
                        'label'    => 'Cell 3',
                        'name'     => 'cell_3',
                        'type'     => 'wysiwyg',
                        'required' => 0,
                        'toolbar'  => 'basic',
                        'media_upload' => 0,
                        'tabs'     => 'visual',
                        'conditional_logic' => array(
                            array(
                                array(
                                    'field'    => 'field_tb_columns',
                                    'operator' => '>',
                                    'value'    => '2',
                                ),
                            ),
                        ),
                    ),
                    array(
                        'key'      => 'field_tb_cell_4',
                        'label'    => 'Cell 4',
                        'name'     => 'cell_4',
                        'type'     => 'wysiwyg',
                        'required' => 0,
                        'toolbar'  => 'basic',
                        'media_upload' => 0,
                        'tabs'     => 'visual',
                        'conditional_logic' => array(
                            array(
                                array(
                                    'field'    => 'field_tb_columns',
                                    'operator' => '>',
                                    'value'    => '3',
                                ),
                            ),
                        ),
                    ),
                    array(
                        'key'      => 'field_tb_cell_5',
                        'label'    => 'Cell 5',
                        'name'     => 'cell_5',
                        'type'     => 'wysiwyg',
                        'required' => 0,
                        'toolbar'  => 'basic',
                        'media_upload' => 0,
                        'tabs'     => 'visual',
                        'conditional_logic' => array(
                            array(
                                array(
                                    'field'    => 'field_tb_columns',
                                    'operator' => '>',
                                    'value'    => '4',
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        ),
    );

    // -------------------------------------------------------------------------
    // Layout 7: Note (Nota)
    // Single visual style (background + left border). Optional bold title.
    // -------------------------------------------------------------------------
    $layout_note = array(
        'key'        => 'layout_note',
        'name'       => 'note',
        'label'      => 'Note',
        'display'    => 'block',
        'min'        => 0,
        'max'        => '',
        'sub_fields' => array(
            array(
                'key'          => 'field_nt_title',
                'label'        => 'Title / Highlight',
                'name'         => 'note_title',
                'type'         => 'text',
                'required'     => 0,
                'placeholder'  => 'e.g. Please note:',
                'instructions' => 'Optional. Rendered in bold at the start of the note.',
            ),
            array(
                'key'          => 'field_nt_content',
                'label'        => 'Content',
                'name'         => 'note_content',
                'type'         => 'wysiwyg',
                'required'     => 1,
                'toolbar'      => 'basic',
                'media_upload' => 0,
                'tabs'         => 'visual',
            ),
        ),
    );

    // -------------------------------------------------------------------------
    // Layout 8: Grid Cards (Cartões em Grelha)
    // Column count selector + cards repeater. Button is icon-only.
    // -------------------------------------------------------------------------
    $layout_grid_cards = array(
        'key'        => 'layout_grid_cards',
        'name'       => 'grid_cards',
        'label'      => 'Grid Cards',
        'display'    => 'block',
        'min'        => 0,
        'max'        => '',
        'sub_fields' => array(
            array(
                'key'           => 'field_gc_columns',
                'label'         => 'Desktop layout',
                'name'          => 'columns_desktop',
                'type'          => 'radio',
                'required'      => 1,
                'choices'       => array(
                    '1'    => '1 column',
                    '2'    => '2 columns',
                    '3'    => '3 columns',
                    '3plus' => '3+ columns',
                ),
                'default_value' => '3',
                'layout'        => 'horizontal',
                'instructions'  => 'Controls the grid on desktop. Responsive breakpoints adapt automatically.',
            ),
            array(
                'key'          => 'field_gc_cards',
                'label'        => 'Cards',
                'name'         => 'cards',
                'type'         => 'repeater',
                'required'     => 1,
                'min'          => 1,
                'max'          => 0,
                'layout'       => 'block',
                'button_label' => 'Add card',
                'sub_fields'   => array(
                    array(
                        'key'           => 'field_gc_card_image',
                        'label'         => 'Image',
                        'name'          => 'card_image',
                        'type'          => 'image',
                        'required'      => 0,
                        'return_format' => 'array',
                        'preview_size'  => 'medium',
                        'instructions'  => 'Optional. Presence determines button placement style.',
                    ),
                    array(
                        'key'         => 'field_gc_card_title',
                        'label'       => 'Title',
                        'name'        => 'card_title',
                        'type'        => 'text',
                        'required'    => 0,
                        'placeholder' => 'Card title',
                    ),
                    array(
                        'key'          => 'field_gc_card_desc',
                        'label'        => 'Description',
                        'name'         => 'card_description',
                        'type'         => 'wysiwyg',
                        'required'     => 0,
                        'toolbar'      => 'basic',
                        'media_upload' => 0,
                        'tabs'         => 'visual',
                    ),
                    array(
                        'key'          => 'field_gc_card_btn',
                        'label'        => 'Button',
                        'name'         => 'card_button',
                        'type'         => 'group',
                        'required'     => 0,
                        'layout'       => 'block',
                        'instructions' => 'Optional. Rendered as an icon: arrow icon for URL, download icon for file. Set one or the other, not both.',
                        'sub_fields'   => array(
                            array(
                                'key'      => 'field_gc_btn_url',
                                'label'    => 'URL',
                                'name'     => 'btn_url',
                                'type'     => 'url',
                                'required' => 0,
                            ),
                            array(
                                'key'           => 'field_gc_btn_file',
                                'label'         => 'File (download)',
                                'name'          => 'btn_file',
                                'type'          => 'file',
                                'required'      => 0,
                                'return_format' => 'array',
                                'library'       => 'all',
                            ),
                        ),
                    ),
                ),
            ),
        ),
    );

    // -------------------------------------------------------------------------
    // Layout 9: Collection (Colecção)
    // Card model (small/large). Two adjacent small cards render side by side.
    // -------------------------------------------------------------------------
    $layout_collection = array(
        'key'        => 'layout_collection',
        'name'       => 'collection',
        'label'      => 'Collection',
        'display'    => 'block',
        'min'        => 0,
        'max'        => '',
        'sub_fields' => array(
            array(
                'key'         => 'field_col_title',
                'label'       => 'Collection Title',
                'name'        => 'collection_title',
                'type'        => 'text',
                'required'    => 1,
                'placeholder' => 'Collection name',
            ),
            array(
                'key'          => 'field_col_label',
                'label'        => 'Label (above title)',
                'name'         => 'collection_label',
                'type'         => 'text',
                'required'     => 0,
                'placeholder'  => 'e.g. Assets',
                'instructions' => 'Optional. Small text rendered above the title.',
            ),
            array(
                'key'           => 'field_col_model',
                'label'         => 'Card Model',
                'name'          => 'card_model',
                'type'          => 'radio',
                'required'      => 1,
                'choices'       => array(
                    'large' => 'Large',
                    'small' => 'Small (two adjacent small cards display side by side)',
                ),
                'default_value' => 'large',
                'layout'        => 'vertical',
            ),
            array(
                'key'          => 'field_col_desc',
                'label'        => 'Collection Description',
                'name'         => 'collection_description',
                'type'         => 'wysiwyg',
                'required'     => 0,
                'toolbar'      => 'basic',
                'media_upload' => 0,
                'tabs'         => 'visual',
                'instructions' => 'Optional. Displayed on the collection detail page.',
            ),
            array(
                'key'           => 'field_col_download_all',
                'label'         => 'Download All File',
                'name'          => 'download_all_file',
                'type'          => 'file',
                'required'      => 0,
                'return_format' => 'array',
                'library'       => 'all',
                'instructions'  => 'Optional. If set, shows a sticky "Download collection" button on the detail page.',
            ),
            array(
                'key'          => 'field_col_assets',
                'label'        => 'Assets',
                'name'         => 'collection_assets',
                'type'         => 'repeater',
                'required'     => 1,
                'min'          => 1,
                'max'          => 0,
                'layout'       => 'block',
                'button_label' => 'Add asset',
                'sub_fields'   => array(
                    array(
                        'key'           => 'field_col_asset_img',
                        'label'         => 'Image',
                        'name'          => 'asset_image',
                        'type'          => 'image',
                        'required'      => 1,
                        'return_format' => 'array',
                        'preview_size'  => 'medium',
                    ),
                    array(
                        'key'          => 'field_col_asset_desc',
                        'label'        => 'Description',
                        'name'         => 'asset_description',
                        'type'         => 'wysiwyg',
                        'required'     => 0,
                        'toolbar'      => 'basic',
                        'media_upload' => 0,
                        'tabs'         => 'visual',
                    ),
                    array(
                        'key'           => 'field_col_asset_file',
                        'label'         => 'File (download)',
                        'name'          => 'asset_file',
                        'type'          => 'file',
                        'required'      => 0,
                        'return_format' => 'array',
                        'library'       => 'all',
                        'instructions'  => 'Optional. If set, shows a download icon button for this asset.',
                    ),
                ),
            ),
        ),
    );

    // -------------------------------------------------------------------------
    // Layout 10: Icon Library (Biblioteca de Ícones)
    // -------------------------------------------------------------------------
    $layout_icon_library = array(
        'key'        => 'layout_icon_library',
        'name'       => 'icon_library',
        'label'      => 'Icon Library',
        'display'    => 'block',
        'min'        => 0,
        'max'        => 1,
        'sub_fields' => array(
            array(
                'key'          => 'field_il_title',
                'label'        => 'Title (H3)',
                'name'         => 'icon_library_title',
                'type'         => 'text',
                'required'     => 0,
                'placeholder'  => 'Icon Library',
                'instructions' => 'Optional. Rendered as H3.',
            ),
            array(
                'key'          => 'field_il_desc',
                'label'        => 'Description / Instructions',
                'name'         => 'icon_description',
                'type'         => 'wysiwyg',
                'required'     => 0,
                'toolbar'      => 'basic',
                'media_upload' => 0,
                'tabs'         => 'visual',
            ),
            array(
                'key'           => 'field_il_download_all',
                'label'         => 'Download All File',
                'name'          => 'icon_download_all',
                'type'          => 'file',
                'required'      => 0,
                'return_format' => 'array',
                'library'       => 'all',
                'instructions'  => 'Optional. If set, shows a "Download all" button.',
            ),
            array(
                'key'          => 'field_il_icons',
                'label'        => 'Icons',
                'name'         => 'icon_library_icons',
                'type'         => 'repeater',
                'required'     => 1,
                'min'          => 1,
                'max'          => 0,
                'layout'       => 'table',
                'button_label' => 'Add icon',
                'instructions' => 'All icons are loaded at once (no pagination). Name is used for search.',
                'sub_fields'   => array(
                    array(
                        'key'         => 'field_il_icon_name',
                        'label'       => 'Icon Name',
                        'name'        => 'icon_name',
                        'type'        => 'text',
                        'required'    => 1,
                        'placeholder' => 'e.g. arrow-right',
                    ),
                    array(
                        'key'           => 'field_il_icon_img',
                        'label'         => 'Icon Image',
                        'name'          => 'icon_image',
                        'type'          => 'image',
                        'required'      => 1,
                        'return_format' => 'array',
                        'preview_size'  => 'thumbnail',
                    ),
                    array(
                        'key'           => 'field_il_icon_file',
                        'label'         => 'Icon File (download)',
                        'name'          => 'icon_file',
                        'type'          => 'file',
                        'required'      => 0,
                        'return_format' => 'array',
                        'library'       => 'all',
                    ),
                ),
            ),
        ),
    );

    // -------------------------------------------------------------------------
    // Register the flexible content field group
    // -------------------------------------------------------------------------
    acf_add_local_field_group( array(
        'key'    => 'group_page_content_blocks',
        'title'  => 'Page Content Blocks',
        'fields' => array(
            array(
                'key'          => 'field_page_content_blocks',
                'label'        => 'Content Blocks',
                'name'         => 'page_content_blocks',
                'type'         => 'flexible_content',
                'required'     => 0,
                'instructions' => 'Assemble the page by adding and reordering blocks. Start with a Page Header.',
                'button_label' => 'Add block',
                'layouts'      => array(
                    $layout_page_header,
                    $layout_divider,
                    $layout_section_top,
                    $layout_paragraph,
                    $layout_quote,
                    $layout_table,
                    $layout_note,
                    $layout_grid_cards,
                    $layout_collection,
                    $layout_icon_library,
                ),
            ),
        ),
        'location' => array(
            array(
                array(
                    'param'    => 'page_template',
                    'operator' => '==',
                    'value'    => 'page-generic-content.php',
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
// Helper: render collection detail
// ---------------------------------------------------------------------------

/**
 * Renders the detail view for one collection.
 * Called from page-generic-content.php when ?collection=N is in the URL.
 *
 * @param array $blocks          All flexible content blocks for the page.
 * @param int   $collection_index 0-based index among collection layouts only.
 */
function abc_render_collection_detail( array $blocks, int $collection_index ) {
    $collection_blocks = array_values( array_filter( $blocks, function ( $b ) {
        return isset( $b['acf_fc_layout'] ) && 'collection' === $b['acf_fc_layout'];
    } ) );

    if ( ! isset( $collection_blocks[ $collection_index ] ) ) {
        wp_safe_redirect( get_permalink() );
        exit;
    }

    $block = $collection_blocks[ $collection_index ];
    include get_template_directory() . '/blocks/collection-detail.php';
}

// ---------------------------------------------------------------------------
// Helper: render sibling navigation buttons (prev / next)
// ---------------------------------------------------------------------------

/**
 * Renders prev/next sibling navigation at the bottom of the content area.
 * Siblings are determined by the primary nav menu order (same parent).
 */
function abc_render_sibling_nav_buttons() {
    $current_id = get_the_ID();
    if ( ! $current_id ) {
        return;
    }

    $siblings = abc_get_sidebar_nav_items( $current_id );
    if ( empty( $siblings ) || count( $siblings ) < 2 ) {
        return;
    }

    $current_pos = null;
    foreach ( $siblings as $i => $sibling ) {
        if ( (int) $sibling['id'] === (int) $current_id ) {
            $current_pos = $i;
            break;
        }
    }

    if ( null === $current_pos ) {
        return;
    }

    $prev = $current_pos > 0 ? $siblings[ $current_pos - 1 ] : null;
    $next = ( $current_pos < count( $siblings ) - 1 ) ? $siblings[ $current_pos + 1 ] : null;

    if ( ! $prev && ! $next ) {
        return;
    }
    ?>
    <nav class="sibling-nav" aria-label="<?php esc_attr_e( 'Page navigation', 'ascendum-brand-center' ); ?>">
        <?php if ( $prev ) : ?>
        <a href="<?php echo esc_url( $prev['url'] ); ?>" class="sibling-nav-btn sibling-nav-prev">
            <?php abc_icon( 'chevron-left-20' ); ?>
            <span class="sibling-nav-text"><?php esc_html_e( 'Previous page', 'ascendum-brand-center' ); ?></span>
        </a>
        <?php endif; ?>
        <?php if ( $next ) : ?>
        <a href="<?php echo esc_url( $next['url'] ); ?>" class="sibling-nav-btn sibling-nav-next">
            <span class="sibling-nav-text"><?php esc_html_e( 'Next page', 'ascendum-brand-center' ); ?></span>
            <?php abc_icon( 'chevron-right-20' ); ?>
        </a>
        <?php endif; ?>
    </nav>
    <?php
}

// ---------------------------------------------------------------------------
// TinyMCE: Check List + Red Cross List custom paragraph styles
// ---------------------------------------------------------------------------

add_filter( 'tiny_mce_before_init', 'abc_tinymce_custom_styles' );
/**
 * Adds Check List and Red Cross List to the TinyMCE Formats dropdown.
 *
 * @param  array $init TinyMCE init settings.
 * @return array
 */
function abc_tinymce_custom_styles( array $init ): array {
    $custom = array(
        array(
            'title'    => 'Check List',
            'selector' => 'ul',
            'classes'  => 'list-check',
        ),
        array(
            'title'    => 'Red Cross List',
            'selector' => 'ul',
            'classes'  => 'list-cross',
        ),
    );

    if ( ! empty( $init['style_formats'] ) ) {
        $existing = json_decode( $init['style_formats'], true );
        if ( is_array( $existing ) ) {
            $custom = array_merge( $existing, $custom );
        }
    }

    $init['style_formats']       = wp_json_encode( $custom );
    $init['style_formats_merge'] = false;

    return $init;
}

add_filter( 'mce_buttons', 'abc_tinymce_add_styleselect' );
/**
 * Ensures the Formats (styleselect) button appears in the TinyMCE toolbar.
 *
 * @param  array $buttons First toolbar row.
 * @return array
 */
function abc_tinymce_add_styleselect( array $buttons ): array {
    if ( ! in_array( 'styleselect', $buttons, true ) ) {
        array_unshift( $buttons, 'styleselect' );
    }
    return $buttons;
}
