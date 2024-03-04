<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

function simpleanalytics_add_settings_page() {
  add_options_page(
    __( 'Simple Analytics Settings', 'simple-analytics' ),
    __( 'Simple Analytics', 'simple-analytics' ),
    'manage_options',
    'simple-analytics-settings',
    'simpleanalytics_render_settings_page'
  );
}

function simpleanalytics_render_settings_page() {
  if ( ! current_user_can( 'manage_options' ) ) {
    return;
  }

  if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
    if ( ! isset( $_POST['simpleanalytics_settings_nonce'] ) ) {
      return;
    }

    if ( ! wp_verify_nonce( $_POST['simpleanalytics_settings_nonce'], 'simpleanalytics_settings' ) ) {
      return;
    }

    if ( ! isset( $_POST['simpleanalytics_custom_domain'] ) ) {
      return;
    }

    $custom_domain = sanitize_text_field( $_POST['simpleanalytics_custom_domain'] );
    $custom_domain = preg_replace( '/^https?:\/\//', '', $custom_domain );

    update_option( 'simpleanalytics_custom_domain', $custom_domain );
  }

  $custom_domain = get_option( 'simpleanalytics_custom_domain' );
  ?>
    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        <form method="post"
              action="<?php echo esc_url( admin_url( 'options-general.php?page=simple-analytics-settings' ) ); ?>">
      <?php wp_nonce_field( 'simpleanalytics_settings', 'simpleanalytics_settings_nonce' ); ?>
            <table class="form-table">
                <tbody>
                <tr>
                    <th scope="row">
                        <label for="simpleanalytics_custom_domain"><?php esc_html_e( 'Custom subdomain', 'simple-analytics' ); ?></label>
                    </th>
                    <td>
                        <input type="text" name="simpleanalytics_custom_domain" id="simpleanalytics_custom_domain"
                               value="<?php echo esc_attr( $custom_domain ); ?>" class="regular-text">
                        <p class="description">
                          <?php esc_html_e( 'E.g. api.example.com. Leave empty to use the default domain (most users).', 'simple-analytics' ); ?>
                          <a href="https://docs.simpleanalytics.com/bypass-ad-blockers"><?php esc_html_e( 'Learn more.', 'simple-analytics' ); ?></a>
                        </p>
                    </td>
                </tr>
                </tbody>
            </table>
      <?php submit_button( __( 'Save Changes', 'simple-analytics' ) ); ?>
        </form>
    </div>
  <?php
}

add_action( 'admin_menu', 'simpleanalytics_add_settings_page' );
