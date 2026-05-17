<!-- Purchase Errors -->
<?php if (!empty($purchase->errors)): ?>
   <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <?php foreach ($purchase->errors as $error): ?>
         <?php echo $error; ?><br>
      <?php endforeach; ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
   </div>
<?php endif; ?>

<!-- Purchase Messages -->
<?php if (!empty($purchase->messages)): ?>
   <div class="alert alert-success alert-dismissible fade show" role="alert">
      <?php foreach ($purchase->messages as $msg): ?>
         <?php echo $msg; ?><br>
      <?php endforeach; ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
   </div>
<?php endif; ?>

<!-- Purchase Form -->
<div class="row">
   <div class="col-lg-12">
      <div class="card">
         <div class="card-header">
            <h5 class="mb-0">Nueva Compra</h5>
         </div>
         <div class="card-body">
            <form method="post" action="<?php echo CONTROLLER_URL; ?>purchase/?action=create" id="purchaseForm" class="needs-validation" novalidate>

               <!-- Campos de cabecera -->
               <div class="row">
                  <div class="col-md-4">
                     <div class="form-group mb-3">
                        <label for="purchase_date" class="form-label">Fecha de Compra <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="purchase_date" name="purchase_date"
                               value="<?php echo isset($_POST['purchase_date']) ? htmlspecialchars($_POST['purchase_date']) : date('Y-m-d'); ?>"
                               required max="<?php echo date('Y-m-d'); ?>">
                        <div class="invalid-feedback">
                           Por favor ingrese una fecha válida.
                        </div>
                     </div>
                  </div>
                  <div class="col-md-8">
                     <div class="form-group mb-3">
                        <label for="observation" class="form-label">Observación</label>
                        <input type="text" class="form-control" id="observation" name="observation"
                               value="<?php echo isset($_POST['observation']) ? htmlspecialchars($_POST['observation']) : ''; ?>"
                               placeholder="Observación general de la compra (opcional)" maxlength="255">
                     </div>
                  </div>
               </div>

               <hr>

               <!-- Supplier Groups -->
               <div class="d-flex justify-content-between align-items-center mb-3">
                  <h5 class="mb-0">Detalle de Productos</h5>
                  <div>
                     <button type="button" class="btn btn-success btn-sm" id="addSupplierGroup">
                        <i class="me-1">
                           <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                              <line x1="12" y1="5" x2="12" y2="19"/>
                              <line x1="5" y1="12" x2="19" y2="12"/>
                           </svg>
                        </i>
                        Agregar Proveedor
                     </button>
                  </div>
               </div>

               <div id="supplierGroupsContainer">
                  <!-- Supplier groups will be added here by JS -->
               </div>

               <!-- Fallback sin JS -->
               <div id="noJsWarning" class="alert alert-warning">
                  <strong>Nota:</strong> Para una mejor experiencia, habilite JavaScript. El formulario funciona también sin JavaScript.
               </div>

               <div id="noJsFallback" class="d-none">
                  <p class="text-muted">Complete los detalles a continuación (use el botón "Agregar Fila" para añadir más productos al mismo proveedor).</p>
                  <div class="table-responsive">
                     <table class="table table-bordered" id="noJsTable">
                        <thead>
                           <tr>
                              <th>Producto</th>
                              <th>Proveedor</th>
                              <th>Cantidad</th>
                              <th>Precio Unit.</th>
                              <th>Observación</th>
                           </tr>
                        </thead>
                        <tbody id="noJsRows">
                           <tr>
                              <td>
                                 <select class="form-select" name="nojs_products[0][id_product]" required>
                                    <option value="">Seleccione producto</option>
                                    <?php foreach ($products as $prod): ?>
                                       <option value="<?php echo $prod['id_product']; ?>"><?php echo htmlspecialchars($prod['name']); ?></option>
                                    <?php endforeach; ?>
                                 </select>
                              </td>
                              <td>
                                 <select class="form-select" name="nojs_products[0][id_supplier]" required>
                                    <option value="">Seleccione proveedor</option>
                                    <?php foreach ($suppliers as $sup): ?>
                                       <option value="<?php echo $sup['id_supplier']; ?>"><?php echo htmlspecialchars($sup['name']); ?></option>
                                    <?php endforeach; ?>
                                 </select>
                              </td>
                              <td>
                                 <input type="number" class="form-control" name="nojs_products[0][quantity]" placeholder="0.00" step="0.01" min="0.01" required>
                              </td>
                              <td>
                                 <input type="number" class="form-control" name="nojs_products[0][unit_price]" placeholder="0.00" step="0.01" min="0" required>
                              </td>
                              <td>
                                 <input type="text" class="form-control" name="nojs_products[0][observation]" placeholder="Opcional" maxlength="255">
                              </td>
                           </tr>
                        </tbody>
                     </table>
                  </div>
                  <button type="button" class="btn btn-outline-primary btn-sm" id="addNoJsRow">
                     <i class="me-1">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                           <line x1="12" y1="5" x2="12" y2="19"/>
                           <line x1="5" y1="12" x2="19" y2="12"/>
                        </svg>
                     </i>
                     Agregar Fila
                  </button>
               </div>

               <!-- Botones -->
               <div class="row mt-4">
                  <div class="col-12">
                     <div class="form-group">
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                           <i class="me-2">
                              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                 <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                                 <polyline points="17 21 17 13 7 13 7 21"/>
                                 <polyline points="7 3 7 8 15 8"/>
                              </svg>
                           </i>
                           Registrar Compra
                        </button>
                        <a href="<?php echo CONTROLLER_URL; ?>purchase/" class="btn btn-secondary ms-2">
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

