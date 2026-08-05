// Global table variable
var table;

$(document).ready(function () {
    // Flag to prevent change handler loops during edit population
    var isEditPopulating = false;

    // Get invoice type from URL parameter
    var urlParams = new URLSearchParams(window.location.search);
    var invoiceType = urlParams.get('type') || 'government';

    // Initialize DataTable
    table = $('#invoicesTable').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "get_charge_invoices.php",
            "type": "POST",
            "data": function (d) {
                d.invoice_type = invoiceType;
            },
            "error": function (xhr, error, thrown) {
                console.error('DataTable AJAX error:', error, thrown);
                console.error('Response:', xhr.responseText);
            }
        },
        "columns": [
            { "data": 0 },
            { "data": 1 },
            { "data": 2 },
            { "data": 3 },
            { "data": 4 },
            { "data": 5 },
            { "data": 6, "orderable": false }
        ],
        "order": [[1, 'desc']],
        "pageLength": 25,
        "language": {
            "processing": "Loading invoices...",
            "loadingRecords": "Loading...",
            "zeroRecords": "No invoices found"
        },
        "drawCallback": function (settings) {
            // Ensure processing indicator is hidden
            $('#invoicesTable_processing').hide();
        }
    });

    // Initialize Select2
    $('#addCustomer').select2({
        dropdownParent: $('#addInvoiceModal'),
        theme: 'bootstrap-5'
    });

    $('#editCustomer').select2({
        dropdownParent: $('#editInvoiceModal'),
        theme: 'bootstrap-5'
    });

    // Set today's date as default
    $('#addInvoiceDate, #editInvoiceDate').val(new Date().toISOString().split('T')[0]);

    // Generate Invoice Number
    $('#generateInvoiceNo').on('click', function () {
        $.ajax({
            url: 'ajax_handler.php',
            method: 'POST',
            data: { action: 'generate_invoice_no' },
            dataType: 'json',
            contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
            success: function (response) {
                if (response.invoice_no) {
                    $('#addInvoiceNo').val(response.invoice_no);
                } else {
                    console.error('Generate invoice error:', response);
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX error:', xhr.responseText);
            }
        });
    });

    // Customer selection - auto-fill address and load deliveries
    $('#addCustomer, #editCustomer').on('change', function () {
        // Prevent handler from running during edit form population
        if (isEditPopulating && $(this).attr('id') === 'editCustomer') {
            return;
        }

        const selectedOption = $(this).find('option:selected');
        const customerId = $(this).val();
        const address = selectedOption.data('address') || '';
        const customerType = selectedOption.data('type') || '';
        const isAdd = $(this).attr('id') === 'addCustomer';
        const prefix = isAdd ? 'add' : 'edit';

        if (isAdd) {
            $('#addAddress').val(address);
        } else {
            $('#editAddress').val(address);
        }

        // Reset totals
        $('#' + prefix + 'TotalAmount').val('0.00');
        $('#' + prefix + 'TotalDisplay').text('₱0.00');
        updateTaxDisplay(prefix, customerType);

        if (customerId) {
            if (isAdd) {
                loadDeliveries(customerId, 'add');
            } else {
                // For edit, we handle delivery loading differently (merging linked + available)
                // This is triggered by editInvoice function usually, but if user changes customer in edit,
                // we should probably reload deliveries for new customer
                loadDeliveries(customerId, 'edit', []);
            }
        } else {
            $('#' + prefix + 'DeliveriesContainer').html('<p class="text-center text-muted my-3">Select a customer to view available deliveries.</p>');
        }
    });

    function loadDeliveries(customerId, modalType, preselectedIds = []) {
        const container = $('#' + modalType + 'DeliveriesContainer');
        container.html('<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary"></div> Loading deliveries...</div>');

        $.ajax({
            url: 'process.php',
            method: 'POST',
            data: {
                action: 'get_available_deliveries',
                customer_id: customerId,
                csrf_token: $('input[name="csrf_token"]').val()
            },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    let deliveries = response.deliveries;

                    // If editing, we might need to include currently linked deliveries that are not in "available" list
                    // Logic: The "get_available_deliveries" returns unlinked ones. 
                    // When editing, we need to manually add the ones currently linked to THIS invoice.
                    // This merging happens if we are called from editInvoice().
                    // If called from Change Customer, preselectedIds is empty, so we just show available.

                    // Note: If we are in edit mode and switching customers, preselectedIds should be empty.
                    // If we are opening edit modal, preselectedIds has the linked ones.

                    if (modalType === 'edit' && preselectedIds.length > 0 && window.currentLinkedDeliveries) {
                        // Merge currentLinkedDeliveries with available deliveries
                        // We need to mark them as checked
                        deliveries = [...window.currentLinkedDeliveries, ...deliveries];
                        // Sort by date desc
                        deliveries.sort((a, b) => new Date(b.delivery_date) - new Date(a.delivery_date));
                        // Remove duplicates just in case
                        deliveries = deliveries.filter((v, i, a) => a.findIndex(t => (t.id === v.id)) === i);
                    }

                    renderDeliveries(deliveries, container, modalType, preselectedIds);
                } else {
                    container.html('<div class="alert alert-danger">Failed to load deliveries.</div>');
                }
            },
            error: function () {
                container.html('<div class="alert alert-danger">Error loading deliveries.</div>');
            }
        });
    }

    function renderDeliveries(deliveries, container, modalType, preselectedIds) {
        if (deliveries.length === 0) {
            container.html('<p class="text-center text-muted my-3">No available deliveries found for this customer.</p>');
            return;
        }

        let html = '<div class="table-responsive"><table class="table table-sm table-hover">';
        html += '<thead><tr>';
        html += '<th></th>'; // For checkbox
        html += '<th>DR No</th>';
        html += '<th>Date</th>';
        html += '<th class="text-end">Amount</th>';
        html += '</tr></thead><tbody>';

        deliveries.forEach(function (delivery) {
            const isChecked = preselectedIds.includes(delivery.id.toString()) || preselectedIds.includes(parseInt(delivery.id));
            const formattedDate = new Date(delivery.delivery_date).toLocaleDateString();
            const formattedAmount = parseFloat(delivery.total_amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            html += `
                <tr>
                    <td>
                        <input class="form-check-input delivery-checkbox" type="checkbox" 
                               name="delivery_ids[]" 
                               value="${delivery.id}" 
                               id="${modalType}_delivery_${delivery.id}"
                               data-amount="${delivery.total_amount}"
                               ${isChecked ? 'checked' : ''}>
                    </td>
                    <td><label class="form-check-label cursor-pointer" for="${modalType}_delivery_${delivery.id}"><strong>${delivery.delivery_no}</strong></label></td>
                    <td><label class="form-check-label cursor-pointer" for="${modalType}_delivery_${delivery.id}"><small class="text-muted">${formattedDate}</small></label></td>
                    <td class="text-end"><label class="form-check-label cursor-pointer" for="${modalType}_delivery_${delivery.id}">₱${formattedAmount}</label></td>
                </tr>
            `;
        });

        html += '</tbody></table></div>';
        container.html(html);

        // Recalculate total if any checked
        calculateTotalFromDeliveries(modalType);
    }

    // Calculate total when deliveries are checked/unchecked
    $(document).on('change', '.delivery-checkbox', function () {
        const container = $(this).closest('.deliveries-list-container');
        const modalType = container.attr('id').includes('add') ? 'add' : 'edit';
        calculateTotalFromDeliveries(modalType);
    });

    function calculateTotalFromDeliveries(modalType) {
        const container = $('#' + modalType + 'DeliveriesContainer');
        let total = 0;

        container.find('.delivery-checkbox:checked').each(function () {
            total += parseFloat($(this).data('amount')) || 0;
        });

        $('#' + modalType + 'TotalAmount').val(total.toFixed(2));
        $('#' + modalType + 'TotalDisplay').text('₱' + total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

        const customerSelect = $('#' + modalType + 'Customer');
        const selectedOption = customerSelect.find('option:selected');
        const customerType = selectedOption.data('type') || '';
        updateTaxDisplay(modalType, customerType);
    }

    function updateTaxDisplay(modalType, customerType) {
        const total = parseFloat($('#' + modalType + 'TotalAmount').val()) || 0;
        const prefix = modalType;

        if (customerType === 'Government' && total > 0) {
            const tax5 = total * 0.05;
            const tax1 = total * 0.01;
            const netAmount = total - tax5 - tax1;

            $('#' + prefix + 'Tax5Display').text('₱' + tax5.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
            $('#' + prefix + 'Tax1Display').text('₱' + tax1.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
            $('#' + prefix + 'NetAmountDisplay').text('₱' + netAmount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
            $('#' + prefix + 'TaxInfoTitle').text('Government Tax Calculation');
            $('#' + prefix + 'TaxInfo').show().addClass('government');
        } else {
            $('#' + prefix + 'TaxInfo').hide().removeClass('government');
        }
    }

    // Check duplicate invoice number
    $('#addInvoiceNo').on('blur', function () {
        const invoiceNo = $(this).val();
        if (invoiceNo) {
            checkDuplicateInvoiceNo(invoiceNo, null, 'add');
        }
    });

    $('#editInvoiceNo').on('blur', function () {
        const invoiceNo = $(this).val();
        const invoiceId = $('#editInvoiceId').val();
        if (invoiceNo) {
            checkDuplicateInvoiceNo(invoiceNo, invoiceId, 'edit');
        }
    });

    function checkDuplicateInvoiceNo(invoiceNo, excludeId, modalType) {
        const spinner = $('#' + modalType + 'InvoiceNoSpinner');
        spinner.show();

        $.ajax({
            url: 'check_duplicate.php',
            method: 'POST',
            data: {
                invoice_no: invoiceNo,
                invoice_id: excludeId
            },
            dataType: 'json',
            success: function (response) {
                const inputEl = $('#' + modalType + 'InvoiceNo');
                const errorEl = $('#' + modalType + 'InvoiceNoError');
                const submitBtn = $('#' + modalType + 'InvoiceSubmit');

                if (response.duplicate) {
                    inputEl.addClass('is-invalid');
                    errorEl.text('This invoice number is already taken.');
                    submitBtn.prop('disabled', true);
                } else {
                    inputEl.removeClass('is-invalid');
                    errorEl.text('');
                    submitBtn.prop('disabled', false);
                }
            },
            complete: function () {
                spinner.hide();
            }
        });
    }

    // Edit Invoice - Load Data
    $(document).on('click', '.edit-btn', function () {
        const invoiceId = $(this).data('id');

        $.ajax({
            url: 'process.php',
            method: 'POST',
            data: { action: 'get_invoice', id: invoiceId },
            dataType: 'json',
            success: function (invoice) {
                $('#editInvoiceId').val(invoice.id);
                $('#editInvoiceNo').val(invoice.invoice_no);
                $('#editInvoiceDate').val(invoice.invoice_date);

                isEditPopulating = true;
                $('#editCustomer').val(invoice.customer_id).trigger('change');
                isEditPopulating = false;

                $('#editAddress').val(invoice.address);
                $('#editPaymentStatus').val(invoice.payment_status);

                // Store linked deliveries globally so we can merge them later
                window.currentLinkedDeliveries = invoice.linked_deliveries || [];

                // Get IDs of linked deliveries
                const linkedIds = window.currentLinkedDeliveries.map(d => d.id);

                // Load deliveries (both linked and available)
                loadDeliveries(invoice.customer_id, 'edit', linkedIds);
            }
        });
    });

    // View Invoice
    $(document).on('click', '.view-btn', function () {
        const invoiceId = $(this).data('id');
        viewInvoice(invoiceId);
    });

    // Delete Invoice
    $(document).on('click', '.delete-btn', function () {
        $('#deleteInvoiceId').val($(this).data('id'));
        const invoiceNo = $(this).data('invoice-no');
        $('#deleteInvoiceText').html('Are you sure you want to delete invoice <strong>' + invoiceNo + '</strong>?');
    });

    // Form Submissions
    $('#addInvoiceForm').on('submit', function (e) {
        e.preventDefault();

        const submitBtn = $('#addInvoiceSubmit');
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Saving...');

        $.ajax({
            url: 'process.php',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    $('#addInvoiceModal').modal('hide');
                    setTimeout(function () {
                        $('.modal-backdrop').remove();
                        $('body').removeClass('modal-open').removeAttr('style');
                    }, 300);

                    showToast('success', response.message);
                    table.ajax.reload(null, false);
                    $('#addInvoiceForm')[0].reset();
                    $('#addDeliveriesContainer').html('<p class="text-center text-muted my-3">Select a customer to view available deliveries.</p>');
                    $('#addTotalAmount').val('0.00');
                    $('#addTotalDisplay').text('₱0.00');
                    $('#addTaxInfo').hide();
                } else {
                    showToast('error', response.message);
                }
                submitBtn.prop('disabled', false).html('Save Invoice');
            },
            error: function (xhr) {
                try {
                    const res = JSON.parse(xhr.responseText);
                    showToast('error', res.message || 'An error occurred.');
                } catch (e) {
                    showToast('error', 'An error occurred. Please try again.');
                }
                submitBtn.prop('disabled', false).html('Save Invoice');
            }
        });
    });

    $('#editInvoiceForm').on('submit', function (e) {
        e.preventDefault();

        const submitBtn = $('#editInvoiceSubmit');
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Updating...');

        $.ajax({
            url: 'process.php',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    $('#editInvoiceModal').modal('hide');
                    setTimeout(function () {
                        $('.modal-backdrop').remove();
                        $('body').removeClass('modal-open').removeAttr('style');
                    }, 300);

                    showToast('success', response.message);
                    table.ajax.reload(null, false);
                } else {
                    showToast('error', response.message);
                }
                submitBtn.prop('disabled', false).html('Update Invoice');
            },
            error: function (xhr) {
                try {
                    const res = JSON.parse(xhr.responseText);
                    showToast('error', res.message || 'An error occurred.');
                } catch (e) {
                    showToast('error', 'An error occurred. Please try again.');
                }
                submitBtn.prop('disabled', false).html('Update Invoice');
            }
        });
    });

    // Delete Invoice Form Submit Handler
    $('#deleteInvoiceForm').on('submit', function (e) {
        e.preventDefault();

        const submitBtn = $(this).find('button[type="submit"]');
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Deleting...');

        $.ajax({
            url: 'process.php',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    $('#deleteInvoiceModal').modal('hide');
                    setTimeout(function () {
                        $('.modal-backdrop').remove();
                        $('body').removeClass('modal-open').removeAttr('style');
                    }, 300);

                    showToast('success', response.message);
                    table.ajax.reload(null, false);
                } else {
                    showToast('error', response.message);
                }
                submitBtn.prop('disabled', false).html('Delete');
            },
            error: function () {
                showToast('error', 'An error occurred. Please try again.');
                submitBtn.prop('disabled', false).html('Delete');
            }
        });
    });

    // Reset modal on close
    $('#addInvoiceModal, #editInvoiceModal').on('hidden.bs.modal', function () {
        $(this).find('input.is-invalid').removeClass('is-invalid');
        $(this).find('.invalid-feedback').text('');
        $(this).find('button[type="submit"]').prop('disabled', false);
        // Clean backdrops
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open').removeAttr('style');
    });

    // Force cleanup on any modal close
    $(document).on('click', '.btn-close, .btn-secondary', function () {
        setTimeout(function () {
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open').removeAttr('style');
        }, 300);
    });

    function showToast(type, message) {
        var toastEl = document.getElementById('liveToast');
        if (!toastEl) { alert(message); return; }

        var toastBody = toastEl.querySelector('.toast-body');
        var toastHeader = toastEl.querySelector('.toast-header');

        if (toastBody && toastHeader) {
            toastHeader.classList.remove('bg-success', 'bg-danger', 'text-white');
            if (type === 'success') {
                toastHeader.classList.add('bg-success', 'text-white');
            } else {
                toastHeader.classList.add('bg-danger', 'text-white');
            }
            toastBody.textContent = message;
            var toast = new bootstrap.Toast(toastEl);
            toast.show();
        } else {
            alert(message);
        }
    }

    // Initialize CSRF token for AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('input[name="csrf_token"]').val()
        }
    });
});

