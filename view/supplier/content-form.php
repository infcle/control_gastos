<!-- Supplier Errors -->
<?php if (!empty($supplier->errors)): ?>
   <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <?php foreach ($supplier->errors as $error): ?>
         <?php echo $error; ?><br>
      <?php endforeach; ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
   </div>
<?php endif; ?>

<!-- Supplier Form -->
<div class="row">
   <div class="col-lg-8">
      <div class="card">
         <div class="card-header">
            <h5 class="mb-0"><?php echo isset($supplierData) ? 'Editar Proveedor' : 'Nuevo Proveedor'; ?></h5>
         </div>
         <div class="card-body">
            <form method="post" action="<?php echo BASE_URL; ?>supplier/<?php echo isset($supplierData) ? 'edit/' . $supplierData['id_supplier'] : 'create'; ?>" class="needs-validation" novalidate>
               <div class="row">
                  <div class="col-md-6">
                     <div class="form-group">
                        <label for="name" class="form-label">Nombre <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name"
                               value="<?php echo isset($supplierData) ? htmlspecialchars($supplierData['name']) : (isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''); ?>"
                               placeholder="Ingrese nombre del proveedor" required maxlength="255">
                        <div class="invalid-feedback">
                           Por favor ingrese un nombre para el proveedor.
                        </div>
                     </div>
                  </div>
               </div>

               <div class="row mt-3">
                  <div class="col-md-8">
                     <div class="form-group">
                        <label for="location" class="form-label">Ubicación</label>
                        <textarea class="form-control" id="location" name="location" rows="3"
                                  placeholder="Ubicación opcional del proveedor"><?php echo isset($supplierData) ? htmlspecialchars($supplierData['location']) : (isset($_POST['location']) ? htmlspecialchars($_POST['location']) : ''); ?></textarea>
                     </div>
                  </div>
               </div>

               <div class="row mt-4">
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
                           <?php echo isset($supplierData) ? 'Actualizar Proveedor' : 'Crear Proveedor'; ?>
                        </button>
                        <a href="<?php echo BASE_URL; ?>supplier" class="btn btn-secondary ms-2">
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
