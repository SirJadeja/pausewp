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
$heading          = $settings['heading'] ?? __( 'We\'ll Be Right Back', 'pausewp' );
$subheading       = $settings['subheading'] ?? __( 'Our site is currently undergoing scheduled maintenance.', 'pausewp' );
$logo_id          = $settings['logo_id'] ?? 0;
$logo_alt         = $settings['logo_alt'] ?? '';
$seo_title        = $settings['seo_title'] ?? __( 'Site Under Maintenance', 'pausewp' );
$meta_description = $settings['meta_description'] ?? '';
$cta_buttons      = $settings['cta_buttons'] ?? [];

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
		/* Reset & Base */
		*, *::before, *::after {
			box-sizing: border-box;
			margin: 0;
			padding: 0;
		}

		html {
			font-size: 16px;
			-webkit-font-smoothing: antialiased;
			-moz-osx-font-smoothing: grayscale;
		}

		body {
			font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
			background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f0f23 100%);
			color: #e4e4e7;
			min-height: 100vh;
			display: flex;
			align-items: center;
			justify-content: center;
			padding: 2rem;
			line-height: 1.6;
		}

		/* Container */
		.pausewp-maintenance {
			max-width: 600px;
			width: 100%;
			text-align: center;
			animation: fadeIn 0.6s ease-out;
		}

		@keyframes fadeIn {
			from {
				opacity: 0;
				transform: translateY(20px);
			}
			to {
				opacity: 1;
				transform: translateY(0);
			}
		}

		/* Logo */
		.pausewp-logo {
			margin-bottom: 2rem;
		}

		.pausewp-logo img {
			max-width: 180px;
			max-height: 80px;
			width: auto;
			height: auto;
			filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.3));
		}

		/* Heading */
		.pausewp-heading {
			font-size: clamp(1.75rem, 5vw, 2.5rem);
			font-weight: 700;
			color: #ffffff;
			margin-bottom: 1rem;
			letter-spacing: -0.02em;
		}

		/* Subheading */
		.pausewp-subheading {
			font-size: 1.125rem;
			color: #a1a1aa;
			margin-bottom: 2rem;
			max-width: 480px;
			margin-left: auto;
			margin-right: auto;
		}

		.pausewp-subheading a {
			color: #60a5fa;
			text-decoration: none;
			transition: color 0.2s ease;
		}

		.pausewp-subheading a:hover {
			color: #93c5fd;
			text-decoration: underline;
		}

		/* CTA Buttons */
		.pausewp-buttons {
			display: flex;
			flex-wrap: wrap;
			gap: 1rem;
			justify-content: center;
			margin-top: 1.5rem;
		}

		.pausewp-btn {
			display: inline-flex;
			align-items: center;
			gap: 0.5rem;
			padding: 0.75rem 1.5rem;
			background: rgba(255, 255, 255, 0.1);
			border: 1px solid rgba(255, 255, 255, 0.2);
			border-radius: 8px;
			color: #ffffff;
			text-decoration: none;
			font-size: 0.9375rem;
			font-weight: 500;
			transition: all 0.2s ease;
			backdrop-filter: blur(4px);
		}

		.pausewp-btn:hover {
			background: rgba(255, 255, 255, 0.15);
			border-color: rgba(255, 255, 255, 0.3);
			transform: translateY(-2px);
		}

		.pausewp-btn svg {
			width: 16px;
			height: 16px;
			flex-shrink: 0;
		}

		/* Responsive */
		@media (max-width: 480px) {
			body {
				padding: 1.5rem;
			}

			.pausewp-buttons {
				flex-direction: column;
				align-items: center;
			}

			.pausewp-btn {
				width: 100%;
				justify-content: center;
			}
		}
	</style>
</head>
<body>
	<main class="pausewp-maintenance" role="main">
		<?php if ( ! empty( $logo_url ) ) : ?>
			<div class="pausewp-logo">
				<img 
					src="<?php echo esc_url( $logo_url ); ?>" 
					alt="<?php echo esc_attr( $logo_alt ?: get_bloginfo( 'name' ) ); ?>"
				>
			</div>
		<?php endif; ?>

		<h1 class="pausewp-heading"><?php echo esc_html( $heading ); ?></h1>

		<div class="pausewp-subheading">
			<?php echo wp_kses_post( $subheading ); ?>
		</div>

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
							<!-- Arrow Icon (Inline SVG) -->
							<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
								<path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
							</svg>
						</a>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</main>
</body>
</html>
