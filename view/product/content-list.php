<!-- Success/Error Messages -->
<?php if (isset($_GET['success'])): ?>
   <div class="alert alert-success alert-dismissible fade show" role="alert">
      <?php
      switch ($_GET['success']) {
         case 'created':       echo 'Producto creado exitosamente.'; break;
         case 'updated':       echo 'Producto actualizado exitosamente.'; break;
         case 'price_updated': echo 'Precio actualizado exitosamente.'; break;
         case 'deleted':       echo 'Producto eliminado exitosamente.'; break;
         default:              echo 'Operación realizada exitosamente.';
      }
      ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
   </div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
   <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <?php
      switch ($_GET['error']) {
         case 'delete_failed': echo 'Error al eliminar el producto.'; break;
         default:              echo 'Error en la operación.';
      }
      ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
   </div>
<?php endif; ?>

<!-- Product Errors -->
<?php if (!empty($product->errors)): ?>
   <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <?php foreach ($product->errors as $error): ?>
         <?php echo $error; ?><br>
      <?php endforeach; ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
   </div>
<?php endif; ?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
   <div>
      <h4 class="mb-1">Gestión de Productos</h4>
      <p class="text-muted mb-0">Administra el catálogo de productos y sus precios</p>
   </div>
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

<!-- Products Table -->
<div class="card">
   <div class="card-header d-flex justify-content-between">
      <div class="header-title">
         <h4 class="card-title">Lista de Productos</h4>
      </div>
   </div>
   <div class="card-body px-0">
      <div class="table-responsive">
         <table id="product-list-table" class="table table-striped" role="grid">
            <thead>
               <tr class="ligth">
                  <th>Nombre</th>
                  <th>Descripción</th>
                  <th>Precio actual</th>
                  <th>Fecha de creación</th>
                  <th style="min-width: 200px">Acciones</th>
               </tr>
            </thead>
            <tbody>
               <?php if (!empty($products)): ?>
                  <?php foreach ($products as $product_item): ?>
                     <tr>
                        <td><?php echo htmlspecialchars($product_item['name']); ?></td>
                        <td><?php echo htmlspecialchars($product_item['description'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars(number_format($product_item['price'], 2)); ?></td>
                        <td><?php echo htmlspecialchars($product_item['created_at']); ?></td>
                        <td>
                           <div class="flex align-items-center list-user-action">
                              <!-- Editar -->
                              <a class="btn btn-sm btn-icon btn-warning" data-bs-toggle="tooltip" data-bs-placement="top" title="Editar"
                                 href="<?php echo CONTROLLER_URL; ?>product/?action=edit&id=<?php echo htmlspecialchars($product_item['id_product']); ?>">
                                 <span class="btn-inner">
                                    <svg class="icon-20" width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                       <path d="M11.4925 2.78906H7.75349C4.67849 2.78906 2.75049 4.96606 2.75049 8.04806V16.3621C2.75049 19.4441 4.66949 21.6211 7.75349 21.6211H16.5775C19.6625 21.6211 21.5815 19.4441 21.5815 16.3621V12.3341" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                       <path fill-rule="evenodd" clip-rule="evenodd" d="M8.82812 10.921L16.3011 3.44799C17.2321 2.51799 18.7411 2.51799 19.6721 3.44799L20.8891 4.66499C21.8201 5.59599 21.8201 7.10599 20.8891 8.03599L13.3801 15.545C12.9731 15.952 12.4211 16.181 11.8451 16.181H8.09912L8.19312 12.401C8.20712 11.845 8.43412 11.315 8.82812 10.921Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                       <path d="M15.1655 4.60254L19.7315 9.16854" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                    </svg>
                                 </span>
                              </a>
                              <!-- Actualizar precio -->
                              <a class="btn btn-sm btn-icon btn-info" data-bs-toggle="tooltip" data-bs-placement="top" title="Actualizar Precio"
                                 href="<?php echo CONTROLLER_URL; ?>product/?action=update_price&id=<?php echo htmlspecialchars($product_item['id_product']); ?>">
                                 <span class="btn-inner">
                                    <svg class="icon-20" width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                       <path d="M12 2C6.48 2 2 6.48 2 12C2 17.52 6.48 22 12 22C17.52 22 22 17.52 22 12C22 6.48 17.52 2 12 2Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                       <path d="M15 8.5C14.315 7.81 13.385 7.5 12 7.5C9.515 7.5 8 9.015 8 11.5C8 13.985 9.515 15.5 12 15.5C13.385 15.5 14.315 15.19 15 14.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                       <path d="M12 7.5V6M12 18V16.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                    </svg>
                                 </span>
                              </a>
                              <!-- Eliminar -->
                              <a class="btn btn-sm btn-icon btn-danger" data-bs-toggle="tooltip" data-bs-placement="top" title="Eliminar"
                                 href="#"
                                 onclick="if(confirm('¿Está seguro de que desea eliminar este producto?')) { window.location.href='<?php echo CONTROLLER_URL; ?>product/?action=delete&id=<?php echo htmlspecialchars($product_item['id_product']); ?>'; }">
                                 <span class="btn-inner">
                                    <svg class="icon-20" width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="currentColor">
                                       <path d="M19.3248 9.46826C19.3248 9.46826 18.7818 16.2033 18.4668 19.0403C18.3168 20.3953 17.4798 21.1893 16.1088 21.2143C13.4998 21.2613 10.8878 21.2643 8.27979 21.2093C6.96079 21.1823 6.13779 20.3783 5.99079 19.0473C5.67379 16.1853 5.13379 9.46826 5.13379 9.46826" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                       <path d="M20.708 6.23975H3.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                       <path d="M17.4406 6.23973C16.6556 6.23973 15.9796 5.68473 15.8256 4.91573L15.5826 3.69973C15.4326 3.13873 14.9246 2.75073 14.3456 2.75073H10.1126C9.53358 2.75073 9.02558 3.13873 8.87558 3.69973L8.63258 4.91573C8.47858 5.68473 7.80258 6.23973 7.01758 6.23973" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                    </svg>
                                 </span>
                              </a>
                           </div>
                        </td>
                     </tr>
                  <?php endforeach; ?>
               <?php else: ?>
                  <tr>
                     <td colspan="5" class="text-center py-4">
                        <p class="text-muted mb-0">No hay productos registrados.</p>
                        <a href="<?php echo CONTROLLER_URL; ?>product/?action=create" class="btn btn-primary btn-sm mt-2">
                           Crear Primer Producto
                        </a>
                     </td>
                  </tr>
               <?php endif; ?>
            </tbody>
         </table>
      </div>
   </div>
</div>

<script>
// Initialize tooltips
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
  return new bootstrap.Tooltip(tooltipTriggerEl)
})
</script>
