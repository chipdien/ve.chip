<?php
namespace App\Services;

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

/**
 * S3StorageService
 *
 * Cung cấp các phương thức để tương tác với Amazon S3.
 */
class S3StorageService
{
    /** @var S3Client */
    private $s3Client;

    /** @var string */
    private $bucketName;

    public function __construct()
    {
        $this->s3Client = new S3Client([
            'version'     => 'latest',
            'region'      => \AWS_REGION,
            'credentials' => [
                'key'    => \AWS_ACCESS_KEY_ID,
                'secret' => \AWS_SECRET_ACCESS_KEY,
            ],
        ]);
        $this->bucketName = \S3_BUCKET_NAME;
    }

    /**
     * Tải một file lên S3.
     *
     * @param string $filePath Đường dẫn tạm thời của file được tải lên (ví dụ: $_FILES['avatar']['tmp_name']).
     * @param string $s3Key Đường dẫn và tên file trên S3 (ví dụ: 'public/images/students/student_101.jpg').
     * @param string $mimeType Kiểu MIME của file.
     * @return string|null URL công khai của file trên S3 nếu thành công, ngược lại là null.
     */
    public function uploadFile(string $filePath, string $s3Key, string $mimeType): ?string
    {
        try {
            $result = $this->s3Client->putObject([
                'Bucket'      => $this->bucketName,
                'Key'         => $s3Key,
                'SourceFile'  => $filePath,
                'ContentType' => $mimeType,
                'ACL'         => 'public-read', // Đặt quyền để file có thể được truy cập công khai
            ]);

            // Trả về URL công khai của đối tượng đã tải lên
            return $result['ObjectURL'] ?? null;

        } catch (AwsException $e) {
            // Ghi lại lỗi để debug
            error_log("S3 Upload Error: " . $e->getMessage());
            return null;
        }
    }


    /**
     * (MỚI) Xóa một hoặc nhiều file khỏi S3.
     * @param array $s3Keys Mảng chứa các key của file cần xóa.
     * @return bool True nếu thành công, false nếu thất bại.
     */
    public function deleteFiles(array $s3Keys): bool {
        if (empty($s3Keys)) {
            return true; // Không có gì để xóa
        }

        // --- LOGIC S3 THẬT (BỎ COMMENT KHI DÙNG) ---

        try {
            $objectsToDelete = [];
            foreach ($s3Keys as $key) {
                $objectsToDelete[] = ['Key' => $key];
            }

            $result = $this->s3Client->deleteObjects([
                'Bucket' => $this->bucketName,
                'Delete' => [
                    'Objects' => $objectsToDelete,
                ],
            ]);
            
            // Kiểm tra xem có lỗi nào không
            if (!empty($result['Errors'])) {
                 foreach ($result['Errors'] as $error) {
                    error_log("S3 Delete Error: Code=" . $error['Code'] . ", Message=" . $error['Message']);
                 }
                 return false;
            }
            return true;

        } catch (AwsException $e) {
            error_log("S3 Delete Exception: " . $e->getMessage());
            return false;
        }


        // --- LOGIC GIẢ LẬP ĐỂ DEMO ---
        // Giả lập việc xóa thành công
        // error_log("Giả lập xóa các file S3: " . implode(', ', $s3Keys));
        // return true;
    }

    /**
     * (MỚI) Liệt kê tất cả các file trong một thư mục trên S3.
     * @param string $folderPath Đường dẫn thư mục (prefix) cần liệt kê.
     * @return array|false Mảng chứa URL của các file hoặc false nếu có lỗi.
     */
    public function listFiles(string $folderPath) {
        // --- LOGIC S3 THẬT (BỎ COMMENT KHI DÙNG) ---
        
        try {
            $result = $this->s3Client->listObjectsV2([
                'Bucket' => $this->bucketName,
                'Prefix' => $folderPath,
            ]);

            $fileUrls = [];
            if (!empty($result['Contents'])) {
                foreach ($result['Contents'] as $object) {
                    // Bỏ qua chính thư mục đó nếu nó xuất hiện trong danh sách
                    if (substr($object['Key'], -1) !== '/') {
                        $fileUrls[] = [
                            'url' => $object['Key'],
                            'name' => basename($object['Key']),
                        ];
                    }
                }
            }
            return $fileUrls;

        } catch (AwsException $e) {
            error_log("S3 List Files Exception: " . $e->getMessage());
            return false;
        }
        

        // --- LOGIC GIẢ LẬP ĐỂ DEMO ---
        // Giả lập trả về một danh sách rỗng vì không thể biết file thật
        // error_log("Giả lập liệt kê file trong thư mục S3: " . $folderPath);
        // return [];
    }


