<!-- Loan Request Modal -->
<div class="modal fade" id="loanRequestModal" tabindex="-1" aria-labelledby="loanRequestModalLabel" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="loanRequestModalLabel">
                    <i class="fas fa-music me-2"></i>Solicitar Préstamo de Partitura
                </h5>
                <button type="button" class="btn-close" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="loanRequestForm">
                    @csrf
                    
                    {{-- Selector de Partitura --}}
                    <div class="mb-3">
                        <label for="partituraSelect" class="form-label">
                            <i class="fas fa-book me-1"></i>Partitura
                        </label>
                        <select class="form-select" id="partituraSelect" name="partitura_id" required>
                            <option value="">Cargando partituras disponibles...</option>
                        </select>
                        <div class="form-text">Seleccione la partitura que desea solicitar</div>
                        <div class="d-none text-danger small" id="partituraLoadingError">
                            <i class="fas fa-exclamation-triangle me-1"></i>Error al cargar partituras. Intente nuevamente.
                        </div>
                    </div>

                    {{-- Selector de Instrumento --}}
                    <div class="mb-3">
                        <label for="instrumentoSelect" class="form-label">
                            <i class="fas fa-guitar me-1"></i>Instrumento
                        </label>
                        <select class="form-select" id="instrumentoSelect" name="instrumento" required disabled>
                            <option value="">Primero seleccione una partitura...</option>
                        </select>
                        <div class="form-text">Instrumento para el que necesita la partitura</div>
                    </div>

                    {{-- Campo de Cantidad - Oculto cuando no hay partitura seleccionada --}}
                    <div class="mb-3 d-none" id="cantidadField">
                        <label for="cantidadInput" class="form-label">
                            <i class="fas fa-calculator me-1"></i>Cantidad a prestar
                        </label>
                        <input type="number" class="form-control" id="cantidadInput" name="cantidad"
                               min="1" max="1" value="1" required>
                        <div class="form-text">
                            <span id="cantidadHelp">Máximo disponible: <span id="maxCantidad">1</span></span>
                        </div>
                        <div class="invalid-feedback" id="cantidadError">
                            La cantidad debe ser mayor a 0 y no exceder el stock disponible.
                        </div>
                    </div>

                    {{-- Resumen de la solicitud --}}
                    <div class="alert alert-info d-none" id="requestSummary">
                        <h6><i class="fas fa-info-circle me-2"></i>Resumen de la solicitud:</h6>
                        <ul class="mb-0">
                            <li><strong>Partitura:</strong> <span id="summaryPartitura"></span></li>
                            <li><strong>Instrumento:</strong> <span id="summaryInstrumento"></span></li>
                            <li><strong>Cantidad:</strong> <span id="summaryCantidad"></span></li>
                        </ul>
                    </div>

                    {{-- Mensajes de error --}}
                    <div class="alert alert-danger d-none" id="formErrors"></div>
                    
                    {{-- Loading indicator --}}
                    <div class="d-flex justify-content-center d-none" id="loadingIndicator">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="cancelLoanRequest" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancelar
                </button>
                <button type="button" class="btn btn-primary" id="submitLoanRequest" disabled>
                    <i class="fas fa-paper-plane me-2"></i>Enviar solicitud
                </button>
            </div>
        </div>
    </div>
</div>