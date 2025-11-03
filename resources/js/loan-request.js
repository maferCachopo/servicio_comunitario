// Este es el único cambio necesario para que apunte al servidor.
const baselink = 'http://localhost:8050/api/v1';
class LoanRequestManager {

    constructor() {
         console.log('antes del fetch');
        this.availablePartituras = [];
        this.currentSelection = {
            partitura: null,
            instrumento: null,
            cantidad: 1
            
        };
        
        this.init();
    }

    init() {
        this.bindEvents();
        this.loadAvailablePartituras();
        this.loadUserLoanHistory();
    }

    bindEvents() {
        // Partitura selection change
        document.getElementById('partituraSelect')?.addEventListener('change', (e) => {
            this.handlePartituraChange(e.target.value);
        });

        // Instrumento selection change
        document.getElementById('instrumentoSelect')?.addEventListener('change', (e) => {
            this.handleInstrumentoChange(e.target.value);
        });

        // Cantidad input change
        document.getElementById('cantidadInput')?.addEventListener('input', (e) => {
            this.handleCantidadChange(e.target.value);
        });

        // Submit button click
        document.getElementById('submitLoanRequest')?.addEventListener('click', () => {
            this.submitLoanRequest();
        });

        // Cancel button click - handle manually to ensure proper focus management
        document.getElementById('cancelLoanRequest')?.addEventListener('click', (e) => {
            e.preventDefault();
            this.handleModalClose();
        });

        // Close button (X) - handle manually for proper focus management
        document.querySelector('.btn-close')?.addEventListener('click', (e) => {
            e.preventDefault();
            this.handleModalClose();
        });

        // Modal events for proper cleanup
        const modal = document.getElementById('loanRequestModal');
        if (modal) {
            modal.addEventListener('shown.bs.modal', () => {
                this.handleModalShown();
            });
            
            modal.addEventListener('hide.bs.modal', (e) => {
                // Allow hide during loading by checking if we should prevent it
                if (this.isLoading && !this.allowCloseDuringLoading) {
                    e.preventDefault();
                    return;
                }
            });
            
            modal.addEventListener('hidden.bs.modal', () => {
                this.resetForm();
                this.handleModalHidden();
            });
        }
    }

    async loadAvailablePartituras() {
        this.isLoading = true;
        this.allowCloseDuringLoading = true; // Allow closing during loading
        this.showLoadingState(true);
        
        // Ensure modal can be closed during loading by enabling backdrop and keyboard
        this.enableModalInteraction(true);

         
        
        try {
            // Add timeout to prevent indefinite loading
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 10000); // 10 second timeout
            
            

            const response = await fetch(`${baselink}/partituras-disponibles`, {
                signal: controller.signal,
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            console.log(response);
            
            clearTimeout(timeoutId);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.success !== false) {
                this.availablePartituras = data.partituras || [];
                this.populatePartituraSelect();
                this.hideLoadingError();
            } else {
                throw new Error(data.message || 'Error al cargar partituras disponibles');
            }
        } catch (error) {
            console.error('Error loading partituras:', error);
            this.handleLoadingError(error.message);
        } finally {
            this.isLoading = false;
            this.showLoadingState(false);
            this.enableModalInteraction(false); // Restore normal interaction
        }
    }

    populatePartituraSelect() {
        const select = document.getElementById('partituraSelect');
        if (!select) return;
        
        // Clear existing options
        select.innerHTML = '';
        
        if (this.availablePartituras.length === 0) {
            select.innerHTML = '<option value="">No hay partituras disponibles</option>';
            select.disabled = true;
            return;
        }
        
        // Add default option
        const defaultOption = document.createElement('option');
        defaultOption.value = '';
        defaultOption.textContent = 'Seleccione una partitura...';
        select.appendChild(defaultOption);
        
        // Add partitura options
        this.availablePartituras.forEach(partitura => {
            const option = document.createElement('option');
            option.value = partitura.id;
            option.textContent = `${partitura.titulo} - ${partitura.autor} (${partitura.cantidad_disponible} disponibles)`;
            option.dataset.instrumentos = JSON.stringify(partitura.instrumentos);
            option.dataset.cantidad = partitura.cantidad_disponible;
            select.appendChild(option);
        });
        
        // Enable the select
        select.disabled = false;
    }

