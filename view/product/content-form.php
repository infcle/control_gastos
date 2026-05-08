<!-- Product Errors -->
<?php if (!empty($product->errors)): ?>
   <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <?php foreach ($product->errors as $error): ?>
         <?php echo $error; ?><br>
      <?php endforeach; ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
   </div>
<?php endif; ?>

<!-- Product Form -->
<div class="row">
   <div class="col-lg-8">
      <div class="card">
         <div class="card-header">
            <h5 class="mb-0"><?php echo isset($productData) ? 'Editar Producto' : 'Crear Nuevo Producto'; ?></h5>
         </div>
         <div class="card-body">
            <form method="post"
                  action="<?php echo CONTROLLER_URL; ?>product/?action=<?php echo isset($productData) ? 'edit&id=' . htmlspecialchars($productData['id_product']) : 'create'; ?>"
                  class="needs-validation" novalidate>

               <?php if (isset($productData)): ?>
                  <input type="hidden" name="id" value="<?php echo htmlspecialchars($productData['id_product']); ?>">
               <?php endif; ?>

               <!-- Nombre -->
               <div class="row">
                  <div class="col-md-12">
                     <div class="form-group mb-3">
                        <label for="name" class="form-label">Nombre <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name"
                               value="<?php echo isset($productData) ? htmlspecialchars($productData['name']) : (isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''); ?>"
                               placeholder="Ingrese nombre del producto" required maxlength="255">
                        <div class="invalid-feedback">
                           Por favor ingrese un nombre de producto válido.
                        </div>
                     </div>
                  </div>
               </div>

               <!-- Descripción -->
               <div class="row">
                  <div class="col-md-12">
                     <div class="form-group mb-3">
                        <label for="description" class="form-label">Descripción</label>
                        <textarea class="form-control" id="description" name="description"
                                  rows="4" placeholder="Ingrese descripción del producto (opcional)"><?php echo isset($productData) ? htmlspecialchars($productData['description'] ?? '') : (isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''); ?></textarea>
                     </div>
                  </div>
               </div>

               <!-- Precio (solo en modo creación) -->
               <?php if (!isset($productData)): ?>
                  <div class="row">
                     <div class="col-md-6">
                        <div class="form-group mb-3">
                           <label for="price" class="form-label">Precio <span class="text-danger">*</span></label>
                           <input type="number" class="form-control" id="price" name="price"
                                  value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price']) : ''; ?>"
                                  placeholder="0.00" step="0.01" min="0.01" required>
                           <div class="invalid-feedback">
                              Por favor ingrese un precio válido (mayor a 0).
                           </div>
                        </div>
                     </div>
                  </div>
               <?php endif; ?>

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
                           <?php echo isset($productData) ? 'Actualizar Producto' : 'Crear Producto'; ?>
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
