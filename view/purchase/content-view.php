<!-- Success/Error Messages -->
<?php if (isset($_GET['success'])): ?>
   <div class="alert alert-success alert-dismissible fade show" role="alert">
      <?php echo htmlspecialchars($_GET['success']); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
   </div>
<?php endif; ?>

<!-- Purchase Detail -->
<div class="row">
   <div class="col-lg-12">
      <div class="card">
         <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Detalle de Compra #<?php echo $purchaseData['id_purchase']; ?></h5>
            <a href="<?php echo BASE_URL; ?>purchase" class="btn btn-secondary btn-sm">
               <i class="me-2">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                     <line x1="18" y1="6" x2="6" y2="18"/>
                     <line x1="6" y1="6" x2="18" y2="18"/>
                  </svg>
               </i>
               Volver
            </a>
         </div>
         <div class="card-body">
            <!-- Purchase Info -->
            <div class="row mb-4">
               <div class="col-md-4">
                  <strong>Fecha:</strong>
                  <p><?php echo date('d/m/Y', strtotime($purchaseData['purchase_date'])); ?></p>
               </div>
               <div class="col-md-4">
                  <strong>Registrado por:</strong>
                  <p><?php echo htmlspecialchars($purchaseData['user_name']); ?></p>
               </div>
               <div class="col-md-4">
                  <strong>Fecha de registro:</strong>
                  <p><?php echo date('d/m/Y H:i', strtotime($purchaseData['created_at'])); ?></p>
               </div>
            </div>

            <?php if (!empty($purchaseData['observation'])): ?>
               <div class="row mb-4">
                  <div class="col-12">
                     <strong>Observación:</strong>
                     <p><?php echo htmlspecialchars($purchaseData['observation']); ?></p>
                  </div>
               </div>
            <?php endif; ?>

            <!-- Details Table -->
            <h6 class="mb-3">Productos</h6>
            <div class="table-responsive">
               <table class="table table-striped table-hover">
                  <thead>
                     <tr>
                        <th>Producto</th>
                        <th>Proveedor</th>
                        <th>Cantidad</th>
                        <th>Precio Unit.</th>
                        <th>Subtotal</th>
                        <th>Observación</th>
                     </tr>
                  </thead>
                  <tbody>
                     <?php if (!empty($purchaseData['details'])): ?>
                        <?php $total = 0; ?>
                        <?php foreach ($purchaseData['details'] as $detail): ?>
                           <?php 
                           $subtotal = $detail['quantity'] * $detail['unit_price'];
                           $total += $subtotal;
                           ?>
                           <tr>
                              <td><?php echo htmlspecialchars($detail['product_name']); ?></td>
                              <td><?php echo htmlspecialchars($detail['supplier_name']); ?></td>
                              <td><?php echo number_format($detail['quantity'], 2); ?></td>
                              <td>$<?php echo number_format($detail['unit_price'], 2); ?></td>
                              <td>$<?php echo number_format($subtotal, 2); ?></td>
                              <td>
                                 <?php if (!empty($detail['observation'])): ?>
                                    <span title="<?php echo htmlspecialchars($detail['observation']); ?>">
                                       <?php echo htmlspecialchars(substr($detail['observation'], 0, 30)) . (strlen($detail['observation']) > 30 ? '...' : ''); ?>
                                    </span>
                                 <?php else: ?>
                                    <span class="text-muted">—</span>
                                 <?php endif; ?>
                              </td>
                           </tr>
                        <?php endforeach; ?>
                        <tr class="table-active">
                           <td colspan="4" class="text-end"><strong>Total:</strong></td>
                           <td colspan="2"><strong>$<?php echo number_format($total, 2); ?></strong></td>
                        </tr>
                     <?php else: ?>
                        <tr>
                           <td colspan="6" class="text-center py-4">
                              <p class="text-muted mb-0">No hay productos en esta compra.</p>
                           </td>
                        </tr>
                     <?php endif; ?>
                  </tbody>
               </table>
            </div>

            <div class="mt-3">
               <a href="<?php echo BASE_URL; ?>purchase/delete/<?php echo $purchaseData['id_purchase']; ?>" 
                  class="btn btn-danger" 
                  onclick="return confirm('¿Está seguro de eliminar esta compra? Se eliminarán todos sus detalles.')">
                  <i class="me-2">
                     <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                     </svg>
                  </i>
                  Eliminar Compra
               </a>
               <a href="<?php echo BASE_URL; ?>purchase" class="btn btn-secondary ms-2">
                  Volver a Compras
               </a>
            </div>
         </div>
      </div>
   </div>
</div>
