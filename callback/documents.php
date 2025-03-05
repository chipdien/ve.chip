<?php
include 'init.php';

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;
use Medoo\Medoo;

$db25 = new Medoo($dbconfig);

$s3Client = new S3Client([
      'version'     => 'latest',
      'region'      => $credentials['region'],
      'credentials' => $credentials,
    ]);

function checkFolderString($s3FolderPath) {
    // Kiểm tra xem s3FolderPath có chứa ký tự không hợp lệ không
    if (!preg_match('/^[\p{L}\p{N}_\/\.-]*$/u', $s3FolderPath)) {
        die("Invalid s3FolderPath.  Contains invalid characters.");
    }

    // Kiểm tra xem s3FolderPath có chứa "..", ngăn chặn directory traversal
    if (strpos($s3FolderPath, '..') !== false) {
         die("Invalid s3FolderPath.  Directory traversal detected.");
    }
    //Giới hạn độ dài của s3FolderPath, ví dụ 255 kí tự
    if(mb_strlen($s3FolderPath, 'UTF-8') > 255){
        die("Invalid s3FolderPath. Path too long.");
    }

    $s3FolderPath = trim($s3FolderPath, '/') . '/'; //Xử lý như bình thường
    return $s3FolderPath;
}

function getFileURLsFromS3Folder(string $s3FolderPath, string $bucketName, array $credentials): array
{
    global $s3Client;
//    // 1. Chuẩn hóa và kiểm tra s3FolderPath.
//    $s3FolderPath = checkFolderString($s3FolderPath);  // Sử dụng hàm đã viết trước đó.

    $fileURLs = [];

    try {
        // 3. Lấy danh sách object (sử dụng ListObjectsV2 - tốt hơn ListObjects).
        $result = $s3Client->listObjectsV2([
            'Bucket'    => $bucketName,
            'Prefix'    => $s3FolderPath,
            'Delimiter' => '/', // Chỉ lấy các object trực tiếp trong thư mục, không đệ quy.
        ]);

        // 4. Lặp qua kết quả và tạo URL.
        if (isset($result['Contents'])) {
            foreach ($result['Contents'] as $object) {
                //Bỏ qua folder
                if ($object['Key'] != $s3FolderPath) {
                    $fileURLs[] = $object['Key'];
                            // $s3Client->getObjectUrl($bucketName, $object['Key']);
                }
            }
        }

        // 5. (Tùy chọn) Lấy danh sách các thư mục con (nếu cần).
        // if (isset($result['CommonPrefixes'])) {
        //     foreach ($result['CommonPrefixes'] as $prefix) {
        //         $subfolder = rtrim($prefix['Prefix'], '/'); // Loại bỏ dấu / cuối.
        //         // Xử lý subfolder (ví dụ: thêm vào mảng riêng).
        //     }
        // }

    } catch (S3Exception $e) {
        // Xử lý lỗi S3 (ghi log, hiển thị thông báo, ...).
        error_log("S3 Error: " . $e->getMessage()); // Ghi vào error log.
        // Hoặc: throw new Exception("Error listing files: " . $e->getMessage());
        return []; // Trả về mảng rỗng nếu có lỗi.
    }

    return $fileURLs;
}

// Lấy s3FolderPath từ URL (GET parameter).
if ( isset($_GET['s3FolderPath']) && $_GET['s3FolderPath'] <> '' ) {
    $s3FolderPath = $_GET['s3FolderPath'];
} else {
    $s3FolderPath = 'public/docs/'.$_GET['class'].'/'.$_GET['session_date'].'_'.$_GET['session_id'].'/'.$_GET['type'];
}
$actionUrl = 'documents.php?s3FolderPath='.htmlspecialchars(urlencode($s3FolderPath)).'&type='.$_GET['type'].'&session_id='.$_GET['session_id'].'&ckeyget='.C_KEY_GET;
$s3FolderPath = checkFolderString($s3FolderPath);
//print_r($s3FolderPath);

