<!-- User Errors -->
<?php if (!empty($user->errors)): ?>
   <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <?php foreach ($user->errors as $error): ?>
         <?php echo $error; ?><br>
      <?php endforeach; ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
   </div>
<?php endif; ?>

<!-- Password Change Form -->
<div class="row justify-content-center">
   <div class="col-lg-6">
      <div class="card">
         <div class="card-header">
            <h5 class="mb-0">Cambiar Contraseña</h5>
            <p class="text-muted mb-0 mt-2">Usuario: <strong><?php echo htmlspecialchars($userData['username']); ?></strong></p>
         </div>
         <div class="card-body">
            <form method="post" action="<?php echo CONTROLLER_URL; ?>user/?action=change_password&id=<?php echo $userData['id_user']; ?>" class="needs-validation" novalidate>
               <div class="form-group">
                  <label for="new_password" class="form-label">Nueva Contraseña <span class="text-danger">*</span></label>
                  <input type="password" class="form-control" id="new_password" name="new_password" 
                         placeholder="Ingrese nueva contraseña" required minlength="6" maxlength="255">
                  <small class="form-text text-muted">Mínimo 6 caracteres</small>
                  <div class="invalid-feedback">
                     La contraseña debe tener al menos 6 caracteres.
                  </div>
               </div>
               
               <div class="form-group">
                  <label for="confirm_password" class="form-label">Confirmar Contraseña <span class="text-danger">*</span></label>
                  <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                         placeholder="Confirme nueva contraseña" required minlength="6" maxlength="255">
                  <div class="invalid-feedback">
                     Las contraseñas no coinciden.
                  </div>
               </div>

               <div class="form-group mt-4">
                  <button type="submit" class="btn btn-primary">
                     <i class="me-2">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                           <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                           <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                     </i>
                     Cambiar Contraseña
                  </button>
                  <a href="<?php echo CONTROLLER_URL; ?>user/" class="btn btn-secondary ms-2">
                     <i class="me-2">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                           <line x1="18" y1="6" x2="6" y2="18"/>
                           <line x1="6" y1="6" x2="18" y2="18"/>
                        </svg>
                     </i>
                     Cancelar
                  </a>
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
         // Password confirmation validation
         var newPassword = document.getElementById('new_password');
         var confirmPassword = document.getElementById('confirm_password');
         
         if (newPassword && confirmPassword) {
            if (newPassword.value !== confirmPassword.value) {
               confirmPassword.setCustomValidity('Las contraseñas no coinciden');
            } else {
               confirmPassword.setCustomValidity('');
            }
         }
         
         if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
         }
         
         form.classList.add('was-validated');
      }, false);
      
      // Real-time password confirmation validation
      var confirmPassword = document.getElementById('confirm_password');
      if (confirmPassword) {
         confirmPassword.addEventListener('input', function() {
            var newPassword = document.getElementById('new_password');
            if (newPassword.value !== confirmPassword.value) {
               confirmPassword.setCustomValidity('Las contraseñas no coinciden');
            } else {
               confirmPassword.setCustomValidity('');
            }
         });
      }
   });
})();
</script>