<!-- Template para grupo de proveedor (JS) -->
<template id="supplierGroupTemplate">
   <div class="supplier-group card mb-3 border-primary">
      <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
         <h6 class="mb-0">
            <span class="badge bg-primary supplier-index me-2">1</span>
            Proveedor
         </h6>
         <button type="button" class="btn btn-sm btn-outline-danger remove-supplier-group" title="Eliminar grupo">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor">
               <line x1="18" y1="6" x2="6" y2="18"/>
               <line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
         </button>
      </div>
      <div class="card-body">
         <div class="row mb-3">
            <div class="col-md-6">
               <div class="form-group">
                  <label class="form-label">Proveedor <span class="text-danger">*</span></label>
                  <select class="form-select supplier-select" required>
                     <option value="">Seleccione proveedor</option>
                     <?php foreach ($suppliers as $sup): ?>
                        <option value="<?php echo $sup['id_supplier']; ?>"><?php echo htmlspecialchars($sup['name']); ?></option>
                     <?php endforeach; ?>
                  </select>
               </div>
            </div>
            <div class="col-md-6 d-flex align-items-end justify-content-end">
               <button type="button" class="btn btn-outline-success btn-sm add-product-row">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                     <line x1="12" y1="5" x2="12" y2="19"/>
                     <line x1="5" y1="12" x2="19" y2="12"/>
                  </svg>
                  Producto
               </button>
            </div>
         </div>
         <div class="product-rows-container">
            <!-- Product rows will be added here by JS -->
         </div>
      </div>
   </div>
</template>

<!-- Template para fila de producto (JS) -->
<template id="productRowTemplate">
   <div class="product-row row g-2 mb-2 p-2 border rounded bg-white">
      <div class="col-md-3">
         <select class="form-select form-select-sm product-select" required>
            <option value="">Producto</option>
            <?php foreach ($products as $prod): ?>
               <option value="<?php echo $prod['id_product']; ?>"><?php echo htmlspecialchars($prod['name']); ?></option>
            <?php endforeach; ?>
         </select>
      </div>
      <div class="col-md-2">
         <input type="number" class="form-control form-select-sm product-qty" placeholder="Cant." step="0.01" min="0.01" required>
      </div>
      <div class="col-md-2">
         <input type="number" class="form-control form-select-sm product-price" placeholder="Precio" step="0.01" min="0" required>
      </div>
      <div class="col-md-3">
         <input type="text" class="form-control form-select-sm product-obs" placeholder="Observación (opcional)" maxlength="255">
      </div>
      <div class="col-md-1 d-flex align-items-center">
         <button type="button" class="btn btn-sm btn-outline-danger remove-product-row" title="Eliminar producto">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor">
               <line x1="18" y1="6" x2="6" y2="18"/>
               <line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
         </button>
      </div>
   </div>
</template>

