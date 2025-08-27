function formatTimeRange(from, to) {
    const date1 = new Date(from);
    const date2 = new Date(to);
    if (isNaN(date1.getTime()) || isNaN(date2.getTime())) {
        console.error("Invalid date format");
        return '';
    }
    const time1 = String(date1.getHours()).padStart(2, '0') + ':' + String(date1.getMinutes()).padStart(2, '0');
    const time2 = String(date2.getHours()).padStart(2, '0') + ':' + String(date2.getMinutes()).padStart(2, '0');
    return `${time1} - ${time2}`;
};

function formatDate(date) {
    const dateObj = new Date(date);

    // Get day, month, and year from the Date object
    const day = String(dateObj.getDate()).padStart(2, '0');
    const month = String(dateObj.getMonth() + 1).padStart(2, '0'); // Months are 0-indexed
    const year = dateObj.getFullYear();

    // Combine the parts into the desired format
    return `${day}/${month}/${year}`;

}


function getAcronym(fullName) {
  // Bảng ánh xạ các ký tự có dấu sang không dấu
  const vietnamese_chars = [
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
  ];
  const non_vietnamese_chars = [
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
  ];

  // Sử dụng regex để thay thế các ký tự có dấu
  let normalizedString = fullName;
  for (let i = 0; i < vietnamese_chars.length; i++) {
    const regex = new RegExp(vietnamese_chars[i], 'g');
    normalizedString = normalizedString.replace(regex, non_vietnamese_chars[i]);
  }

  // Tách chuỗi thành mảng các từ
  const words = normalizedString.split(' ').filter(word => word);
  let acronym = '';

  // Lấy chữ cái đầu của mỗi từ, chuyển thành chữ hoa và ghép lại
  words.forEach(word => {
    acronym += word.charAt(0).toUpperCase();
  });

  return acronym;
}


/**
 * Định dạng lại URL ảnh đại diện một cách an toàn.
 *
 * @param {string} avatarPath Đường dẫn ảnh từ CSDL (null hoặc undefined).
 * @param {string} [imageText='Avatar'] Văn bản hiển thị trên ảnh mặc định.
 * @param {string} [imageSize='100x100'] Kích thước ảnh mặc định.
 * @returns {string} URL ảnh hoàn chỉnh.
 */
function formatAvatarUrl(avatarPath, imageText = 'Avatar', imageSize = '100x100') {
  // URL cơ sở của Amazon S3 bucket
  const s3BaseUrl = 'https://vietelite.s3.ap-southeast-1.amazonaws.com';

  // Trường hợp 3: Nếu đường dẫn rỗng hoặc null, trả về ảnh mặc định.
  if (!avatarPath) {
    const defaultText = imageText ? getAcronym(imageText) : 'Avatar';
    return `https://placehold.co/${imageSize}/EBF5FF/7F9CF5?text=${encodeURIComponent(defaultText)}`;
  }

  // Trường hợp 1: Nếu đã là một URL đầy đủ, giữ nguyên.
  if (avatarPath.startsWith('http://') || avatarPath.startsWith('https://')) {
    return avatarPath;
  }

  // Trường hợp 2: Nếu là đường dẫn tương đối, ghép với URL S3.
  // ltrim() được thay thế bằng trim() kết hợp với startswith()
  const trimmedPath = avatarPath.startsWith('/') ? avatarPath.substring(1) : avatarPath;
  return `${s3BaseUrl}/${trimmedPath}`;
}

// --- Ví dụ sử dụng ---

// // Ví dụ 1: Đường dẫn ảnh đại diện có sẵn
// const avatarUrl1 = formatAvatarUrl('public/images/profile.jpg');
// console.log(avatarUrl1); // https://vietelite.s3.ap-southeast-1.amazonaws.com/public/images/profile.jpg

// // Ví dụ 2: Đường dẫn đầy đủ
// const avatarUrl2 = formatAvatarUrl('https://example.com/images/user.png');
// console.log(avatarUrl2); // https://example.com/images/user.png

// // Ví dụ 3: Đường dẫn rỗng (avatar mặc định)
// const avatarUrl3 = formatAvatarUrl(null, 'Phan Việt Anh');
// console.log(avatarUrl3); // https://placehold.co/100x100/EBF5FF/7F9CF5?text=PVA

    /**
     * Hàm tiện ích để định dạng kích thước file.
     */
function formatFileSize(bytes) {
        if (!bytes || bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
    }

    /**
     * Hàm tiện ích để lấy icon cho loại file.
     */
function getFileIcon(fileType) {
        const icons = {
            'pdf': '📄', 'doc': '📝', 'docx': '📝',
            'xls': '📊', 'xlsx': '📊', 'ppt': '🖥️',
            'pptx': '🖥️', 'jpg': '🖼️', 'jpeg': '🖼️',
            'png': '🖼️', 'zip': '📦',
        };
        return icons[fileType.toLowerCase()] || '📁'; // Icon mặc định
    }