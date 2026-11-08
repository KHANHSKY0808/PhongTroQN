<?php
// app/Views/partials/footer.php
?>
<link rel="stylesheet" href="<?= ASSETS_URL ?>/css/footer.css?v=<?= time() ?>">

<!-- Footer -->
<footer class="footer">
  <div class="footer-top">
    <div class="footer-col">
      <h4>PHÒNG TRỌ, NHÀ TRỌ</h4>
      <a href="<?= BASE_URL ?>/loc-tin?huyen=Quy+Nhơn">Phòng trọ Quy Nhơn</a>
      <a href="<?= BASE_URL ?>/loc-tin?huyen=An+Nhơn">Phòng trọ An Nhơn</a>
      <a href="<?= BASE_URL ?>/loc-tin?huyen=Tuy+Phước">Phòng trọ Tuy Phước</a>
      <a href="<?= BASE_URL ?>/">Tất cả phòng trọ</a>
    </div>
    <div class="footer-col">
      <h4>TIN TỨC</h4>
      <a href="#">Tin thị trường</a>
      <a href="#">Xu hướng thuê trọ</a>
      <a href="#">Cẩm nang thuê phòng</a>
    </div>
    <div class="footer-col">
      <h4>HƯỚNG DẪN</h4>
      <a href="<?= BASE_URL ?>/dang-tin">Cách đăng tin</a>
      <a href="#">Quy trình thuê phòng</a>
      <a href="#">Câu hỏi thường gặp</a>
    </div>
    <div class="footer-col">
      <h4>CHÍNH SÁCH</h4>
      <a href="#">Quy chế hoạt động</a>
      <a href="#">Quy định sử dụng</a>
      <a href="#">Chính sách bảo mật</a>
    </div>
    <div class="footer-col">
      <h4>HỢP ĐỒNG MẪU</h4>
      <a href="#">Hợp đồng thuê nhà</a>
      <a href="#">Hợp đồng cho thuê phòng</a>
      <a href="#">Hợp đồng cho thuê mặt bằng</a>
    </div>
  </div>

  <div class="footer-bottom">
    <div class="footer-col">
      <h4>VỀ NHATROQN.COM</h4>
      <a href="#">Giới thiệu</a>
      <a href="#">Quy chế hoạt động</a>
      <a href="#">Quy định sử dụng</a>
      <a href="#">Liên hệ</a>
    </div>
    <div class="footer-col">
      <h4>DÀNH CHO KHÁCH HÀNG</h4>
      <a href="<?= BASE_URL ?>/dang-tin">Hướng dẫn đăng tin</a>
      <a href="#">Bảng giá dịch vụ</a>
      <a href="#">Quy định đăng tin</a>
      <a href="#">Giải quyết khiếu nại</a>
    </div>
    <div class="footer-col">
      <h4>PHƯƠNG THỨC THANH TOÁN</h4>
      <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
        <img src="<?= ASSETS_URL ?>/img/visa.png" alt="Visa" style="max-height: 28px;">
        <img src="<?= ASSETS_URL ?>/img/momo.png" alt="MoMo" style="max-height: 28px;">
        <img src="<?= ASSETS_URL ?>/img/zalopay.png" alt="ZaloPay" style="max-height: 28px;">
        <img src="<?= ASSETS_URL ?>/img/shopeepay.jpeg" alt="ShopeePay" style="max-height: 28px;">
      </div>
      <small style="display:block; color:#888; font-size:0.8rem; margin-top:6px;">* Phương thức thanh toán minh họa</small>
    </div>
    <div class="footer-col">
      <h4>THEO DÕI NHATROQN.COM</h4>
      <div class="socials">
        <a href="https://facebook.com" target="_blank" rel="noopener"><i class="fab fa-facebook-f"></i></a>
        <a href="https://youtube.com" target="_blank" rel="noopener"><i class="fab fa-youtube"></i></a>
        <a href="https://zalo.me" target="_blank" rel="noopener"><img src="<?= ASSETS_URL ?>/img/zalo.png" alt="Zalo" style="width: 20px; height: 20px;"></a>
        <a href="https://tiktok.com" target="_blank" rel="noopener"><i class="fab fa-tiktok"></i></a>
      </div>
    </div>
  </div>
</footer>
</body>
</html>