<script>
(function() {
   'use strict';

   // Detectar si JS está disponible
   document.getElementById('noJsWarning').classList.add('d-none');
   document.getElementById('noJsFallback').classList.add('d-none');

   var supplierIndex = 0;

   // Elementos del DOM
   var container = document.getElementById('supplierGroupsContainer');
   var supplierTemplate = document.getElementById('supplierGroupTemplate');
   var productTemplate = document.getElementById('productRowTemplate');
   var addSupplierBtn = document.getElementById('addSupplierGroup');

   // Función para serializar el formulario al formato suppliers[]
   function serializeForm() {
      var groups = container.querySelectorAll('.supplier-group');
      var suppliers = [];

      groups.forEach(function(group, sIdx) {
         var supplierSelect = group.querySelector('.supplier-select');
         var supplierId = supplierSelect.value;
         if (!supplierId) return;

         var productRows = group.querySelectorAll('.product-row');
         var products = [];

         productRows.forEach(function(row) {
            var productSelect = row.querySelector('.product-select');
            var qtyInput = row.querySelector('.product-qty');
            var priceInput = row.querySelector('.product-price');
            var obsInput = row.querySelector('.product-obs');

            var productId = productSelect.value;
            var qty = qtyInput.value;
            var price = priceInput.value;

            if (!productId || !qty || !price) return;

            products.push({
               id_product: productId,
               quantity: qty,
               unit_price: price,
               observation: obsInput.value
            });
         });

         if (products.length > 0) {
            suppliers.push({
               id_supplier: supplierId,
               products: products
            });
         }
      });

      return suppliers;
   }

   // Función para crear hidden inputs y agregarlos al form
   function buildHiddenInputs(form) {
      // Remover inputs dinámicos anteriores
      var existing = form.querySelectorAll('.dynamic-supplier-input');
      existing.forEach(function(el) { el.remove(); });

      var suppliers = serializeForm();

      suppliers.forEach(function(supplier, sIdx) {
         var sInput = document.createElement('input');
         sInput.type = 'hidden';
         sInput.name = 'suppliers[' + sIdx + '][id_supplier]';
         sInput.value = supplier.id_supplier;
         sInput.className = 'dynamic-supplier-input';
         form.appendChild(sInput);

         supplier.products.forEach(function(product, pIdx) {
            var pInput = document.createElement('input');
            pInput.type = 'hidden';
            pInput.name = 'suppliers[' + sIdx + '][products][' + pIdx + '][id_product]';
            pInput.value = product.id_product;
            pInput.className = 'dynamic-supplier-input';
            form.appendChild(pInput);

            var qInput = document.createElement('input');
            qInput.type = 'hidden';
            qInput.name = 'suppliers[' + sIdx + '][products][' + pIdx + '][quantity]';
            qInput.value = product.quantity;
            qInput.className = 'dynamic-supplier-input';
            form.appendChild(qInput);

            var uInput = document.createElement('input');
            uInput.type = 'hidden';
            uInput.name = 'suppliers[' + sIdx + '][products][' + pIdx + '][unit_price]';
            uInput.value = product.unit_price;
            uInput.className = 'dynamic-supplier-input';
            form.appendChild(uInput);

            var oInput = document.createElement('input');
            oInput.type = 'hidden';
            oInput.name = 'suppliers[' + sIdx + '][products][' + pIdx + '][observation]';
            oInput.value = product.observation;
            oInput.className = 'dynamic-supplier-input';
            form.appendChild(oInput);
         });
      });
   }

   // Agregar fila de producto a un grupo
   function addProductRow(group) {
      var clone = document.importNode(productTemplate.content, true);
      var rowsContainer = group.querySelector('.product-rows-container');
      rowsContainer.appendChild(clone);

      // Evento para eliminar fila
      var newRow = rowsContainer.lastElementChild;
      var removeBtn = newRow.querySelector('.remove-product-row');
      removeBtn.addEventListener('click', function() {
         if (rowsContainer.querySelectorAll('.product-row').length > 1) {
            newRow.remove();
         } else {
            alert('Debe haber al menos un producto por proveedor.');
         }
      });
   }

   // Agregar grupo de proveedor
   function addSupplierGroup() {
      supplierIndex++;
      var clone = document.importNode(supplierTemplate.content, true);
      var group = clone.querySelector('.supplier-group');

      // Actualizar índice
      group.querySelector('.supplier-index').textContent = supplierIndex;

      // Evento para eliminar grupo
      group.querySelector('.remove-supplier-group').addEventListener('click', function() {
         if (container.querySelectorAll('.supplier-group').length > 1) {
            group.remove();
            reindexGroups();
         } else {
            alert('Debe haber al menos un proveedor.');
         }
      });

      // Evento para agregar producto
      group.querySelector('.add-product-row').addEventListener('click', function() {
         addProductRow(group);
      });

      container.appendChild(group);

      // Agregar primera fila de producto automáticamente
      addProductRow(group);

      // Hacer scroll al nuevo grupo
      group.scrollIntoView({ behavior: 'smooth', block: 'center' });
   }

   // Reindexar números de grupo
   function reindexGroups() {
      var groups = container.querySelectorAll('.supplier-group');
      groups.forEach(function(g, idx) {
         g.querySelector('.supplier-index').textContent = idx + 1;
      });
      supplierIndex = groups.length;
   }

   // Inicializar con un grupo por defecto
   addSupplierGroup();

   // Evento: agregar grupo
   addSupplierBtn.addEventListener('click', addSupplierGroup);

   // Interceptar submit para serializar datos
   var form = document.getElementById('purchaseForm');
   form.addEventListener('submit', function(event) {
      var jsGroups = container.querySelectorAll('.supplier-group');

      if (jsGroups.length === 0) {
         alert('Debe agregar al menos un proveedor con productos.');
         event.preventDefault();
         return;
      }

      var suppliers = serializeForm();
      if (suppliers.length === 0) {
         alert('Debe completar al menos un producto con todos sus datos.');
         event.preventDefault();
         return;
      }

      // Validar que todos los campos requeridos estén completos
      var valid = true;
      suppliers.forEach(function(s) {
         s.products.forEach(function(p) {
            if (!p.id_product || !p.quantity || !p.unit_price) {
               valid = false;
            }
            if (parseFloat(p.quantity) <= 0) {
               valid = false;
            }
            if (parseFloat(p.unit_price) < 0) {
               valid = false;
            }
         });
      });

      if (!valid) {
         alert('Complete todos los campos requeridos (producto, cantidad > 0, precio >= 0).');
         event.preventDefault();
         return;
      }

      buildHiddenInputs(form);
   });

   // Bootstrap form validation
   var forms = document.querySelectorAll('.needs-validation');
   Array.prototype.slice.call(forms).forEach(function(f) {
      f.addEventListener('submit', function(event) {
         if (!f.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
         }
         f.classList.add('was-validated');
      }, false);
   });

})();
</script>
