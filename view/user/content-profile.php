<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Mi Perfil</h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <img class="rounded-circle bg-soft-primary img-fluid avatar-100"
                         src="<?php echo ASSETS_URL; ?>images/avatars/01.png"
                         alt="profile">
                    <h4 class="mt-3 mb-1"><?php echo htmlspecialchars($userData['username'] ?? ''); ?></h4>
                    <p class="text-muted"><?php echo htmlspecialchars($userData['email'] ?? ''); ?></p>
                </div>
                <table class="table table-borderless">
                    <tr>
                        <th class="ps-0" width="140">Usuario:</th>
                        <td><?php echo htmlspecialchars($userData['username'] ?? ''); ?></td>
                    </tr>
                    <tr>
                        <th class="ps-0">Email:</th>
                        <td><?php echo htmlspecialchars($userData['email'] ?? ''); ?></td>
                    </tr>
                    <tr>
                        <th class="ps-0">Rol:</th>
                        <td><?php echo htmlspecialchars($userData['rol'] ?? ''); ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
