<?php
/**
 * Plugin Name:       โหมดไว้อาลัย (Mourning Mode)
 * Plugin URI:        https://dokkooon.com
 * Description:       เปลี่ยนหน้าเว็บเป็นโทนสีขาวดำ พร้อมโบว์ดำมุมขวาบน สามารถเลือกแสดงโบว์/โทนขาวดำแยกกันได้ และปรับระดับความเข้มของสีได้
 * Version:           1.1.0
 * Requires at least: 5.0
 * Requires PHP:      7.4
 * Author:            Mr.Parich Suriya
 * Author URI:        https://github.com/parich
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       mourning-mode
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Add admin menu
 */
function mourning_mode_admin_menu()
{
	add_options_page(
		'ตั้งค่าโหมดไว้อาลัย',
		'โหมดไว้อาลัย',
		'manage_options',
		'mourning-mode-settings',
		'mourning_mode_settings_page'
	);
}
add_action('admin_menu', 'mourning_mode_admin_menu');

/**
 * Register settings
 */
function mourning_mode_register_settings()
{
	register_setting('mourning_mode_settings_group', 'mourning_mode_enabled');
	register_setting('mourning_mode_settings_group', 'mourning_mode_message');
	register_setting('mourning_mode_settings_group', 'mourning_mode_show_ribbon');
	register_setting('mourning_mode_settings_group', 'mourning_mode_ribbon_size', [
		'type' => 'integer',
		'default' => 150,
		'sanitize_callback' => function ($val) {
			$val = absint($val);
			return max(50, min(500, $val));
		},
	]);
	register_setting('mourning_mode_settings_group', 'mourning_mode_show_grayscale');
	register_setting('mourning_mode_settings_group', 'mourning_mode_grayscale_level', [
		'type' => 'integer',
		'default' => 100,
		'sanitize_callback' => function ($val) {
			$val = absint($val);
			return max(0, min(100, $val));
		},
	]);
}
add_action('admin_init', 'mourning_mode_register_settings');

/**
 * Settings page
 */
function mourning_mode_settings_page()
{
	$enabled = get_option('mourning_mode_enabled', false);
	$show_ribbon = get_option('mourning_mode_show_ribbon', true);
	$ribbon_size = get_option('mourning_mode_ribbon_size', 150);
	$show_grayscale = get_option('mourning_mode_show_grayscale', true);
	$grayscale_level = get_option('mourning_mode_grayscale_level', 100);
	?>
	<div class="wrap">
		<h1>ตั้งค่าโหมดไว้อาลัย</h1>
		<form method="post" action="options.php">
			<?php settings_fields('mourning_mode_settings_group'); ?>
			<?php do_settings_sections('mourning_mode_settings_group'); ?>
			<table class="form-table">
				<tr valign="top">
					<th scope="row">เปิดใช้งานโหมดไว้อาลัย</th>
					<td>
						<label>
							<input type="checkbox" name="mourning_mode_enabled" value="1" <?php checked($enabled, true); ?> />
							เปิดใช้งานโหมดไว้อาลัย
						</label>
						<p class="description">เมื่อเปิดใช้งาน จะแสดงผลตามตัวเลือกด้านล่าง</p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">แสดงโบว์ไว้อาลัย</th>
					<td>
						<label>
							<input type="checkbox" name="mourning_mode_show_ribbon" value="1" <?php checked($show_ribbon, true); ?> />
							แสดงรูปโบว์ดำมุมขวาบน
						</label>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">ขนาดรูปโบว์ (px)</th>
					<td>
						<input type="number" name="mourning_mode_ribbon_size" value="<?php echo esc_attr($ribbon_size); ?>"
							min="50" max="500" step="10" style="width: 100px;" />
						<p class="description">กำหนดขนาดรูปโบว์ไว้อาลัย (50-500 px, ค่าเริ่มต้น 150)</p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">แสดงโทนสีขาวดำ</th>
					<td>
						<label>
							<input type="checkbox" name="mourning_mode_show_grayscale" value="1"
								id="mourning_mode_show_grayscale" <?php checked($show_grayscale, true); ?> />
							เปลี่ยนหน้าเว็บเป็นโทนสีขาวดำ
						</label>
					</td>
				</tr>
				<tr valign="top" id="grayscale_level_row">
					<th scope="row">ระดับความเข้ม (%)</th>
					<td>
						<input type="range" name="mourning_mode_grayscale_level" id="mourning_mode_grayscale_level"
							value="<?php echo esc_attr($grayscale_level); ?>" min="0" max="100" step="5"
							style="width: 300px; vertical-align: middle;" />
						<span id="grayscale_level_value"
							style="font-weight: bold; margin-left: 10px;"><?php echo esc_html($grayscale_level); ?>%</span>
						<p class="description">0% = สีปกติ, 100% = ขาวดำทั้งหมด (ค่าเริ่มต้น 100%)</p>
					</td>
				</tr>
			</table>
			<?php submit_button(); ?>
		</form>
		<script>
			(function () {
				var slider = document.getElementById('mourning_mode_grayscale_level');
				var display = document.getElementById('grayscale_level_value');
				if (slider && display) {
					slider.addEventListener('input', function () {
						display.textContent = this.value + '%';
					});
				}
			})();
		</script>
	</div>
	<?php
}

/**
 * Apply mourning mode styles to frontend
 */
function mourning_mode_apply_styles()
{
	if (!get_option('mourning_mode_enabled', false)) {
		return;
	}

	$show_ribbon = get_option('mourning_mode_show_ribbon', true);
	$show_grayscale = get_option('mourning_mode_show_grayscale', true);
	$grayscale_level = absint(get_option('mourning_mode_grayscale_level', 100));
	$plugin_url = plugin_dir_url(__FILE__);
	$size = absint(get_option('mourning_mode_ribbon_size', 150));
	$size_tablet = round($size * 0.67);
	$size_mobile = round($size * 0.53);
	?>
	<style id="mourning-mode-styles">
		<?php if ($show_grayscale): ?>
			/* โหมดไว้อาลัย - เปลี่ยนทั้งหน้าเว็บเป็นโทนขาวดำ */
			html {
				filter: grayscale(<?php echo $grayscale_level; ?>%) !important;
				-webkit-filter: grayscale(<?php echo $grayscale_level; ?>%) !important;
			}

		<?php endif; ?>

		<?php if ($show_ribbon): ?>
			/* โบว์ไว้อาลัยมุมขวาบน */
			body::before {
				content: "";
				display: block;
				position: fixed;
				top: 0;
				right: 0;
				width:
					<?php echo $size; ?>
					px;
				height:
					<?php echo $size; ?>
					px;
				background-image: url('<?php echo esc_url($plugin_url . 'black_ribbon_top_right.png'); ?>');
				background-size: contain;
				background-repeat: no-repeat;
				background-position: top right;
				z-index: 999999;
				pointer-events: none;
			}

			/* สำหรับ admin bar */
			body.admin-bar::before {
				top: 32px;
			}

			/* Mobile responsive */
			@media screen and (max-width: 782px) {
				body.admin-bar::before {
					top: 46px;
				}

				body::before {
					width:
						<?php echo $size_tablet; ?>
						px;
					height:
						<?php echo $size_tablet; ?>
						px;
				}
			}

			@media screen and (max-width: 600px) {
				body::before {
					width:
						<?php echo $size_mobile; ?>
						px;
					height:
						<?php echo $size_mobile; ?>
						px;
				}
			}

		<?php endif; ?>
	</style>
	<?php
}
add_action('wp_head', 'mourning_mode_apply_styles', 1);

