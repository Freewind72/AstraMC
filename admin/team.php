<?php
require_once 'header.php';

// 获取团队成员列表
$teamMembers = [];

try {
    // 获取团队成员列表
    $membersResult = $db->query("SELECT * FROM team_members ORDER BY sort_order ASC, id ASC");
    while ($row = $membersResult->fetchArray(SQLITE3_ASSOC)) {
        $teamMembers[] = $row;
    }
} catch (Exception $e) {
    $message = '获取当前设置时出错：' . $e->getMessage();
}

// 处理表单提交
if (isset($_POST['action'])) {
    if ($_POST['action'] === 'add_member') {
        // 添加团队成员
        $qqNumber = trim($_POST['qq_number']);
        $name = trim($_POST['name']);
        $role = trim($_POST['role']);
        $description = trim($_POST['description']);
        $sortOrder = intval($_POST['sort_order']);
        
        if (empty($qqNumber) || empty($name) || empty($role) || empty($description)) {
            $message = '所有字段都不能为空。';
        } else {
            try {
                $stmt = $db->prepare("INSERT INTO team_members (qq_number, name, role, description, sort_order) VALUES (:qq_number, :name, :role, :description, :sort_order)");
                $stmt->bindValue(':qq_number', $qqNumber, SQLITE3_TEXT);
                $stmt->bindValue(':name', $name, SQLITE3_TEXT);
                $stmt->bindValue(':role', $role, SQLITE3_TEXT);
                $stmt->bindValue(':description', $description, SQLITE3_TEXT);
                $stmt->bindValue(':sort_order', $sortOrder, SQLITE3_INTEGER);
                
                if ($stmt->execute()) {
                    header("Location: team.php?message=" . urlencode('团队成员已成功添加！'));
                    exit();
                } else {
                    $message = '添加团队成员失败，请重试。';
                }
            } catch (Exception $e) {
                $message = '添加失败：' . $e->getMessage();
            }
        }
    } elseif ($_POST['action'] === 'update_member') {
        // 更新团队成员
        $id = intval($_POST['member_id']);
        $qqNumber = trim($_POST['qq_number']);
        $name = trim($_POST['name']);
        $role = trim($_POST['role']);
        $description = trim($_POST['description']);
        $sortOrder = intval($_POST['sort_order']);
        
        if (empty($qqNumber) || empty($name) || empty($role) || empty($description)) {
            $message = '所有字段都不能为空。';
        } else {
            try {
                $stmt = $db->prepare("UPDATE team_members SET qq_number = :qq_number, name = :name, role = :role, description = :description, sort_order = :sort_order WHERE id = :id");
                $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
                $stmt->bindValue(':qq_number', $qqNumber, SQLITE3_TEXT);
                $stmt->bindValue(':name', $name, SQLITE3_TEXT);
                $stmt->bindValue(':role', $role, SQLITE3_TEXT);
                $stmt->bindValue(':description', $description, SQLITE3_TEXT);
                $stmt->bindValue(':sort_order', $sortOrder, SQLITE3_INTEGER);
                
                if ($stmt->execute()) {
                    header("Location: team.php?message=" . urlencode('团队成员已成功更新！'));
                    exit();
                } else {
                    $message = '更新团队成员失败，请重试。';
                }
            } catch (Exception $e) {
                $message = '更新失败：' . $e->getMessage();
            }
        }
    } elseif ($_POST['action'] === 'delete_member') {
        // 删除团队成员
        $id = intval($_POST['member_id']);
        
        try {
            $stmt = $db->prepare("DELETE FROM team_members WHERE id = :id");
            $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
            
            if ($stmt->execute()) {
                header("Location: team.php?message=" . urlencode('团队成员已成功删除！'));
                exit();
            } else {
                $message = '删除团队成员失败，请重试。';
            }
        } catch (Exception $e) {
            $message = '删除失败：' . $e->getMessage();
        }
    }
}
?>

