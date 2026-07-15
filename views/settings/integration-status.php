<?php
/**
 * Admin View: Section - WP Safelink Integration Status
 *
 * @package WP Safelink
 * @since 5.2.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get current settings
$options = wpsafelink_options();
$current_template = $options['template'] ?? 'template1';
// For fresh installations, auto integration defaults to enabled
// For existing installations, preserve their current setting
$is_fresh_install = empty($options) || (count($options) <= 5 && isset($options['auto_integration_enable']));
$auto_integration_enabled = ($options['auto_integration_enable'] ?? ($is_fresh_install ? 'yes' : 'no')) === 'yes';

// Templates that require integration
$integration_templates = array( 'template2', 'template3' );
$integration_required = in_array( $current_template, $integration_templates );

// Get theme info
$theme = wp_get_theme();
$theme_root = get_theme_root();
$current_theme = get_stylesheet();
?>
<div id="wpsafelink-integration-status" class="ultra-thin-integration">
	<?php if ( ! $integration_required ): ?>
	<!-- No integration needed -->
	<div class="integration-info-box" style="background: #e6f7ff; border: 1px solid #91d5ff; padding: 12px; border-radius: 4px;">
		<div style="display: flex; align-items: center;">
			<span class="dashicons dashicons-info-outline" style="color: #1ABC9C; margin-right: 10px; font-size: 20px;"></span>
			<div>
				<div style="font-size: 13px; color: #003d82; font-weight: 600;">
					Perfect choice! Template <?php echo ucfirst( str_replace( 'template', '', $current_template ) ); ?> works independently
				</div>
				<div style="font-size: 11px; color: #0050b3; margin-top: 2px;">
					No theme modifications required - your safelinks are ready to use
				</div>
			</div>
		</div>
	</div>

	<?php else: ?>
	<!-- Integration required for template2/template3 -->
	<div class="integration-container">

		<?php if ( $auto_integration_enabled ): ?>
		<!-- Auto Integration Enabled -->
		<div class="integration-success-banner" style="background: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px; padding: 12px; margin-bottom: 10px;">
			<div style="display: flex; align-items: center;">
				<span class="dashicons dashicons-yes-alt" style="color: #155724; margin-right: 10px; font-size: 20px;"></span>
				<div>
					<div style="font-size: 14px; color: #155724; font-weight: 600;">
						Auto Integration Active
					</div>
					<div style="font-size: 12px; color: #155724; margin-top: 2px;">
						WP Safelink functions are automatically integrated into your theme. No manual editing required!
					</div>
				</div>
			</div>
		</div>

		<!-- Auto Integration Settings Summary -->
		<div class="integration-cards" style="display: flex; gap: 8px; margin-bottom: 10px;">
			<!-- Current Settings Card -->
			<div class="integration-card" style="flex: 1; background: #fff; border: 1px solid #d4edda; border-radius: 4px; padding: 10px;">
				<div style="display: flex; align-items: center; margin-bottom: 8px;">
					<span class="dashicons dashicons-admin-settings" style="color: #28a745; margin-right: 6px; font-size: 14px;"></span>
					<span style="font-size: 12px; font-weight: 600; color: #333;">Current Configuration</span>
				</div>
				<div style="font-size: 11px; color: #666; line-height: 1.5;">
					<div style="margin-bottom: 4px;">
						<strong>Top Section:</strong> <?php
						$top_placement = $options['auto_integration_top_placement'] ?? 'after_title';
						$top_labels = [
							'wp_body_open' => 'After Body Tag',
							'before_title' => 'Before Post Title',
							'after_title' => 'After Post Title',
							'content_start' => 'Start of Content'
						];
						echo $top_labels[$top_placement] ?? $top_placement;
						?>
					</div>
					<div>
						<strong>Bottom Section:</strong> <?php
						$bottom_placement = $options['auto_integration_bottom_placement'] ?? 'content_end';
						$bottom_labels = [
							'wp_footer' => 'Footer',
							'content_end' => 'End of Content',
							'after_content' => 'After Content'
						];
						echo $bottom_labels[$bottom_placement] ?? $bottom_placement;
						?>
					</div>
				</div>
			</div>

			<!-- Go to Settings Card -->
			<div class="integration-card" style="flex: 1; background: #fff; border: 1px solid #e5e7eb; border-radius: 4px; padding: 10px;">
				<div style="display: flex; align-items: center; margin-bottom: 8px;">
					<span class="dashicons dashicons-admin-generic" style="color: #1ABC9C; margin-right: 6px; font-size: 14px;"></span>
					<span style="font-size: 12px; font-weight: 600; color: #333;">Manage Integration</span>
				</div>
				<div style="font-size: 11px; color: #666; margin-bottom: 8px;">
					Adjust placement settings or disable Auto Integration
				</div>
				<a href="<?php echo admin_url('admin.php?page=wpsafelink&tab=templates#auto_integration_enable'); ?>" class="button button-small" style="width: 100%; text-align: center; font-size: 11px;">
					⚙️ Configure Settings
				</a>
			</div>
		</div>

		<?php else: ?>
		<!-- Auto Integration Disabled -->
		<div class="integration-info-box" style="background: #fff4e5; border: 1px solid #ffb74d; padding: 12px; border-radius: 4px; margin-bottom: 10px;">
			<div style="display: flex; align-items: center;">
				<span class="dashicons dashicons-warning" style="color: #ff9800; margin-right: 10px; font-size: 20px;"></span>
				<div>
					<div style="font-size: 14px; color: #e65100; font-weight: 600;">
						Integration Required for Template <?php echo ucfirst( str_replace( 'template', '', $current_template ) ); ?>
					</div>
					<div style="font-size: 12px; color: #bf360c; margin-top: 2px;">
						Enable Auto Integration for seamless setup - no manual theme editing needed!
					</div>
				</div>
			</div>
		</div>

		<!-- Enable Auto Integration Card -->
		<div class="integration-card" style="background: #fff; border: 2px solid #1ABC9C; border-radius: 4px; padding: 15px;">
			<div style="display: flex; align-items: center; justify-content: space-between;">
				<div>
					<div style="display: flex; align-items: center; margin-bottom: 8px;">
						<span class="dashicons dashicons-admin-plugins" style="color: #1ABC9C; margin-right: 8px; font-size: 18px;"></span>
						<span style="font-size: 14px; font-weight: 600; color: #333;">Enable Auto Integration (Recommended)</span>
					</div>
					<div style="font-size: 12px; color: #666; margin-bottom: 12px;">
						Automatically integrate WP Safelink functions into your theme without editing any files.
					</div>
					<a href="<?php echo admin_url('admin.php?page=wpsafelink&tab=templates#auto_integration_enable'); ?>" class="button button-primary" style="font-size: 12px;">
						🚀 Enable Auto Integration
					</a>
				</div>
			</div>
		</div>

		<!-- Manual Integration Alternative -->
		<details style="margin-top: 15px;">
			<summary style="cursor: pointer; font-size: 12px; color: #666; padding: 8px; background: #f8f9fa; border-radius: 4px;">
				Advanced: Manual Integration Instructions
			</summary>
			<div style="padding: 12px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 0 0 4px 4px; margin-top: -1px;">
				<p style="font-size: 11px; color: #666; margin-bottom: 10px;">
					If you prefer manual integration, add these functions to your theme:
				</p>
				<div style="margin-bottom: 10px;">
					<strong style="font-size: 11px;">In header.php (before &lt;/head&gt;):</strong>
					<pre style="background: #fff; padding: 8px; border: 1px solid #dee2e6; border-radius: 3px; font-size: 11px; margin-top: 4px;">&lt;?php if(function_exists('newwpsafelink_top')) newwpsafelink_top();?&gt;</pre>
				</div>
				<div>
					<strong style="font-size: 11px;">In footer.php (before &lt;/body&gt;):</strong>
					<pre style="background: #fff; padding: 8px; border: 1px solid #dee2e6; border-radius: 3px; font-size: 11px; margin-top: 4px;">&lt;?php if(function_exists('newwpsafelink_bottom')) newwpsafelink_bottom();?&gt;</pre>
				</div>
			</div>
		</details>
		<?php endif; ?>
	</div>
	<?php endif; ?>
</div>

<style>
/* Ultra-thin integration styles */
.ultra-thin-integration {
	border-top: 1px solid #e5e5e5;
	margin-top: 0;
}

