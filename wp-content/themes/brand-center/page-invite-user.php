<?php
/**
 * Template Name: Invite User
 *
 * A02.1 — Local Admin sends an invitation to a new external user.
 * Access restricted to local_admin and administrator roles (enforced via
 * abc_auth_redirect in functions.php).
 *
 * @package brand-center
 */

defined( 'ABSPATH' ) || exit;

// ----- Error & success message maps -----------------------------------------
$error_messages = array(
    'required'     => __( 'This field is required.', 'brand-center' ),
    'invalid_email' => __( 'Please enter a valid email address.', 'brand-center' ),
    'user_exists'  => __( 'An account or a pending invitation already exists for this email address.', 'brand-center' ),
    'system'       => __( 'An unexpected error occurred. Please try again shortly.', 'brand-center' ),
);

$error_key = isset( $_GET['invite_error'] ) ? sanitize_key( $_GET['invite_error'] ) : '';
$error_msg = isset( $error_messages[ $error_key ] ) ? $error_messages[ $error_key ] : '';

$invite_success = ! empty( $_GET['invite_success'] );
$cancelled      = ! empty( $_GET['cancelled'] );

// ----- Pending invitations --------------------------------------------------
$pending_users = get_users( array(
    'meta_key'   => 'abc_invite_pending',
    'meta_value' => '1',
    'number'     => -1,
    'orderby'    => 'registered',
    'order'      => 'DESC',
) );

get_header();
?>

<main class="invite-page">
    <div class="invite-container">

        <!-- Invite form -->
        <section class="invite-form-section">
            <h1 class="invite-title"><?php esc_html_e( 'Invite new user', 'brand-center' ); ?></h1>
            <p class="invite-intro"><?php esc_html_e( 'Enter the email address of the person you want to invite. They will receive an email with a link to set their password.', 'brand-center' ); ?></p>

            <?php if ( $invite_success ) : ?>
            <div class="invite-notice invite-notice--success" role="status">
                <?php esc_html_e( 'Invitation sent successfully.', 'brand-center' ); ?>
            </div>
            <?php endif; ?>

            <?php if ( $cancelled ) : ?>
            <div class="invite-notice invite-notice--success" role="status">
                <?php esc_html_e( 'Invitation cancelled.', 'brand-center' ); ?>
            </div>
            <?php endif; ?>

            <form
                class="invite-form"
                method="post"
                action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
                novalidate
            >
                <input type="hidden" name="action" value="abc_invite_user">
                <?php wp_nonce_field( 'abc_invite_user', 'abc_invite_nonce' ); ?>

                <div class="invite-field-group">
                    <label class="invite-label" for="invite-email">
                        <?php esc_html_e( 'New user e-mail', 'brand-center' ); ?>
                    </label>

                    <?php if ( $error_msg ) : ?>
                    <div class="invite-error-message" role="alert">
                        <?php echo esc_html( $error_msg ); ?>
                    </div>
                    <?php endif; ?>

                    <input
                        type="email"
                        id="invite-email"
                        name="invite_email"
                        class="invite-input<?php echo $error_key ? ' is-error' : ''; ?>"
                        placeholder="<?php esc_attr_e( 'name@company.com', 'brand-center' ); ?>"
                        autocomplete="email"
                        spellcheck="false"
                    >
                </div>

                <button type="submit" class="invite-btn">
                    <?php esc_html_e( 'Send invite', 'brand-center' ); ?>
                </button>
            </form>
        </section>

        <?php if ( ! empty( $pending_users ) ) : ?>
        <!-- Pending invitations -->
        <section class="invite-pending">
            <h2 class="invite-pending-title">
                <?php
                printf(
                    /* translators: %d: number of pending invitations */
                    esc_html__( 'Pending invites (%d)', 'brand-center' ),
                    count( $pending_users )
                );
                ?>
            </h2>

            <ul class="invite-pending-list">
                <?php foreach ( $pending_users as $pending_user ) :
                    $pending_email = get_user_meta( $pending_user->ID, 'abc_invite_email', true );
                    $pending_email = $pending_email ?: $pending_user->user_email;
                ?>
                <li class="invite-pending-item">
                    <span class="invite-pending-email"><?php echo esc_html( $pending_email ); ?></span>

                    <form
                        class="invite-cancel-form"
                        method="post"
                        action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
                    >
                        <input type="hidden" name="action" value="abc_cancel_invite">
                        <input type="hidden" name="invite_user_id" value="<?php echo esc_attr( $pending_user->ID ); ?>">
                        <?php wp_nonce_field( 'abc_cancel_invite', 'abc_cancel_nonce' ); ?>
                        <button type="submit" class="invite-cancel-btn">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
                                <path d="M2 4h12M6 4V2h4v2M3 4l1 10h8l1-10" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <?php esc_html_e( 'Cancel invite', 'brand-center' ); ?>
                        </button>
                    </form>
                </li>
                <?php endforeach; ?>
            </ul>
        </section>
        <?php endif; ?>

    </div>
</main>

<?php get_footer(); ?>
