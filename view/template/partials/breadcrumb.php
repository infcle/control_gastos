<!-- Breadcrumb -->
<div class="content-header">
   <div class="d-flex align-items-center">
      <div class="me-auto">
         <h4 class="page-title"><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Dashboard'; ?></h4>
         <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
               <?php if (isset($breadcrumb) && is_array($breadcrumb)): ?>
                  <?php foreach ($breadcrumb as $index => $item): ?>
                     <li class="breadcrumb-item <?php echo $index === array_key_last($breadcrumb) ? 'active' : ''; ?>">
                        <?php if (!empty($item['url']) && $index !== array_key_last($breadcrumb)): ?>
                           <a href="<?php echo $item['url']; ?>"><?php echo htmlspecialchars($item['name']); ?></a>
                        <?php else: ?>
                           <?php echo htmlspecialchars($item['name']); ?>
                        <?php endif; ?>
                     </li>
                  <?php endforeach; ?>
               <?php else: ?>
                  <li class="breadcrumb-item active">Dashboard</li>
               <?php endif; ?>
            </ol>
         </nav>
      </div>
   </div>
</div>