    handlePartituraChange(partituraId) {
        if (!partituraId) {
            this.resetInstrumentoSelect();
            this.hideCantidadField();
            return;
        }

        const partitura = this.availablePartituras.find(p => p.id == partituraId);
        if (partitura) {
            this.currentSelection.partitura = partitura;
            this.populateInstrumentoSelect(partitura.instrumentos);
            this.showCantidadField(partitura.cantidad_disponible);
            this.updateSummary();
        }
    }

    populateInstrumentoSelect(instrumentos) {
        const select = document.getElementById('instrumentoSelect');
        if (!select) return;
        
        select.innerHTML = '<option value="">Seleccione un instrumento...</option>';
        select.disabled = false;
        
        instrumentos.forEach(instrumento => {
            const option = document.createElement('option');
            option.value = instrumento;
            option.textContent = instrumento;
            select.appendChild(option);
        });
    }

    showCantidadField(maxCantidad) {
        const cantidadField = document.getElementById('cantidadField');
        const input = document.getElementById('cantidadInput');
        const maxCantidadSpan = document.getElementById('maxCantidad');
        
        if (!cantidadField || !input || !maxCantidadSpan) return;
        
        // Show the quantity field
        cantidadField.classList.remove('d-none');
        
        // Set constraints
        input.max = maxCantidad;
        input.value = Math.min(1, maxCantidad); // Set to 1 or max if max is 0
        input.disabled = false;
        maxCantidadSpan.textContent = maxCantidad;
        
        this.currentSelection.cantidad = parseInt(input.value);
        
        // Add validation class
        input.classList.remove('is-invalid');
    }

    hideCantidadField() {
        const cantidadField = document.getElementById('cantidadField');
        const input = document.getElementById('cantidadInput');
        
        if (!cantidadField || !input) return;
        
        cantidadField.classList.add('d-none');
        input.value = 1;
        input.disabled = true;
        this.currentSelection.cantidad = 1;
        input.classList.remove('is-invalid');
    }

    updateCantidadConstraints(maxCantidad) {
        const input = document.getElementById('cantidadInput');
        const maxCantidadSpan = document.getElementById('maxCantidad');
        
        if (!input || !maxCantidadSpan) return;
        
        input.max = maxCantidad;
        maxCantidadSpan.textContent = maxCantidad;
        
        // Reset value if it exceeds new max
        if (parseInt(input.value) > maxCantidad) {
            input.value = maxCantidad;
            this.currentSelection.cantidad = maxCantidad;
        }
        
        // Validate the current value
        this.validateCantidad();
    }

    validateCantidad() {
        const input = document.getElementById('cantidadInput');
        const errorDiv = document.getElementById('cantidadError');
        
        if (!input || !errorDiv) return;
        
        const value = parseInt(input.value) || 0;
        const max = parseInt(input.max) || 1;
        
        if (value < 1 || value > max) {
            input.classList.add('is-invalid');
            errorDiv.style.display = 'block';
            return false;
        } else {
            input.classList.remove('is-invalid');
            errorDiv.style.display = 'none';
            return true;
        }
    }

    handleInstrumentoChange(instrumento) {
        this.currentSelection.instrumento = instrumento;
        this.updateSummary();
        this.validateForm();
    }

