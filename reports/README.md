# Statement of Accounts Module

## Overview
The Statement of Accounts (SOA) module provides comprehensive reporting functionality for tracking customer invoices, payments, and outstanding balances.

## Files Structure
```
/reports/
├── index.php                 - Main SOA interface with filters
├── get_customers_list.php    - AJAX endpoint for customer dropdown
├── get_soa_data.php         - AJAX endpoint for generating SOA data
├── export_soa.php           - CSV export functionality
└── README.md                - This file
```

## Features

### 1. Dynamic Filtering
- **Customer Selection**: Filter by specific customer or view all customers
- **Date Range**: From/To date filtering for invoice and payment records
- **Status Filter**: Filter by invoice status (Unpaid, Partially Paid, Paid)
- **Customer Type**: Filter by customer type (Private, Business, Government)

### 2. Report Display
The SOA report displays:

#### Customer Information
- Customer name and business name
- Customer type (with badge)
- Address and contact details

#### Invoice Summary Table
- Invoice Number
- Invoice Date
- Total Amount
- Withholding Tax (for Government customers)
- Net Amount (for Government customers)
- Amount Paid
- Balance Due
- Payment Status (with colored badges)

#### Payment History Table
- Official Receipt (OR) Number
- Payment Date
- Payment Method (Cash-Private, Cash-DR V, Bank, Government)
- Amount Received
- Invoices the payment was applied to

#### Financial Summary
- Total Invoiced Amount
- Total Withholding Tax (Government only)
- Net Amount (Government only)
- Total Paid
- **Outstanding Balance** (highlighted)

### 3. Export Capabilities

#### Print View
- Click "Print" button to generate print-friendly version
- Automatically formats for paper output
- Includes all report data and formatting

#### CSV Export
- Click "Export" button to download CSV file
- Filename format: `SOA_CustomerName_YYYY-MM-DD.csv`
- Compatible with Excel and Google Sheets
- Includes UTF-8 BOM for proper character encoding
- Contains all invoice and payment data with summary

### 4. Smart Features

#### Government vs Private Handling
- Automatically detects customer type
- Shows withholding tax columns for Government customers
- Hides withholding columns for Private/Business customers
- Displays appropriate payment methods per customer type

#### Real-time Calculations
- Automatically calculates outstanding balances
- Sums up all invoices and payments
- Computes withholding tax totals
- Updates summary dynamically

#### Responsive UI
- Bootstrap 5 design matches existing system
- Mobile-friendly layout
- Card-based interface
- Loading indicators for AJAX operations

## Usage

### Basic Usage
1. Navigate to **Reports** → **Statement of Accounts** from sidebar
2. Select filter options:
   - Choose a customer (or leave blank for all)
   - Set date range (optional)
   - Select status filter (optional)
   - Choose customer type (optional)
3. Click **"Generate Report"** button
4. Review the displayed SOA

### Printing a Report
1. Generate the report using filters
2. Click **"Print"** button
3. Use browser's print dialog to print or save as PDF

### Exporting to CSV
1. Generate the report using filters
2. Click **"Export"** button
3. CSV file will download automatically
4. Open in Excel, Google Sheets, or any spreadsheet application

## Technical Details

### Database Queries
The module queries the following tables:
- `tbl_customers` - Customer information
- `tbl_charge_invoices` - Invoice data
- `tbl_charge_invoice_items` - Invoice line items (for totals)
- `tbl_official_receipts` - Payment records
- `tbl_or_invoice` - Junction table linking payments to invoices
- `tbl_app_setting` - Company information for report header

### AJAX Endpoints

#### GET /reports/get_customers_list.php
Returns list of active customers for dropdown
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Customer Name",
      "business_name": "Business Name",
      "customer_type": "Private"
    }
  ]
}
```

#### GET /reports/get_soa_data.php
Generates SOA data based on filters
Parameters:
- `customer_id` (optional)
- `date_from` (optional)
- `date_to` (optional)
- `status` (optional)
- `customer_type` (optional)

#### GET /reports/export_soa.php
Exports SOA to CSV with same parameters as get_soa_data.php

### Security
- Session validation on all pages
- SQL injection prevention with prepared statements
- XSS prevention with htmlspecialchars()
- User authentication required
- Error logging for debugging

## Integration with System

### Navigation
- Added to sidebar under "REPORTS" section
- Menu item: "Statement of Accounts"
- Active state highlighting when on reports page

### Design Consistency
- Uses same UI patterns as Users module
- Bootstrap 5 AdminLTE theme
- Toast notifications for user feedback
- Card-based layout
- Consistent button styling and icons

### Data Flow
```
User Selects Filters
    ↓
Click Generate Report
    ↓
AJAX Request to get_soa_data.php
    ↓
Query Database (Invoices + Payments)
    ↓
Calculate Totals & Balances
    ↓
Return JSON Response
    ↓
JavaScript Renders HTML Table
    ↓
Display Results
    ↓
Enable Print/Export Buttons
```

## Future Enhancements (Planned)

### Phase 2 - Additional Reports
- Collection Summary Report
- Aging Report (AR Aging)
- Withholding Tax Report
- Sales/Invoice Report
- Payment Analytics Dashboard

### Phase 3 - Advanced Features
- PDF export (using TCPDF/mPDF)
- Email SOA to customers
- Scheduled report generation
- Report templates
- Custom report builder

## Troubleshooting

### No data showing
- Check filter criteria (date range might be too narrow)
- Verify customer has invoices in the system
- Check database connection

### Export not working
- Ensure PHP has write permissions
- Check for special characters in customer names
- Verify CSV headers are being sent

### Print formatting issues
- Use modern browsers (Chrome, Firefox, Edge)
- Check print preview before printing
- Adjust page margins in print settings

## Support
For issues or feature requests, contact the development team or refer to the main system documentation.

---
**Module Version**: 1.0
**Created**: December 2025
**Template Based On**: Users Module (index.php)
**Status**: Production Ready ✅