.integration-cards {
	display: flex;
	gap: 8px;
	flex-direction: column;
}

.integration-card {
	transition: all 0.2s ease;
}

.integration-card:hover {
	box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.show-integration-details {
	transition: all 0.2s ease;
}

.show-integration-details:hover {
	background: #16A085 !important;
	transform: translateY(-1px);
}

.copy-btn {
	background: #1ABC9C;
	color: #fff;
	border: none;
	padding: 4px 8px;
	border-radius: 3px;
	cursor: pointer;
	font-size: 11px;
	transition: all 0.2s ease;
}

.copy-btn:hover {
	background: #16A085;
}

.copy-btn.copied {
	background: #28a745;
}

.code-snippet {
	background: #f8f9fa;
	padding: 8px;
	border: 1px solid #dee2e6;
	border-radius: 4px;
	margin: 8px 0;
	font-family: monospace;
	font-size: 11px;
	position: relative;
	cursor: pointer;
	transition: all 0.2s ease;
}

.code-snippet:hover {
	background: #e9ecef;
	border-color: #adb5bd;
}

@media (max-width: 600px) {
	.integration-cards {
		flex-direction: column;
	}
}
</style>

<script>
jQuery(document).ready(function($) {
	// Copy code functionality for manual integration instructions
	$(document).on('click', 'pre', function() {
		const code = $(this).text();
		const $pre = $(this);

		// Copy to clipboard
		if (navigator.clipboard) {
			navigator.clipboard.writeText(code).then(function() {
				const originalBg = $pre.css('background');
				$pre.css('background', '#d4edda');
				setTimeout(() => $pre.css('background', originalBg), 1500);
			});
		} else {
			const $temp = $('<textarea>');
			$('body').append($temp);
			$temp.val(code).select();
			document.execCommand('copy');
			$temp.remove();
			const originalBg = $pre.css('background');
			$pre.css('background', '#d4edda');
			setTimeout(() => $pre.css('background', originalBg), 1500);
		}

		// Show tooltip
		const tooltip = $('<div style="position: absolute; background: #333; color: #fff; padding: 4px 8px; border-radius: 3px; font-size: 11px; z-index: 9999;">Copied!</div>');
		$('body').append(tooltip);
		const offset = $pre.offset();
		tooltip.css({
			top: offset.top - 30,
			left: offset.left + ($pre.width() / 2) - (tooltip.width() / 2)
		}).fadeIn(200);

		setTimeout(() => {
			tooltip.fadeOut(200, function() {
				$(this).remove();
			});
		}, 1000);
	});

	// Add hover effect to pre elements
	$('pre').attr('title', 'Click to copy');
});
</script>