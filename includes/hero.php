<?php
/**
 * Page hero. Expects $hero = [
 *   'image'   => 'assets/img/hero/home.jpg',
 *   'kicker'  => 'small red label',
 *   'title'   => 'Headline',
 *   'text'    => 'Supporting sentence',
 *   'buttons' => [['label' => '…', 'url' => '…', 'style' => 'lah|outline'], …],
 *   'size'    => 'lg' | 'md' | 'sm',
 *   'align'   => 'left' | 'center',
 *   'crumbs'  => ['Inventory' => 'inventory', 'Vehicle' => null],
 * ]
 */
$hero += ['kicker' => '', 'text' => '', 'buttons' => [], 'size' => 'md', 'align' => 'left', 'crumbs' => [], 'image' => 'assets/img/hero/showroom.jpg', 'focus' => '72% 30%'];
?>
<section class="hero hero-<?= e($hero['size']) ?> hero-<?= e($hero['align']) ?>">
    <div class="hero-media">
        <img src="<?= asset($hero['image']) ?>" alt="" style="object-position: <?= e($hero['focus']) ?>" <?= $hero['size'] === 'lg' ? 'fetchpriority="high"' : 'loading="eager"' ?>>
    </div>
    <div class="hero-overlay"></div>
    <div class="container hero-content">
        <?php if ($hero['crumbs']): ?>
            <nav aria-label="breadcrumb" class="hero-crumbs">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= url() ?>">Home</a></li>
                    <?php foreach ($hero['crumbs'] as $label => $route): ?>
                        <?php if ($route === null): ?>
                            <li class="breadcrumb-item active" aria-current="page"><?= e($label) ?></li>
                        <?php else: ?>
                            <li class="breadcrumb-item"><a href="<?= url($route) ?>"><?= e($label) ?></a></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ol>
            </nav>
        <?php endif; ?>
        <?php if ($hero['kicker'] !== ''): ?>
            <span class="hero-kicker reveal"><?= e($hero['kicker']) ?></span>
        <?php endif; ?>
        <h1 class="hero-title reveal"><?= $hero['title'] ?></h1>
        <?php if ($hero['text'] !== ''): ?>
            <p class="hero-text reveal"><?= e($hero['text']) ?></p>
        <?php endif; ?>
        <?php if ($hero['buttons']): ?>
            <div class="hero-actions reveal">
                <?php foreach ($hero['buttons'] as $b): ?>
                    <a href="<?= e($b['url']) ?>" class="btn <?= ($b['style'] ?? 'lah') === 'lah' ? 'btn-lah' : 'btn-outline-light' ?> btn-lg"><?= $b['label'] ?></a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
