<?php
/**
 * FILE-ACCESS — URL helper for protected downloads.
 *
 * @package ascendum-brand-center
 */

defined( 'ABSPATH' ) || exit;

/**
 * Returns the gated download URL for a file stored in the protected directory.
 *
 * The file must be stored under ABC_PROTECTED_FILES_DIR (or the default
 * ../protected-files/uploads/ path). The relative path passed here must
 * match exactly the path used when writing the file to that directory.
 *
 * Example:
 *   // File on disk: /home/user/protected-files/uploads/volvo/electric/machine.pdf
 *   echo abc_protected_file_url( 'volvo/electric/machine.pdf' );
 *   // → https://example.com/download.php?file=volvo%2Felectric%2Fmachine.pdf
 *
 * @param  string $relative_path Path relative to the protected-files directory.
 * @return string                Full URL to the download endpoint.
 */
function abc_protected_file_url( $relative_path ) {
    return home_url( '/download.php?file=' . urlencode( $relative_path ) );
}
