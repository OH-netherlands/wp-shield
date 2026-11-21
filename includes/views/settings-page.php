<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/** @var array $s settings, @var array $log recent log entries (from WP_Shield_Admin::render_page) */
?>
<div class="wrap wp-shield-wrap">
	<h1><?php esc_html_e( 'WP Shield', 'wp-shield' ); ?></h1>
	<p><?php esc_html_e( 'A lightweight, open-source firewall for this site. Version', 'wp-shield' ); ?> <?php echo esc_html( WP_SHIELD_VERSION ); ?></p>

	<?php if ( isset( $_GET['updated'] ) ) : ?>
		<div class="notice notice-success"><p><?php esc_html_e( 'Settings saved.', 'wp-shield' ); ?></p></div>
	<?php endif; ?>
	<?php if ( isset( $_GET['cleared'] ) ) : ?>
		<div class="notice notice-success"><p><?php esc_html_e( 'Log cleared.', 'wp-shield' ); ?></p></div>
	<?php endif; ?>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="wp_shield_save_settings" />
		<?php wp_nonce_field( 'wp_shield_save_settings' ); ?>

		<h2><?php esc_html_e( 'Modules', 'wp-shield' ); ?></h2>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><?php esc_html_e( 'Firewall', 'wp-shield' ); ?></th>
				<td>
					<label><input type="checkbox" name="firewall_enabled" <?php checked( ! empty( $s['firewall_enabled'] ) ); ?> />
					<?php esc_html_e( 'Block requests matching known-malicious patterns and the IP blocklist below.', 'wp-shield' ); ?></label>
					<br />
					<label><input type="checkbox" name="block_bad_bots" <?php checked( ! empty( $s['block_bad_bots'] ) ); ?> />
					<?php esc_html_e( 'Also block a conservative list of known scraper/bot user agents.', 'wp-shield' ); ?></label>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Rate limiting', 'wp-shield' ); ?></th>
				<td>
					<label><input type="checkbox" name="rate_limit_enabled" <?php checked( ! empty( $s['rate_limit_enabled'] ) ); ?> />
					<?php esc_html_e( 'Enabled', 'wp-shield' ); ?></label>
					<p>
						<label><?php esc_html_e( 'Max requests', 'wp-shield' ); ?>
							<input type="number" min="10" name="rate_limit_requests" value="<?php echo esc_attr( $s['rate_limit_requests'] ); ?>" style="width:90px" />
						</label>
						<label><?php esc_html_e( 'per (seconds)', 'wp-shield' ); ?>
							<input type="number" min="5" name="rate_limit_window" value="<?php echo esc_attr( $s['rate_limit_window'] ); ?>" style="width:90px" />
						</label>
						<span class="description"><?php esc_html_e( 'per visitor IP. Logged-in administrators are never rate limited.', 'wp-shield' ); ?></span>
					</p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Login protection', 'wp-shield' ); ?></th>
				<td>
					<label><input type="checkbox" name="login_guard_enabled" <?php checked( ! empty( $s['login_guard_enabled'] ) ); ?> />
					<?php esc_html_e( 'Enabled', 'wp-shield' ); ?></label>
					<p>
						<label><?php esc_html_e( 'Max failed attempts', 'wp-shield' ); ?>
							<input type="number" min="3" name="login_max_attempts" value="<?php echo esc_attr( $s['login_max_attempts'] ); ?>" style="width:90px" />
						</label>
						<label><?php esc_html_e( 'within (seconds)', 'wp-shield' ); ?>
							<input type="number" min="60" name="login_window" value="<?php echo esc_attr( $s['login_window'] ); ?>" style="width:90px" />
						</label>
						<label><?php esc_html_e( 'lockout duration (seconds)', 'wp-shield' ); ?>
							<input type="number" min="60" name="login_lockout" value="<?php echo esc_attr( $s['login_lockout'] ); ?>" style="width:90px" />
						</label>
					</p>
				</td>
			</tr>
		</table>

		<h2><?php esc_html_e( 'IP lists', 'wp-shield' ); ?></h2>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="wp_shield_allowlist"><?php esc_html_e( 'Allowlist', 'wp-shield' ); ?></label></th>
				<td>
					<textarea id="wp_shield_allowlist" name="ip_allowlist" rows="4" cols="40" class="large-text code"><?php echo esc_textarea( implode( "\n", (array) $s['ip_allowlist'] ) ); ?></textarea>
					<p class="description"><?php esc_html_e( 'One IP or CIDR range per line. Allowlisted IPs skip the firewall entirely — use for your office/VPN IP.', 'wp-shield' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="wp_shield_blocklist"><?php esc_html_e( 'Blocklist', 'wp-shield' ); ?></label></th>
				<td>
					<textarea id="wp_shield_blocklist" name="ip_blocklist" rows="4" cols="40" class="large-text code"><?php echo esc_textarea( implode( "\n", (array) $s['ip_blocklist'] ) ); ?></textarea>
					<p class="description"><?php esc_html_e( 'One IP or CIDR range per line.', 'wp-shield' ); ?></p>
				</td>
			</tr>
		</table>

		<?php submit_button( __( 'Save settings', 'wp-shield' ) ); ?>
	</form>

	<h2><?php esc_html_e( 'Recent activity', 'wp-shield' ); ?></h2>
	<?php if ( empty( $log ) ) : ?>
		<p><?php esc_html_e( 'Nothing blocked yet.', 'wp-shield' ); ?></p>
	<?php else : ?>
		<table class="widefat striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Time', 'wp-shield' ); ?></th>
					<th><?php esc_html_e( 'IP', 'wp-shield' ); ?></th>
					<th><?php esc_html_e( 'Reason', 'wp-shield' ); ?></th>
					<th><?php esc_html_e( 'Request URI', 'wp-shield' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( array_slice( $log, 0, 100 ) as $entry ) : ?>
					<tr>
						<td><?php echo esc_html( gmdate( 'Y-m-d H:i:s', (int) $entry['time'] ) ); ?> UTC</td>
						<td><?php echo esc_html( $entry['ip'] ); ?></td>
						<td><?php echo esc_html( $entry['reason'] ); ?></td>
						<td><code><?php echo esc_html( $entry['uri'] ); ?></code></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top:12px">
			<input type="hidden" name="action" value="wp_shield_clear_log" />
			<?php wp_nonce_field( 'wp_shield_clear_log' ); ?>
			<?php submit_button( __( 'Clear log', 'wp-shield' ), 'secondary', 'submit', false ); ?>
		</form>
	<?php endif; ?>
</div>
