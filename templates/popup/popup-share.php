<?php if (! defined('ABSPATH')) {
    exit;
} ?>
<div class="modal" id="modalShare" tabindex="-1" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" id="modal-dialog">
        <div class="modal-content" id="modal-content">
            <div class="modal-header" id="modal-header">
                <h5 class="modal-title" id="modal-title"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="feather feather-share-2"
                        style="width: 30px;heigh:30px;vertical-align: middle;">
                        <circle cx="18" cy="5" r="3"></circle>
                        <circle cx="6" cy="12" r="3"></circle>
                        <circle cx="18" cy="19" r="3"></circle>
                        <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line>
                        <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line>
                    </svg> Chia sẻ</h5> <button type="button" id="modal-close" class="btn-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body py-3" id="modal-body">
                <div class="mb-3"> <strong>Link chia sẻ</strong> là một liên kết tới vòng quay hiện tại bao gồm tên, màu
                    sắc, âm thanh và các cài đặt hiện tại.</div>
                <div class="mb-3"><strong>Ví dụ:</strong> <a href="https://vongquaymayman.co/c:35A4E901"
                        target="_blank">https://vongquaymayman.co/c:35A4E901</a></div>
                <div class="mb-3">Việc tạo liên kết này sẽ giúp bạn dễ dàng chia sẻ vòng quay của mình tới bất kì ai qua
                    tin nhắn, email, nhúng lên website, đăng mạng xã hội, v.v. <strong>Các liên kết không sử dụng sẽ bị
                        xóa sau 365 ngày</strong>.</div>
            </div>
            <div class="modal-footer" id="modal-footer">
                <button class="btn btn-primary" data-bs-dismiss="modal" onclick="createLinkForm();">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="feather feather-plus">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg> Tạo link chia sẻ ngay</button>
            </div>
        </div>
    </div>
</div>