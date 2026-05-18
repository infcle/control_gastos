<!-- Success/Error Messages -->
<?php if (isset($_GET['success'])): ?>
   <div class="alert alert-success alert-dismissible fade show" role="alert">
      <?php 
      switch($_GET['success']) {
         case 'created': echo 'Producto creado exitosamente.'; break;
         case 'updated': echo 'Producto actualizado exitosamente.'; break;
         case 'deleted': echo 'Producto eliminado exitosamente.'; break;
         case 'status_toggled': echo 'Estado del producto actualizado exitosamente.'; break;
      }
      ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
   </div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
   <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <?php 
      switch($_GET['error']) {
         case 'not_found': echo 'Producto no encontrado.'; break;
         case 'delete_failed': echo 'Error al eliminar el producto.'; break;
         case 'status_failed': echo 'Error al actualizar el estado del producto.'; break;
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
             <h5 class="mb-0">Lista de Productos</h5>
            <a href="<?php echo CONTROLLER_URL; ?>product/?action=create" class="btn btn-primary">
               <i class="me-2">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                     <line x1="12" y1="5" x2="12" y2="19"/>
                     <line x1="5" y1="12" x2="19" y2="12"/>
                  </svg>
               </i>
               Nuevo Producto
            </a>
         </div>
         <div class="card-body">
            <div class="table-responsive">
               <table class="table table-striped table-hover">
                  <thead>
                      <tr>
                          <th>ID</th>
                          <th>Nombre</th>
                          <th>Descripción</th>
                          <th>Categoría</th>
                          <th>Precio</th>
                          <th>Estado</th>
                          <th>Creado</th>
                          <th>Acciones</th>
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
                                     <span class="badge bg-success">Activo</span>
                                  <?php else: ?>
                                     <span class="badge bg-danger">Inactivo</span>
                                 <?php endif; ?>
                              </td>
                               <td><?php echo date('d/m/Y', strtotime($product['created_at'])); ?></td>
                              <td>
                                 <div class="d-flex gap-1">
                                    <a href="<?php echo CONTROLLER_URL; ?>product/?action=edit&id=<?php echo $product['id_product']; ?>" 
                                        class="btn btn-sm btn-primary" title="Editar">
                                       <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="<?php echo CONTROLLER_URL; ?>product/?action=price_history&id=<?php echo $product['id_product']; ?>" 
                                       class="btn btn-sm btn-info" title="Historial de Precios">
                                       <i class="bi bi-clock-history"></i>
                                    </a>
                                    <button onclick="toggleStatus(<?php echo $product['id_product']; ?>)" 
                                       class="btn btn-sm <?php echo $product['status'] == 1 ? 'btn-warning' : 'btn-success'; ?>" 
                                        title="<?php echo $product['status'] == 1 ? 'Desactivar' : 'Activar'; ?>">
                                       <i class="bi bi-<?php echo $product['status'] == 1 ? 'pause' : 'play'; ?>"></i>
                                    </button>
                                     <button onclick="deleteProduct(<?php echo $product['id_product']; ?>)" 
                                        class="btn btn-sm btn-danger" title="Eliminar">
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
                                  No se encontraron productos
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
   if (confirm('¿Está seguro de cambiar el estado de este producto?')) {
      window.location.href = '<?php echo CONTROLLER_URL; ?>product/?action=toggle_status&id=' + id;
   }
}

function deleteProduct(id) {
   if (confirm('¿Está seguro de eliminar este producto?')) {
      window.location.href = '<?php echo CONTROLLER_URL; ?>product/?action=delete&id=' + id;
   }
}
</script>
