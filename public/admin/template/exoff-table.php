<?php
use Core\Token;
$exoffs = $exoffs ?? [];
?>

<?php if (empty($exoffs)): ?>
    <div class="empty-state" id="officialsEmpty">
        <i data-lucide="users-round"></i>
        <h3>No officials found</h3>
        <p>Try a different search term.</p>
    </div>
<?php else: ?>
    <div class="table-wrap">
        <table class="officials-table">
            <thead>
                <tr>
                    <th>Photo</th>
                    <th>Name</th>
                    <th>Position</th>
                    <th>Status</th>
                    <th class="actions-head">Actions</th>
                </tr>
            </thead>
            <tbody id="officialsTableBody">
                <?php foreach ($exoffs as $exoff): ?>
                    <tr>
                        <td>
                            <div class="table-photo">
                                <?php if (!empty($exoff['image'])): ?>
                                    <img src="<?= e('/admin/storage/uploads/' . $exoff['image']) ?>" alt="<?= e($exoff['name']) ?>">
                                <?php else: ?>
                                    <span><?= e(strtoupper(substr($exoff['name'], 0, 2))) ?></span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <div class="name-cell">
                                <strong><?= e($exoff['name']) ?></strong>
                                <small><?= e($exoff['bio'] ?: '—') ?></small>
                            </div>
                        </td>
                        <td>
                            <?= e($exoff['position']) ?>
                        </td>
                        <td>
                            <span class="status-badge <?= $exoff['status'] === 'active' ? 'active' : 'hidden-status' ?>">
                                <?= e(ucfirst($exoff['status'])) ?>
                            </span>
                        </td>
                        <td class="actions-cell">
                            <button class="icon-btn view" type="button" title="View" aria-label="View"
                                data-action="view" data-id="<?= (int) $exoff['id'] ?>">
                                <i data-lucide="eye"></i>
                            </button>
                            <button class="icon-btn edit" type="button" title="Edit" aria-label="Edit"
                                data-action="edit" data-id="<?= (int) $exoff['id'] ?>">
                                <i data-lucide="pencil"></i>
                            </button>
                            <form method="post">
                                <input type="hidden" name="csrf_key" value="<?= e(Token::generate()) ?>">
                                <input type="hidden" name="exoff_id" value="<?= e((string) $exoff['id']) ?>">
                                <button type="submit" name="action" value="remove" class="icon-btn delete" 
                                    title="Delete"aria-label="Delete">
                                    <i data-lucide="trash-2"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
