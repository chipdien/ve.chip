<?php

// =================================================================
// CÁC HÀM TRỢ GIÚP (HELPER FUNCTIONS)
// =================================================================

function dump($data) {
    echo '<pre>';
    var_dump($data);
    echo '</pre>';
}

function dump_and_die($data) {
    dump($data);
    die();
}

/**
 * Trả về một chuỗi an toàn, đã được làm sạch (viết tắt của nice text).
 * @param string|null $var Chuỗi đầu vào.
 * @param string $default Giá trị trả về nếu chuỗi đầu vào rỗng.
 * @return string
 */
function nt(?string $var = null, string $default = ''): string
{
    return (isset($var) && !empty($var)) ? htmlspecialchars($var) : $default;
}

/**
 * Trả về ngày tháng đã được định dạng dd/mm/yyyy (viết tắt của nice date).
 * @param string|null $date_string Chuỗi ngày tháng từ CSDL.
 * @param string $default Giá trị trả về nếu ngày tháng không hợp lệ.
 * @return string
 */
function nd(?string $date_string, string $default = 'N/A'): string
{
    if (empty($date_string) || $date_string === '0000-00-00 00:00:00' || $date_string === '0000-00-00') {
        return $default;
    }
    try {
        return date('d/m/Y', strtotime($date_string));
    } catch (Exception $e) {
        return $default;
    }
}

/**
 * Trả về một số hoặc tiền tệ đã được định dạng (viết tắt của nice format number).
 * @param mixed $number Số cần định dạng.
 * @param int $decimals Số chữ số thập phân.
 * @param string $currency Đơn vị tiền tệ (ví dụ: 'đ').
 * @return string
 */
function nf($number, int $decimals = 0, string $currency = 'đ'): string
{
    if (!is_numeric($number)) {
        $number = 0;
    }
    $formatted = number_format((float)$number, $decimals, ',', '.');
    return $currency ? $formatted . ' ' . $currency : $formatted;
}

/**
 * Định dạng lại URL ảnh đại diện một cách an toàn.
 *
 * @param string|null $avatar_path Đường dẫn ảnh từ CSDL.
 * @return string URL ảnh hoàn chỉnh.
 */
function format_avatar_url(?string $avatar_path, string $image_text = 'Avatar', string $image_size = '100x100'): string
{
    // URL cơ sở của Amazon S3 bucket
    $s3_base_url = 'https://vietelite.s3.ap-southeast-1.amazonaws.com';

    // Trường hợp 3: Nếu đường dẫn rỗng hoặc null, trả về ảnh mặc định.
    if (empty($avatar_path)) {
        $image_text = $image_text ? getAcronym($image_text) : 'Avatar';
        return 'https://placehold.co/' . $image_size . '/EBF5FF/7F9CF5?text=' . urlencode($image_text);
    }

    // Trường hợp 1: Nếu đã là một URL đầy đủ, giữ nguyên.
    if (str_starts_with($avatar_path, 'http://') || str_starts_with($avatar_path, 'https://')) {
        return $avatar_path;
    }

    // Trường hợp 2: Nếu là đường dẫn tương đối, ghép với URL S3.
    // ltrim() để đảm bảo không có dấu gạch chéo kép (//) ở đầu.
    return $s3_base_url . '/' . ltrim($avatar_path, '/');
}

/* Function cũ, không tương thích với ký tự UTF8 như Đ Â ....
function getAcronym($fullName) {
    $words = explode(" ", $fullName); // Tách chuỗi thành mảng các từ dựa trên dấu cách
    $acronym = "";
    foreach ($words as $word) {
        if (!empty($word)) { // Đảm bảo từ không rỗng (trường hợp có nhiều dấu cách liên tiếp)
            $acronym .= strtoupper(substr($word, 0, 1)); // Lấy chữ cái đầu tiên của mỗi từ và chuyển thành chữ hoa
        }
    }
    return $acronym;
}
*/

