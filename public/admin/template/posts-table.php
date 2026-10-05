<?php
use Core\Token;
$posts = $posts ?? []; 
?>

<?php if (empty($posts)): ?>
    <tr>
        <td colspan="7" class="empty-row">No posts found.</td>
    </tr>
<?php else: ?>
    <?php foreach ($posts as $post): ?>
        <tr>
            <td>
                <div class="featured-thumb">
                    <?php if (!empty($post['featured_image'])): ?>
                        <img src="<?= e('/admin/storage/uploads/' . $post['featured_image']) ?>" alt="">
                    <?php else: ?>
                        <i data-lucide="image"></i>
                    <?php endif; ?>
                </div>
            </td>

            <td class="post-title-cell"><?= e($post['title']) ?></td>
            <td class="category-text"><?= e($post['category']) ?></td>
            <td><?= e((new DateTime($post['published_at']))->format('M d, Y')) ?></td>
            <td><?= e(number_format(0)) ?></td>
            <td><span class="status-badge status-<?= e(strtolower($post['display_status'])) ?>"><?= e($post['display_status']) ?></span></td>
            <td>
                <div class="action-buttons">
                        <button type="button" data-post-id="<?= e((string) $post['id']) ?>" class="action-btn edit-post-btn" title="Edit">
                            <i data-lucide="pencil"></i>
                        </button>
                    <form method="post" class="post-action-form">
                        <input type="hidden" name="csrf_key" value="<?= e(Token::generate()) ?>">
                        <input type="hidden" name="post_id" value="<?= e((string) $post['id']) ?>">
                        <button type="submit" name="action" value="archive" class="action-btn archive" title="Archive">
                            <i data-lucide="archive"></i>
                        </button>
                        <button type="submit" name="action" value="remove" class="action-btn delete" title="Delete">
                            <i data-lucide="trash-2"></i>
                        </button>
                    </form>
                </div>
            </td>
        </tr>
    <?php endforeach; ?>
<?php endif; ?>
