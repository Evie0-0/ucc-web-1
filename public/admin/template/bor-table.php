<?php
use Core\Token;
$bors = $bors ?? [];
?>
<?php if (empty($bors)): ?>
	<div class="empty-state" id="borEmpty">
		<i data-lucide="users-round"></i>
		<h3>No regents found</h3>
		<p>Try a different search term.</p>
	</div>
<?php else: ?>
	<div class="table-wrap">
		<table class="bor-table">
			<thead>
				<tr>
					<th>Photo</th>
					<th>Name</th>
					<th>Position</th>
					<th>Status</th>
					<th class="actions-head">Actions</th>
				</tr>
			</thead>
			<tbody id="borTableBody">
				<?php foreach ($bors as $bor): ?>
					<tr>
						<td>
							<div class="table-photo">
								<?php if (!empty($bor['image'])): ?>
									<img src="<?= e('/admin/storage/uploads/' . $bor['image']) ?>" alt="<?= e($bor['name']) ?>">
								<?php else: ?>
									<span><?= e(strtoupper(substr($bor['name'], 0, 2))) ?></span>
								<?php endif; ?>
							</div>
						</td>
						<td>
							<div class="name-cell">
								<strong><?= e($bor['name']) ?></strong>
								<small><?= e($bor['bio'] ?: '—') ?></small>
							</div>
						</td>
						<td>
							<?= e($bor['position']) ?>
						</td>
						<td>
							<span class="status-badge <?= $bor['status'] === 'active' ? 'active' : 'hidden-status' ?>">
								<?= e(ucfirst($bor['status'])) ?>
							</span>
						</td>
						<td class="actions-cell">
							<div class="table-actions">
								<button class="icon-btn view" type="button" title="View" aria-label="View" data-action="view" data-id="<?= (int) $bor['id'] ?>">
									<i data-lucide="eye"></i>
								</button>
								<button class="icon-btn edit" type="button" title="Edit" aria-label="Edit" data-action="edit" data-id="<?= (int) $bor['id'] ?>">
									<i data-lucide="pencil"></i>
								</button>
								<form method="post" class="delete-form">
									<input type="hidden" name="csrf_key" value="<?= e(Token::generate()) ?>">
									<input type="hidden" name="bor_id" value="<?= e((string) $bor['id']) ?>">
									<button type="submit" name="action" value="remove" class="icon-btn delete" title="Delete" aria-label="Delete">
										<i data-lucide="trash-2"></i>
									</button>
								</form>
							</div>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
<?php endif; ?>