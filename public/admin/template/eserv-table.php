<?php
use Core\Token;
$services = $services ?? [];
?>

<?php if (empty($services)): ?>
    <div class="no-services" id="noServices">
        <i data-lucide="search-x"></i>
        <h3>No services found</h3>
        <p>Try searching for a different e-service.</p>
    </div>
<?php else: ?>
    <?php foreach ($services as $service): ?>
        <article class="service-card">
            <div class="service-card-top">
                <div class="service-icon">
                    <?php if (!empty($service['logo'])): ?>
                    <img src="<?= e('/admin/storage/uploads/' . $service['logo']) ?>" alt="">
                    <?php else: ?>
                        <i data-lucide="landmark"></i>
                    <?php endif; ?>
                </div>
                <div class="service-actions">
                <button class="service-action edit-action" type="button" data-service-id="<?= e((string) $service['id']) ?>" title="Edit">
                        <i data-lucide="pencil"></i>
                    </button>
                    <form method="post">
                        <input type="hidden" name="csrf_key" value="<?= e(Token::generate()) ?>">
                        <input type="hidden" name="service_id" value="<?= e((string) $service['id']) ?>">
                        <button type="submit" name="action" value="remove" class="service-action delete-action" title="Delete">
                            <i data-lucide="trash-2"></i>
                        </button>
                    </form>
                </div>
            </div>
            <div class="service-card-content">
                <h3><?= e($service['name']) ?></h3>
                <span><?= e($service['category']) ?></span>
                <p><?= e($service['description']) ?></p>
            </div>
            <div class="service-card-footer">
                <a href="<?= e($service['url']) ?>" target="_blank">Visit Website <i data-lucide="arrow-up-right"></i></a>
            </div>
        </article>
    <?php endforeach; ?>
<?php endif; ?>

