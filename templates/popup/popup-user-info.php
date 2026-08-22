<?php if (! defined('ABSPATH')) {
    exit;
}
$current_user = is_user_logged_in() ? wp_get_current_user() : null;
$user_id      = $current_user ? $current_user->ID : 0;
$user_login   = $current_user ? $current_user->user_login : '';
$display_name = $current_user ? $current_user->display_name : '';
$user_email   = $current_user ? $current_user->user_email : '';
$reflink      = $current_user ? home_url( '/?ref=' . $user_id ) : home_url('/');
?>
<div class="modal" id="modalUserInfo" tabindex="-1" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" id="modal-dialog">
        <div class="modal-content" id="modal-content">
            <div class="modal-header" id="modal-header">
                <h5 class="modal-title" id="modal-title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-user" style="vertical-align:middle;margin-right:4px;">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg> Hồ sơ tài khoản
                </h5>
                <button type="button" id="modal-close" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-3" id="modal-body">
                <div class="mb-3"><strong>Thông tin tài khoản (ID: <span id="userid"><?php echo esc_html( $user_id ); ?></span>)</strong></div>
                <div class="input-group mb-3">
                    <span class="input-group-text">Tên đăng nhập</span>
                    <input type="text" class="form-control bg-light" placeholder="Tên đăng nhập" id="username" value="<?php echo esc_attr( $user_login ); ?>" readonly="">
                </div>
                <div class="input-group mb-3">
                    <span class="input-group-text">Tên hiển thị</span>
                    <input type="text" class="form-control" placeholder="Tên hiển thị" id="displayname" value="<?php echo esc_attr( $display_name ); ?>">
                </div>
                <div class="input-group mb-3">
                    <span class="input-group-text">Email</span>
                    <input type="email" class="form-control" placeholder="hoten@gmail.com" id="useremail" value="<?php echo esc_attr( $user_email ); ?>">
                </div>
                <div class="input-group mb-3">
                    <span class="input-group-text">Mật khẩu mới</span>
                    <input type="password" class="form-control" placeholder="Bỏ trống nếu không đổi" id="userpassword">
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <a href="<?php echo esc_url( wp_logout_url( home_url('/') ) ); ?>" class="btn btn-outline-danger btn-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-right:2px;"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                        Đăng xuất
                    </a>
                    <button type="button" class="btn btn-primary btn-sm px-3" id="btn-update-profile">Cập nhật hồ sơ</button>
                </div>
                <div class="mb-2">
                    <strong>Link chia sẻ & Giới thiệu:</strong>
                    <hr class="my-2">
                </div>
                <div class="input-group mb-3">
                    <span class="input-group-text">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-link" style="vertical-align:middle;margin-right:2px;">
                            <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path>
                            <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path>
                        </svg>
                        Giới thiệu
                    </span>
                    <input type="text" class="form-control bg-white" onclick="this.select();" id="reflink" value="<?php echo esc_attr( $reflink ); ?>" readonly="">
                    <button class="btn btn-outline-primary" id="btn-copy-reflink" type="button" onclick="navigator.clipboard.writeText(document.getElementById('reflink').value);this.innerText='✓ Đã chép';setTimeout(()=>this.innerText='Sao chép',1500);">Sao chép</button>
                </div>
            </div>
            <div class="modal-footer" id="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>