function viewInvoice(invoiceId) {
    $('#viewInvoiceContent').html('<div class="text-center"><div class="spinner-border" role="status"></div></div>');
    $('#viewInvoiceModal').modal('show');

    $.ajax({
        url: 'process.php',
        method: 'POST',
        data: { action: 'get_invoice', id: invoiceId },
        dataType: 'json',
        success: function (invoice) {
            let deliveriesHtml = '';
            let subtotal = 0;

            if (invoice.linked_deliveries && invoice.linked_deliveries.length > 0) {
                invoice.linked_deliveries.forEach(function (delivery) {
                    subtotal += parseFloat(delivery.total_amount);
                    deliveriesHtml += `
                        <tr>
                            <td>${delivery.delivery_no}</td>
                            <td>${new Date(delivery.delivery_date).toLocaleDateString()}</td>
                            <td class="text-end">₱${parseFloat(delivery.total_amount).toLocaleString('en-US', { minimumFractionDigits: 2 })}</td>
                        </tr>
                    `;
                });
            } else {
                deliveriesHtml = '<tr><td colspan="3" class="text-center text-muted">No linked deliveries</td></tr>';
            }

            let taxSection = '';
            if (invoice.customer_type === 'Government') {
                const tax5 = subtotal * 0.05;
                const tax1 = subtotal * 0.01;
                const netAmount = subtotal - tax5 - tax1;

                taxSection = `
                    <div class="alert alert-info mt-3">
                        <h6>Government Tax Calculation</h6>
                        <div class="row">
                            <div class="col-4"><strong>5% Withholding Tax:</strong><br>₱${tax5.toLocaleString('en-US', { minimumFractionDigits: 2 })}</div>
                            <div class="col-4"><strong>1% Withholding Tax:</strong><br>₱${tax1.toLocaleString('en-US', { minimumFractionDigits: 2 })}</div>
                            <div class="col-4"><strong>Net Amount:</strong><br><span class="text-success fs-5">₱${netAmount.toLocaleString('en-US', { minimumFractionDigits: 2 })}</span></div>
                        </div>
                    </div>
                `;
            }

            const content = `
                <div class="invoice-header mb-4">
                    <div class="row">
                        <div class="col-md-6">
                            <h4>Invoice #${invoice.invoice_no}</h4>
                            <p class="text-muted">Date: ${new Date(invoice.invoice_date).toLocaleDateString()}</p>
                        </div>
                        <div class="col-md-6">
                            <div class="text-end">
                                <strong>Customer:</strong><br>
                                ${invoice.customer_name}<br>
                                <small class="text-muted">${invoice.address}</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <strong>Linked Deliveries:</strong>
                    <div class="table-responsive mt-2">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Delivery No</th>
                                    <th>Date</th>
                                    <th class="text-end">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${deliveriesHtml}
                            </tbody>
                            <tfoot>
                                <tr class="table-light">
                                    <th colspan="2" class="text-end">Total Amount:</th>
                                    <th class="text-end">₱${subtotal.toLocaleString('en-US', { minimumFractionDigits: 2 })}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                
                ${taxSection}
            `;

            $('#viewInvoiceContent').html(content);
        },
        error: function () {
            $('#viewInvoiceContent').html('<div class="alert alert-danger">Error loading invoice details.</div>');
        }
    });
}