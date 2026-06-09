<!-- Success/Error Messages -->
<?php if (isset($_GET['success'])): ?>
   <div class="alert alert-success alert-dismissible fade show" role="alert">
      <?php 
      switch($_GET['success']) {
         case 'created': echo 'Product created successfully!'; break;
         case 'updated': echo 'Product updated successfully!'; break;
         case 'deleted': echo 'Product deleted successfully!'; break;
         case 'status_toggled': echo 'Product status updated successfully!'; break;
      }
      ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
   </div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
   <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <?php 
      switch($_GET['error']) {
         case 'not_found': echo 'Product not found!'; break;
         case 'delete_failed': echo 'Failed to delete product!'; break;
         case 'status_failed': echo 'Failed to update product status!'; break;
      }
      ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
   </div>
<?php endif; ?>

<!-- Product List -->
<div class="row">
   <div class="col-lg-12">
      <div class="card">
         <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Products List</h5>
            <a href="<?php echo BASE_URL; ?>product/create" class="btn btn-primary">
               <i class="me-2">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                     <line x1="12" y1="5" x2="12" y2="19"/>
                     <line x1="5" y1="12" x2="19" y2="12"/>
                  </svg>
               </i>
               New Product
            </a>
         </div>
         <div class="card-body">
            <div class="table-responsive">
               <table class="table table-striped table-hover">
                  <thead>
                      <tr>
                         <th>ID</th>
                         <th>Name</th>
                         <th>Description</th>
                         <th>Category</th>
                         <th>Price</th>
                         <th>Status</th>
                         <th>Created</th>
                         <th>Actions</th>
                      </tr>
                  </thead>
                  <tbody>
                     <?php if (!empty($products)): ?>
                        <?php foreach ($products as $product): ?>
                           <tr>
                              <td><?php echo $product['id_product']; ?></td>
                              <td>
                                 <div class="d-flex align-items-center">
                                    <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2">
                                       <span class="avatar-text"><?php echo strtoupper(substr($product['name'], 0, 2)); ?></span>
                                    </div>
                                    <span class="fw-medium"><?php echo htmlspecialchars($product['name']); ?></span>
                                 </div>
                              </td>
                               <td>
                                  <span class="text-truncate d-block" style="max-width: 200px;" title="<?php echo htmlspecialchars($product['description']); ?>">
                                     <?php echo htmlspecialchars(substr($product['description'], 0, 50)) . (strlen($product['description']) > 50 ? '...' : ''); ?>
                                  </span>
                               </td>
                               <td>
                                  <?php if (!empty($product['category_name'])): ?>
                                     <span class="badge bg-info"><?php echo htmlspecialchars($product['category_name']); ?></span>
                                  <?php else: ?>
                                     <span class="text-muted">—</span>
                                  <?php endif; ?>
                               </td>
                               <td>
                                  <span class="badge bg-success">$<?php echo number_format($product['price'], 2); ?></span>
                               </td>
                              <td>
                                 <?php if ($product['status'] == 1): ?>
                                    <span class="badge bg-success">Active</span>
                                 <?php else: ?>
                                    <span class="badge bg-danger">Inactive</span>
                                 <?php endif; ?>
                              </td>
                              <td><?php echo date('M d, Y', strtotime($product['created_at'])); ?></td>
                              <td>
                                 <div class="d-flex gap-1">
                                    <a href="<?php echo BASE_URL; ?>product/edit/<?php echo $product['id_product']; ?>" 
                                       class="btn btn-sm btn-primary" title="Edit">
                                       <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="<?php echo BASE_URL; ?>product/price-history/<?php echo $product['id_product']; ?>" 
                                       class="btn btn-sm btn-info" title="Price History">
                                       <i class="bi bi-clock-history"></i>
                                    </a>
                                    <button onclick="toggleStatus(<?php echo $product['id_product']; ?>)" 
                                       class="btn btn-sm <?php echo $product['status'] == 1 ? 'btn-warning' : 'btn-success'; ?>" 
                                       title="<?php echo $product['status'] == 1 ? 'Deactivate' : 'Activate'; ?>">
                                       <i class="bi bi-<?php echo $product['status'] == 1 ? 'pause' : 'play'; ?>"></i>
                                    </button>
                                    <button onclick="deleteProduct(<?php echo $product['id_product']; ?>)" 
                                       class="btn btn-sm btn-danger" title="Delete">
                                       <i class="bi bi-trash"></i>
                                    </button>
                                 </div>
                              </td>
                           </tr>
                        <?php endforeach; ?>
                     <?php else: ?>
                         <tr>
                            <td colspan="7" class="text-center py-4">
                              <div class="text-muted">
                                 <i class="bi bi-box fs-1 d-block mb-2"></i>
                                 No products found
                              </div>
                           </td>
                        </tr>
                     <?php endif; ?>
                  </tbody>
               </table>
            </div>
         </div>
      </div>
   </div>
</div>

<!-- JavaScript for actions -->
<script>
function toggleStatus(id) {
   if (confirm('Are you sure you want to toggle the status of this product?')) {
      window.location.href = '<?php echo BASE_URL; ?>product/toggle-status/' + id;
   }
}

function deleteProduct(id) {
   if (confirm('Are you sure you want to delete this product? This action can be undone.')) {
      window.location.href = '<?php echo BASE_URL; ?>product/delete/' + id;
   }
}
</script>
