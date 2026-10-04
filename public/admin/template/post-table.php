<?php $posts = $posts ?? []; ?>

<?php if (empty($posts)): ?>
    <tr>
        <td colspan="7" class="empty-row">No posts found.</td>
    </tr>
<?php else: ?>
    <?php foreach ($posts as $post): ?>
        <tr>
            <td>
                <div class="featured-thumb">
                    <?php if (!empty($post['image'])): ?>
                        <img src="<?= $post['image'] ?>" alt="">
                    <?php else: ?>
                        <i data-lucide="image"></i>
                    <?php endif; ?>
                </div>
            </td>

            <td class="post-title-cell"><?= $post['title'] ?></td>
            <td class="category-text"><?= $post['category'] ?></td>
            <td><?= (new DateTime($post['published_at']))->format('M d, Y') ?></td>
            <td><?= number_format(0) ?></td>
            <td><span class="status-badge status-<?= strtolower($post['status']) ?>"><?= $post['status'] ?></span></td>
            <td>
                <div class="action-buttons">
                    <form method="get">
                        <input type="hidden" name="post_id" value="<?= (string) $post['id'] ?>">
                        <button type="submit" class="action-btn" title="Edit">
                            <i data-lucide="pencil"></i>
                        </button>
                    </form>
                    <button class="action-btn" data-action="edit" data-id="<?= e((string) $post['id']) ?>" title="Edit">
                        <i data-lucide="pencil"></i>
                    </button>
                    <form method="post" class="post-action-form">
                        <input type="hidden" name="<?= e(Token::CSRF_KEY) ?>" value="<?= e(Token::generate()) ?>">
                        <input type="hidden" name="post_id" value="<?= (string) $post['id'] ?>">
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
