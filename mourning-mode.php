<?php
/**
 * Plugin Name:       โหมดไว้อาลัย (Mourning Mode)
 * Plugin URI:        https://dokkooon.com
 * Description:       เปลี่ยนหน้าเว็บเป็นโทนสีขาวดำ พร้อมโบว์ดำมุมขวาบน สามารถเลือกแสดงโบว์/โทนขาวดำแยกกันได้ และปรับระดับความเข้มของสีได้
 * Version:           1.2.0
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
	register_setting('mourning_mode_settings_group', 'mourning_mode_enabled', [
		'sanitize_callback' => function ($val) {
			return $val ? 1 : 0;
		},
	]);
	register_setting('mourning_mode_settings_group', 'mourning_mode_show_ribbon', [
		'sanitize_callback' => function ($val) {
			return $val ? 1 : 0;
		},
	]);
	register_setting('mourning_mode_settings_group', 'mourning_mode_ribbon_size', [
		'type' => 'integer',
		'default' => 150,
		'sanitize_callback' => function ($val) {
			$val = absint($val);
			return max(50, min(500, $val));
		},
	]);
	register_setting('mourning_mode_settings_group', 'mourning_mode_show_grayscale', [
		'sanitize_callback' => function ($val) {
			return $val ? 1 : 0;
		},
	]);
	register_setting('mourning_mode_settings_group', 'mourning_mode_grayscale_level', [
		'type' => 'integer',
		'default' => 100,
		'sanitize_callback' => function ($val) {
			$val = absint($val);
			return max(0, min(100, $val));
		},
	]);
	register_setting('mourning_mode_settings_group', 'mourning_mode_show_message', [
		'sanitize_callback' => function ($val) {
			return $val ? 1 : 0;
		},
	]);
	register_setting('mourning_mode_settings_group', 'mourning_mode_message', [
		'default' => 'น้อมรำลึกในพระมหากรุณาธิคุณอันหาที่สุดมิได้',
		'sanitize_callback' => 'sanitize_text_field',
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
	$show_message = get_option('mourning_mode_show_message', false);
	$message = get_option('mourning_mode_message', 'น้อมรำลึกในพระมหากรุณาธิคุณอันหาที่สุดมิได้');
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
				<tr valign="top">
					<th scope="row">แสดงข้อความไว้อาลัย</th>
					<td>
						<label>
							<input type="checkbox" name="mourning_mode_show_message" value="1" <?php checked($show_message, true); ?> />
							แสดงแถบข้อความด้านบนของหน้าเว็บ
						</label>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">ข้อความ</th>
					<td>
						<input type="text" name="mourning_mode_message" value="<?php echo esc_attr($message); ?>"
							class="large-text" />
						<p class="description">ข้อความที่จะแสดงในแถบด้านบนของหน้าเว็บ</p>
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

	// Build CSS string to avoid linter breaking "100px" into "100 px"
	$css = '';

	$show_message = get_option('mourning_mode_show_message', false);

	if ($show_grayscale) {
		$css .= "html { filter: grayscale({$grayscale_level}%) !important; -webkit-filter: grayscale({$grayscale_level}%) !important; }\n";
	}

	if ($show_message) {
		$css .= "#mourning-mode-banner { position: fixed; top: 0; left: 0; width: 100%; background: #000; color: #fff; text-align: center; padding: 10px 15px; font-size: 14px; z-index: 999998; box-sizing: border-box; }\n";
		$css .= "body { margin-top: 38px !important; }\n";
		$css .= "body.admin-bar #mourning-mode-banner { top: 32px; }\n";
		$css .= "body.admin-bar { margin-top: 38px !important; }\n";
		$css .= ".site-header.affix-top, .site-header.sticky-header, header.fixed-top, .sticky-header { top: 38px !important; }\n";
		$css .= "body.admin-bar .site-header.affix-top, body.admin-bar .site-header.sticky-header, body.admin-bar header.fixed-top, body.admin-bar .sticky-header { top: 70px !important; }\n";
		$css .= "@media screen and (max-width: 782px) { body.admin-bar #mourning-mode-banner { top: 46px; } .site-header.affix-top, .site-header.sticky-header { top: 38px !important; } body.admin-bar .site-header.affix-top, body.admin-bar .site-header.sticky-header { top: 84px !important; } }\n";
	}

	if ($show_ribbon) {
		$ribbon_url = esc_url($plugin_url . 'black_ribbon_top_right.png');
		$css .= "body::before { content: \"\"; display: block; position: fixed; top: 0; right: 0; width: {$size}px; height: {$size}px; background-image: url('{$ribbon_url}'); background-size: contain; background-repeat: no-repeat; background-position: top right; z-index: 999999; pointer-events: none; }\n";
		$css .= "body.admin-bar::before { top: 32px; }\n";
		$css .= "@media screen and (max-width: 782px) { body.admin-bar::before { top: 46px; } body::before { width: {$size_tablet}px; height: {$size_tablet}px; } }\n";
		$css .= "@media screen and (max-width: 600px) { body::before { width: {$size_mobile}px; height: {$size_mobile}px; } }\n";
	}

	if ($css) {
		echo '<style id="mourning-mode-styles">' . "\n" . $css . '</style>' . "\n";
	}
}
add_action('wp_head', 'mourning_mode_apply_styles', 1);

/**
 * Display mourning message banner
 */
