<!-- User Errors -->
<?php if (!empty($user->errors)): ?>
   <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <?php foreach ($user->errors as $error): ?>
         <?php echo $error; ?><br>
      <?php endforeach; ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
   </div>
<?php endif; ?>

<!-- User Form -->
<div class="row">
   <div class="col-lg-8">
      <div class="card">
         <div class="card-header">
            <h5 class="mb-0"><?php echo isset($userData) ? 'Editar Usuario' : 'Crear Nuevo Usuario'; ?></h5>
         </div>
         <div class="card-body">
            <form method="post" action="<?php echo BASE_URL; ?>user/<?php echo isset($userData) ? 'edit/' . $userData['id_user'] : 'create'; ?>" class="needs-validation" novalidate>
               <div class="row">
                  <div class="col-md-6">
                     <div class="form-group">
                        <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="username" name="username" 
                               value="<?php echo isset($userData) ? htmlspecialchars($userData['username']) : (isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''); ?>" 
                               placeholder="Ingrese nombre de usuario" required minlength="3" maxlength="50">
                        <div class="invalid-feedback">
                           Por favor ingrese un nombre de usuario válido (mínimo 3 caracteres).
                        </div>
                     </div>
                  </div>
                  <div class="col-md-6">
                     <div class="form-group">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?php echo isset($userData) ? htmlspecialchars($userData['email']) : (isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''); ?>" 
                               placeholder="Ingrese email" required>
                        <div class="invalid-feedback">
                           Por favor ingrese un email válido.
                        </div>
                     </div>
                  </div>
               </div>

               <?php if (!isset($userData)): ?>
                  <div class="row">
                     <div class="col-md-6">
                        <div class="form-group">
                           <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                           <input type="password" class="form-control" id="password" name="password" 
                                  placeholder="Ingrese contraseña" required minlength="6" maxlength="255">
                           <small class="form-text text-muted">Mínimo 6 caracteres</small>
                           <div class="invalid-feedback">
                              La contraseña debe tener al menos 6 caracteres.
                           </div>
                        </div>
                     </div>
                     <div class="col-md-6">
                        <div class="form-group">
                           <label for="confirm_password" class="form-label">Confirmar Password <span class="text-danger">*</span></label>
                           <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                  placeholder="Confirme contraseña" required minlength="6" maxlength="255">
                           <div class="invalid-feedback">
                              Las contraseñas no coinciden.
                           </div>
                        </div>
                     </div>
                  </div>
               <?php endif; ?>

               <div class="row">
                  <div class="col-md-6">
                     <div class="form-group">
                        <label for="id_rol" class="form-label">Rol <span class="text-danger">*</span></label>
                        <select class="form-select" id="id_rol" name="id_rol" required>
                           <option value="">Seleccione un rol</option>
                           <?php
                           $roles = $user->getAllRoles();
                           foreach ($roles as $role):
                           ?>
                           <option value="<?php echo $role['id_rol']; ?>" 
                                   <?php echo (isset($userData) && $userData['id_rol'] == $role['id_rol']) || (isset($_POST['id_rol']) && $_POST['id_rol'] == $role['id_rol']) ? 'selected' : ''; ?>>
                                   <?php echo htmlspecialchars($role['name']); ?>
                           </option>
                           <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">
                           Por favor seleccione un rol.
                        </div>
                     </div>
                  </div>
                  <?php if (isset($userData)): ?>
                     <div class="col-md-6">
                        <div class="form-group">
                           <label for="status" class="form-label">Estado</label>
                           <select class="form-select" id="status" name="status">
                              <option value="1" <?php echo (isset($userData) && $userData['status'] == 1) || (isset($_POST['status']) && $_POST['status'] == 1) ? 'selected' : ''; ?>>Activo</option>
                              <option value="0" <?php echo (isset($userData) && $userData['status'] == 0) || (isset($_POST['status']) && $_POST['status'] == 0) ? 'selected' : ''; ?>>Inactivo</option>
                           </select>
                        </div>
                     </div>
                  <?php endif; ?>
               </div>

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
                           <?php echo isset($userData) ? 'Actualizar Usuario' : 'Crear Usuario'; ?>
                        </button>
                        <a href="<?php echo BASE_URL; ?>user" class="btn btn-secondary ms-2">
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
         // Password confirmation validation
         var password = document.getElementById('password');
         var confirmPassword = document.getElementById('confirm_password');
         
         if (password && confirmPassword) {
            if (password.value !== confirmPassword.value) {
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
            var password = document.getElementById('password');
            if (password.value !== confirmPassword.value) {
               confirmPassword.setCustomValidity('Las contraseñas no coinciden');
            } else {
               confirmPassword.setCustomValidity('');
            }
         });
      }
   });
})();
</script>
