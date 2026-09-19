<?php
/**
 * Shared review card. Expects $t and accepts an optional $reviewExcerpt length.
 * Google reviews retain all author attribution and a direct source link.
 */
$reviewExcerpt = isset($reviewExcerpt) ? (int) $reviewExcerpt : 0;
$reviewText    = $reviewExcerpt > 0 ? excerpt((string) $t['quote'], $reviewExcerpt) : (string) $t['quote'];
$isGoogle      = ($t['source'] ?? '') === 'google';
$profileUrl    = $isGoogle ? (string) ($t['profile_url'] ?? '') : '';
$avatarUrl     = $isGoogle ? (string) ($t['avatar_url'] ?? '') : '';
$reviewUrl     = $isGoogle ? (string) ($t['review_url'] ?? '') : '';
?>
<div class="testimonial-card<?= $isGoogle ? ' google-review-card' : '' ?>">
    <?= stars($t['rating']) ?>
    <p class="testimonial-quote mt-3">&ldquo;<?= nl2br(e($reviewText)) ?>&rdquo;</p>
    <div class="testimonial-meta">
        <?php if ($profileUrl !== ''): ?><a class="testimonial-avatar" href="<?= e($profileUrl) ?>" target="_blank" rel="noopener" aria-label="View <?= e($t['name']) ?> on Google Maps"><?php else: ?><span class="testimonial-avatar"><?php endif; ?>
            <?php if ($avatarUrl !== ''): ?><img src="<?= e($avatarUrl) ?>" alt="" width="46" height="46" loading="lazy" referrerpolicy="no-referrer"><?php else: ?><?= e(initials((string) $t['name'])) ?><?php endif; ?>
        <?php if ($profileUrl !== ''): ?></a><?php else: ?></span><?php endif; ?>
        <div>
            <strong><?php if ($profileUrl !== ''): ?><a href="<?= e($profileUrl) ?>" target="_blank" rel="noopener"><?= e($t['name']) ?></a><?php else: ?><?= e($t['name']) ?><?php endif; ?></strong>
            <?php if ($isGoogle): ?>
                <span><a href="<?= e($reviewUrl !== '' ? $reviewUrl : setting('google_maps_url')) ?>" target="_blank" rel="noopener"><span class="gmp-attribution" translate="no">Google Maps</span><?= $t['location'] !== '' ? ' · ' . e($t['location']) : '' ?> <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i></a></span>
            <?php else: ?>
                <span><?= e($t['location']) ?><?= $t['vehicle'] ? ' · ' . e($t['vehicle']) : '' ?></span>
            <?php endif; ?>
        </div>
    </div>
</div>
