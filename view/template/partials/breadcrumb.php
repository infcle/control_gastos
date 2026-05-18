<!-- Page Header & Breadcrumb -->
<div class="container-fluid iq-container iq-page-header">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">

                <!-- Page Title -->
                <div>
                    <h4 class="page-title">
                        <?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Panel Principal'; ?>
                    </h4>
                    <p class="page-subtitle">
                        <?php
                        if (isset($breadcrumb) && is_array($breadcrumb) && count($breadcrumb) > 1) {
                            $last = end($breadcrumb);
                            echo htmlspecialchars($last['name']);
                        }
                        ?>
                    </p>
                </div>

                <!-- Breadcrumb -->
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb iq-breadcrumb">
                        <?php if (isset($breadcrumb) && is_array($breadcrumb)): ?>
                            <?php foreach ($breadcrumb as $index => $item): ?>
                                <?php $isLast = ($index === array_key_last($breadcrumb)); ?>
                                <li class="breadcrumb-item <?php echo $isLast ? 'active' : ''; ?>"
                                    <?php echo $isLast ? 'aria-current="page"' : ''; ?>>
                                    <?php if (!empty($item['url']) && !$isLast): ?>
                                        <a href="<?php echo $item['url']; ?>">
                                            <?php echo htmlspecialchars($item['name']); ?>
                                        </a>
                                    <?php else: ?>
                                        <span><?php echo htmlspecialchars($item['name']); ?></span>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="breadcrumb-item active" aria-current="page">
                                <span>Panel Principal</span>
                            </li>
                        <?php endif; ?>
                    </ol>
                </nav>

            </div>
        </div>
    </div>
</div>