function getAcronym($fullName) {
    $vietnamese_chars = array(
        'à', 'á', 'ạ', 'ả', 'ã', 'â', 'ầ', 'ấ', 'ậ', 'ẩ', 'ẫ', 'ă', 'ằ', 'ắ', 'ặ', 'ẳ', 'ẵ',
        'è', 'é', 'ẹ', 'ẻ', 'ẽ', 'ê', 'ề', 'ế', 'ệ', 'ể', 'ễ',
        'ì', 'í', 'ị', 'ỉ', 'ĩ',
        'ò', 'ó', 'ọ', 'ỏ', 'õ', 'ô', 'ồ', 'ố', 'ộ', 'ổ', 'ỗ', 'ơ', 'ờ', 'ớ', 'ợ', 'ở', 'ỡ',
        'ù', 'ú', 'ụ', 'ủ', 'ũ', 'ư', 'ừ', 'ứ', 'ự', 'ử', 'ữ',
        'ỳ', 'ý', 'ỵ', 'ỷ', 'ỹ',
        'đ', 'Đ',
        'À', 'Á', 'Ạ', 'Ả', 'Ã', 'Â', 'Ầ', 'Ấ', 'Ậ', 'Ẩ', 'Ẫ', 'Ă', 'Ằ', 'Ắ', 'Ặ', 'Ẳ', 'Ẵ',
        'È', 'É', 'Ẹ', 'Ẻ', 'Ẽ', 'Ê', 'Ề', 'Ế', 'Ệ', 'Ể', 'Ễ',
        'Ì', 'Í', 'Ị', 'Ỉ', 'Ĩ',
        'Ò', 'Ó', 'Ọ', 'Ỏ', 'Õ', 'Ô', 'Ồ', 'Ố', 'Ộ', 'Ổ', 'Ỗ', 'Ơ', 'Ờ', 'Ớ', 'Ợ', 'Ở', 'Ỡ',
        'Ù', 'Ú', 'Ụ', 'Ủ', 'Ũ', 'Ư', 'Ừ', 'Ứ', 'Ự', 'Ử', 'Ữ',
        'Ỳ', 'Ý', 'Ỵ', 'Ỷ', 'Ỹ'
    );
    
    $non_vietnamese_chars = array(
        'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a',
        'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e',
        'i', 'i', 'i', 'i', 'i',
        'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o',
        'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u',
        'y', 'y', 'y', 'y', 'y',
        'd', 'D',
        'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A',
        'E', 'E', 'E', 'E', 'E', 'E', 'E', 'E', 'E', 'E', 'E',
        'I', 'I', 'I', 'I', 'I',
        'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O',
        'U', 'U', 'U', 'U', 'U', 'U', 'U', 'U', 'U', 'U', 'U',
        'Y', 'Y', 'Y', 'Y', 'Y'
    );
    // 1. Chuyển đổi chuỗi sang dạng chuẩn hóa để loại bỏ dấu
    // $normalizedString = Normalizer::normalize($fullName, Normalizer::FORM_NFD);
    // 2. Loại bỏ các ký tự dấu và các ký tự không phải chữ cái
    // $normalizedString = preg_replace('/[^\p{L}\s]/u', '', $normalizedString);
    
    $normalizedString = str_replace($vietnamese_chars, $non_vietnamese_chars, $fullName);

    // 3. Tách chuỗi thành mảng các từ
    $words = explode(" ", $normalizedString);
    $acronym = "";

    // 4. Lấy chữ cái đầu của mỗi từ, chuyển thành chữ hoa và ghép lại
    foreach ($words as $word) {
        // Đảm bảo từ không rỗng sau khi explode
        if (!empty($word)) {
            $firstCharacter = mb_substr($word, 0, 1, 'UTF-8');
            $acronym .= mb_strtoupper($firstCharacter, 'UTF-8');
        }
    }

    return $acronym;
}

/**
 * 💡 HÀM HỖ TRỢ: Định dạng thời gian thân thiện (ví dụ: "10:30 Hôm nay", "14:00 Hôm qua")
 *
 * @param string $datetime_string Chuỗi thời gian từ CSDL (Y-m-d H:i:s).
 * @return string Chuỗi thời gian đã được định dạng.
 */
function format_relative_time($datetime_string) {
    if (empty($datetime_string)) return '';
    $time = strtotime($datetime_string);
    $now = time();
    $today = strtotime('today', $now);
    $yesterday = strtotime('yesterday', $now);

    if ($time >= $today) {
        return 'lúc ' . date('H:i', $time) . ' Hôm nay';
    } elseif ($time >= $yesterday) {
        return 'lúc ' . date('H:i', $time) . ' Hôm qua';
    } else {
        return date('d/m/Y H:i', $time);
    }
}


function split_fullname(string $fullName): array
{
    // 1. Loại bỏ các khoảng trắng thừa ở đầu, cuối và giữa chuỗi
    $fullName = trim(preg_replace('/\s+/', ' ', $fullName));

    // Trường hợp chuỗi rỗng
    if (empty($fullName)) {
        return ['first_name' => '', 'last_name' => ''];
    }

    // 2. Tách chuỗi thành một mảng các từ
    $nameParts = explode(' ', $fullName);

    // 3. Lấy tên (phần tử cuối cùng của mảng)
    //    array_pop sẽ lấy và xóa phần tử cuối cùng khỏi mảng $nameParts
    $firstName = array_pop($nameParts);

    // 4. Họ và tên đệm là các phần tử còn lại trong mảng,
    //    được ghép lại với nhau bằng dấu cách.
    $lastName = implode(' ', $nameParts);

    return [
        'first_name' => trim(ucwords($firstName)),
        'last_name'  => trim(ucwords($lastName)),
    ];
}


/**
 * Tạo một URL hoàn chỉnh và an toàn cho ứng dụng.
 *
 * Hàm này tự động sử dụng hằng số APP_BASE_PATH đã được định nghĩa
 * và xây dựng chuỗi truy vấn (query string) từ một mảng tham số.
 *
 * Ví dụ 1:
 * url('session', ['id' => 101, 'view' => 'print']);
 * // Sẽ trả về: /teacher/session.php?id=101&view=print
 *
 * Ví dụ 2:
 * url('class_list');
 * // Sẽ trả về: /teacher/class_list.php
 *
 * @param string $pageName Tên của file PHP (không bao gồm .php).
 * @param array $params Mảng kết hợp chứa các tham số cho URL (tùy chọn).
 * @return string URL đã được tạo.
 */
function url(string $pageName, array $params = []): string
{
    // Bắt đầu với đường dẫn cơ sở đã được định nghĩa trong config.php
    $baseUrl = APP_BASE_PATH . '/' . $pageName . '.php';

    // Nếu có tham số, xây dựng chuỗi truy vấn
    if (!empty($params)) {
        // http_build_query() là hàm an toàn của PHP để tạo chuỗi query
        // nó sẽ tự động mã hóa các ký tự đặc biệt.
        $queryString = http_build_query($params);
        return $baseUrl . '?' . $queryString;
    }

    return $baseUrl;
}

function hasRole(string $roleName): bool 
{
    global $user;
    if (!isset($user['roles']) || !is_array($user['roles'])) {
        return false;
    }
    foreach ($user['roles'] as $role) {
        if (isset($role['name']) && $role['name'] === $roleName) {
            return true;
        }
    }
    return false;
}