function mourning_mode_display_message()
{
	if (!get_option('mourning_mode_enabled', false)) {
		return;
	}
	if (!get_option('mourning_mode_show_message', false)) {
		return;
	}

	$message = get_option('mourning_mode_message', 'น้อมรำลึกในพระมหากรุณาธิคุณอันหาที่สุดมิได้');
	if (empty($message)) {
		return;
	}

	echo '<div id="mourning-mode-banner">' . esc_html($message) . '</div>' . "\n";
}
add_action('wp_footer', 'mourning_mode_display_message', 1);

/**
 * GitHub Update Checker
 */
class Mourning_Mode_GitHub_Updater
{
	private $slug = 'mourning-mode';
	private $plugin_file;
	private $plugin_basename;
	private $github_owner = 'parich';
	private $github_repo = 'mourning-mode';
	private $current_version;
	private $github_response;
	private $cache_key = 'mourning_mode_github_update';
	private $cache_expiry = 21600; // 6 hours

	public function __construct($plugin_file)
	{
		$this->plugin_file = $plugin_file;
		$this->plugin_basename = plugin_basename($plugin_file);

		$plugin_data = get_file_data($plugin_file, ['Version' => 'Version']);
		$this->current_version = $plugin_data['Version'];

		add_filter('pre_set_site_transient_update_plugins', [$this, 'check_update']);
		add_filter('plugins_api', [$this, 'plugin_info'], 20, 3);
		add_filter('upgrader_post_install', [$this, 'after_install'], 10, 3);
	}

	private function get_github_release()
	{
		if ($this->github_response !== null) {
			return $this->github_response;
		}

		$cached = get_transient($this->cache_key);
		if ($cached !== false) {
			$this->github_response = $cached;
			return $cached;
		}

		$url = "https://api.github.com/repos/{$this->github_owner}/{$this->github_repo}/releases/latest";
		$response = wp_remote_get($url, [
			'headers' => [
				'Accept' => 'application/vnd.github.v3+json',
				'User-Agent' => 'WordPress/' . get_bloginfo('version'),
			],
		]);

		if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
			$this->github_response = false;
			return false;
		}

		$body = json_decode(wp_remote_retrieve_body($response));
		if (empty($body) || !isset($body->tag_name)) {
			$this->github_response = false;
			return false;
		}

		$this->github_response = $body;
		set_transient($this->cache_key, $body, $this->cache_expiry);

		return $body;
	}

	public function check_update($transient)
	{
		if (empty($transient->checked)) {
			return $transient;
		}

		$release = $this->get_github_release();
		if (!$release) {
			return $transient;
		}

		$remote_version = ltrim($release->tag_name, 'v');

		if (version_compare($remote_version, $this->current_version, '>')) {
			$download_url = $release->zipball_url;

			// Use uploaded zip asset if available
			if (!empty($release->assets)) {
				foreach ($release->assets as $asset) {
					if (substr($asset->name, -4) === '.zip') {
						$download_url = $asset->browser_download_url;
						break;
					}
				}
			}

			$transient->response[$this->plugin_basename] = (object) [
				'slug' => $this->slug,
				'plugin' => $this->plugin_basename,
				'new_version' => $remote_version,
				'url' => $release->html_url,
				'package' => $download_url,
			];
		}

		return $transient;
	}

	public function plugin_info($result, $action, $args)
	{
		if ($action !== 'plugin_information' || $args->slug !== $this->slug) {
			return $result;
		}

		$release = $this->get_github_release();
		if (!$release) {
			return $result;
		}

		$remote_version = ltrim($release->tag_name, 'v');

		$download_url = $release->zipball_url;
		if (!empty($release->assets)) {
			foreach ($release->assets as $asset) {
				if (substr($asset->name, -4) === '.zip') {
					$download_url = $asset->browser_download_url;
					break;
				}
			}
		}

		return (object) [
			'name' => 'โหมดไว้อาลัย (Mourning Mode)',
			'slug' => $this->slug,
			'version' => $remote_version,
			'author' => '<a href="https://github.com/parich">Mr.Parich Suriya</a>',
			'homepage' => "https://github.com/{$this->github_owner}/{$this->github_repo}",
			'requires' => '5.0',
			'requires_php' => '7.4',
			'sections' => [
				'description' => 'เปลี่ยนหน้าเว็บเป็นโทนสีขาวดำ พร้อมโบว์ดำมุมขวาบน',
				'changelog' => nl2br(esc_html($release->body ?? '')),
			],
			'download_link' => $download_url,
		];
	}

	public function after_install($response, $hook_extra, $result)
	{
		if (!isset($hook_extra['plugin']) || $hook_extra['plugin'] !== $this->plugin_basename) {
			return $result;
		}

		global $wp_filesystem;

		$install_dir = plugin_dir_path($this->plugin_file);

		// Remove old plugin folder before moving new one
		if ($wp_filesystem->exists($install_dir)) {
			$wp_filesystem->delete($install_dir, true);
		}

		$wp_filesystem->move($result['destination'], $install_dir);
		$result['destination'] = $install_dir;

		activate_plugin($this->plugin_basename);

		return $result;
	}
}

new Mourning_Mode_GitHub_Updater(__FILE__);

