<?php
/**
 * FILE-ACCESS — Protected file download endpoint.
 *
 * Protected files must be stored outside the document root in the directory
 * defined by ABC_PROTECTED_FILES_DIR (set in wp-config.php). When that
 * constant is absent the default is one level above ABSPATH:
 *   <server_root>/protected-files/uploads/
 *
 * That directory must be created manually on the server before any file is
 * uploaded there. Files placed under wp-content/uploads are NOT protected —
 * only files written to the protected directory are served through this endpoint.
 *
 * Usage from templates / blocks:
 *   $url = abc_protected_file_url( 'brand/logos/logo.pdf' );
 *   // → /download.php?file=brand%2Flogos%2Flogo.pdf
 */

require_once __DIR__ . '/wp-load.php';

// 1. Verificar autenticação
if ( ! is_user_logged_in() ) {
    wp_redirect( wp_login_url( $_SERVER['REQUEST_URI'] ) );
    exit;
}

// 2. Validar o parâmetro do ficheiro
$file = isset( $_GET['file'] ) ? $_GET['file'] : '';

if ( empty( $file ) ) {
    wp_die( 'Ficheiro não especificado.', '', array( 'response' => 403 ) );
}

// 3. Resolver o diretório base
if ( defined( 'ABC_PROTECTED_FILES_DIR' ) ) {
    $base_dir = realpath( ABC_PROTECTED_FILES_DIR );
} else {
    // Default: sibling of the document root → outside public access.
    $base_dir = realpath( dirname( rtrim( ABSPATH, '/\\' ) ) . '/protected-files/uploads' );
}

if ( false === $base_dir ) {
    wp_die( 'Diretório de ficheiros protegidos não encontrado. Confirme que ABC_PROTECTED_FILES_DIR está correto e que o diretório existe.', '', array( 'response' => 500 ) );
}

// 4. Sanitizar e construir o caminho
$file     = str_replace( '\\', '/', $file );
$file     = ltrim( $file, '/' );
$filepath = realpath( $base_dir . '/' . $file );

// 5. Garantir que o ficheiro está dentro da pasta permitida (path traversal prevention).
// Append DIRECTORY_SEPARATOR to base_dir to block sibling-directory bypass
// (a bare strpos check would incorrectly pass /protected/uploads-evil/ as valid).
if ( false === $filepath || 0 !== strpos( $filepath, $base_dir . DIRECTORY_SEPARATOR ) ) {
    wp_die( 'Acesso negado.', '', array( 'response' => 403 ) );
}

if ( ! file_exists( $filepath ) || ! is_file( $filepath ) ) {
    wp_die( 'Ficheiro não encontrado.', '', array( 'response' => 404 ) );
}

// 6. Servir o ficheiro
$mime = mime_content_type( $filepath );
$size = filesize( $filepath );

header( 'Content-Type: ' . $mime );
header( 'Content-Length: ' . $size );
header( 'Content-Disposition: attachment; filename="' . basename( $filepath ) . '"' );
header( 'Cache-Control: no-store, no-cache, must-revalidate' );
header( 'Pragma: no-cache' );
header( 'X-Content-Type-Options: nosniff' );

if ( ob_get_level() ) {
    ob_end_clean();
}

// Files larger than 10 MB are streamed in 8 KB chunks to avoid memory spikes.
// Smaller files are served with readfile() which is sufficient.
if ( $size > 10 * 1024 * 1024 ) {
    $fp = fopen( $filepath, 'rb' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
    if ( $fp ) {
        while ( ! feof( $fp ) ) {
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo fread( $fp, 8192 ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fread
            flush();
        }
        fclose( $fp ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
    }
} else {
    readfile( $filepath ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile
}

exit;
