<?php
// เพิ่ม limit สำหรับเนื้อหาขนาดใหญ่
@ini_set('post_max_size', '64M');
@ini_set('upload_max_filesize', '64M');
@ini_set('max_execution_time', '300');
@ini_set('max_input_time', '300');
@ini_set('memory_limit', '256M');

session_start();
require_once 'config/db.php';
require_once 'includes/header.php';

$database = new Database();
$db = $database->getConnection();

$id = isset($_GET['id']) ? $_GET['id'] : null;
$page = null;

if (!$id) {
    header("Location: pages.php");
    exit;
}

// Fetch existing data
$stmt = $db->prepare("SELECT * FROM pages WHERE id = ?");
$stmt->execute([$id]);
$page = $stmt->fetch();

if (!$page) {
    echo "Page not found.";
    exit;
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];

    // Update
    $sql = "UPDATE pages SET title=?, content=?, updated_at=NOW() WHERE id=?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$title, $content, $id]);

    echo "<script>window.location.href='pages.php';</script>";
    exit;
}
?>

<div class="page-header">
    <h1 class="page-title">
        แก้ไขเนื้อหา:
        <?php echo $page['title']; ?>
    </h1>
</div>

<div class="card" style="max-width: 1000px; margin: 0 auto;">
    <form method="POST" id="pageForm">

        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 500;">หัวข้อ (Title)</label>
            <input type="text" name="title" value="<?php echo $page['title']; ?>" class="form-control" required
                style="padding: 10px; width: 100%; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box;">
        </div>

        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 500;">เนื้อหา (HTML Content)</label>
            <textarea name="content" id="content-editor" class="form-control" rows="20"
                style="padding: 10px; width: 100%; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box;"><?php echo htmlspecialchars($page['content']); ?></textarea>
        </div>

        <div style="display: flex; gap: 15px; align-items: center;">
            <button type="submit" id="submitBtn"
                style="background: var(--primary-blue); color: white; padding: 12px 30px; border: none; border-radius: 5px; cursor: pointer; font-size: 1rem;">บันทึกข้อมูล</button>
            <a href="pages.php"
                style="background: #eee; color: #333; padding: 12px 30px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; font-size: 1rem;">ยกเลิก</a>
            <span id="statusMsg" style="color: #666; font-size: 0.9rem;"></span>
        </div>

    </form>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay"
    style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center;">
    <div style="background: white; padding: 30px 50px; border-radius: 10px; text-align: center;">
        <div
            style="width: 40px; height: 40px; border: 4px solid #f3f3f3; border-top: 4px solid var(--primary-blue); border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 15px;">
        </div>
        <p style="margin: 0; color: #333;">กำลังบันทึกข้อมูล...</p>
    </div>
</div>

<style>
    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }
</style>

<!-- jQuery (required for Summernote) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Summernote CSS & JS -->
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/lang/summernote-th-TH.min.js"></script>

<script>
    $(document).ready(function () {
        // Initialize Summernote
        $('#content-editor').summernote({
            lang: 'th-TH',
            height: 450,
            placeholder: 'พิมพ์เนื้อหาที่นี่...',
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'italic', 'underline', 'strikethrough', 'clear']],
                ['fontsize', ['fontsize']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'picture', 'video', 'hr']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ],
            fontSizes: ['8', '9', '10', '11', '12', '14', '16', '18', '20', '24', '36', '48'],
            styleTags: ['p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'],
            callbacks: {
                onImageUpload: function (files) {
                    for (var i = 0; i < files.length; i++) {
                        var reader = new FileReader();
                        reader.onloadend = function () {
                            var img = $('<img>').attr('src', reader.result);
                            $('#content-editor').summernote('insertNode', img[0]);
                        }
                        reader.readAsDataURL(files[i]);
                    }
                }
            }
        });

        // Form submit handler - sync Summernote before submit
        $('#pageForm').on('submit', function (e) {
            // Show loading
            $('#loadingOverlay').css('display', 'flex');
            $('#submitBtn').prop('disabled', true);

            // Sync Summernote content to textarea
            var content = $('#content-editor').summernote('code');
            $('#content-editor').val(content);

            // Check content size
            var contentSize = new Blob([content]).size;
            var maxSize = 8 * 1024 * 1024; // 8MB limit (safe for most hosting)

            if (contentSize > maxSize) {
                e.preventDefault();
                $('#loadingOverlay').hide();
                $('#submitBtn').prop('disabled', false);
                alert('เนื้อหามีขนาดใหญ่เกินไป (' + (contentSize / 1024 / 1024).toFixed(2) + ' MB)\n\nกรุณาลดขนาดรูปภาพหรือเนื้อหา\nแนะนำ: ใช้ลิงก์รูปภาพจากภายนอกแทนการอัปโหลดรูปภาพโดยตรง');
                return false;
            }

            // Let form submit normally
            return true;
        });
    });
</script>

<style>
    .note-editor.note-frame {
        border: 1px solid #ddd;
        border-radius: 5px;
    }

    .note-editor .note-toolbar {
        background-color: #f8f9fa;
        border-bottom: 1px solid #ddd;
    }

    .note-editor .note-editing-area .note-editable {
        font-family: 'Prompt', sans-serif;
        font-size: 14px;
    }
</style>

<?php require_once 'includes/footer.php'; ?>