// Xử lý hành động (upload, delete, view).
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (empty($_POST['ckeypost']) || $_POST['ckeypost']<> C_KEY_POST) {
        echo 'Upss!';
        die();
    }

    // UPLOAD FILE
    if (isset($_FILES['fileToUpload'])) {

        $uploadedFiles = [];
        $uploadErrors = [];

        // Lặp qua từng file được upload.
        for ($i = 0; $i < count($_FILES['fileToUpload']['name']); $i++) {
            if ($_FILES['fileToUpload']['error'][$i] === UPLOAD_ERR_OK) {
                $fileName = $_FILES['fileToUpload']['name'][$i];
                $tempFilePath = $_FILES['fileToUpload']['tmp_name'][$i];
                $s3Key = $s3FolderPath . $fileName;

                try {
                    $result = $s3Client->putObject([
                        'Bucket' => $bucketName,
                        'Key'    => $s3Key,
                        'SourceFile' => $tempFilePath,
                        'ACL'    => 'public-read', // Cẩn thận với ACL!
                    ]);
                    $uploadedFiles[] = $result['ObjectURL'];

                } catch (S3Exception $e) {
                    $uploadErrors[] = "Error uploading $fileName: " . $e->getMessage();
                } catch (Exception $e){
                     $uploadErrors[] = "Error uploading $fileName: " . $e->getMessage();
                }
            } else {
              //Xử lý lỗi upload file
                $error_message = "No file uploaded or an error occurred during upload.";
                switch($_FILES['fileToUpload']['error'][$i]) {
                  case UPLOAD_ERR_INI_SIZE:
                    $error_message = "The uploaded file exceeds the upload_max_filesize directive in php.ini.";
                    break;
                  case UPLOAD_ERR_FORM_SIZE:
                    $error_message = "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.";
                    break;
                  case UPLOAD_ERR_PARTIAL:
                    $error_message = "The uploaded file was only partially uploaded.";
                    break;
                  case UPLOAD_ERR_NO_FILE:
                    $error_message = "No file was uploaded.";
                    break;
                  case UPLOAD_ERR_NO_TMP_DIR:
                    $error_message = "Missing a temporary folder.";
                    break;
                  case UPLOAD_ERR_CANT_WRITE:
                    $error_message = "Failed to write file to disk.";
                    break;
                  case UPLOAD_ERR_EXTENSION:
                    $error_message = "File upload stopped by extension.";
                    break;
                }
                $uploadErrors[] = $error_message;
            }
        }

        $getFiles = getFileURLsFromS3Folder($s3FolderPath, $bucketName, $credentials);
        if ($getFiles) {
            if ($_GET['type'] == 'documents') {
                $data = ['content_files' => implode(',', $getFiles)];
            } else {
                $data = ['exercice_files' => implode(',', $getFiles)];
            }
            $db25->update('edu_sessions', $data, [
                'id' => $_GET['session_id']
            ]);
        }
        
        // Hiển thị thông báo sau khi upload.
        if (!empty($uploadedFiles)) {
            echo "Files uploaded successfully:<br>";
            foreach ($uploadedFiles as $url) {
                echo "<a href=\"" . htmlspecialchars($url) . "\" target=\"_blank\">" . htmlspecialchars(basename($url)) . "</a><br>";
            }
        }
        if (!empty($uploadErrors)) {
            echo "Upload errors:<br>";
            foreach ($uploadErrors as $error) {
                echo htmlspecialchars($error) . "<br>";
            }
        }
    }

    // DELETE FILE
    if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['fileToDelete'])) {
        $fileToDelete = $_POST['fileToDelete'];

        try {
            $s3Client->deleteObject([
                'Bucket' => $bucketName,
                'Key'    => $fileToDelete,
            ]);
            echo "File deleted successfully: " . htmlspecialchars($fileToDelete) . "<br>"; //Sanitize output

        } catch (S3Exception $e) {
            echo "Error deleting file: " . $e->getMessage() . "<br>";
        }
        
        $getFiles = getFileURLsFromS3Folder($s3FolderPath, $bucketName, $credentials);
        if ($getFiles) {
            if ($_GET['type'] == 'documents') {
                $data = ['content_files' => implode(',', $getFiles)];
            } else {
                $data = ['exercice_files' => implode(',', $getFiles)];
            }
            $db25->update('edu_sessions', $data, [
                'id' => $_GET['session_id']
            ]);
        }
    }
}

if (empty($_GET['ckeyget']) || $_GET['ckeyget']<> C_KEY_GET) {
    echo 'Upss!';
    die();
}
    
    
$files = [];
try {
    $result = $s3Client->listObjectsV2([
        'Bucket' => $bucketName,
        'Prefix' => $s3FolderPath,
        'Delimiter' => '/', //Quan trọng: chỉ list file trong folder hiện tại
    ]);

    // Lọc và chuẩn bị dữ liệu file.
      if (isset($result['Contents'])) {
        foreach ($result['Contents'] as $object) {
            // Bỏ qua chính thư mục
            if ($object['Key'] != $s3FolderPath) {
              $files[] = [
                  'key' => $object['Key'],
                  'url' => $s3Client->getObjectUrl($bucketName, $object['Key']), // Get the URL
              ];
            }
        }
    }
} catch (S3Exception $e) {
    echo "Error listing files: " . $e->getMessage() . "<br>";
}


?>

<!DOCTYPE html>
<html>
<head>
    <title>File Upload and Management</title>
</head>
<body>

    <?php if ($_GET['type'] == 'documents') { ?>
    <h3>Tài liệu buổi học</h3>
    <?php } else { ?>    
    <h3>Phiếu bài tập</h3>
    <?php } ?>
    
    <?php if (isset($_GET['status']) && $_GET['status']=='0') { ?>
    <form action="<?php echo $actionUrl; ?>" method="post" enctype="multipart/form-data">
        Select file to upload:
        <input type="file" name="fileToUpload[]" id="fileToUpload" multiple>
        <input type="hidden" name="ckeypost" value="<?php echo C_KEY_POST; ?>">
        <input type="submit" value="Upload File" name="submit">
    </form>
    <?php } ?>

    <h4>Danh sách files hiện có:</h4> <!--Files in <?php echo htmlspecialchars($s3FolderPath); ?>:-->
    <ul>
        <?php foreach ($files as $file): ?>
            <li>
                <a href="<?php echo htmlspecialchars($file['url']); ?>" target="_blank"><?php echo htmlspecialchars(basename($file['key'])); ?> </a>&nbsp;&nbsp;&nbsp; 

                <?php if (isset($_GET['status']) && $_GET['status']=='0') { ?>
                <form action="<?php echo $actionUrl; ?>" method="post" style="display:inline;">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="ckeypost" value="<?php echo C_KEY_POST; ?>">
                    <input type="hidden" name="fileToDelete" value="<?php echo htmlspecialchars($file['key']); ?>">
                    <button type="submit" onclick="return confirm('Are you sure you want to delete this file?');">Delete</button>
                </form>
                <?php } ?>
            </li>
        <?php endforeach; ?>
    </ul>
    <script>
    // Không có gì đặc biệt ở đây, chỉ là confirm trước khi xoá.
    </script>
</body>
</html>


