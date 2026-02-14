// Admin Panel JavaScript

class AdminManager {
    constructor() {
        this.init();
    }
    
    init() {
        this.setupBookForm();
        this.setupDeleteConfirmations();
        this.setupImagePreview();
        this.setupDataTables();
        this.setupCharts();
        this.setupSearchFilters();
        this.setupBulkActions();
    }
    
    setupBookForm() {
        const bookForm = document.getElementById('bookForm');
        if (!bookForm) return;
        
        // Toggle Ethiopian specific fields
        const countrySelect = bookForm.querySelector('select[name="country"]');
        const ethiopianFields = bookForm.querySelectorAll('.ethiopian-field');
        
        if (countrySelect) {
            countrySelect.addEventListener('change', function() {
                const isEthiopian = this.value === 'Ethiopia';
                ethiopianFields.forEach(field => {
                    field.style.display = isEthiopian ? 'block' : 'none';
                });
            });
        }
        
        // Form validation
        bookForm.addEventListener('submit', (e) => {
            e.preventDefault();
            if (this.validateBookForm(bookForm)) {
                bookForm.submit();
            }
        });
        
        // Auto-generate slug from title
        const titleInput = bookForm.querySelector('input[name="title"]');
        const slugInput = bookForm.querySelector('input[name="slug"]');
        
        if (titleInput && slugInput) {
            titleInput.addEventListener('blur', function() {
                if (!slugInput.value) {
                    slugInput.value = this.value
                        .toLowerCase()
                        .replace(/[^\w\s]/gi, '')
                        .replace(/\s+/g, '-');
                }
            });
        }
    }
    
