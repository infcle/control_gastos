<!-- Category Errors -->
<?php if (!empty($category->errors)): ?>
   <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <?php foreach ($category->errors as $error): ?>
         <?php echo $error; ?><br>
      <?php endforeach; ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
   </div>
<?php endif; ?>

<!-- Category Form -->
<div class="row">
   <div class="col-lg-8">
      <div class="card">
         <div class="card-header">
            <h5 class="mb-0"><?php echo isset($categoryData) ? 'Editar Categoría' : 'Nueva Categoría'; ?></h5>
         </div>
         <div class="card-body">
            <form method="post" action="<?php echo BASE_URL; ?>category/<?php echo isset($categoryData) ? 'edit/' . $categoryData['id_category'] : 'create'; ?>" class="needs-validation" novalidate>
               <div class="row">
                  <div class="col-md-6">
                     <div class="form-group">
                        <label for="name" class="form-label">Nombre <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name"
                               value="<?php echo isset($categoryData) ? htmlspecialchars($categoryData['name']) : (isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''); ?>"
                               placeholder="Ingrese nombre de la categoría" required maxlength="100">
                        <div class="invalid-feedback">
                           Por favor ingrese un nombre para la categoría.
                        </div>
                     </div>
                  </div>
               </div>

               <div class="row mt-3">
                  <div class="col-md-8">
                     <div class="form-group">
                        <label for="description" class="form-label">Descripción</label>
                        <textarea class="form-control" id="description" name="description" rows="3"
                                  placeholder="Descripción opcional de la categoría"><?php echo isset($categoryData) ? htmlspecialchars($categoryData['description']) : (isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''); ?></textarea>
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
                           <?php echo isset($categoryData) ? 'Actualizar Categoría' : 'Crear Categoría'; ?>
                        </button>
                        <a href="<?php echo BASE_URL; ?>category" class="btn btn-secondary ms-2">
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
