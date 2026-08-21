<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WP_Spin_Wheel_Meta_Box {
    public function __construct() {
        add_action( 'add_meta_boxes', array( $this, 'register_meta_boxes' ) );
        add_action( 'save_post', array( $this, 'save_meta_boxes' ) );
    }

    public function register_meta_boxes() {
        add_meta_box(
            'spin_wheel_options_json',
            __( 'Cấu hình JSON Vòng quay (Wheel Options & Prizes JSON)', 'wp-spin-wheel' ),
            array( $this, 'render_json_meta_box' ),
            'spin_wheel',
            'normal',
            'high'
        );

        add_meta_box(
            'spin_box_options_json',
            __( 'Cấu hình JSON Hộp quà (Box Options & Gifts JSON)', 'wp-spin-wheel' ),
            array( $this, 'render_box_json_meta_box' ),
            'spin_box',
            'normal',
            'high'
        );
    }

    public function render_json_meta_box( $post ) {
        wp_nonce_field( 'spin_wheel_save', 'spin_wheel_nonce' );

        $author = get_userdata( $post->post_author );
        $author_name = $author ? ( $author->display_name ?: $author->user_login ) . ' (ID: ' . $author->ID . ')' : 'Guest / System';

        // Lấy settings và prizes hiện tại
        $settings = WP_Spin_Wheel_Helper::get_wheel_overrides( $post->ID );
        if ( empty( $settings ) || ! is_array( $settings ) ) {
            $settings = WP_Spin_Wheel_Helper::get_wheel_settings( $post->ID );
        }
        $prizes = WP_Spin_Wheel_Prize::get_prizes( $post->ID );
        if ( empty( $prizes ) || ! is_array( $prizes ) ) {
            $prizes = array(
                array( 'title' => 'Giải 1', 'color' => '#f87171', 'weight' => 10, 'stock' => 9999 ),
                array( 'title' => 'Giải 2', 'color' => '#60a5fa', 'weight' => 10, 'stock' => 9999 ),
                array( 'title' => 'Giải 3', 'color' => '#34d399', 'weight' => 10, 'stock' => 9999 ),
                array( 'title' => 'Giải 4', 'color' => '#fbbf24', 'weight' => 10, 'stock' => 9999 ),
                array( 'title' => 'Giải 5', 'color' => '#a78bfa', 'weight' => 10, 'stock' => 9999 ),
                array( 'title' => 'Giải 6', 'color' => '#f472b6', 'weight' => 10, 'stock' => 9999 ),
            );
        }

        $wheel_data = array(
            'settings' => $settings,
            'prizes'   => $prizes,
        );

        $json_content = wp_json_encode( $wheel_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
        ?>
        <div style="margin-bottom: 15px; padding: 10px 14px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; display: flex; flex-wrap: wrap; gap: 20px; align-items: center; justify-content: space-between;">
            <div>
                <strong>Shortcode:</strong> <code>[spin_wheel id="<?php echo esc_attr( $post->ID ); ?>"]</code>
            </div>
            <div>
                <strong>User sở hữu:</strong> <span style="color: #0284c7; font-weight: 600;"><?php echo esc_html( $author_name ); ?></span>
            </div>
            <div>
                <button type="button" class="button button-secondary" id="btn_format_wheel_json">✨ Định dạng JSON</button>
                <button type="button" class="button button-secondary" id="btn_validate_wheel_json">🔍 Kiểm tra cú pháp</button>
            </div>
        </div>

        <div id="wheel_json_status" style="display: none; margin-bottom: 10px; padding: 8px 12px; border-radius: 4px;"></div>

        <p style="margin: 0 0 6px 0; color: #64748b; font-size: 13px;">
            Toàn bộ cài đặt giao diện, âm thanh, hiệu ứng và danh sách giải thưởng được lưu trữ gọn trong bảng JSON dưới đây:
        </p>

        <textarea id="spin_wheel_options_json" name="spin_wheel_options_json" rows="22" style="width: 100%; font-family: 'Consolas', 'Monaco', 'Courier New', monospace; font-size: 13px; line-height: 1.5; background: #1e293b; color: #f8fafc; padding: 14px; border-radius: 6px; box-shadow: inset 0 1px 3px rgba(0,0,0,0.2); border: 1px solid #334155;"><?php echo esc_textarea( $json_content ); ?></textarea>

        <script>
        (function() {
            var textarea = document.getElementById('spin_wheel_options_json');
            var status = document.getElementById('wheel_json_status');
            var btnFormat = document.getElementById('btn_format_wheel_json');
            var btnValidate = document.getElementById('btn_validate_wheel_json');

            if (btnFormat && textarea) {
                btnFormat.addEventListener('click', function() {
                    try {
                        var parsed = JSON.parse(textarea.value);
                        textarea.value = JSON.stringify(parsed, null, 2);
                        showStatus('Đã định dạng JSON thành công!', '#10b981', '#ecfdf5', '#a7f3d0');
                    } catch (e) {
                        showStatus('Lỗi cú pháp JSON: ' + e.message, '#ef4444', '#fef2f2', '#fecaca');
                    }
                });
            }

            if (btnValidate && textarea) {
                btnValidate.addEventListener('click', function() {
                    try {
                        JSON.parse(textarea.value);
                        showStatus('Cú pháp JSON hợp lệ 100%!', '#10b981', '#ecfdf5', '#a7f3d0');
                    } catch (e) {
                        showStatus('Lỗi cú pháp JSON: ' + e.message, '#ef4444', '#fef2f2', '#fecaca');
                    }
                });
            }

            function showStatus(msg, color, bg, border) {
                if (!status) return;
                status.style.display = 'block';
                status.style.color = color;
                status.style.backgroundColor = bg;
                status.style.border = '1px solid ' + border;
                status.textContent = msg;
                setTimeout(function() {
                    status.style.display = 'none';
                }, 4000);
            }
        })();
        </script>
        <?php
    }

    public function save_meta_boxes( $post_id ) {
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( empty( $_POST['spin_wheel_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['spin_wheel_nonce'] ), 'spin_wheel_save' ) ) {
            return;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        if ( isset( $_POST['spin_wheel_options_json'] ) ) {
            $raw_json = wp_unslash( $_POST['spin_wheel_options_json'] );
            $data = json_decode( $raw_json, true );

            if ( is_array( $data ) ) {
                // Tách settings
                $settings = isset( $data['settings'] ) && is_array( $data['settings'] ) ? $data['settings'] : $data;
                update_post_meta( $post_id, '_spin_wheel_overrides', wp_json_encode( $settings ) );
                update_post_meta( $post_id, '_spin_wheel_design', wp_json_encode( $settings ) );

                // Tách prizes và đồng bộ vào DB
                if ( isset( $data['prizes'] ) && is_array( $data['prizes'] ) ) {
                    $this->sync_prizes_to_db( $post_id, $data['prizes'] );
                    update_post_meta( $post_id, '_spin_wheel_prizes_json', wp_json_encode( $data['prizes'] ) );
                }
            }
        }

        if ( isset( $_POST['spin_box_options_json'] ) ) {
            $raw_json = wp_unslash( $_POST['spin_box_options_json'] );
            $data = json_decode( $raw_json, true );

            if ( is_array( $data ) ) {
                $settings = isset( $data['settings'] ) && is_array( $data['settings'] ) ? $data['settings'] : array();
                $gifts    = isset( $data['gifts'] ) && is_array( $data['gifts'] ) ? $data['gifts'] : ( isset( $data['prizes'] ) && is_array( $data['prizes'] ) ? $data['prizes'] : array() );

                update_post_meta( $post_id, '_spin_box_overrides', wp_json_encode( $settings ) );
                update_post_meta( $post_id, '_spin_box_design', wp_json_encode( $settings ) );
                update_post_meta( $post_id, '_spin_box_gifts_json', wp_json_encode( $gifts ) );

                if ( ! empty( $gifts ) ) {
                    $prizes_formatted = array_map( function( $g ) {
                        $t = is_array( $g ) ? ( $g['title'] ?? '' ) : $g;
                        return array( 'title' => $t, 'color' => '#dc3545', 'weight' => 10, 'stock' => 9999 );
                    }, $gifts );
                    $this->sync_prizes_to_db( $post_id, $prizes_formatted );
                }
            }
        }
    }

    public function render_box_json_meta_box( $post ) {
        wp_nonce_field( 'spin_wheel_save', 'spin_wheel_nonce' );

        $author = get_userdata( $post->post_author );
        $author_name = $author ? ( $author->display_name ?: $author->user_login ) . ' (ID: ' . $author->ID . ')' : 'Guest / System';

        $settings = WP_Spin_Wheel_Box::get_box_settings( $post->ID );
        $gifts    = WP_Spin_Wheel_Box::get_box_gifts( $post->ID );

        $box_data = array(
            'settings' => $settings,
            'gifts'    => $gifts,
        );

        $json_content = wp_json_encode( $box_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
        ?>
        <div style="margin-bottom: 15px; padding: 10px 14px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; display: flex; flex-wrap: wrap; gap: 20px; align-items: center; justify-content: space-between;">
            <div>
                <strong>Shortcode:</strong> <code>[lucky_box id="<?php echo esc_attr( $post->ID ); ?>"]</code>
            </div>
            <div>
                <strong>User sở hữu:</strong> <span style="color: #0284c7; font-weight: 600;"><?php echo esc_html( $author_name ); ?></span>
            </div>
            <div>
                <button type="button" class="button button-secondary" id="btn_format_box_json">✨ Định dạng JSON</button>
                <button type="button" class="button button-secondary" id="btn_validate_box_json">🔍 Kiểm tra cú pháp</button>
            </div>
        </div>

        <div id="box_json_status" style="display: none; margin-bottom: 10px; padding: 8px 12px; border-radius: 4px;"></div>

        <p style="margin: 0 0 6px 0; color: #64748b; font-size: 13px;">
            Toàn bộ cài đặt giao diện, âm thanh, chủ đề mẫu và danh sách quà tặng của Hộp quà được lưu trữ trong bảng JSON dưới đây:
        </p>

        <textarea id="spin_box_options_json" name="spin_box_options_json" rows="22" style="width: 100%; font-family: 'Consolas', 'Monaco', 'Courier New', monospace; font-size: 13px; line-height: 1.5; background: #1e293b; color: #f8fafc; padding: 14px; border-radius: 6px; box-shadow: inset 0 1px 3px rgba(0,0,0,0.2); border: 1px solid #334155;"><?php echo esc_textarea( $json_content ); ?></textarea>

        <script>
        (function() {
            var textarea = document.getElementById('spin_box_options_json');
            var status = document.getElementById('box_json_status');
            var btnFormat = document.getElementById('btn_format_box_json');
            var btnValidate = document.getElementById('btn_validate_box_json');

            if (btnFormat && textarea) {
                btnFormat.addEventListener('click', function() {
                    try {
                        var parsed = JSON.parse(textarea.value);
                        textarea.value = JSON.stringify(parsed, null, 2);
                        showStatus('Đã định dạng JSON thành công!', '#10b981', '#ecfdf5', '#a7f3d0');
                    } catch (e) {
                        showStatus('Lỗi cú pháp JSON: ' + e.message, '#ef4444', '#fef2f2', '#fecaca');
                    }
                });
            }

            if (btnValidate && textarea) {
                btnValidate.addEventListener('click', function() {
                    try {
                        JSON.parse(textarea.value);
                        showStatus('Cú pháp JSON hợp lệ 100%!', '#10b981', '#ecfdf5', '#a7f3d0');
                    } catch (e) {
                        showStatus('Lỗi cú pháp JSON: ' + e.message, '#ef4444', '#fef2f2', '#fecaca');
                    }
                });
            }

            function showStatus(msg, color, bg, border) {
                if (!status) return;
                status.style.display = 'block';
                status.style.color = color;
                status.style.backgroundColor = bg;
                status.style.border = '1px solid ' + border;
                status.textContent = msg;
                setTimeout(function() {
                    status.style.display = 'none';
                }, 4000);
            }
        })();
        </script>
        <?php
    }

    private function sync_prizes_to_db( $post_id, $prizes ) {
        global $wpdb;
        $table = $wpdb->prefix . 'spin_prizes';
        $post_id = absint( $post_id );

        $wpdb->delete( $table, array( 'wheel_id' => $post_id ), array( '%d' ) );
        $sort = 0;
        foreach ( $prizes as $prize ) {
            $title = sanitize_text_field( $prize['title'] ?? ( $prize['name'] ?? '' ) );
            if ( empty( $title ) ) {
                continue;
            }
            $wpdb->insert(
                $table,
                array(
                    'wheel_id'      => $post_id,
                    'title'         => $title,
                    'color'         => sanitize_text_field( $prize['color'] ?? '#3b82f6' ),
                    'weight'        => max( 1, intval( $prize['weight'] ?? 10 ) ),
                    'image'         => esc_url_raw( $prize['image'] ?? '' ),
                    'icon'          => esc_url_raw( $prize['icon'] ?? '' ),
                    'stock'         => max( 0, intval( $prize['stock'] ?? 9999 ) ),
                    'initial_stock' => max( 0, intval( $prize['initial_stock'] ?? ( $prize['stock'] ?? 9999 ) ) ),
                    'status'        => sanitize_text_field( $prize['status'] ?? 'active' ),
                    'sort_order'    => $sort++,
                    'description'   => sanitize_textarea_field( $prize['description'] ?? '' ),
                    'created_at'    => current_time( 'mysql' ),
                ),
                array( '%d', '%s', '%s', '%d', '%s', '%s', '%d', '%d', '%s', '%d', '%s', '%s' )
            );
        }
    }
}
