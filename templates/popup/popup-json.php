<?php if (! defined('ABSPATH')) {
    exit;
} ?>
<!-- option json cho vòng quay -->
<div class="modal" id="modalTemplate" tabindex="-1" aria-modal="true" aria-hidden="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="feather feather-folder">
                        <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
                    </svg>
                    Chủ đề mẫu
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-3">
                <button type="button" class="btn btn-success btn-sm mb-2 rounded-pill btn-fill"
                    data-content="Tuấn||Thông||Sơn||Dũng||Phong||Lan||Hương||Hoa||Mai||Ngọc||Hà||Thành||Trang||Giang||Tuyền||Linh">
                    Tên ngẫu nhiên
                </button><button type="button" class="btn btn-secondary btn-sm mb-2 rounded-pill btn-fill"
                    data-content="Mời người đối diện||Tự uống 1 ly||Tìm người uống cùng||Tất cả cùng uống||Tự uống 2 ly||Được ăn mồi||Quay lại||Chỉ ai đó uống||Bên phải uống 1 ly||Thoát nạn (khỏi uống)||Bên trái uống 1 ly||Tự uống 1/2 ly||Nhảy một điệu nhảy ngẫu nhiên||Uống hai ly nước lọc.||Hỏi một câu hỏi khó cho người khác trả lời||Giữ thăng bằng trên một chân trong 30 giây||Khen một người bất kỳ trong bàn nhậu||Kể một câu chuyện hài hước||Hát một bài hát">
                    Trên bàn nhậu
                </button>
                <button type="button" class="btn btn-primary btn-sm mb-2 rounded-pill btn-fill"
                    data-content="Ăn Phở||Ăn Bún||Gà KFC||Gà Lotteria||Gà 36||Tokyo Deli||Lẩu Gogi||Isushi||Sumo BBQ||Phốn gon 37||Kichi-Kichi||Ba con cừu||Shogun||MANWAH||HUTONG||DARUMA||Quay lại||Mỳ UDON">
                    Trưa nay ăn gì
                </button>
                <button type="button" class="btn btn-danger btn-sm mb-2 rounded-pill btn-fill"
                    data-content="Ôm rồi hôn||Đi nhà nghỉ||Về nhà ngủ||Quay lại||Ra công viên||Ăn rồi ngủ||Đi xem film||Mua 2 trà sữa||Mát xa cho em||Chơi đuổi nhau||Chơi chốn tìm||Chơi game 69">
                    Rủ dê gái
                </button>
                <button type="button" class="btn btn-warning btn-sm mb-2 rounded-pill btn-fill"
                    data-content="Chẵn||Lẻ||Chẵn||Lẻ||Chẵn||Lẻ||Chẵn||Lẻ||chẵn||Lẻ||Chẵn||Lẻ">
                    Chẵn lẻ
                </button>
                <button type="button" class="btn btn-danger btn-sm mb-2 rounded-pill btn-fill"
                    data-content="Bên phải||Tên Cường||Đeo kính||Đối diện||Bên trái||Quay lại||Nói nhiều||Thằng quay||Mồm to||Vừa đi vệ sinh">
                    Ai trả tiền
                </button>

                <button type="button" class="btn btn-info btn-sm mb-2 rounded-pill btn-fill"
                    data-content="A||B||C||D||E||F||G||H||I||J||K||L||M||N||O||P||Q||R||S||T||U||V||W||X||Y||Z">
                    Chữ cái A→Z
                </button>
                <button type="button" class="btn btn-dark btn-sm mb-2 rounded-pill btn-fill-number" data-from="1"
                    data-to="10" title="Số từ 1→10 (rất nhanh)">
                    Số (1→10)
                </button>
                <button type="button" class="btn btn-secondary btn-sm mb-2 rounded-pill btn-fill-number" data-from="1"
                    data-to="100" title="Số từ 1→100 (nhanh)">
                    Số (1→100)
                </button>
                <button type="button" class="btn btn-warning btn-sm mb-2 rounded-pill btn-fill-number" data-from="1"
                    data-to="500" title="Số từ 1→500 (trung bình)">
                    Số (1→500)
                </button>
                <button type="button" class="btn btn-danger btn-sm mb-2 rounded-pill btn-fill-number" data-from="1"
                    data-to="1000" title="Số từ 1→1000 (chậm)">
                    Số (1→1000)
                </button>
                <button type="button" class="btn btn-warning btn-sm mb-2 rounded-pill btn-fill"
                    data-content="🇻🇳 Việt Nam||🇹🇭 Thái Lan||🇲🇾 Malaysia||🇮🇩 Indonesia||🇸🇬 Singapore||🇱🇦 Lào||🇰🇭 Campuchia||🇵🇭 Philippines||🇲🇲 Myanmar||Quay lại">
                    Bóng đá
                </button>
                <button type="button" class="btn btn-info btn-sm mb-2 rounded-pill btn-fill"
                    data-content="5k||10k||20k||50k||30k||100k||Nhân đôi||200k||Chia đôi||500k">
                    Phần thưởng
                </button>
                <button type="button" class="btn btn-primary btn-sm mb-2 rounded-pill btn-fill"
                    data-content="💍Ring||📿Necklake||👙Bikini||👗Dress||👚blouse||👕T-shirt||👘Kimono||️🎽Runingshirt||👖Jean||👠Highheels||👢Boot||👞Man\sshoe||👒Hat||🎩Tophat">
                    Thời trang
                </button>
                <button type="button" class="btn btn-success btn-sm mb-2 rounded-pill btn-fill"
                    data-content="😽Cat||🐶Puppy||🐰Bunny||🐹Hamster||🦊Fox||🐻Bear||🐼Panda||🐨Koala||🐯Tiger||🦁Lion||🐮Cow||🐂Ox||🐷Pig||🐸Frog||🐵Monkey||🦍Gorilla||🐺Wolf||🐑Sheep||🐐Goat||🐏Ram||🦌Deer||🐪Camel||🐎Horse||🐊Croccodile||🐢Turtle||🐬Dolphin||🦈Shark||🐋Whale||🦐Shrimp||🦀Crab||🐙Octopus||🦑Squid||🐜Ant||🕷️Spider||🐞Ladybug||🦋Butterfly||🐝Bee||🐌Snail||🐲Dragon||🦉Owl||🐔Chicken||🐓Rooster||🐧Penguin||🦇Bat">
                    Động vật
                </button>
                <a href="#" class="btn btn-light btn-sm mb-2 rounded-pill btn-fill"
                    data-content="" id="btn-more">Xem tiếp
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="feather feather-chevrons-right">
                        <polyline points="13 17 18 12 13 7"></polyline>
                        <polyline points="6 17 11 12 6 7"></polyline>
                    </svg></a>
            </div>
        </div>
    </div>
</div>