    validateBookForm(form) {
        let isValid = true;
        const requiredFields = form.querySelectorAll('[required]');
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                this.showFieldError(field, 'This field is required');
                isValid = false;
            } else {
                this.removeFieldError(field);
            }
        });
        
        // Validate price
        const priceField = form.querySelector('input[name="price"]');
        if (priceField && priceField.value) {
            const price = parseFloat(priceField.value);
            if (isNaN(price) || price <= 0) {
                this.showFieldError(priceField, 'Please enter a valid price');
                isValid = false;
            }
        }
        
        // Validate PDF file
        const pdfField = form.querySelector('input[name="pdf_file"]');
        if (pdfField && pdfField.files.length > 0) {
            const file = pdfField.files[0];
            const fileSize = file.size / 1024 / 1024; // in MB
            const fileExt = file.name.split('.').pop().toLowerCase();
            
            if (fileExt !== 'pdf') {
                this.showFieldError(pdfField, 'Only PDF files are allowed');
                isValid = false;
            } else if (fileSize > 50) {
                this.showFieldError(pdfField, 'File size must be less than 50MB');
                isValid = false;
            }
        }
        
        // Validate cover image
        const coverField = form.querySelector('input[name="cover_image"]');
        if (coverField && coverField.files.length > 0) {
            const file = coverField.files[0];
            const fileExt = file.name.split('.').pop().toLowerCase();
            const validExts = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (!validExts.includes(fileExt)) {
                this.showFieldError(coverField, 'Only JPG, PNG, and GIF files are allowed');
                isValid = false;
            }
        }
        
        return isValid;
    }
    
    setupDeleteConfirmations() {
        const deleteButtons = document.querySelectorAll('.delete-btn, .btn-delete');
        
        deleteButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                
                const itemType = button.dataset.type || 'item';
                const itemName = button.dataset.name || '';
                
                Swal.fire({
                    title: 'Are you sure?',
                    html: `You are about to delete ${itemName ? `<strong>${itemName}</strong>` : `this ${itemType}`}.<br>This action cannot be undone!`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d63031',
                    cancelButtonColor: '#636e72',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = button.href;
                    }
                });
            });
        });
    }
    
    setupImagePreview() {
        const imageInput = document.querySelector('input[name="cover_image"]');
        const previewContainer = document.getElementById('imagePreview');
        
        if (imageInput && previewContainer) {
            imageInput.addEventListener('change', function() {
                const file = this.files[0];
                
                if (file) {
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        previewContainer.innerHTML = `
                            <img src="${e.target.result}" alt="Preview" style="max-width: 200px; max-height: 300px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.2);">
                            <p style="margin-top: 10px; color: #00b894;">✓ Image ready to upload</p>
                        `;
                    };
                    
                    reader.readAsDataURL(file);
                } else {
                    previewContainer.innerHTML = '<p style="color: #636e72;">No image selected</p>';
                }
            });
        }
    }
    
    setupDataTables() {
        const dataTable = document.querySelector('.data-table');
        
        if (dataTable) {
            const searchInput = document.createElement('input');
            searchInput.type = 'text';
            searchInput.placeholder = 'Search...';
            searchInput.className = 'form-control';
            searchInput.style.cssText = `
                width: 250px;
                margin-bottom: 20px;
                padding: 10px 15px;
                border: 2px solid #e1e1e1;
                border-radius: 10px;
            `;
            
            dataTable.parentNode.insertBefore(searchInput, dataTable);
            
            searchInput.addEventListener('keyup', function() {
                const searchTerm = this.value.toLowerCase();
                const rows = dataTable.querySelectorAll('tbody tr');
                
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(searchTerm) ? '' : 'none';
                });
            });
            
            // Add sorting functionality
            const headers = dataTable.querySelectorAll('th[data-sortable]');
            headers.forEach(header => {
                header.addEventListener('click', function() {
                    const column = this.cellIndex;
                    const rows = Array.from(dataTable.querySelectorAll('tbody tr'));
                    const direction = this.dataset.direction === 'asc' ? 'desc' : 'asc';
                    
                    rows.sort((a, b) => {
                        const aValue = a.cells[column].textContent;
                        const bValue = b.cells[column].textContent;
                        
                        if (direction === 'asc') {
                            return aValue.localeCompare(bValue);
                        } else {
                            return bValue.localeCompare(aValue);
                        }
                    });
                    
                    rows.forEach(row => dataTable.querySelector('tbody').appendChild(row));
                    
                    headers.forEach(h => delete h.dataset.direction);
                    this.dataset.direction = direction;
                });
            });
        }
    }
    
    setupCharts() {
        // Sales Chart
        const salesChartCanvas = document.getElementById('salesChart');
        if (salesChartCanvas) {
            const ctx = salesChartCanvas.getContext('2d');
            
            // Get data from data attributes
            const labels = JSON.parse(salesChartCanvas.dataset.labels || '[]');
            const data = JSON.parse(salesChartCanvas.dataset.data || '[]');
            
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Sales',
                        data: data,
                        borderColor: '#6c5ce7',
                        backgroundColor: 'rgba(108, 92, 231, 0.1)',
                        borderWidth: 3,
                        pointBackgroundColor: '#6c5ce7',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        pointHoverRadius: 8,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0,0,0,0.05)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }
        
        // Category Distribution Chart
        const categoryChartCanvas = document.getElementById('categoryChart');
        if (categoryChartCanvas) {
            const ctx = categoryChartCanvas.getContext('2d');
            
            const labels = JSON.parse(categoryChartCanvas.dataset.labels || '[]');
            const data = JSON.parse(categoryChartCanvas.dataset.data || '[]');
            
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: [
                            '#6c5ce7',
                            '#00b894',
                            '#fdcb6e',
                            '#e17055',
                            '#0984e3',
                            '#e84342'
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                usePointStyle: true,
                                pointStyle: 'circle'
                            }
                        }
                    },
                    cutout: '70%'
                }
            });
        }
    }
    
    setupSearchFilters() {
        const filterInputs = document.querySelectorAll('.filter-input');
        const filterSelects = document.querySelectorAll('.filter-select');
        
        function applyFilters() {
            const filters = {};
            
            filterInputs.forEach(input => {
                if (input.value) {
                    filters[input.dataset.filter] = input.value.toLowerCase();
                }
            });
            
            filterSelects.forEach(select => {
                if (select.value) {
                    filters[select.dataset.filter] = select.value;
                }
            });
            
            const rows = document.querySelectorAll('.data-table tbody tr');
            
            rows.forEach(row => {
                let showRow = true;
                
                for (const [key, value] of Object.entries(filters)) {
                    const cell = row.querySelector(`[data-${key}]`);
                    if (cell) {
                        const cellValue = cell.dataset[`${key}`].toLowerCase();
                        if (!cellValue.includes(value)) {
                            showRow = false;
                            break;
                        }
                    }
                }
                
                row.style.display = showRow ? '' : 'none';
            });
        }
        
        filterInputs.forEach(input => {
            input.addEventListener('keyup', debounce(applyFilters, 300));
        });
        
        filterSelects.forEach(select => {
            select.addEventListener('change', applyFilters);
        });
    }
    
    setupBulkActions() {
        const selectAllCheckbox = document.getElementById('selectAll');
        const itemCheckboxes = document.querySelectorAll('.select-item');
        const bulkActionsSelect = document.getElementById('bulkActions');
        const applyBulkAction = document.getElementById('applyBulkAction');
        
        if (selectAllCheckbox && itemCheckboxes.length > 0) {
            selectAllCheckbox.addEventListener('change', function() {
                itemCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
            });
            
            itemCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const allChecked = Array.from(itemCheckboxes).every(cb => cb.checked);
                    const someChecked = Array.from(itemCheckboxes).some(cb => cb.checked);
                    
                    selectAllCheckbox.checked = allChecked;
                    selectAllCheckbox.indeterminate = !allChecked && someChecked;
                });
            });
        }
        
        if (applyBulkAction && bulkActionsSelect) {
            applyBulkAction.addEventListener('click', function() {
                const action = bulkActionsSelect.value;
                const selectedIds = Array.from(itemCheckboxes)
                    .filter(cb => cb.checked)
                    .map(cb => cb.value);
                
                if (selectedIds.length === 0) {
                    Swal.fire('No items selected', 'Please select at least one item', 'warning');
                    return;
                }
                
                if (action) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = window.location.href;
                    
                    const idsInput = document.createElement('input');
                    idsInput.type = 'hidden';
                    idsInput.name = 'selected_ids';
                    idsInput.value = JSON.stringify(selectedIds);
                    
                    const actionInput = document.createElement('input');
                    actionInput.type = 'hidden';
                    actionInput.name = 'bulk_action';
                    actionInput.value = action;
                    
                    form.appendChild(idsInput);
                    form.appendChild(actionInput);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }
    }
    
    showFieldError(field, message) {
        this.removeFieldError(field);
        
        field.classList.add('error');
        const errorDiv = document.createElement('div');
        errorDiv.className = 'field-error';
        errorDiv.textContent = message;
        errorDiv.style.color = '#d63031';
        errorDiv.style.fontSize = '12px';
        errorDiv.style.marginTop = '5px';
        
        field.parentNode.insertBefore(errorDiv, field.nextSibling);
    }
    
    removeFieldError(field) {
        field.classList.remove('error');
        const errorDiv = field.parentNode.querySelector('.field-error');
        if (errorDiv) {
            errorDiv.remove();
        }
    }
}

// Utility function for debouncing
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Initialize Admin Manager
document.addEventListener('DOMContentLoaded', () => {
    window.adminManager = new AdminManager();
});