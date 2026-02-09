<?php
/**
 * Maintenance Mode Template.
 *
 * This is a standalone template - no wp_head() or wp_footer().
 * All styles are inline to avoid external requests.
 *
 * @package PauseWP
 *
 * @var array $settings Plugin settings passed from Engine.
 */

declare(strict_types=1);

defined( 'ABSPATH' ) || exit;

// Extract settings with defaults.
$heading            = $settings['heading'] ?? __( 'We\'ll Be Right Back', 'pausewp' );
$subheading         = $settings['subheading'] ?? __( 'Our site is currently undergoing scheduled maintenance.', 'pausewp' );
$logo_id            = $settings['logo_id'] ?? 0;
$logo_alt           = $settings['logo_alt'] ?? '';
$seo_title          = $settings['seo_title'] ?? __( 'Site Under Maintenance', 'pausewp' );
$meta_description   = $settings['meta_description'] ?? '';
$cta_buttons        = $settings['cta_buttons'] ?? [];
$countdown_enabled  = $settings['countdown_enabled'] ?? false;
$countdown_datetime = $settings['countdown_datetime'] ?? '';

// Convert countdown datetime to timestamp using WordPress timezone.
$countdown_timestamp = 0;
if ( $countdown_enabled && ! empty( $countdown_datetime ) ) {
	$wp_timezone         = wp_timezone();
	$countdown_date      = \DateTime::createFromFormat( 'Y-m-d\TH:i', $countdown_datetime, $wp_timezone );
	if ( $countdown_date ) {
		$countdown_timestamp = $countdown_date->getTimestamp() * 1000; // Convert to milliseconds for JS.
	}
}

// Get logo URL if set.
$logo_url = '';
if ( ! empty( $logo_id ) ) {
	$logo_url = wp_get_attachment_image_url( (int) $logo_id, 'medium' );
}

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="robots" content="noindex, nofollow">
	<?php if ( ! empty( $meta_description ) ) : ?>
		<meta name="description" content="<?php echo esc_attr( $meta_description ); ?>">
	<?php endif; ?>
	<title><?php echo esc_html( $seo_title ); ?></title>
	<style>
		/* ========================================
		   Modern Clean Maintenance Page
		   Neutral Colors | Pure CSS | No 3rd Party
		   ======================================== */

		/* Reset */
		*, *::before, *::after {
			box-sizing: border-box;
			margin: 0;
			padding: 0;
		}

		/* Base */
		html {
			font-size: 16px;
			-webkit-font-smoothing: antialiased;
			-moz-osx-font-smoothing: grayscale;
		}

		body {
			font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
			background-color: #fafafa;
			color: #333;
			min-height: 100vh;
			display: flex;
			align-items: center;
			justify-content: center;
			padding: 24px;
			line-height: 1.6;
		}

		/* Main Container */
		.pausewp-container {
			max-width: 480px;
			width: 100%;
			text-align: center;
			animation: fadeUp 0.5s ease-out;
		}

		@keyframes fadeUp {
			from {
				opacity: 0;
				transform: translateY(16px);
			}
			to {
				opacity: 1;
				transform: translateY(0);
			}
		}

		/* Card */
		.pausewp-card {
			background: #fff;
			border-radius: 12px;
			padding: 48px 32px;
			box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06), 
			            0 4px 12px rgba(0, 0, 0, 0.04);
		}

		/* Icon */
		.pausewp-icon {
			width: 64px;
			height: 64px;
			margin: 0 auto 24px;
			background: #f5f5f5;
			border-radius: 50%;
			display: flex;
			align-items: center;
			justify-content: center;
		}

		.pausewp-icon svg {
			width: 28px;
			height: 28px;
			color: #666;
		}

		/* Logo */
		.pausewp-logo {
			margin-bottom: 24px;
		}

		.pausewp-logo img {
			max-width: 160px;
			max-height: 60px;
			width: auto;
			height: auto;
		}

		/* Heading */
		.pausewp-heading {
			font-size: 1.5rem;
			font-weight: 600;
			color: #111;
			margin-bottom: 12px;
			letter-spacing: -0.01em;
		}

		/* Subheading */
		.pausewp-subheading {
			font-size: 0.9375rem;
			color: #666;
			margin-bottom: 0;
			line-height: 1.7;
		}

		.pausewp-subheading a {
			color: #333;
			text-decoration: underline;
			text-underline-offset: 2px;
		}

		.pausewp-subheading a:hover {
			color: #000;
		}

		/* Countdown Timer */
		.pausewp-countdown {
			display: flex;
			justify-content: center;
			gap: 16px;
			margin-top: 24px;
			padding-top: 24px;
			border-top: 1px solid #eee;
		}

		.pausewp-countdown__item {
			text-align: center;
			min-width: 60px;
		}

		.pausewp-countdown__value {
			display: block;
			font-size: 2rem;
			font-weight: 600;
			color: #111;
			line-height: 1.2;
		}

		.pausewp-countdown__label {
			display: block;
			font-size: 0.75rem;
			color: #666;
			text-transform: uppercase;
			letter-spacing: 0.05em;
			margin-top: 4px;
		}

		.pausewp-countdown--expired {
			display: none;
		}

		/* CTA Buttons */
		.pausewp-buttons {
			display: flex;
			flex-wrap: wrap;
			gap: 12px;
			justify-content: center;
			margin-top: 28px;
			padding-top: 28px;
			border-top: 1px solid #eee;
		}

		.pausewp-btn {
			display: inline-flex;
			align-items: center;
			gap: 6px;
			padding: 10px 20px;
			background: #111;
			border-radius: 6px;
			color: #fff;
			text-decoration: none;
			font-size: 0.875rem;
			font-weight: 500;
			transition: background-color 0.15s ease, transform 0.15s ease;
		}

		.pausewp-btn:hover {
			background: #333;
			transform: translateY(-1px);
		}

		.pausewp-btn svg {
			width: 14px;
			height: 14px;
			opacity: 0.7;
		}

		/* Footer */
		.pausewp-footer {
			margin-top: 24px;
			font-size: 0.8125rem;
			color: #999;
		}

		/* Responsive */
		@media (max-width: 480px) {
			body {
				padding: 16px;
			}

			.pausewp-card {
				padding: 36px 24px;
			}

			.pausewp-heading {
				font-size: 1.25rem;
			}

			.pausewp-buttons {
				flex-direction: column;
			}

			.pausewp-btn {
				width: 100%;
				justify-content: center;
			}
		}
	</style>
