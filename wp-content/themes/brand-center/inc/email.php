<?php
/**
 * Email functions — Brand Center
 *
 * Email #1: Invitation email to a new external user.
 * Email #2: Password recovery link (self-service).
 * Email #3: Admin-initiated password reset notification.
 * Email #4: Contact form reception.
 *
 * @package brand-center
 */

defined( 'ABSPATH' ) || exit;

/**
 * Sends Email #1 — Invitation to a new external user.
 *
 * @param string $to_email  Recipient email address.
 * @param string $token     Plain-text invite token (not the stored hash).
 */
function abc_send_invite_email( $to_email, $token ) {
    $site_name  = get_bloginfo( 'name' );
    $set_pw_url = add_query_arg( 'token', rawurlencode( $token ), home_url( '/set-password/' ) );

    $subject = sprintf(
        /* translators: %s: site name */
        __( "You've been invited to %s", 'brand-center' ),
        $site_name
    );

    $message = abc_invite_email_html( $set_pw_url, $site_name );

    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        'From: ' . $site_name . ' <' . get_option( 'admin_email' ) . '>',
    );

    wp_mail( $to_email, $subject, $message, $headers );
}

/**
 * Builds the HTML body for the invite email.
 *
 * @param  string $set_pw_url  Full URL to the set-password page with token.
 * @param  string $site_name   Site display name.
 * @return string              HTML email body.
 */
function abc_invite_email_html( $set_pw_url, $site_name ) {
    $set_pw_url = esc_url( $set_pw_url );
    $site_name  = esc_html( $site_name );

    return '<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>' . $site_name . '</title>
</head>
<body style="margin:0;padding:0;background-color:#F4F4F4;font-family:\'IBM Plex Sans\',Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#F4F4F4;">
  <tr>
    <td align="center" style="padding:40px 16px;">
      <table width="100%" style="max-width:520px;background-color:#ffffff;border-radius:8px;overflow:hidden;">
        <tr>
          <td style="background-color:#003846;padding:32px 40px;">
            <p style="margin:0;font-family:\'Montserrat\',Arial,sans-serif;font-size:22px;font-weight:700;color:#ffffff;line-height:1.3;">' . $site_name . '</p>
          </td>
        </tr>
        <tr>
          <td style="padding:40px;">
            <p style="margin:0 0 16px;font-size:16px;font-weight:400;line-height:1.5;color:#585858;">You have been invited to access the <strong style="color:#003846;">' . $site_name . '</strong> &mdash; a centralised platform for brand guidelines and ready-to-use assets.</p>
            <p style="margin:0 0 32px;font-size:16px;font-weight:400;line-height:1.5;color:#585858;">Click the button below to set your password and activate your account. This link is valid for <strong>24&nbsp;hours</strong>.</p>
            <table cellpadding="0" cellspacing="0">
              <tr>
                <td style="border-radius:100px;background-color:#003846;">
                  <a href="' . $set_pw_url . '" style="display:inline-block;padding:12px 32px;font-family:\'IBM Plex Sans\',Arial,sans-serif;font-size:16px;font-weight:600;color:#ffffff;text-decoration:none;border-radius:100px;">Set your password</a>
                </td>
              </tr>
            </table>
            <p style="margin:32px 0 0;font-size:14px;font-weight:400;line-height:1.5;color:#B1B1B1;">If you did not expect this invitation, please ignore this email. The link will expire after 24&nbsp;hours.</p>
          </td>
        </tr>
        <tr>
          <td style="padding:24px 40px;border-top:1px solid #D6D6D6;">
            <p style="margin:0;font-size:14px;color:#B1B1B1;">&copy; Ascendum 2025. All rights reserved.</p>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</body>
</html>';
}

/**
 * Sends Email #2 — Password recovery link to an external user (self-service).
 *
 * @param string $to_email  Recipient email address.
 * @param string $token     Plain-text recovery token (not the stored hash).
 */
function abc_send_recovery_email( $to_email, $token ) {
    $site_name = get_bloginfo( 'name' );
    $reset_url = add_query_arg( 'token', rawurlencode( $token ), home_url( '/reset-password/' ) );

    $subject = sprintf(
        /* translators: %s: site name */
        __( '[%s] Password reset request', 'brand-center' ),
        $site_name
    );

    $message = abc_recovery_email_html( $reset_url, $site_name );

    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        'From: ' . $site_name . ' <' . get_option( 'admin_email' ) . '>',
    );

    wp_mail( $to_email, $subject, $message, $headers );
}

/**
 * Builds the HTML body for Email #2 — Self-service password recovery.
 *
 * @param  string $reset_url  Full URL to the reset-password page with token.
 * @param  string $site_name  Site display name.
 * @return string             HTML email body.
 */
