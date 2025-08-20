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
}
