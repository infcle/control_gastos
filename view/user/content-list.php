<!-- Success/Error Messages -->
<?php if (isset($_GET['success'])): ?>
   <div class="alert alert-success alert-dismissible fade show" role="alert">
      <?php
      switch ($_GET['success']) {
         case 'created': echo 'Usuario creado exitosamente.'; break;
         case 'updated': echo 'Usuario actualizado exitosamente.'; break;
         case 'deleted': echo 'Usuario eliminado exitosamente.'; break;
         case 'status_toggled': echo 'Estado del usuario actualizado exitosamente.'; break;
         case 'password_changed': echo 'Contraseña actualizada exitosamente.'; break;
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
         case 'delete_failed': echo 'Error al eliminar el usuario.'; break;
         case 'status_failed': echo 'Error al cambiar el estado del usuario.'; break;
         default: echo 'Error en la operación.';
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

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
   <div>
      <h4 class="mb-1">Users Management</h4>
      <p class="text-muted mb-0">Manage system users and their permissions</p>
   </div>
   <a href="<?php echo BASE_URL; ?>user/create" class="btn btn-primary">
      <i class="me-2">
         <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <line x1="12" y1="5" x2="12" y2="19"/>
            <line x1="5" y1="12" x2="19" y2="12"/>
         </svg>
      </i>
      Nuevo Usuario
   </a>
</div>

<!-- Users Table -->
<div class="card">
   <div class="card-header d-flex justify-content-between">
      <div class="header-title">
         <h4 class="card-title">User List</h4>
      </div>
   </div>
   <div class="card-body px-0">
      <div class="table-responsive">
         <table id="user-list-table" class="table table-striped" role="grid">
            <thead>
               <tr class="ligth">
                  <th>Profile</th>
                  <th>Username</th>
                  <th>Email</th>
                  <th>Role</th>
                  <th>Status</th>
                  <th style="min-width: 200px">Action</th>
               </tr>
            </thead>
            <tbody>
               <?php if (!empty($users)): ?>
                  <?php foreach ($users as $user_item): ?>
                     <tr>
                        <td class="text-center">
                           <img class="bg-soft-primary rounded img-fluid avatar-40" src="<?php echo ASSETS_URL; ?>images/avatars/01.png" alt="profile">
                        </td>
                        <td><?php echo htmlspecialchars($user_item['username']); ?></td>
                        <td><?php echo htmlspecialchars($user_item['email']); ?></td>
                        <td>
                           <span class="badge bg-info text-white">
                              <?php echo htmlspecialchars($user_item['role_name']); ?>
                           </span>
                        </td>
                        <td>
                           <span class="badge bg-<?php echo $user_item['status'] == 1 ? 'success' : 'danger'; ?>">
                              <?php echo $user_item['status'] == 1 ? 'Active' : 'Inactive'; ?>
                           </span>
                        </td>
                        <td>
                           <div class="flex align-items-center list-user-action">
                              <a class="btn btn-sm btn-icon btn-warning" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit" href="<?php echo BASE_URL; ?>user/edit/<?php echo $user_item['id_user']; ?>">
                                 <span class="btn-inner">
                                    <svg class="icon-20" width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                       <path d="M11.4925 2.78906H7.75349C4.67849 2.78906 2.75049 4.96606 2.75049 8.04806V16.3621C2.75049 19.4441 4.66949 21.6211 7.75349 21.6211H16.5775C19.6625 21.6211 21.5815 19.4441 21.5815 16.3621V12.3341" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                       <path fill-rule="evenodd" clip-rule="evenodd" d="M8.82812 10.921L16.3011 3.44799C17.2321 2.51799 18.7411 2.51799 19.6721 3.44799L20.8891 4.66499C21.8201 5.59599 21.8201 7.10599 20.8891 8.03599L13.3801 15.545C12.9731 15.952 12.4211 16.181 11.8451 16.181H8.09912L8.19312 12.401C8.20712 11.845 8.43412 11.315 8.82812 10.921Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                       <path d="M15.1655 4.60254L19.7315 9.16854" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                    </svg>
                                 </span>
                              </a>
                              <?php if ($user_item['id_user'] != 1): ?>
                                 <a class="btn btn-sm btn-icon btn-<?php echo $user_item['status'] == 1 ? 'secondary' : 'success'; ?>" data-bs-toggle="tooltip" data-bs-placement="top" title="<?php echo $user_item['status'] == 1 ? 'Deactivate' : 'Activate'; ?>" href="<?php echo BASE_URL; ?>user/toggle-status/<?php echo $user_item['id_user']; ?>">
                                    <span class="btn-inner">
                                       <svg class="icon-20" width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                          <path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                          <path d="M15 12H9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                       </svg>
                                    </span>
                                 </a>
                                 <a class="btn btn-sm btn-icon btn-info" data-bs-toggle="tooltip" data-bs-placement="top" title="Change Password" href="<?php echo BASE_URL; ?>user/change-password/<?php echo $user_item['id_user']; ?>">
                                    <span class="btn-inner">
                                       <svg class="icon-20" width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                          <path d="M18 7.5C18 5.01 15.99 3 13.5 3C11.01 3 9 5.01 9 7.5C9 8.34 9.23 9.12 9.63 9.79C8.73 10.36 8.13 11.35 8.13 12.5V20.5C8.13 22.16 9.47 23.5 11.13 23.5H15.87C17.53 23.5 18.87 22.16 18.87 20.5V12.5C18.87 11.35 18.27 10.36 17.37 9.79C17.77 9.12 18 8.34 18 7.5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                          <path d="M13.5 7.5C13.5 8.33 12.83 9 12 9C11.17 9 10.5 8.33 10.5 7.5C10.5 6.67 11.17 6 12 6C12.83 6 13.5 6.67 13.5 7.5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                       </svg>
                                    </span>
                                 </a>
                                 <a class="btn btn-sm btn-icon btn-danger" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete" href="#" onclick="if(confirm('Are you sure you want to delete this user?')) { window.location.href='<?php echo BASE_URL; ?>user/delete/<?php echo $user_item['id_user']; ?>'; }">
                                    <span class="btn-inner">
                                       <svg class="icon-20" width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="currentColor">
                                          <path d="M19.3248 9.46826C19.3248 9.46826 18.7818 16.2033 18.4668 19.0403C18.3168 20.3953 17.4798 21.1893 16.1088 21.2143C13.4998 21.2613 10.8878 21.2643 8.27979 21.2093C6.96079 21.1823 6.13779 20.3783 5.99079 19.0473C5.67379 16.1853 5.13379 9.46826 5.13379 9.46826" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                          <path d="M20.708 6.23975H3.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                          <path d="M17.4406 6.23973C16.6556 6.23973 15.9796 5.68473 15.8256 4.91573L15.5826 3.69973C15.4326 3.13873 14.9246 2.75073 14.3456 2.75073H10.1126C9.53358 2.75073 9.02558 3.13873 8.87558 3.69973L8.63258 4.91573C8.47858 5.68473 7.80258 6.23973 7.01758 6.23973" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                       </svg>
                                    </span>
                                 </a>
                              <?php endif; ?>
                           </div>
                        </td>
                     </tr>
                  <?php endforeach; ?>
               <?php else: ?>
                  <tr>
                     <td colspan="6" class="text-center py-4">
                        <p class="text-muted mb-0">No users found.</p>
                        <a href="<?php echo BASE_URL; ?>user/create" class="btn btn-primary btn-sm mt-2">
                           Create First User
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