    /**
     * (MỚI) Lấy toàn bộ tài liệu của một lớp học, nhóm theo từng ca học và loại tài liệu.
     * @param string $classCode Mã của lớp học (dùng để xác định thư mục gốc).
     * @return array|false Mảng dữ liệu đã được cấu trúc hoặc false nếu có lỗi.
     */
    public function getClassDocuments(string $classCode) {
        if (empty($classCode)) {
            return false;
        }

        $classFolderPath = "public/docs/{$classCode}/";

        // --- LOGIC S3 THẬT (BỎ COMMENT KHI DÙNG) ---
        
        try {
            $result = $this->s3Client->listObjectsV2([
                'Bucket' => $this->bucketName,
                'Prefix' => $classFolderPath,
            ]);

            if (empty($result['Contents'])) {
                return []; // Không có tài liệu nào
            }

            $structuredDocuments = [];
            foreach ($result['Contents'] as $object) {
                $s3Key = $object['Key'];

                // Bỏ qua nếu là thư mục
                if (substr($s3Key, -1) === '/') continue;

                // Phân tích đường dẫn để lấy thông tin
                // Ví dụ: public/docs/CLASSCODE/2025-08-27_123/documents/file.pdf
                // $pattern = "/{$classFolderPath}([\d]{4}-[\d]{2}-[\d]{2})_(\d+)\/(documents|exercices|comments)\/(.+)/";
                $escapedFolderPath = preg_quote($classFolderPath, '/');
                $pattern = "/^{$escapedFolderPath}([\d]{4}-[\d]{2}-[\d]{2})_(\d+)\/(documents|exercices|comments)\/(.+)/";
                
                if (preg_match($pattern, $s3Key, $matches)) {
                    $sessionDate = $matches[1];
                    $sessionId = $matches[2];
                    $docType = $matches[3];
                    $fileName = $matches[4];

                    // Tạo mảng cho session nếu chưa tồn tại
                    if (!isset($structuredDocuments[$sessionId])) {
                        $structuredDocuments[$sessionId] = [
                            'session_date' => $sessionDate,
                            'documents' => [],
                            'exercices' => [],
                            'comments' => []
                        ];
                    }

                    // Thêm thông tin file vào đúng nhóm
                    $structuredDocuments[$sessionId][$docType][] = [
                        'id' => md5($s3Key), // Tạo ID duy nhất từ đường dẫn
                        'name' => $fileName,
                        'size' => $object['Size'], // Kích thước file (bytes)
                        'type' => pathinfo($fileName, PATHINFO_EXTENSION), // Lấy phần mở rộng file
                        'url' => \S3_BASE_URL . '/' . $s3Key
                    ];
                }
            }
            return $structuredDocuments;

        } catch (AwsException $e) {
            error_log("S3 Get Class Documents Exception: " . $e->getMessage());
            return false;
        }
        

        // --- LOGIC GIẢ LẬP ĐỂ DEMO ---
        // Giả lập dữ liệu trả về với cấu trúc hoàn chỉnh
        // return [
        //     '61892' => [ // session_id
        //         'session_date' => '2025-07-28',
        //         'documents' => [
        //             [
        //                 'id' => 'doc1',
        //                 'name' => 'bai_giang_unit_1.pdf',
        //                 'size' => 1258291, // ~1.2 MB
        //                 'type' => 'pdf',
        //                 'url' => 'https://vietelite.s3.ap-southeast-1.amazonaws.com/public/docs/25.ĐQ.TEST/2025-07-28_61892/documents/bai_giang_unit_1.pdf'
        //             ]
        //         ],
        //         'exercices' => [
        //             [
        //                 'id' => 'ex1',
        //                 'name' => 'btvn_unit_1.docx',
        //                 'size' => 24576, // 24 KB
        //                 'type' => 'docx',
        //                 'url' => 'https://vietelite.s3.ap-southeast-1.amazonaws.com/public/docs/25.ĐQ.TEST/2025-07-28_61892/exercices/btvn_unit_1.docx'
        //             ]
        //         ],
        //         'comments' => []
        //     ],
        //     '61893' => [ // session_id
        //         'session_date' => '2025-07-30',
        //         'documents' => [
        //             [
        //                 'id' => 'doc2',
        //                 'name' => 'tu_vung_unit_2.jpg',
        //                 'size' => 819200, // 800 KB
        //                 'type' => 'jpg',
        //                 'url' => 'https://vietelite.s3.ap-southeast-1.amazonaws.com/public/docs/25.ĐQ.TEST/2025-07-30_61893/documents/tu_vung_unit_2.jpg'
        //             ]
        //         ],
        //         'exercices' => [],
        //         'comments' => []
        //     ]
        // ];
    }
}
