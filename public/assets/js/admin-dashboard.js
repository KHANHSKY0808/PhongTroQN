// === GLOBAL VARIABLES (sẽ được định nghĩa từ inline script) ===
let currentImages = [];
let currentIndex = 0;

// === CHI TIẾT BÀI ĐĂNG ===
function openDetailModal(postId) {
    document.getElementById('detailModal').style.display = 'flex';
    document.getElementById('detailContent').style.display = 'none';
    document.getElementById('detailLoading').style.display = 'block';
    currentIndex = 0;

    fetch(`${BASE_URL}/admin/post-detail?id=${postId}`)
    .then(r => r.json())
    .then(data => {
        if (!data.success) throw new Error(data.message || 'Lỗi tải dữ liệu');

        const p = data.post;
        currentImages = data.images;

        document.getElementById('detailTitle').textContent = p.TieuDe;
        document.getElementById('detailPrice').textContent = new Intl.NumberFormat('vi-VN').format(p.GiaPhong) + ' đ';
        document.getElementById('detailArea').textContent = p.DienTich;
        document.getElementById('detailAddress').textContent = p.DiaChi;
        document.getElementById('detailPoster').textContent = p.HoTen;
        document.getElementById('detailDate').textContent = new Date(p.NgayDang).toLocaleString('vi-VN');
        document.getElementById('detailDesc').textContent = p.MoTa || '(Không có mô tả)';

        // Ảnh
        const mainImg = document.getElementById('mainImage');
        const thumbContainer = document.getElementById('thumbnailContainer');
        thumbContainer.innerHTML = '';

        if (currentImages.length > 0) {
            mainImg.src = currentImages[0];
            currentImages.forEach((img, i) => {
                const thumb = document.createElement('img');
                thumb.src = img;
                thumb.style.width = '60px';
                thumb.style.height = '60px';
                thumb.style.objectFit = 'cover';
                thumb.style.borderRadius = '6px';
                thumb.style.cursor = 'pointer';
                thumb.style.opacity = i === 0 ? '1' : '0.6';
                thumb.style.border = i === 0 ? '2px solid #2d5aa0' : '2px solid transparent';
                thumb.onclick = () => { currentIndex = i; showImage(i); };
                thumbContainer.appendChild(thumb);
            });
        } else {
            mainImg.src = `${ASSETS_URL}/img/noimage.png`;  // Sử dụng ASSETS_URL nếu có, hoặc thay bằng đường dẫn cứng
            thumbContainer.innerHTML = '<i>Không có hình ảnh</i>';
        }

        document.getElementById('detailLoading').style.display = 'none';
        document.getElementById('detailContent').style.display = 'block';
    })
    .catch(err => {
        document.getElementById('detailLoading').textContent = 'Lỗi: ' + err.message;
    });
}

// === CAROUSEL FUNCTIONS ===
function showImage(index) {
    document.getElementById('mainImage').src = currentImages[index];
    document.querySelectorAll('#thumbnailContainer img').forEach((t, i) => {
        t.style.opacity = i === index ? '1' : '0.6';
        t.style.border = i === index ? '2px solid #2d5aa0' : '2px solid transparent';
    });
}
function prevImage() { currentIndex = (currentIndex - 1 + currentImages.length) % currentImages.length; showImage(currentIndex); }
function nextImage() { currentIndex = (currentIndex + 1) % currentImages.length; showImage(currentIndex); }

// === ĐÓNG MODAL ===
document.getElementById('closeDetail').onclick = () => {
    document.getElementById('detailModal').style.display = 'none';
};
window.addEventListener('click', (e) => {
    if (e.target === document.getElementById('detailModal')) {
        document.getElementById('detailModal').style.display = 'none';
    }
});

// === GẮN SỰ KIỆN CHO TẤT CẢ NÚT "XEM" ===
document.querySelectorAll('a[href*="/chi-tiet/"]').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        const url = this.getAttribute('href');
        const postId = url.split('/').pop();
        openDetailModal(postId);
    });
});

// === XÓA BÀI ĐĂNG ===
let postIdToDelete = null;

document.querySelectorAll('.btn-delete').forEach(btn => {
  btn.addEventListener('click', function() {
    postIdToDelete = this.dataset.id;
    document.getElementById('deleteModal').classList.add('active');
  });
});

document.getElementById('cancelDelete').onclick = () => {
  document.getElementById('deleteModal').classList.remove('active');
  postIdToDelete = null;
};

document.getElementById('confirmDelete').onclick = () => {
  if (!postIdToDelete) return;

  fetch(`${BASE_URL}/admin/delete-post`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'id=' + postIdToDelete
  })
  .then(r => r.json())
  .then(data => {
    document.getElementById('deleteModal').classList.remove('active');

    const toast = document.getElementById('toast');
    toast.textContent = data.message;
    toast.className = 'toast show ' + (data.success ? 'success' : 'error');

    setTimeout(() => toast.classList.remove('show'), 4000);

    if (data.success) {
      // Xóa dòng khỏi bảng
      document.querySelector(`.btn-delete[data-id="${postIdToDelete}"]`).closest('tr').remove();
      // Cập nhật số liệu tổng tin
      const totalEl = document.querySelector('.card-container .card:nth-child(1) h3');
      let [today, total] = totalEl.textContent.split(' / ');
      totalEl.innerHTML = today + ' / ' + (parseInt(total) - 1);
    }
  });
};