</head>
<body>
	<main class="pausewp-container" role="main">
		<div class="pausewp-card">
			<?php if ( ! empty( $logo_url ) ) : ?>
				<div class="pausewp-logo">
					<img 
						src="<?php echo esc_url( $logo_url ); ?>" 
						alt="<?php echo esc_attr( $logo_alt ?: get_bloginfo( 'name' ) ); ?>"
					>
				</div>
			<?php else : ?>
				<!-- Default Icon when no logo -->
				<div class="pausewp-icon">
					<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
						<path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437l1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008z" />
					</svg>
				</div>
			<?php endif; ?>

			<h1 class="pausewp-heading"><?php echo esc_html( $heading ); ?></h1>

			<p class="pausewp-subheading">
				<?php echo wp_kses_post( $subheading ); ?>
			</p>

			<?php if ( $countdown_enabled && $countdown_timestamp > 0 ) : ?>
				<div id="pausewp-countdown" class="pausewp-countdown" data-target="<?php echo esc_attr( $countdown_timestamp ); ?>">
					<div class="pausewp-countdown__item">
						<span class="pausewp-countdown__value" id="countdown-days">00</span>
						<span class="pausewp-countdown__label"><?php esc_html_e( 'Days', 'pausewp' ); ?></span>
					</div>
					<div class="pausewp-countdown__item">
						<span class="pausewp-countdown__value" id="countdown-hours">00</span>
						<span class="pausewp-countdown__label"><?php esc_html_e( 'Hours', 'pausewp' ); ?></span>
					</div>
					<div class="pausewp-countdown__item">
						<span class="pausewp-countdown__value" id="countdown-minutes">00</span>
						<span class="pausewp-countdown__label"><?php esc_html_e( 'Minutes', 'pausewp' ); ?></span>
					</div>
					<div class="pausewp-countdown__item">
						<span class="pausewp-countdown__value" id="countdown-seconds">00</span>
						<span class="pausewp-countdown__label"><?php esc_html_e( 'Seconds', 'pausewp' ); ?></span>
					</div>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $cta_buttons ) && is_array( $cta_buttons ) ) : ?>
				<div class="pausewp-buttons">
					<?php foreach ( $cta_buttons as $button ) : ?>
						<?php if ( ! empty( $button['label'] ) && ! empty( $button['url'] ) ) : ?>
							<a 
								href="<?php echo esc_url( $button['url'] ); ?>" 
								class="pausewp-btn"
								target="_blank"
								rel="noopener noreferrer"
							>
								<?php echo esc_html( $button['label'] ); ?>
								<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
									<path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
								</svg>
							</a>
						<?php endif; ?>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>

		<p class="pausewp-footer">
			<?php echo esc_html( get_bloginfo( 'name' ) ); ?>
		</p>
	</main>

	<?php if ( $countdown_enabled && $countdown_timestamp > 0 ) : ?>
	<script>
	(function() {
		var countdown = document.getElementById('pausewp-countdown');
		if (!countdown) return;

		var target = parseInt(countdown.getAttribute('data-target'), 10);
		var days = document.getElementById('countdown-days');
		var hours = document.getElementById('countdown-hours');
		var minutes = document.getElementById('countdown-minutes');
		var seconds = document.getElementById('countdown-seconds');
		var hasReloaded = sessionStorage.getItem('pausewp_reloaded');

		function pad(n) {
			return n < 10 ? '0' + n : n;
		}

		function update() {
			var now = Date.now();
			var diff = target - now;

			if (diff <= 0) {
				countdown.classList.add('pausewp-countdown--expired');
				// Reload page once to show live site (prevent infinite loop)
				if (!hasReloaded) {
					sessionStorage.setItem('pausewp_reloaded', '1');
					setTimeout(function() {
						window.location.reload();
					}, 2000);
				}
				return;
			}

			var d = Math.floor(diff / (1000 * 60 * 60 * 24));
			var h = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
			var m = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
			var s = Math.floor((diff % (1000 * 60)) / 1000);

			days.textContent = pad(d);
			hours.textContent = pad(h);
			minutes.textContent = pad(m);
			seconds.textContent = pad(s);
		}

		update();
		setInterval(update, 1000);
	})();
	</script>
	<?php endif; ?>

	<?php
	// Auto-refresh when countdown is hidden but target time is set (silent mode)
	if ( ! $countdown_enabled && $countdown_timestamp > 0 ) :
		?>
		<script>
	(function() {
		var target = <?php echo esc_js( $countdown_timestamp ); ?>;
		var hasReloaded = sessionStorage.getItem('pausewp_reloaded');
		
		function checkTime() {
			var now = Date.now();
			if (now >= target && !hasReloaded) {
				sessionStorage.setItem('pausewp_reloaded', '1');
				setTimeout(function() {
					window.location.reload();
				}, 2000);
			}
		}
		
		// Check every 5 seconds
		setInterval(checkTime, 5000);
	})();
	</script>
	<?php endif; ?>
</body>
</html>
