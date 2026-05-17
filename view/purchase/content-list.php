<!-- Success/Error Messages -->
<?php if (isset($_GET['success'])): ?>
   <div class="alert alert-success alert-dismissible fade show" role="alert">
      <?php
      switch ($_GET['success']) {
         case 'created': echo 'Compra creada exitosamente.'; break;
         case 'deleted': echo 'Compra eliminada exitosamente.'; break;
         default: echo 'Operación realizada exitosamente.';
      }
      ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
   </div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
   <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <?php
      switch ($_GET['error']) {
         case 'not_found': echo 'Compra no encontrada.'; break;
         case 'delete_failed': echo 'Error al eliminar la compra.'; break;
         default: echo 'Error en la operación.';
      }
      ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
   </div>
<?php endif; ?>

<!-- Purchase Errors -->
<?php if (!empty($purchase->errors)): ?>
   <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <?php foreach ($purchase->errors as $error): ?>
         <?php echo $error; ?><br>
      <?php endforeach; ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
   </div>
<?php endif; ?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
   <div>
      <h4 class="mb-1">Compras</h4>
      <p class="text-muted mb-0">Registro de compras realizadas</p>
   </div>
   <a href="<?php echo CONTROLLER_URL; ?>purchase/?action=create" class="btn btn-primary">
      <i class="me-2">
         <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <line x1="12" y1="5" x2="12" y2="19"/>
            <line x1="5" y1="12" x2="19" y2="12"/>
         </svg>
      </i>
      Nueva Compra
   </a>
</div>

<!-- Purchases Table -->
<div class="card">
   <div class="card-header d-flex justify-content-between">
      <div class="header-title">
         <h4 class="card-title">Lista de Compras</h4>
      </div>
   </div>
   <div class="card-body px-0">
      <div class="table-responsive">
         <table class="table table-striped" role="grid">
            <thead>
               <tr class="ligth">
                  <th>Fecha</th>
                  <th>Usuario</th>
                  <th>Observación</th>
                  <th># Items</th>
                  <th style="min-width: 150px">Acciones</th>
               </tr>
            </thead>
            <tbody>
               <?php if (!empty($purchases)): ?>
                  <?php foreach ($purchases as $compra): ?>
                     <tr>
                        <td><?php echo date('d/m/Y', strtotime($compra['purchase_date'])); ?></td>
                        <td><?php echo htmlspecialchars($compra['user_name']); ?></td>
                        <td>
                           <?php if (!empty($compra['observation'])): ?>
                              <span class="text-truncate d-block" style="max-width: 250px;" title="<?php echo htmlspecialchars($compra['observation']); ?>">
                                 <?php echo htmlspecialchars(substr($compra['observation'], 0, 60)) . (strlen($compra['observation']) > 60 ? '...' : ''); ?>
                              </span>
                           <?php else: ?>
                              <span class="text-muted">—</span>
                           <?php endif; ?>
                        </td>
                        <td>
                           <span class="badge bg-info"><?php echo $compra['items_count']; ?></span>
                        </td>
                        <td>
                           <div class="flex align-items-center list-user-action">
                              <a class="btn btn-sm btn-icon btn-primary" data-bs-toggle="tooltip" data-bs-placement="top" title="Ver detalle" href="<?php echo CONTROLLER_URL; ?>purchase/?action=view&id=<?php echo $compra['id_purchase']; ?>">
                                 <span class="btn-inner">
                                    <svg class="icon-20" width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="currentColor">
                                       <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                       <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                 </span>
                              </a>
                              <a class="btn btn-sm btn-icon btn-danger" data-bs-toggle="tooltip" data-bs-placement="top" title="Eliminar" href="#" onclick="if(confirm('¿Está seguro de eliminar esta compra? Se eliminarán todos sus detalles.')) { window.location.href='<?php echo CONTROLLER_URL; ?>purchase/?action=delete&id=<?php echo $compra['id_purchase']; ?>'; }">
                                 <span class="btn-inner">
                                    <svg class="icon-20" width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="currentColor">
                                       <path d="M19.3248 9.46826C19.3248 9.46826 18.7818 16.2033 18.4668 19.0403C18.3168 20.3953 17.4798 21.1893 16.1088 21.2143C13.4998 21.2613 10.8878 21.2643 8.27979 21.2093C6.96079 21.1823 6.13779 20.3783 5.99079 19.0473C5.67379 16.1853 5.13379 9.46826 5.13379 9.46826" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                       <path d="M20.708 6.23975H3.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                       <path d="M17.4406 6.23973C16.6556 6.23973 15.9796 5.68473 15.8256 4.91573L15.5826 3.69973C15.4326 3.13873 14.9246 2.75073 14.3456 2.75073H10.1126C9.53358 2.75073 9.02558 3.13873 8.87558 3.69973L8.63258 4.91573C8.47858 5.68473 7.80258 6.23973 7.01758 6.23973" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
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
                        <p class="text-muted mb-0">No hay compras registradas.</p>
                        <a href="<?php echo CONTROLLER_URL; ?>purchase/?action=create" class="btn btn-primary btn-sm mt-2">
                           Registrar Primera Compra
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
