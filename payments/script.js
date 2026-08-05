$(document).ready(function () {
    // Flag to prevent change handler loops during edit population
    var isEditPopulating = false;

    // Get payment category from URL
    var urlParams = new URLSearchParams(window.location.search);
    var paymentCategory = urlParams.get('category') || 'all';
    
    // Store original options for customer selects
    var addCustomerOptions = null;
    var editCustomerOptions = null;
    
    // Initialize stored options after a brief delay to ensure DOM is ready
    setTimeout(function() {
        addCustomerOptions = $('#addCustomerId').find('option').clone();
        editCustomerOptions = $('#editCustomerId').find('option').clone();

        // Initialize Select2 for customer dropdowns
        $('#addCustomerId').select2({
            dropdownParent: $('#addPaymentModal'), // Ensure dropdown is within modal
            theme: 'bootstrap-5'
        });
        $('#editCustomerId').select2({
            dropdownParent: $('#editPaymentModal'), // Ensure dropdown is within modal
            theme: 'bootstrap-5'
        });
    }, 100);

    // Filter customers based on payment category (for future dual-workflow support)
    function filterCustomersByCategory(categorySelector, customerSelector) {
        try {
            var $customerSelect = $(customerSelector);
            var storedOptions = $(customerSelector).attr('id') === 'addCustomerId' ? addCustomerOptions : editCustomerOptions;
            
            // If stored options not ready yet, skip filtering
            if (!storedOptions || storedOptions.length === 0) {
                return;
            }
            
            // Get all options from stored copy
            var allOptions = [];
            storedOptions.each(function() {
                allOptions.push({
                    $element: $(this).clone(),
                    value: $(this).val(),
                    text: $(this).text(),
                    customerType: $(this).data('customer-type')
                });
            });
            
            // Filter based on payment category
            var visibleOptions = allOptions.filter(function(option) {
                if (option.value === '') {
                    return true; // Always show placeholder
                }
                
                // Filter by category if specified
                if (paymentCategory === 'private' && (option.customerType === 'Private' || option.customerType === 'Business')) {
                    return true;
                } else if (paymentCategory === 'government' && option.customerType === 'Government') {
                    return true;
                } else if (paymentCategory === 'all') {
                    return true; // Show all customers
                }
                return false;
            });
            
            // Update the select options
            $customerSelect.empty();
            visibleOptions.forEach(function(option) {
                $customerSelect.append(option.$element);
            });
            
            // Trigger change to update Select2 if used
            if ($customerSelect.data('select2')) {
                $customerSelect.trigger('change.select2');
            }
        } catch(e) {
            console.log('Filter error:', e);
        }
    }

    // Initialize DataTables with category filtering
    var table = $('#paymentsTable').DataTable({
        "ajax": {
            "url": "get_payments.php",
            "type": "POST",
            "data": function(d) {
                d.category = paymentCategory;
            }
        },
        "processing": true,
        "columns": [
            { "data": 0, "visible": paymentCategory !== 'private' },
            { "data": 1 },
            { "data": 2 },
            { "data": 3 },
            { "data": 4 },
            { "data": 5, "orderable": false }
        ]
    });

    // Adjust UI based on category
    if (paymentCategory === 'private') {
        $('label[for="addOrDate"], label[for="editOrDate"]').html('Date Received <span class="text-danger">*</span>');
        $('#addOrNo, #editOrNo').closest('.mb-3').hide();
    }

    // Payment Type Change Handler
    function handlePaymentTypeChange(prefix) {
        const paymentType = $('#' + prefix + 'PaymentType').val();
        const checkFields = $('#' + prefix + 'CheckFields');

        if (paymentType === 'Check' || paymentType === 'Bank Transfer') {
            checkFields.show();
            $('#' + prefix + 'BankId').attr('required', true);
            $('#' + prefix + 'ChequeNo').attr('required', true);
        } else {
            checkFields.hide();
            $('#' + prefix + 'BankId').attr('required', false);
            $('#' + prefix + 'ChequeNo').attr('required', false);
        }
    }

    $('#addPaymentType, #editPaymentType').on('change', function () {
        const prefix = $(this).attr('id').replace('PaymentType', '');
        handlePaymentTypeChange(prefix);
    });

    // Customer Selection Handler - Load Unpaid Invoices
    $('#addCustomerId, #editCustomerId').on('change', function () {
        const isEdit = $(this).attr('id') === 'editCustomerId';
        
        // Prevent handler from running during edit form population
        if (isEdit && isEditPopulating) {
            return;
        }

        const prefix = isEdit ? 'edit' : 'add';
        const customerId = $(this).val();
        const invoiceListDiv = $('#' + prefix + 'InvoiceList');

        if (!customerId) {
            invoiceListDiv.html('<p class="text-muted">Select a customer to view unpaid invoices</p>');
            return;
        }

        // For edit modal, skip change handler if we're still loading initial data
        // The edit-btn click handler will load invoices separately
        if (isEdit && invoiceListDiv.find('table').length > 0) {
            return;
        }

        invoiceListDiv.html('<div class="text-center"><div class="spinner-border spinner-border-sm" role="status"></div> Loading invoices...</div>');

        $.ajax({
            url: 'get_unpaid_invoices.php',
            type: 'GET',
            data: { customer_id: customerId },
            dataType: 'json',
            success: function (response) {
                                    if (response.success && response.invoices.length > 0) {
                                        let html = '<div class="table-responsive"><table class="table table-sm">';
                                        html += '<thead><tr><th>Invoice / DR</th><th>Date</th><th>Original Balance</th><th>Current Balance</th><th>Apply Amount</th></tr></thead><tbody>';
                
                                        response.invoices.forEach(function (invoice) {
                                            html += '<tr class="invoice-allocation-row">';
                                            html += '<td>' + invoice.invoice_no + '</td>';
                                            html += '<td>' + invoice.invoice_date + '</td>';
                                            html += '<td>₱' + parseFloat(invoice.original_balance).toFixed(2) + '</td>';
                                            html += '<td>₱' + parseFloat(invoice.current_balance).toFixed(2) + '</td>';
                                            html += '<td><input type="number" class="form-control form-control-sm ' + prefix + '-apply-amount" ';
                                            html += 'data-invoice-id="' + invoice.id + '" ';
                                            html += 'data-balance="' + invoice.current_balance + '" ';
                                            html += 'step="0.01" min="0" max="' + invoice.current_balance + '" value="0"></td>';
                                            html += '</tr>';
                                        });
                    html += '</tbody></table></div>';
                    invoiceListDiv.html(html);
                    updateRemainingAmount(prefix);
                } else {
                    invoiceListDiv.html('<div class="alert alert-warning">No unpaid invoices found for this customer</div>');
                }
            },
            error: function () {
                invoiceListDiv.html('<div class="alert alert-danger">Error loading invoices</div>');
            }
        });
    });

    // Calculate Remaining Amount with improved display
    function updateRemainingAmount(prefix) {
        const totalAmount = parseFloat($('#' + prefix + 'AmountReceived').val()) || 0;
        let appliedAmount = 0;

        $('.' + prefix + '-apply-amount').each(function () {
            appliedAmount += parseFloat($(this).val()) || 0;
        });

        const remaining = Math.round((totalAmount - appliedAmount) * 100) / 100;
        const overpayment = Math.round((appliedAmount - totalAmount) * 100) / 100;

        console.log('Update remaining for ' + prefix + ': total=' + totalAmount + ', applied=' + appliedAmount + ', remaining=' + remaining);

        // Update display fields
        $('#' + prefix + 'TotalAmount').text(totalAmount.toFixed(2));
        $('#' + prefix + 'AppliedAmount').text(appliedAmount.toFixed(2));
        $('#' + prefix + 'RemainingAmount').text(Math.abs(remaining).toFixed(2));

        // Show/hide overpayment warning
        if (remaining < 0) {
            $('#' + prefix + 'OverpaymentWarning').show();
            $('#' + prefix + 'OverpaymentAmount').text(overpayment.toFixed(2));
            $('#' + prefix + 'RemainingAmount').addClass('text-danger').removeClass('text-success');
        } else if (remaining === 0 && totalAmount > 0) {
            $('#' + prefix + 'OverpaymentWarning').hide();
            $('#' + prefix + 'RemainingAmount').addClass('text-success').removeClass('text-danger');
        } else {
            $('#' + prefix + 'OverpaymentWarning').hide();
            $('#' + prefix + 'RemainingAmount').removeClass('text-danger text-success');
        }
    }

    // Update remaining amount on input change
    $(document).on('input', '#addAmountReceived', function () {
        updateRemainingAmount('add');
    });

    $(document).on('input', '.add-apply-amount', function () {
        updateRemainingAmount('add');
    });

    $(document).on('input', '#editAmountReceived', function () {
        updateRemainingAmount('edit');
    });

    $(document).on('input', '.edit-apply-amount', function () {
        updateRemainingAmount('edit');
    });

    // Generate OR Number
    $('#generateOrNo').on('click', function () {
        const csrfToken = $('input[name="csrf_token"]').val();
        
        $.ajax({
            url: 'process.php',
            method: 'POST',
            data: { 
                action: 'generate_or_no',
                csrf_token: csrfToken
            },
            dataType: 'json',
            success: function (response) {
                if (response.success && response.or_no) {
                    $('#addOrNo').val(response.or_no);
                } else {
                    console.error('Generate OR error:', response);
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX error:', xhr.responseText);
            }
        });
    });

    // Edit Payment - Populate Modal
    $(document).on('click', '.edit-btn', function () {
        const paymentId = $(this).data('id');
        const customerId = $(this).data('customer_id');
        
        isEditPopulating = true;

        $('#editPaymentId').val(paymentId);
        $('#editOrNo').val($(this).data('or_no'));
        $('#editOrDate').val($(this).data('or_date'));
        $('#editCustomerId').val(customerId).trigger('change');
        $('#editAmountReceived').val($(this).data('amount_received'));
        $('#editPaymentType').val($(this).data('payment_type'));
        $('#editBankId').val($(this).data('bank_id'));
        $('#editChequeNo').val($(this).data('cheque_no'));
        $('#editChequeDate').val($(this).data('cheque_date'));
        $('#editCheckName').val($(this).data('cheque_name'));
        $('#editCheckAmount').val($(this).data('check_amount'));
        $('#editNotes').val($(this).data('notes'));
        
        isEditPopulating = false;

        handlePaymentTypeChange('edit');

        // Load customer invoices and allocations
        if (customerId) {
            $.ajax({
                url: 'get_unpaid_invoices.php',
                type: 'GET',
                data: { 
                    customer_id: customerId,
                    payment_id: paymentId 
                },
                dataType: 'json',
                success: function (response) {
                    if (response.success && response.invoices.length > 0) {
                        let html = '<div class="table-responsive"><table class="table table-sm">';
                        html += '<thead><tr><th>Invoice / DR</th><th>Date</th><th>Balance</th><th>Apply Amount</th></tr></thead><tbody>';

                        response.invoices.forEach(function (invoice) {
                            html += '<tr class="invoice-allocation-row">';
                            html += '<td>' + invoice.invoice_no + '</td>';
                            html += '<td>' + invoice.invoice_date + '</td>';
                            html += '<td>₱' + parseFloat(invoice.original_balance).toFixed(2) + '</td>';
                            html += '<td><input type="number" class="form-control form-control-sm edit-apply-amount" ';
                            html += 'data-invoice-id="' + invoice.id + '" ';
                            html += 'data-balance="' + invoice.original_balance + '" ';
                            html += 'step="0.01" min="0" max="' + invoice.original_balance + '" value="0"></td>';
                            html += '</tr>';
                        });

                        html += '</tbody></table></div>';
                        $('#editInvoiceList').html(html);

                        // Load existing allocations
                        $.ajax({
                            url: 'get_payment_allocations.php',
                            type: 'GET',
                            data: { payment_id: paymentId },
                            dataType: 'json',
                            success: function (allocations) {
                                if (allocations && allocations.length > 0) {
                                    // Use setTimeout to ensure DOM is fully rendered
                                    setTimeout(function() {
                                        allocations.forEach(function (allocation) {
                                            console.log('Setting allocation for invoice:', allocation.invoice_id, 'amount:', allocation.amount_applied);
                                            const $input = $('.edit-apply-amount[data-invoice-id="' + allocation.invoice_id + '"]');
                                            console.log('Found inputs:', $input.length);
                                            if ($input.length > 0) {
                                                // Force string to number conversion for setting value
                                                const amount = parseFloat(allocation.amount_applied).toFixed(2);
                                                $input.val(amount);
                                                // Trigger input event to update calculations
                                                $input.trigger('input');
                                            }
                                        });
                                        updateRemainingAmount('edit');
                                    }, 100);
                                } else {
                                    console.log('No allocations found');
                                }
                            },
                            error: function (e) {
                                console.log('Error loading allocations:', e);
                            }
                        });
                    } else {
                        $('#editInvoiceList').html('<div class="alert alert-warning">No unpaid invoices found for this customer</div>');
                    }
                }
            });
        }
    });

    // View Payment Details
    $(document).on('click', '.view-btn', function () {
        const paymentId = $(this).data('id');
        const contentDiv = $('#viewPaymentContent');

        contentDiv.html('<div class="text-center"><div class="spinner-border" role="status"></div></div>');

        $.ajax({
            url: 'get_payment_details.php',
            type: 'GET',
            data: { payment_id: paymentId },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    const data = response.data;
                    let html = '<div class="row">';
                    html += '<div class="col-md-6">';
                    html += '<h6 class="border-bottom pb-2 mb-3">Payment Information</h6>';
                    html += '<table class="table table-sm">';
                    html += '<tr><th width="40%">OR Number:</th><td>' + data.or_no + '</td></tr>';
                    html += '<tr><th>OR Date:</th><td>' + data.or_date + '</td></tr>';
                    html += '<tr><th>Customer:</th><td>' + data.customer_name;
                    if (data.customer_type) {
                        html += ' <span class="badge ' + (data.customer_type === 'Government' ? 'bg-danger' : 'bg-info') + '">' + data.customer_type + '</span>';
                    }
                    html += '</td></tr>';
                    html += '<tr><th>Amount Received:</th><td><strong>₱' + parseFloat(data.amount_received).toFixed(2) + '</strong></td></tr>';
                    html += '<tr><th>Payment Method:</th><td>' + data.payment_type + '</td></tr>';

                    if (data.bank_name) {
                        html += '<tr><th>Bank:</th><td>' + data.bank_name + '</td></tr>';
                    }
                    if (data.cheque_no) {
                        html += '<tr><th>Check Number:</th><td>' + data.cheque_no + '</td></tr>';
                    }
                    if (data.cheque_date) {
                        html += '<tr><th>Check Date:</th><td>' + data.cheque_date + '</td></tr>';
                    }
                    if (data.cheque_name) {
                        html += '<tr><th>Name on Check:</th><td>' + data.cheque_name + '</td></tr>';
                    }
                    if (data.check_amount) {
                        html += '<tr><th>Check Amount:</th><td>₱' + parseFloat(data.check_amount).toFixed(2) + '</td></tr>';
                    }
                    if (data.notes) {
                        html += '<tr><th>Notes:</th><td>' + data.notes + '</td></tr>';
                    }
                    html += '<tr><th>Created By:</th><td>' + data.created_by_name + '</td></tr>';
                    html += '<tr><th>Created At:</th><td>' + data.created_at + '</td></tr>';
                    html += '</table></div>';

                    html += '<div class="col-md-6">';
                    html += '<h6 class="border-bottom pb-2 mb-3">Payment Allocation</h6>';

                    if (response.allocations && response.allocations.length > 0) {
                        html += '<table class="table table-sm">';
                        html += '<thead><tr><th>Invoice / DR</th><th>Amount Applied</th><th>Balance</th></tr></thead><tbody>';
                        response.allocations.forEach(function (allocation) {
                            html += '<tr>';
                            html += '<td>' + allocation.invoice_no + '</td>';
                            html += '<td>₱' + parseFloat(allocation.amount_applied).toFixed(2) + '</td>';
                            html += '<td>₱' + parseFloat(allocation.balance).toFixed(2) + '</td>';
                            html += '</tr>';
                        });
                        html += '</tbody></table>';
                    } else {
                        html += '<p class="text-muted">No invoice allocations recorded</p>';
                    }

                    html += '</div></div>';

                    contentDiv.html(html);
                } else {
                    contentDiv.html('<div class="alert alert-danger">' + response.message + '</div>');
                }
            },
            error: function () {
                contentDiv.html('<div class="alert alert-danger">Error loading payment details</div>');
            }
        });
    });

    // Print Payment
    $('#printPaymentBtn').on('click', function () {
        const content = $('#viewPaymentContent').html();
        const originalContents = document.body.innerHTML;
        
        // Extract data from the content for better formatting
        const orNo = $('#viewPaymentContent').find('th:contains("OR Number")').next().text().trim() || 'N/A';
        const orDate = $('#viewPaymentContent').find('th:contains("OR Date")').next().text().trim() || 'N/A';
        const customer = $('#viewPaymentContent').find('th:contains("Customer")').next().text().trim() || 'N/A';
        const amount = $('#viewPaymentContent').find('th:contains("Amount Received")').next().text().trim() || '₱0.00';
        
        // Create enhanced print styles and layout
        const printStyles = `
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body { 
                    font-family: 'Courier New', Arial, sans-serif; 
                    font-size: 11pt; 
                    padding: 20px;
                    background: white;
                }
                .or-container {
                    max-width: 8.5in;
                    margin: 0 auto;
                    border: 2px solid #333;
                    padding: 20px;
                    background: white;
                }
                .or-header {
                    text-align: center;
                    margin-bottom: 20px;
                    border-bottom: 3px solid #333;
                    padding-bottom: 10px;
                }
                .or-header h2 {
                    font-size: 18pt;
                    font-weight: bold;
                    margin-bottom: 5px;
                }
                .or-header p {
                    font-size: 10pt;
                    color: #666;
                }
                .or-info {
                    display: flex;
                    justify-content: space-between;
                    margin-bottom: 20px;
                    padding-bottom: 10px;
                    border-bottom: 1px solid #ddd;
                }
                .info-block {
                    flex: 1;
                }
                .info-label {
                    font-weight: bold;
                    font-size: 9pt;
                    color: #666;
                }
                .info-value {
                    font-size: 11pt;
                    margin-top: 3px;
                }
                .customer-section {
                    margin-bottom: 20px;
                    padding: 10px;
                    background: #f9f9f9;
                    border: 1px solid #ddd;
                }
                .customer-label {
                    font-weight: bold;
                    font-size: 10pt;
                    color: #666;
                }
                .customer-name {
                    font-size: 12pt;
                    margin-top: 5px;
                }
                .payment-details {
                    margin-bottom: 20px;
                }
                .detail-row {
                    display: flex;
                    justify-content: space-between;
                    padding: 8px 0;
                    border-bottom: 1px dotted #ddd;
                }
                .detail-label {
                    font-weight: bold;
                    width: 40%;
                }
                .detail-value {
                    width: 60%;
                    text-align: right;
                }
                .amount-section {
                    margin-bottom: 20px;
                    padding: 15px;
                    background: #f0f0f0;
                    border: 2px solid #333;
                    text-align: right;
                }
                .amount-label {
                    font-size: 10pt;
                    color: #666;
                }
                .amount-value {
                    font-size: 16pt;
                    font-weight: bold;
                    margin-top: 5px;
                }
                .allocations-table {
                    width: 100%;
                    margin-bottom: 20px;
                }
                .allocations-table th {
                    background: #333;
                    color: white;
                    padding: 8px;
                    text-align: left;
                    font-size: 10pt;
                }
                .allocations-table td {
                    padding: 8px;
                    border-bottom: 1px solid #ddd;
                }
                .allocations-table tr:nth-child(even) {
                    background: #f9f9f9;
                }
                .notes-section {
                    margin-bottom: 20px;
                    padding: 10px;
                    border: 1px solid #ddd;
                    background: #f9f9f9;
                    font-size: 9pt;
                }
                .footer {
                    margin-top: 20px;
                    padding-top: 10px;
                    border-top: 2px solid #333;
                    text-align: center;
                    font-size: 9pt;
                    color: #666;
                }
                .signature-area {
                    margin-top: 20px;
                    display: flex;
                    justify-content: space-around;
                }
                .signature-line {
                    flex: 1;
                    text-align: center;
                }
                .signature-line p {
                    margin-top: 50px;
                    border-top: 1px solid #000;
                    padding-top: 5px;
                    font-size: 9pt;
                }
                @media print {
                    body { padding: 0; margin: 0; }
                    .or-container { border: none; padding: 0; }
                }
            </style>
        `;
        
        const printDocument = `
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <title>Official Receipt - ${orNo}</title>
            </head>
            <body>
                ${printStyles}
                <div class="or-container">
                    <div class="or-header">
                        <h2>OFFICIAL RECEIPT</h2>
                        <p>Payment Receipt for Invoice Collections</p>
                    </div>
                    
                    <div class="or-info">
                        <div class="info-block">
                            <div class="info-label">Receipt No.</div>
                            <div class="info-value"><strong>${orNo}</strong></div>
                        </div>
                        <div class="info-block">
                            <div class="info-label">Date</div>
                            <div class="info-value"><strong>${orDate}</strong></div>
                        </div>
                    </div>
                    
                    <div class="customer-section">
                        <div class="customer-label">RECEIVED FROM:</div>
                        <div class="customer-name">${customer}</div>
                    </div>
                    
                    <div class="payment-details">
                        <div class="detail-row">
                            <span class="detail-label">Payment Method:</span>
                            <span class="detail-value">${$('#viewPaymentContent').find('th:contains("Payment Method")').next().text().trim()}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Created By:</span>
                            <span class="detail-value">${$('#viewPaymentContent').find('th:contains("Created By")').next().text().trim()}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Created Date/Time:</span>
                            <span class="detail-value">${$('#viewPaymentContent').find('th:contains("Created At")').next().text().trim()}</span>
                        </div>
                    </div>
                    
                    <div class="amount-section">
                        <div class="amount-label">TOTAL AMOUNT RECEIVED</div>
                        <div class="amount-value">${amount}</div>
                    </div>
                    
                    <h3 style="font-size: 12pt; margin-bottom: 10px;">INVOICE / DR ALLOCATION DETAILS</h3>
                    <table class="allocations-table">
                        <thead>
                            <tr>
                                <th>Invoice / DR Number</th>
                                <th style="text-align: right; width: 30%;">Amount Applied</th>
                                <th style="text-align: right; width: 30%;">Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${$('#viewPaymentContent').find('table').eq(1).html() || '<tr><td colspan="3" style="text-align: center; color: #999;">No allocations</td></tr>'}
                        </tbody>
                    </table>
                    
                    <div class="notes-section">
                        <strong>Notes:</strong><br>
                        ${$('#viewPaymentContent').find('th:contains("Notes")').next().text().trim() || 'None'}
                    </div>
                    
                    <div class="signature-area">
                        <div class="signature-line">
                            <p>Received By</p>
                        </div>
                        <div class="signature-line">
                            <p>Authorized By</p>
                        </div>
                    </div>
                    
                    <div class="footer">
                        <p>This is an official receipt. Please keep this document for your records.</p>
                        <p>Generated on: ${new Date().toLocaleString()}</p>
                    </div>
                </div>
            </body>
            </html>
        `;
        
        document.body.innerHTML = printDocument;
        window.print();
        document.body.innerHTML = originalContents;
        location.reload();
    });

    // Delete Payment - Populate Modal
    $(document).on('click', '.delete-btn', function () {
        $('#deletePaymentId').val($(this).data('id'));
        const orNo = $(this).data('or_no');
        $('#deletePaymentConfirmationText').html('Are you sure you want to delete payment <strong>' + orNo + '</strong>? This will also remove all invoice allocations.');
    });

    // Duplicate Check Function
    function checkDuplicate(field, value, paymentId, errorSelector, inputSelector, submitSelector) {
        const spinner = $(inputSelector).next('.input-group-text');
        spinner.show();

        $.ajax({
            url: 'check_duplicate.php',
            type: 'POST',
            data: {
                field: field,
                value: value,
                payment_id: paymentId
            },
            dataType: 'json',
            success: function (response) {
                const errorEl = $(errorSelector);
                const inputEl = $(inputSelector);
                const submitBtn = $(submitSelector);

                if (response.duplicate) {
                    inputEl.addClass('is-invalid');
                    errorEl.text('This ' + field.replace('_', ' ') + ' is already taken.');
                    submitBtn.prop('disabled', true);
                } else {
                    inputEl.removeClass('is-invalid');
                    errorEl.text('');

                    const modal = inputEl.closest('.modal');
                    if (modal.find('.is-invalid').length === 0) {
                        submitBtn.prop('disabled', false);
                    }
                }
            },
            error: function () {
                const errorEl = $(errorSelector);
                const inputEl = $(inputSelector);
                errorEl.text('Error checking for duplicates.');
                inputEl.addClass('is-invalid');
                $(submitSelector).prop('disabled', true);
            },
            complete: function () {
                spinner.hide();
            }
        });
    }

    // OR Number validation
    $('#addOrNo').on('input', function () {
        const value = $(this).val();
        if (value.length > 0) {
            checkDuplicate('or_no', value, null, '#addOrNoError', '#addOrNo', '#addPaymentSubmit');
        } else {
            $(this).removeClass('is-invalid');
            $('#addPaymentSubmit').prop('disabled', false);
        }
    });

    $('#editOrNo').on('input', function () {
        const value = $(this).val();
        const paymentId = $('#editPaymentId').val();
        if (value.length > 0) {
            checkDuplicate('or_no', value, paymentId, '#editOrNoError', '#editOrNo', '#editPaymentSubmit');
        } else {
            $(this).removeClass('is-invalid');
            $('#editPaymentSubmit').prop('disabled', false);
        }
    });

    // Form submission handlers
    $('#addPaymentForm').on('submit', function (e) {
        e.preventDefault();

        // Collect invoice allocations
        const allocations = [];
        $('.add-apply-amount').each(function () {
            const amount = parseFloat($(this).val()) || 0;
            if (amount > 0) {
                allocations.push({
                    invoice_id: $(this).data('invoice-id'),
                    amount_applied: amount
                });
            }
        });

        $('#addInvoiceAllocations').val(JSON.stringify(allocations));

        const submitBtn = $('#addPaymentSubmit');
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Saving...');

        $.ajax({
            url: 'process.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    const modal = bootstrap.Modal.getInstance($('#addPaymentModal')[0]);
                    if (modal) {
                        modal.hide();
                    }

                    // Force cleanup backdrop and body state
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open').removeAttr('style');

                    showToast('success', response.message);
                    $('#paymentsTable').DataTable().ajax.reload(null, false);
                    $('#addPaymentForm')[0].reset();
                    $('#addInvoiceList').html('<p class="text-muted">Select a customer to view unpaid invoices</p>');
                    $('#addRemainingAmount').text('0.00');
                } else {
                    showToast('error', response.message);
                }
                submitBtn.prop('disabled', false).html('Record Payment');
            },
            error: function (xhr) {
                let errorMsg = 'An error occurred while recording the payment';
                try {
                    const response = JSON.parse(xhr.responseText);
                    errorMsg = response.message || errorMsg;
                } catch (e) { }
                showToast('error', errorMsg);
                submitBtn.prop('disabled', false).html('Record Payment');
            }
        });
    });

    $('#editPaymentForm').on('submit', function (e) {
        e.preventDefault();

        // Collect invoice allocations for edit
        const allocations = [];
        $('.edit-apply-amount').each(function () {
            const amount = parseFloat($(this).val()) || 0;
            if (amount > 0) {
                allocations.push({
                    invoice_id: $(this).data('invoice-id'),
                    amount_applied: amount
                });
            }
        });

        $('#editInvoiceAllocations').val(JSON.stringify(allocations));

        const submitBtn = $('#editPaymentSubmit');
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Updating...');

        $.ajax({
            url: 'process.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    const modal = bootstrap.Modal.getInstance($('#editPaymentModal')[0]);
                    if (modal) {
                        modal.hide();
                    }

                    // Force cleanup backdrop and body state
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open').removeAttr('style');

                    showToast('success', response.message);
                    $('#paymentsTable').DataTable().ajax.reload(null, false);
                } else {
                    showToast('error', response.message);
                }
                submitBtn.prop('disabled', false).html('Update Payment');
            },
            error: function (xhr) {
                let errorMsg = 'An error occurred while updating the payment';
                try {
                    const response = JSON.parse(xhr.responseText);
                    errorMsg = response.message || errorMsg;
                } catch (e) { }
                showToast('error', errorMsg);
                submitBtn.prop('disabled', false).html('Update Payment');
            }
        });
    });

    $('#deletePaymentForm').on('submit', function (e) {
        e.preventDefault();

        const submitBtn = $(this).find('button[type="submit"]');
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Deleting...');

        $.ajax({
            url: 'process.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    const modal = bootstrap.Modal.getInstance($('#deletePaymentModal')[0]);
                    if (modal) {
                        modal.hide();
                    }

                    // Force cleanup backdrop and body state
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open').removeAttr('style');

                    showToast('success', response.message);
                    $('#paymentsTable').DataTable().ajax.reload(null, false);
                } else {
                    showToast('error', response.message);
                }
                submitBtn.prop('disabled', false).html('Delete');
            },
            error: function (xhr) {
                let errorMsg = 'An error occurred while deleting the payment';
                try {
                    const response = JSON.parse(xhr.responseText);
                    errorMsg = response.message || errorMsg;
                } catch (e) { }
                showToast('error', errorMsg);
                submitBtn.prop('disabled', false).html('Delete');
            }
        });
    });

    // Reset modal state on close and cleanup backdrops
    $('#addPaymentModal, #editPaymentModal, #deletePaymentModal').on('hidden.bs.modal', function () {
        $(this).find('input.is-invalid').removeClass('is-invalid');
        $(this).find('.invalid-feedback').text('');
        
        // Ensure complete backdrop cleanup
        setTimeout(function () {
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open').removeAttr('style');
        }, 300);
    });

    // Filter customers on modal open and reset forms
    $('#addPaymentModal').on('shown.bs.modal', function() {
        filterCustomersByCategory(null, '#addCustomerId');
        $(this).find('button[type="submit"]').prop('disabled', false);
        $('#addPaymentForm')[0].reset();

        if (paymentCategory === 'private') {
            const now = new Date();
            const timestamp = now.getFullYear() +
                String(now.getMonth() + 1).padStart(2, '0') +
                String(now.getDate()).padStart(2, '0') + '-' +
                String(now.getHours()).padStart(2, '0') +
                String(now.getMinutes()).padStart(2, '0') +
                String(now.getSeconds()).padStart(2, '0');
            $('#addOrNo').val('PVT-' + timestamp + '-' + Math.floor(Math.random() * 1000));
        }

        $('#addInvoiceList').html('<p class="text-muted">Select a customer to view unpaid invoices</p>');
        $('#addCheckFields').hide();
        updateRemainingAmount('add');
    });

    $('#editPaymentModal').on('shown.bs.modal', function() {
        // For edit modal, just update the amounts display - don't touch customer or form
        updateRemainingAmount('edit');
    });

    // Force cleanup on any modal close via buttons
    $(document).on('click', '.btn-close, .btn-secondary', function () {
        setTimeout(function () {
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open').removeAttr('style');
        }, 300);
    });

    function showToast(type, message) {
        var toastLiveExample = document.getElementById('liveToast');
        var toastBody = toastLiveExample.querySelector('.toast-body');

        var toastHeader = toastLiveExample.querySelector('.toast-header');
        toastHeader.classList.remove('bg-success', 'bg-danger', 'text-white');
        if (type === 'success') {
            toastHeader.classList.add('bg-success', 'text-white');
        } else {
            toastHeader.classList.add('bg-danger', 'text-white');
        }

        toastBody.textContent = message;
        var toast = new bootstrap.Toast(toastLiveExample);
        toast.show();
    }
});
