<?php
$profilePic = isset($userData['profile_picture']) && !empty($userData['profile_picture'])
    ? ASSETS_URL . 'uploads/profiles/' . htmlspecialchars($userData['profile_picture'])
    : ASSETS_URL . 'images/avatars/01.png';
?>

<!-- Success/Error Messages -->
<?php if (isset($_GET['success'])): ?>
   <div class="alert alert-success alert-dismissible fade show" role="alert">
      <?php
      switch ($_GET['success']) {
         case 'updated': echo 'Perfil actualizado exitosamente.'; break;
         case 'photo_updated': echo 'Foto de perfil actualizada exitosamente.'; break;
         default: echo 'Operación realizada exitosamente.';
      }
      ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
   </div>
<?php endif; ?>

<!-- User Errors -->
<?php if (!empty($user->errors)): ?>
   <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <?php foreach ($user->errors as $error): ?>
         <?php echo $error; ?><br>
      <?php endforeach; ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
   </div>
<?php endif; ?>

<?php if (!empty($user->messages)): ?>
   <div class="alert alert-success alert-dismissible fade show" role="alert">
      <?php foreach ($user->messages as $msg): ?>
         <?php echo $msg; ?><br>
      <?php endforeach; ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
   </div>
<?php endif; ?>

<div class="row">
   <!-- Photo Card -->
   <div class="col-lg-4">
      <div class="card">
         <div class="card-body text-center">
            <div class="mb-3">
               <img id="profilePreview" src="<?php echo $profilePic; ?>" alt="Foto de perfil"
                    class="img-fluid rounded-circle avatar-120 shadow" style="width: 120px; height: 120px; object-fit: cover;">
            </div>
            <h5 class="mb-1"><?php echo htmlspecialchars($userData['username']); ?></h5>
            <p class="text-muted mb-3"><?php echo htmlspecialchars($userData['email']); ?></p>
            <span class="badge bg-info text-white mb-3"><?php echo htmlspecialchars($userData['role_name']); ?></span>

            <form method="post" action="<?php echo CONTROLLER_URL; ?>user/?action=upload_profile_picture" enctype="multipart/form-data" class="mt-3">
               <div class="mb-3">
                  <input type="file" class="form-control form-control-sm" id="profile_picture" name="profile_picture"
                         accept="image/jpeg,image/png,image/gif,image/webp" onchange="previewFile(this)">
                  <small class="text-muted">JPG, PNG, GIF o WebP. Máx. 2MB.</small>
               </div>
               <button type="submit" class="btn btn-primary btn-sm w-100">
                  <i class="me-1">
                     <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                        <circle cx="12" cy="13" r="4"/>
                     </svg>
                  </i>
                  Subir Foto
               </button>
            </form>
         </div>
      </div>
   </div>

   <!-- Profile Form -->
   <div class="col-lg-8">
      <div class="card">
         <div class="card-header">
            <h5 class="mb-0">Información del Perfil</h5>
         </div>
         <div class="card-body">
            <form method="post" action="<?php echo CONTROLLER_URL; ?>user/?action=profile" class="needs-validation" novalidate>
               <div class="row">
                  <div class="col-md-6">
                     <div class="form-group mb-3">
                        <label for="username" class="form-label">Nombre de Usuario <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="username" name="username"
                               value="<?php echo htmlspecialchars($userData['username']); ?>"
                               placeholder="Ingrese nombre de usuario" required minlength="3" maxlength="50">
                        <div class="invalid-feedback">
                           Por favor ingrese un nombre de usuario válido.
                        </div>
                     </div>
                  </div>
                  <div class="col-md-6">
                     <div class="form-group mb-3">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email"
                               value="<?php echo htmlspecialchars($userData['email']); ?>"
                               placeholder="Ingrese email" required>
                        <div class="invalid-feedback">
                           Por favor ingrese un email válido.
                        </div>
                     </div>
                  </div>
               </div>

               <div class="row">
                  <div class="col-12">
                     <div class="form-group">
                        <button type="submit" name="update_profile" class="btn btn-primary">
                           <i class="me-2">
                              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                 <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                                 <polyline points="17 21 17 13 7 13 7 21"/>
                                 <polyline points="7 3 7 8 15 8"/>
                              </svg>
                           </i>
                           Guardar Cambios
                        </button>
                        <a href="<?php echo CONTROLLER_URL; ?>user/?action=change_password&id=<?php echo $userData['id_user']; ?>" class="btn btn-secondary ms-2">
                           <i class="me-2">
                              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                 <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                 <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                              </svg>
                           </i>
                           Cambiar Contraseña
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
function previewFile(input) {
   var preview = document.getElementById('profilePreview');
   if (input.files && input.files[0]) {
      var reader = new FileReader();
      reader.onload = function(e) {
         preview.src = e.target.result;
      };
      reader.readAsDataURL(input.files[0]);
   }
}

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