    handleCantidadChange(cantidad) {
        const maxCantidad = parseInt(document.getElementById('cantidadInput')?.max || 1);
        const intCantidad = parseInt(cantidad) || 1;
        
        if (intCantidad > maxCantidad) {
            document.getElementById('cantidadInput').value = maxCantidad;
            this.currentSelection.cantidad = maxCantidad;
        } else if (intCantidad < 1) {
            document.getElementById('cantidadInput').value = 1;
            this.currentSelection.cantidad = 1;
        } else {
            this.currentSelection.cantidad = intCantidad;
        }
        
        // Validate the quantity
        this.validateCantidad();
        this.updateSummary();
        this.validateForm();
    }

    updateSummary() {
        const summary = document.getElementById('requestSummary');
        const hasSelection = this.currentSelection.partitura && this.currentSelection.instrumento;
        
        if (!summary) return;
        
        if (hasSelection) {
            document.getElementById('summaryPartitura').textContent = 
                `${this.currentSelection.partitura.titulo} - ${this.currentSelection.partitura.autor}`;
            document.getElementById('summaryInstrumento').textContent = this.currentSelection.instrumento;
            document.getElementById('summaryCantidad').textContent = this.currentSelection.cantidad;
            
            summary.classList.remove('d-none');
        } else {
            summary.classList.add('d-none');
        }
    }

    validateForm() {
        const isValid = this.currentSelection.partitura &&
                       this.currentSelection.instrumento &&
                       this.currentSelection.cantidad > 0 &&
                       this.validateCantidad();
        
        const submitBtn = document.getElementById('submitLoanRequest');
        if (submitBtn) {
            submitBtn.disabled = !isValid;
        }
        
        return isValid;
    }

    async submitLoanRequest() {
        if (!this.validateForm()) {
            return;
        }

        const submitBtn = document.getElementById('submitLoanRequest');
        const originalText = submitBtn?.innerHTML || 'Enviar solicitud';
        
        // Show loading state
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Enviando...';
        }

