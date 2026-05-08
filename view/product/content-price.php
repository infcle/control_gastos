<!-- Product Errors -->
<?php if (!empty($product->errors)): ?>
   <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <?php foreach ($product->errors as $error): ?>
         <?php echo $error; ?><br>
      <?php endforeach; ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
   </div>
<?php endif; ?>

<!-- Price Update Form -->
<div class="row">
   <div class="col-lg-6">
      <div class="card">
         <div class="card-header">
            <h5 class="mb-0">Actualizar Precio</h5>
            <?php if (isset($productData)): ?>
               <p class="text-muted mb-0 mt-1">Producto: <strong><?php echo htmlspecialchars($productData['name']); ?></strong></p>
            <?php endif; ?>
         </div>
         <div class="card-body">
            <form method="post"
                  action="<?php echo CONTROLLER_URL; ?>product/?action=update_price&id=<?php echo htmlspecialchars($productData['id_product']); ?>"
                  class="needs-validation" novalidate>

               <input type="hidden" name="id" value="<?php echo htmlspecialchars($productData['id_product']); ?>">

               <!-- Precio -->
               <div class="row">
                  <div class="col-md-12">
                     <div class="form-group mb-3">
                        <label for="price" class="form-label">Nuevo Precio <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="price" name="price"
                               value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price']) : ''; ?>"
                               placeholder="0.00" step="0.01" min="0.01" required>
                        <div class="invalid-feedback">
                           Por favor ingrese un precio válido (mayor a 0).
                        </div>
                     </div>
                  </div>
               </div>

               <!-- Botones -->
               <div class="row">
                  <div class="col-12">
                     <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                           <i class="me-2">
                              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                 <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                                 <polyline points="17 21 17 13 7 13 7 21"/>
                                 <polyline points="7 3 7 8 15 8"/>
                              </svg>
                           </i>
                           Guardar Precio
                        </button>
                        <a href="<?php echo CONTROLLER_URL; ?>product/?action=list" class="btn btn-secondary ms-2">
                           <i class="me-2">
                              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                 <line x1="18" y1="6" x2="6" y2="18"/>
                                 <line x1="6" y1="6" x2="18" y2="18"/>
                              </svg>
                           </i>
                           Cancelar
                        </a>
                     </div>
                  </div>
               </div>

            </form>
         </div>
      </div>
   </div>

   <!-- Price History -->
   <div class="col-lg-6">
      <div class="card">
         <div class="card-header">
            <h5 class="mb-0">Historial de Precios</h5>
         </div>
         <div class="card-body px-0">
            <div class="table-responsive">
               <table class="table table-striped" role="grid">
                  <thead>
                     <tr class="ligth">
                        <th>Precio</th>
                        <th>Fecha</th>
                     </tr>
                  </thead>
                  <tbody>
                     <?php if (!empty($priceHistory)): ?>
                        <?php foreach ($priceHistory as $history_item): ?>
                           <tr>
                              <td><?php echo htmlspecialchars(number_format($history_item['price'], 2)); ?></td>
                              <td><?php echo htmlspecialchars($history_item['created_at']); ?></td>
                           </tr>
                        <?php endforeach; ?>
                     <?php else: ?>
                        <tr>
                           <td colspan="2" class="text-center py-4">
                              <p class="text-muted mb-0">Sin historial de precios.</p>
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

<script>
// Form validation
(function() {
   'use strict';

   var forms = document.querySelectorAll('.needs-validation');

   Array.prototype.slice.call(forms).forEach(function(form) {
      form.addEventListener('submit', function(event) {
         if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
         }
         form.classList.add('was-validated');
      }, false);
   });
})();
</script>
