/**
 * Modal Helper
 * Creates and manages Bootstrap modals
 */

class ModalHelper {
    constructor() {
        this.currentModal = null;
    }

    /**
     * Create a modal with form
     * @param {string} title - Modal title
     * @param {Array} fields - Form fields configuration
     * @param {Function} onSubmit - Submit callback
     * @param {Object} data - Existing data for edit mode
     */
    createFormModal(title, fields, onSubmit, data = null) {
        const modalId = 'dynamicModal_' + Date.now();
        const isEdit = data !== null;

        // Create modal HTML
        const modalHTML = `
            <div class="modal fade" id="${modalId}" tabindex="-1" data-bs-focus="false">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">${title}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="${modalId}Form">
                                ${this.generateFormFields(fields, data)}
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                            <button type="button" class="btn btn-primary" id="${modalId}SubmitBtn">
                                ${isEdit ? 'Cập nhật' : 'Thêm mới'}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Remove existing modal if any
        this.closeModal();

        // Add modal to body
        document.body.insertAdjacentHTML('beforeend', modalHTML);

        // Get modal element
        const modalElement = document.getElementById(modalId);
        const modal = new bootstrap.Modal(modalElement, {
            focus: false,
            keyboard: true
        });

        // Prevent focus on hidden
        modalElement.addEventListener('hide.bs.modal', (e) => {
            // Remove focus from all buttons before closing
            modalElement.querySelectorAll('button').forEach(btn => btn.blur());
            document.activeElement?.blur();
        });

        // Setup submit handler
        const submitBtn = document.getElementById(`${modalId}SubmitBtn`);
        const form = document.getElementById(`${modalId}Form`);

        submitBtn.addEventListener('click', async () => {
            const formData = this.getFormData(form, fields);
            
            // Validate
            if (!this.validateForm(formData, fields)) {
                return;
            }

            // Call submit callback
            const success = await onSubmit(formData, isEdit);
            
            if (success) {
                modal.hide();
            }
        });

        // Cleanup on close
        modalElement.addEventListener('hidden.bs.modal', () => {
            setTimeout(() => {
                modalElement.remove();
                document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
                document.body.classList.remove('modal-open');
                document.body.style.removeProperty('overflow');
                document.body.style.removeProperty('padding-right');
            }, 100);
            this.currentModal = null;
        });

        // Show modal
        modal.show();
        this.currentModal = modal;

        return modal;
    }

    /**
     * Generate form fields HTML
     */
    generateFormFields(fields, data) {
        return fields.map(field => {
            const value = data ? (data[field.name] || '') : (field.defaultValue || '');
            
            switch (field.type) {
                case 'text':
                case 'email':
                case 'tel':
                case 'number':
                case 'date':
                    return `
                        <div class="mb-3">
                            <label class="form-label">${field.label} ${field.required ? '<span class="text-danger">*</span>' : ''}</label>
                            <input type="${field.type}" 
                                   class="form-control" 
                                   name="${field.name}"
                                   value="${value}"
                                   ${field.required ? 'required' : ''}
                                   ${field.placeholder ? `placeholder="${field.placeholder}"` : ''}>
                        </div>
                    `;
                
                case 'textarea':
                    return `
                        <div class="mb-3">
                            <label class="form-label">${field.label} ${field.required ? '<span class="text-danger">*</span>' : ''}</label>
                            <textarea class="form-control" 
                                      name="${field.name}"
                                      rows="${field.rows || 3}"
                                      ${field.required ? 'required' : ''}
                                      ${field.placeholder ? `placeholder="${field.placeholder}"` : ''}>${value}</textarea>
                        </div>
                    `;
                
                case 'select':
                    return `
                        <div class="mb-3">
                            <label class="form-label">${field.label} ${field.required ? '<span class="text-danger">*</span>' : ''}</label>
                            <select class="form-select" 
                                    name="${field.name}"
                                    ${field.required ? 'required' : ''}>
                                <option value="">-- Chọn ${field.label} --</option>
                                ${field.options.map(opt => `
                                    <option value="${opt.value}" ${value == opt.value ? 'selected' : ''}>
                                        ${opt.label}
                                    </option>
                                `).join('')}
                            </select>
                        </div>
                    `;
                
                case 'file':
                    return `
                        <div class="mb-3">
                            <label class="form-label">${field.label} ${field.required ? '<span class="text-danger">*</span>' : ''}</label>
                            <input type="file" 
                                   class="form-control" 
                                   name="${field.name}"
                                   ${field.accept ? `accept="${field.accept}"` : ''}
                                   ${field.required ? 'required' : ''}>
                            ${value ? `<small class="text-muted">File hiện tại: ${value}</small>` : ''}
                        </div>
                    `;
                
                default:
                    return '';
            }
        }).join('');
    }

    /**
     * Get form data
     */
    getFormData(form, fields) {
        const formData = {};
        const formElement = new FormData(form);
        
        fields.forEach(field => {
            if (field.type === 'file') {
                formData[field.name] = form.querySelector(`[name="${field.name}"]`).files[0];
            } else {
                formData[field.name] = formElement.get(field.name);
            }
        });
        
        return formData;
    }

    /**
     * Validate form
     */
    validateForm(formData, fields) {
        for (const field of fields) {
            if (field.required && !formData[field.name]) {
                alert(`Vui lòng nhập ${field.label}`);
                return false;
            }
            
            if (field.type === 'email' && formData[field.name]) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(formData[field.name])) {
                    alert(`Email không hợp lệ`);
                    return false;
                }
            }
            
            if (field.validate && !field.validate(formData[field.name])) {
                alert(field.validateMessage || `${field.label} không hợp lệ`);
                return false;
            }
        }
        
        return true;
    }

    /**
     * Close current modal
     */
    closeModal() {
        if (this.currentModal) {
            this.currentModal.hide();
        }
        
        // Remove all modals
        document.querySelectorAll('.modal').forEach(modal => modal.remove());
        document.querySelectorAll('.modal-backdrop').forEach(backdrop => backdrop.remove());
    }

    /**
     * Show confirmation dialog
     */
    confirm(message, title = 'Xác nhận') {
        return new Promise((resolve) => {
            const modalId = 'confirmModal_' + Date.now();
            
            const modalHTML = `
                <div class="modal fade" id="${modalId}" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" data-bs-focus="false">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">${title}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p>${message}</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="${modalId}CancelBtn">Hủy</button>
                                <button type="button" class="btn btn-primary" id="${modalId}ConfirmBtn">Xác nhận</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.insertAdjacentHTML('beforeend', modalHTML);
            
            const modalElement = document.getElementById(modalId);
            const modal = new bootstrap.Modal(modalElement, {
                backdrop: 'static',
                keyboard: false,
                focus: false
            });
            
            // Prevent focus issues
            modalElement.addEventListener('hide.bs.modal', () => {
                modalElement.querySelectorAll('button').forEach(btn => btn.blur());
                document.activeElement?.blur();
            });
            
            let resolved = false;
            
            document.getElementById(`${modalId}ConfirmBtn`).addEventListener('click', () => {
                resolved = true;
                modal.hide();
            });
            
            document.getElementById(`${modalId}CancelBtn`).addEventListener('click', () => {
                resolved = false;
                modal.hide();
            });
            
            modalElement.addEventListener('hidden.bs.modal', () => {
                setTimeout(() => {
                    modalElement.remove();
                    document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
                    document.body.classList.remove('modal-open');
                    document.body.style.removeProperty('overflow');
                    document.body.style.removeProperty('padding-right');
                }, 100);
                resolve(resolved);
            });
            
            modal.show();
        });
    }
}

const modalHelper = new ModalHelper();
export default modalHelper;