        try {
            const response = await fetch(`${baselink}/solicitar-prestamo`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    partitura_id: this.currentSelection.partitura.id,
                    instrumento: this.currentSelection.instrumento,
                    cantidad: this.currentSelection.cantidad
                })
            });

            const data = await response.json();

            if (data.success) {
                this.showSuccess(data.message);
                this.closeModal();
                this.loadUserLoanHistory(); // Refresh history
            } else {
                this.showError(data.message || 'Error al enviar la solicitud');
            }
        } catch (error) {
            console.error('Error submitting loan request:', error);
            this.showError('Error de conexión al enviar la solicitud');
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        }
    }

    async loadUserLoanHistory() {
        try {
            const response = await fetch(`${baselink}/mis-prestamos`);
            const data = await response.json();
            
            if (data.success !== false) {
                this.renderLoanHistory(data.prestamos || []);
            } else {
                this.showError('Error al cargar historial de préstamos');
            }
        } catch (error) {
            console.error('Error loading loan history:', error);
            this.showError('Error al cargar historial');
        }
    }

    renderLoanHistory(prestamos) {
        const container = document.getElementById('userLoanHistory');
        if (!container) return;
        
        if (prestamos.length === 0) {
            container.innerHTML = '<p class="text-muted">No tiene préstamos registrados</p>';
            return;
        }

        let html = `
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Partitura</th>
                        <th>Instrumento</th>
                        <th>Cantidad</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
        `;

        prestamos.forEach(prestamo => {
            const estadoClass = this.getEstadoClass(prestamo.estado);
            html += `
                <tr>
                    <td>${prestamo.obra_titulo || 'N/A'}</td>
                    <td>${prestamo.instrumento || 'N/A'}</td>
                    <td>${prestamo.cantidad_solicitada || 1}</td>
                    <td>${new Date(prestamo.fecha_solicitud).toLocaleDateString()}</td>
                    <td><span class="badge ${estadoClass}">${prestamo.estado}</span></td>
                </tr>
            `;
        });

        html += '</tbody></table>';
        container.innerHTML = html;
    }

    getEstadoClass(estado) {
        const classes = {
            'pendiente': 'bg-warning text-dark',
            'aceptado': 'bg-success',
            'rechazado': 'bg-danger',
            'activo': 'bg-primary'
        };
        return classes[estado] || 'bg-secondary';
    }

    showSuccess(message) {
        const alert = document.createElement('div');
        alert.className = 'alert alert-success alert-dismissible fade show';
        alert.innerHTML = `
            <i class="fas fa-check-circle me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        const container = document.querySelector('.loan-history') || document.querySelector('.container');
        if (container) {
            container.insertBefore(alert, container.firstChild);
        }
        
        setTimeout(() => {
            if (alert.parentNode) {
                alert.parentNode.removeChild(alert);
            }
        }, 5000);
    }

    showLoadingState(show) {
        const loadingIndicator = document.getElementById('loadingIndicator');
        const partituraSelect = document.getElementById('partituraSelect');
        
        if (loadingIndicator) {
            if (show) {
                loadingIndicator.classList.remove('d-none');
            } else {
                loadingIndicator.classList.add('d-none');
            }
        }
        
        if (partituraSelect) {
            partituraSelect.disabled = show;
        }
    }

    enableModalInteraction(enable) {
        const modalElement = document.getElementById('loanRequestModal');
        if (!modalElement) return;
        
        // Get the Bootstrap modal instance
        const modal = bootstrap.Modal.getInstance(modalElement);
        if (modal) {
            // Update modal configuration to allow/prevent closing
            if (enable) {
                // Allow closing during loading
                modal._config.backdrop = true;
                modal._config.keyboard = true;
            } else {
                // Restore static backdrop (prevent accidental closing)
                modal._config.backdrop = 'static';
                modal._config.keyboard = false;
            }
        }
        
        // Fix ARIA attributes to prevent focus issues
        if (enable) {
            modalElement.removeAttribute('aria-hidden');
            modalElement.removeAttribute('inert');
        }
    }

    handleLoadingError(message) {
        const errorDiv = document.getElementById('partituraLoadingError');
        const select = document.getElementById('partituraSelect');
        
        if (errorDiv) {
            errorDiv.textContent = message || 'Error al cargar partituras. Intente nuevamente.';
            errorDiv.classList.remove('d-none');
        }
        
        if (select) {
            select.innerHTML = '<option value="">Error al cargar partituras</option>';
            select.disabled = true;
        }
        
        // Add retry button
        this.addRetryButton();
    }

    hideLoadingError() {
        const errorDiv = document.getElementById('partituraLoadingError');
        if (errorDiv) {
            errorDiv.classList.add('d-none');
        }
    }

    addRetryButton() {
        const form = document.getElementById('loanRequestForm');
        if (!form) return;
        
        // Remove existing retry button
        const existingRetry = document.getElementById('retryLoadingButton');
        if (existingRetry) {
            existingRetry.remove();
        }
        
        // Create retry button
        const retryButton = document.createElement('button');
        retryButton.type = 'button';
        retryButton.id = 'retryLoadingButton';
        retryButton.className = 'btn btn-sm btn-outline-primary mt-2';
        retryButton.innerHTML = '<i class="fas fa-redo me-1"></i>Reintentar';
        retryButton.onclick = () => {
            this.loadAvailablePartituras();
            retryButton.remove();
        };
        
        // Insert after the select field
        const selectContainer = document.getElementById('partituraSelect')?.parentElement;
        if (selectContainer) {
            selectContainer.appendChild(retryButton);
        }
    }

    showError(message) {
        const errorDiv = document.getElementById('formErrors');
        if (errorDiv) {
            errorDiv.textContent = message;
            errorDiv.classList.remove('d-none');
        }
    }

    resetForm() {
        const form = document.getElementById('loanRequestForm');
        if (form) {
            form.reset();
        }
        this.currentSelection = {
            partitura: null,
            instrumento: null,
            cantidad: 1
        };
        this.resetInstrumentoSelect();
        this.resetCantidadInput();
        const summary = document.getElementById('requestSummary');
        if (summary) {
            summary.classList.add('d-none');
        }
        const errorDiv = document.getElementById('formErrors');
        if (errorDiv) {
            errorDiv.classList.add('d-none');
        }
        const submitBtn = document.getElementById('submitLoanRequest');
        if (submitBtn) {
            submitBtn.disabled = true;
        }
    }

    handleModalShown() {
        // Manage focus properly when modal is shown
        const modalElement = document.getElementById('loanRequestModal');
        if (modalElement) {
            // Remove any aria-hidden that might have been added incorrectly
            modalElement.removeAttribute('aria-hidden');
            
            // Ensure the modal is not inert during normal operation
            modalElement.removeAttribute('inert');
            
            // Set focus to the first focusable element (close button) only if not loading
            if (!this.isLoading) {
                const closeButton = modalElement.querySelector('.btn-close');
                if (closeButton) {
                    // Use requestAnimationFrame for better timing
                    requestAnimationFrame(() => {
                        closeButton.focus();
                    });
                }
            }
        }
    }

    handleModalHidden() {
        // Clean up any remaining attributes and restore focus
        const modalElement = document.getElementById('loanRequestModal');
        if (modalElement) {
            modalElement.removeAttribute('aria-hidden');
            modalElement.removeAttribute('inert');
        }
        
        // Return focus to the element that triggered the modal
        const triggerElement = document.querySelector('[data-bs-target="#loanRequestModal"], [href="#loanRequestModal"]');
        if (triggerElement) {
            requestAnimationFrame(() => {
                triggerElement.focus();
            });
        }
    }

    handleModalClose() {
        // Always allow closing the modal
        this.resetForm();
        
        // Use Bootstrap's proper modal closing mechanism
        const modalElement = document.getElementById('loanRequestModal');
        if (modalElement) {
            // Remove any blocking attributes
            modalElement.removeAttribute('aria-hidden');
            modalElement.removeAttribute('inert');
            
            // Get the Bootstrap modal instance and hide it
            const modal = bootstrap.Modal.getInstance(modalElement);
            if (modal) {
                // Temporarily enable closing if we're in loading state
                const originalBackdrop = modal._config.backdrop;
                const originalKeyboard = modal._config.keyboard;
                
                if (this.isLoading) {
                    modal._config.backdrop = true;
                    modal._config.keyboard = true;
                }
                
                // Hide the modal
                modal.hide();
                
                // Restore original configuration
                modal._config.backdrop = originalBackdrop;
                modal._config.keyboard = originalKeyboard;
            } else {
                // Fallback: manually trigger hide if modal instance doesn't exist
                try {
                    const newModal = new bootstrap.Modal(modalElement);
                    newModal.hide();
                } catch (error) {
                    console.warn('Could not create or hide modal:', error);
                }
            }
        }
    }

    closeModal() {
        this.handleModalClose();
    }

    resetInstrumentoSelect() {
        const select = document.getElementById('instrumentoSelect');
        if (select) {
            select.innerHTML = '<option value="">Primero seleccione una partitura...</option>';
            select.disabled = true;
        }
    }

    resetCantidadInput() {
        const input = document.getElementById('cantidadInput');
        const errorDiv = document.getElementById('cantidadError');
        
        if (input) {
            input.value = 1;
            input.max = 1;
            input.disabled = true;
            input.classList.remove('is-invalid');
            
            const maxCantidadSpan = document.getElementById('maxCantidad');
            if (maxCantidadSpan) {
                maxCantidadSpan.textContent = '1';
            }
        }
        
        if (errorDiv) {
            errorDiv.style.display = 'none';
        }
    }

    cancelLoanRequest() {
        // Always allow canceling - this is the user's escape route
        this.handleModalClose();
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('loanRequestModal')) {
        new LoanRequestManager();
    }
});