<div class="card">
    <h2 class="card-title">
        <i class="fas fa-users"></i> 管理团队管理
    </h2>
    
    <!-- 添加团队成员表单 -->
    <form method="post" class="mb-4">
        <input type="hidden" name="action" value="add_member">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
        <h3>添加新成员</h3>
        <div class="form-group">
            <label class="form-label" for="qq_number">QQ号码</label>
            <input 
                type="text" 
                id="qq_number" 
                name="qq_number" 
                class="form-input" 
                placeholder="请输入成员QQ号码"
                required>
            <small>输入QQ号码即可自动生成头像</small>
        </div>
        <div class="form-group">
            <label class="form-label" for="name">成员姓名</label>
            <input 
                type="text" 
                id="name" 
                name="name" 
                class="form-input" 
                placeholder="请输入成员姓名"
                required>
        </div>
        <div class="form-group">
            <label class="form-label" for="role">成员角色</label>
            <input 
                type="text" 
                id="role" 
                name="role" 
                class="form-input" 
                placeholder="请输入成员角色"
                required>
        </div>
        <div class="form-group">
            <label class="form-label" for="description">成员描述</label>
            <textarea 
                id="description" 
                name="description" 
                class="form-input" 
                rows="3" 
                placeholder="请输入成员描述"
                required></textarea>
        </div>
        <div class="form-group">
            <label class="form-label" for="sort_order">排序</label>
            <input 
                type="number" 
                id="sort_order" 
                name="sort_order" 
                class="form-input" 
                value="0"
                min="0">
        </div>
        <button type="submit" class="btn btn-success">
            <i class="fas fa-plus"></i> 添加成员
        </button>
    </form>
    
    <!-- 团队成员列表 -->
    <h3>现有成员</h3>
    <?php if (empty($teamMembers)): ?>
        <p>暂无团队成员，请添加。</p>
    <?php else: ?>
        <?php foreach ($teamMembers as $index => $member): ?>
        <div class="card mb-3">
            <form method="post">
                <input type="hidden" name="action" value="update_member">
                <input type="hidden" name="member_id" value="<?php echo $member['id']; ?>">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                <div class="form-group">
                    <label class="form-label">QQ号码</label>
                    <input 
                        type="text" 
                        name="qq_number" 
                        class="form-input" 
                        value="<?php echo htmlspecialchars($member['qq_number']); ?>"
                        required>
                </div>
                <div class="form-group">
                    <label class="form-label">成员姓名</label>
                    <input 
                        type="text" 
                        name="name" 
                        class="form-input" 
                        value="<?php echo htmlspecialchars($member['name']); ?>"
                        required>
                </div>
                <div class="form-group">
                    <label class="form-label">成员角色</label>
                    <input 
                        type="text" 
                        name="role" 
                        class="form-input" 
                        value="<?php echo htmlspecialchars($member['role']); ?>"
                        required>
                </div>
                <div class="form-group">
                    <label class="form-label">成员描述</label>
                    <textarea 
                        name="description" 
                        class="form-input" 
                        rows="3"
                        required><?php echo htmlspecialchars($member['description']); ?></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">排序</label>
                    <input 
                        type="number" 
                        name="sort_order" 
                        class="form-input" 
                        value="<?php echo $member['sort_order']; ?>"
                        min="0">
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> 保存
                    </button>
                    <button type="button" class="btn btn-danger" onclick="deleteMember(<?php echo $member['id']; ?>)">
                        <i class="fas fa-trash"></i> 删除
                    </button>
                </div>
            </form>
            <div class="preview mt-3">
                <p>头像预览:</p>
                <img src="https://imgapi.cn/qq.php?qq=<?php echo htmlspecialchars($member['qq_number']); ?>" 
                     alt="<?php echo htmlspecialchars($member['name']); ?>头像" 
                     style="width: 100px; height: 100px; border-radius: 50%; border: 1px solid #ddd;">
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
function deleteMember(memberId) {
    if (confirm('确定要删除这个团队成员吗？')) {
        // 创建一个隐藏的表单并提交
        var form = document.createElement('form');
        form.method = 'POST';
        form.style.display = 'none';
        
        var actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'delete_member';
        form.appendChild(actionInput);
        
        var idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'member_id';
        idInput.value = memberId;
        form.appendChild(idInput);
        
        var csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = 'csrf_token';
        csrfInput.value = '<?php echo htmlspecialchars($csrfToken); ?>';
        form.appendChild(csrfInput);
        
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php
// 关闭数据库连接
$db->close();

require_once 'footer.php';
?>