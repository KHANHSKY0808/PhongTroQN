<?php
// app/Controllers/HomeController.php
class HomeController
{
    public function index()
    {
        $postModel = new Post();
        $posts = $postModel->getActivePostsWithDetails();
        $totalPosts = $postModel->countActivePosts();

        $favoriteModel = new Favorite();
        if (Auth::check()) {
            $userID = Auth::id();
            foreach ($posts as &$p) {
                $p['is_favorited'] = $favoriteModel->isFavorited($userID, $p['PostID']);
            }
            unset($p);
        } else {
            foreach ($posts as &$p) {
                $p['is_favorited'] = false;
            }
            unset($p);
        }

        view('home/index', [
            'posts' => $posts,
            'totalPosts' => $totalPosts
        ]);
    }

    public function filter()
    {
        // Nếu không có tiêu chí nào → về trang chủ
        if (empty($_GET['gia']) && empty($_GET['loai']) && empty($_GET['dientich']) &&
            empty($_GET['dactiem']) && empty($_GET['tinh']) && empty($_GET['huyen'])) {
            return (new HomeController)->index();
        }

        $postModel = new Post();
        $posts = $postModel->getActivePostsWithDetails();
        $filteredPosts = [];

        foreach ($posts as $post) {

            // ================== BẮT ĐẦU LỌC – MỌI TIN ĐỀU PHẢI QUA TẤT CẢ CÁC BỘ LỌC ==================
            $dia_chi_lower = mb_strtolower(vn_str_filter($post['DiaChi'] ?? ''));
            $selected_tinh  = $_GET['tinh'] ?? '';
            $selected_huyen = trim($_GET['huyen'] ?? '');

            // 1. LỌC TỈNH/THÀNH PHỐ – BẮT BUỘC PHẢI CÓ TÊN TỈNH (TRỪ BÌNH ĐỊNH)
            if (!empty($selected_tinh)) {
                if ($selected_tinh === 'binhdinh') {
                    // Bình Định → luôn pass phần tỉnh
                } else {
                    $tinh_keywords = [
                        'hanoi'        => ['ha noi','hanoi','thu do','hà nội','thủ đô'],
                        'haiphong'     => ['hai phong','haiphong','hải phòng'],
                        'quangninh'    => ['quang ninh','ha long','hạ long','quảng ninh'],
                        'bacninh'      => ['bac ninh','bắc ninh'],
                        'vinhphuc'     => ['vinh phuc','vinh yen','vĩnh phúc','vĩnh yên'],
                        'phutho'       => ['phu tho','viet tri','phú thọ','việt trì'],
                        'thainguyen'   => ['thai nguyen','thái nguyên'],
                        'langson'      => ['lang son','lạng sơn'],
                        'caobang'      => ['cao bang','cao bằng'],
                        'backan'       => ['bac kan','bắc kạn'],
                        'tuyenquang'   => ['tuyen quang','tuyên quang'],
                        'yenbai'       => ['yen bai','yên bái'],
                        'laocai'       => ['lao cai','lào cai'],
                        'dienbien'     => ['dien bien','dien bien phu','điện biên','điện biên phủ'],
                        'laichau'      => ['lai chau','lai châu'],
                        'sonla'        => ['son la','sơn la'],
                        'hoabinh'      => ['hoa binh','hòa bình'],
                        'thanhhoa'     => ['thanh hoa','thanh hóa'],
                        'nghean'       => ['nghe an','vinh','nghệ an'],
                        'hatinh'       => ['ha tinh','hà tĩnh'],
                        'quangbinh'    => ['quang binh','dong hoi','quảng bình','đồng hới'],
                        'quangtri'     => ['quang tri','dong ha','quảng trị','đông hà'],
                        'hue'          => ['hue','thua thien hue','huế','thừa thiên huế'],
                        'danang'       => ['da nang','danang','đà nẵng'],
                        'quangnam'     => ['quang nam','tam ky','quảng nam','tam kỳ'],
                        'kontum'       => ['kon tum'],
                        'gialai'       => ['gia lai','pleiku'],
                        'khanhhoa'     => ['khanh hoa','nha trang','khánh hòa'],
                        'ninhthuan'    => ['ninh thuan','phan rang','ninh thuận'],
                        'binhthuan'    => ['binh thuan','phan thiet','bình thuận','phan thiết'],
                        'hcm'          => ['ho chi minh','tphcm','sai gon','hcm','tp hcm','sài gòn'],
                        'dongnai'      => ['dong nai','bien hoa','đồng nai','biên hòa'],
                        'baria-vungtau'=> ['ba ria vung tau','vung tau','bà rịa vũng tàu','vũng tàu'],
                        'camau'        => ['ca mau','cà mau']
                    ];

                    $found = false;
                    if (isset($tinh_keywords[$selected_tinh])) {
                        foreach ($tinh_keywords[$selected_tinh] as $kw) {
                            if (strpos($dia_chi_lower, $kw) !== false) {
                                $found = true;
                                break;
                            }
                        }
                    }
                    if (!$found) {
                        continue; // LOẠI BỎ TIN NÀY NGAY – KHÔNG CHẠY GÌ NỮA
                    }
                }
            }

            // 2. LỌC QUẬN/HUYỆN – CHỈ CHẠY NẾU CÓ CHỌN HUYỆN
            if (!empty($selected_huyen)) {
                $huyen_clean = mb_strtolower(vn_str_filter($selected_huyen));
                $huyen_clean = trim(str_replace(['tp. ','thành phố ','huyện ','thị xã ','quận '], '', $huyen_clean));

                $huyen_keywords = [
                    'quy nhon'     => ['quy nhon','quy nhơn','tp quy nhon'],
                    'an nhon'      => ['an nhon','an nhơn'],
                    'phu cat'      => ['phu cat','phù cát'],
                    'phu my'       => ['phu my','phù mỹ'],
                    'hoai nhon'    => ['hoai nhon','hoài nhơn'],
                    'tuy phuoc'    => ['tuy phuoc','tuy phước'],
                    'quan 1'       => ['q1','quan 1','quận 1'],
                    'quan 7'       => ['q7','quan 7','phu my hung'],
                    'thu duc'      => ['thu duc','thủ đức'],
                    'hoan kiem'    => ['hoan kiem','hoàn kiếm'],
                    'ba dinh'      => ['ba dinh','ba đình'],
                    'hai chau'     => ['hai chau','hải châu'],
                ];

                $found_huyen = false;
                if (isset($huyen_keywords[$huyen_clean])) {
                    foreach ($huyen_keywords[$huyen_clean] as $kw) {
                        if (strpos($dia_chi_lower, $kw) !== false) {
                            $found_huyen = true;
                            break;
                        }
                    }
                } elseif (strpos($dia_chi_lower, $huyen_clean) !== false) {
                    $found_huyen = true;
                }

                if (!$found_huyen) {
                    continue; // LOẠI BỎ TIN NÀY NGAY
                }
            }

            // 3. CÁC BỘ LỌC KHÁC (loại phòng, giá, diện tích, đặc điểm)
            if (!empty($_GET['loai']) && $_GET['loai'] !== 'Tất cả') {
                $keyword = $_GET['loai'];
                if (stripos($post['TieuDe'], $keyword) === false && stripos($post['MoTa'], $keyword) === false) {
                    continue;
                }
            }

            if (!empty($_GET['gia'])) {
                [$min, $max] = explode('-', $_GET['gia']);
                $gia = $post['GiaPhong'];
                if ($gia < $min || $gia > $max) {
                    continue;
                }
            }

            if (!empty($_GET['dientich'])) {
                [$min, $max] = explode('-', $_GET['dientich']);
                $dt = $post['DienTich'];
                $max = $max >= 999 ? 9999 : $max;
                if ($dt < $min || $dt > $max) {
                    continue;
                }
            }

            if (!empty($_GET['dactiem'])) {
                $tags = explode(',', $_GET['dactiem']);
                $match = false;
                foreach ($tags as $tag) {
                    $tag = trim($tag);
                    if ($tag && stripos($post['MoTa'], $tag) !== false) {
                        $match = true;
                        break;
                    }
                }
                if (!$match) continue;
            }

            // QUA TẤT CẢ BỘ LỌC → THÊM VÀO KẾT QUẢ
            $filteredPosts[] = $post;
        }

        $totalPosts = count($filteredPosts);
        $title = $totalPosts > 0
            ? "Tìm thấy " . number_format($totalPosts) . " tin phòng trọ"
            : "Không tìm thấy tin nào phù hợp";

        $favoriteModel = new Favorite();
        if (Auth::check()) {
            $userID = Auth::id();
            foreach ($filteredPosts as &$p) {
                $p['is_favorited'] = $favoriteModel->isFavorited($userID, $p['PostID']);
            }
            unset($p);
        } else {
            foreach ($filteredPosts as &$p) {
                $p['is_favorited'] = false;
            }
            unset($p);
        }

        view('home/index', [
            'title' => $title,
            'posts' => $filteredPosts,
            'totalPosts' => $totalPosts
        ]);
    }
    
}