function abc_recovery_email_html( $reset_url, $site_name ) {
    $reset_url = esc_url( $reset_url );
    $site_name = esc_html( $site_name );

    return '<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>' . $site_name . '</title>
</head>
<body style="margin:0;padding:0;background-color:#F4F4F4;font-family:\'IBM Plex Sans\',Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#F4F4F4;">
  <tr>
    <td align="center" style="padding:40px 16px;">
      <table width="100%" style="max-width:520px;background-color:#ffffff;border-radius:8px;overflow:hidden;">
        <tr>
          <td style="background-color:#003846;padding:32px 40px;">
            <p style="margin:0;font-family:\'Montserrat\',Arial,sans-serif;font-size:22px;font-weight:700;color:#ffffff;line-height:1.3;">' . $site_name . '</p>
          </td>
        </tr>
        <tr>
          <td style="padding:40px;">
            <p style="margin:0 0 16px;font-size:16px;font-weight:400;line-height:1.5;color:#585858;">We received a request to reset the password for your <strong style="color:#003846;">' . $site_name . '</strong> account.</p>
            <p style="margin:0 0 32px;font-size:16px;font-weight:400;line-height:1.5;color:#585858;">Click the button below to set a new password. This link is valid for <strong>24&nbsp;hours</strong>.</p>
            <table cellpadding="0" cellspacing="0">
              <tr>
                <td style="border-radius:100px;background-color:#003846;">
                  <a href="' . $reset_url . '" style="display:inline-block;padding:12px 32px;font-family:\'IBM Plex Sans\',Arial,sans-serif;font-size:16px;font-weight:600;color:#ffffff;text-decoration:none;border-radius:100px;">Reset password</a>
                </td>
              </tr>
            </table>
            <p style="margin:32px 0 0;font-size:14px;font-weight:400;line-height:1.5;color:#B1B1B1;">If you did not request a password reset, you can safely ignore this email. Your password will remain unchanged.</p>
          </td>
        </tr>
        <tr>
          <td style="padding:24px 40px;border-top:1px solid #D6D6D6;">
            <p style="margin:0;font-size:14px;color:#B1B1B1;">&copy; Ascendum 2025. All rights reserved.</p>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</body>
</html>';
}

/**
 * Sends Email #3 — Admin-initiated password reset notification.
 *
 * @param string $to_email  Recipient email address.
 * @param string $token     Plain-text recovery token (not the stored hash).
 */
function abc_send_admin_reset_email( $to_email, $token ) {
    $site_name  = get_bloginfo( 'name' );
    $reset_url  = add_query_arg( 'token', rawurlencode( $token ), home_url( '/reset-password/' ) );

    $subject = sprintf(
        /* translators: %s: site name */
        __( '[%s] Your password has been reset by an administrator', 'brand-center' ),
        $site_name
    );

    $message = abc_admin_reset_email_html( $reset_url, $site_name );

    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        'From: ' . $site_name . ' <' . get_option( 'admin_email' ) . '>',
    );

    wp_mail( $to_email, $subject, $message, $headers );
}

/**
 * Builds the HTML body for Email #3 — Admin-initiated password reset.
 *
 * @param  string $reset_url  Full URL to the reset-password page with token.
 * @param  string $site_name  Site display name.
 * @return string             HTML email body.
 */
function abc_admin_reset_email_html( $reset_url, $site_name ) {
    $reset_url = esc_url( $reset_url );
    $site_name = esc_html( $site_name );

    return '<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>' . $site_name . '</title>
</head>
<body style="margin:0;padding:0;background-color:#F4F4F4;font-family:\'IBM Plex Sans\',Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#F4F4F4;">
  <tr>
    <td align="center" style="padding:40px 16px;">
      <table width="100%" style="max-width:520px;background-color:#ffffff;border-radius:8px;overflow:hidden;">
        <tr>
          <td style="background-color:#003846;padding:32px 40px;">
            <p style="margin:0;font-family:\'Montserrat\',Arial,sans-serif;font-size:22px;font-weight:700;color:#ffffff;line-height:1.3;">' . $site_name . '</p>
          </td>
        </tr>
        <tr>
          <td style="padding:40px;">
            <p style="margin:0 0 16px;font-size:16px;font-weight:400;line-height:1.5;color:#585858;">An administrator has requested a password reset for your account on <strong style="color:#003846;">' . $site_name . '</strong>.</p>
            <p style="margin:0 0 32px;font-size:16px;font-weight:400;line-height:1.5;color:#585858;">Click the button below to set your new password. This link is valid for <strong>24&nbsp;hours</strong>.</p>
            <table cellpadding="0" cellspacing="0">
              <tr>
                <td style="border-radius:100px;background-color:#003846;">
                  <a href="' . $reset_url . '" style="display:inline-block;padding:12px 32px;font-family:\'IBM Plex Sans\',Arial,sans-serif;font-size:16px;font-weight:600;color:#ffffff;text-decoration:none;border-radius:100px;">Set new password</a>
                </td>
              </tr>
            </table>
            <p style="margin:32px 0 0;font-size:14px;font-weight:400;line-height:1.5;color:#B1B1B1;">If you did not request this reset, you can safely ignore this email. Your password will remain unchanged. If the link expires, you can request a new one from the <a href="' . esc_url( home_url( '/password-recovery/' ) ) . '" style="color:#B1B1B1;">password recovery page</a>.</p>
          </td>
        </tr>
        <tr>
          <td style="padding:24px 40px;border-top:1px solid #D6D6D6;">
            <p style="margin:0;font-size:14px;color:#B1B1B1;">&copy; Ascendum 2025. All rights reserved.</p>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</body>
</html>';
}

