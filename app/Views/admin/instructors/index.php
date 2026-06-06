<?php
$pageTitle = 'Manage Instructors';
$currentPage = 'instructors';
ob_start();
?>

<div class="container">
    <?php if (isset($message)): ?>
        <div class="alert"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    
    <!-- Add Instructor Form -->
    <div class="card">
        <div class="card-header">
            <h3>Add New Instructor</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="/admin/instructors/add">
                <input type="text" name="full_name" placeholder="Full Name" required style="width:auto; display:inline-block; margin-right:10px; padding:8px 16px;">
                <input type="text" name="username" placeholder="Username" required style="width:auto; display:inline-block; margin-right:10px; padding:8px 16px;">
                <input type="password" name="password" placeholder="Password" required style="width:auto; display:inline-block; margin-right:10px; padding:8px 16px;">
                <button type="submit" class="btn-primary">Add Instructor</button>
            </form>
        </div>
    </div>

    <!-- Instructors List -->
    <div class="card">
        <div class="card-header">
            <h3>Existing Instructors</h3>
            <form method="GET" style="margin:0;">
                <input type="text" name="search" class="search-bar" placeholder="Search by name or username..." value="<?= htmlspecialchars($search ?? '') ?>">
                <?php if (!empty($search)): ?>
                    <a href="/admin/instructors" class="btn-warning btn-sm" style="margin-left:5px;">Clear</a>
                <?php endif; ?>
            </form>
        </div>
        <div class="card-body">
            <?php if (count($instructors) > 0): ?>
                <?php foreach ($instructors as $inst): ?>
                <div class="instructor-card" id="inst_<?= $inst['id'] ?>">
                    <h3><?= htmlspecialchars($inst['full_name'] ?? $inst['username']) ?></h3>
                    <p><strong>Username:</strong> <?= htmlspecialchars($inst['username']) ?></p>
                    
                    <div class="flex-btns" style="margin-bottom:10px;">
                        <button onclick="editInstructor(<?= $inst['id'] ?>, '<?= htmlspecialchars(addslashes($inst['full_name'] ?? $inst['username'])) ?>')" class="btn-warning btn-sm">Edit Name</button>
                        <button onclick="editUsername(<?= $inst['id'] ?>, '<?= htmlspecialchars($inst['username']) ?>')" class="btn-warning btn-sm">Edit Username</button>
                        <button onclick="resetPassword(<?= $inst['id'] ?>)" class="btn-warning btn-sm">Reset Password</button>
                        <a href="/admin/instructors/delete/<?= $inst['id'] ?>" class="btn-danger btn-sm" onclick="return confirm('Delete this instructor?')">Delete</a>
                    </div>
                    
                    <div class="assignment-section">
                        <div class="assignment-title">Assigned Subjects:</div>
                        <div class="section-list">
                            <?php 
                            $assignedSubjects = getAssignedSubjects($pdo, $inst['id']);
                            if (count($assignedSubjects) > 0): 
                                foreach ($assignedSubjects as $subj): ?>
                                    <span class="badge"><?= htmlspecialchars($subj['name']) ?> 
                                        <a href="/admin/instructors/remove-subject/<?= $inst['id'] ?>/<?= $subj['id'] ?>" style="color:#e2362c; text-decoration:none; margin-left:5px;">✕</a>
                                    </span>
                                <?php endforeach;
                            else: ?> 
                                <span style="color:gray;">No subjects assigned</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="assignment-section">
                        <div class="assignment-title">Assigned Sections:</div>
                        <div class="section-list">
                            <?php 
                            $assignedSections = getAssignedSections($pdo, $inst['id']);
                            if (count($assignedSections) > 0):
                                foreach ($assignedSections as $sec): ?>
                                    <span class="badge"><?= htmlspecialchars($sec['name']) ?>
                                        <a href="/admin/instructors/remove-section/<?= $inst['id'] ?>/<?= $sec['id'] ?>" style="color:#e2362c; text-decoration:none; margin-left:5px;">✕</a>
                                    </span>
                                <?php endforeach;
                            else: ?> 
                                <span style="color:gray;">No sections assigned</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="flex-btns">
                        <button onclick="openAssignSubjectsModal(<?= $inst['id'] ?>)" class="btn-primary btn-sm">Assign Subjects</button>
                        <button onclick="openAssignSectionsModal(<?= $inst['id'] ?>)" class="btn-primary btn-sm">Assign Sections</button>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-results">No instructors found.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$extraCSS = '
.instructor-card {
    background: #fef9e3;
    border-radius: 20px;
    padding: 20px;
    margin-bottom: 20px;
    border-left: 6px solid #e2362c;
}
.assignment-section {
    margin-top: 10px;
    padding-top: 10px;
    border-top: 1px solid #e2e8f0;
}
.section-list {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
    margin-top: 5px;
}
.badge {
    background: #ede432;
    padding: 4px 10px;
    border-radius: 40px;
    font-size: 0.75rem;
}
';
include __DIR__ . '/../../layouts/header.php';
include __DIR__ . '/../../layouts/navbar.php';
echo $content;
include __DIR__ . '/../../layouts/footer.php';
?>