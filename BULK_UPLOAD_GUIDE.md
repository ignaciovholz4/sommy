# Bulk Product Upload Guide

## Overview

The bulk product upload feature allows you to import multiple products at once using Excel files (.xlsx, .xls, or .csv). This feature includes support for all product fields including the new **Location** and **Brand** fields, with **Category** auto-sorting and **Discount** as percentage.

## Features

### ✅ **New Fields Added**
- **Location** (ubicacion) - Optional warehouse location
- **Brand** (marca) - Optional product brand
- **Category** - Auto-sorted alphabetically
- **Discount** - Now percentage-based (0-100) instead of flat amount

### ✅ **Excel Support**
- **Formats**: .xlsx, .xls, .csv
- **Template Download**: Pre-formatted Excel template with sample data
- **Validation**: Comprehensive data validation with detailed error reporting
- **Batch Processing**: Efficient processing of large files

### ✅ **User Interface**
- **Progress Tracking**: Real-time upload progress
- **Error Reporting**: Detailed error messages for failed rows
- **Success Statistics**: Summary of imported vs failed records
- **Field Reference**: Complete field documentation

## Access

### **URL**: `http://fffs.localhost:8000/almacen/bulk-upload`

### **Navigation**: 
- Go to **Almacén** → **Bulk Upload** in the sidebar

## How to Use

### 1. **Download Template**
1. Click the "Download Template" button
2. The template includes:
   - Sample data for reference
   - Proper column headers
   - Data validation comments
   - Formatted cells

### 2. **Prepare Your Data**
Fill in the Excel file with your product data:

| Field | Required | Type | Description | Example |
|-------|----------|------|-------------|---------|
| **nombre** | ✅ Yes | Text | Product name | Laptop HP Pavilion |
| **categoria** | ✅ Yes | Text | Product category | Electrónicos |
| **codigo** | ✅ Yes | Text | Unique product code | LAP001 |
| stock | ❌ No | Number | Quantity in stock | 10.00 |
| precio_compra | ❌ No | Number | Purchase price | 800.00 |
| precio_venta | ❌ No | Number | Sale price | 1200.00 |
| descripcion | ❌ No | Text | Product description | Laptop with Intel i5 |
| **descuento** | ❌ No | Number | Discount percentage (0-100) | 15.00 |
| iva | ❌ No | Number | IVA percentage | 21.00 |
| tipo_producto | ❌ No | Text | Product type | Laptop |
| **ubicacion** | ❌ No | Text | Warehouse location | Estante A-1 |
| **marca** | ❌ No | Text | Product brand | HP |

### 3. **Upload File**
1. Click "Choose file" and select your Excel file
2. Click "Upload Products"
3. Monitor the progress bar
4. Review the results

## Field Details

### **Required Fields**

#### **nombre** (Product Name)
- **Type**: Text
- **Max Length**: 200 characters
- **Example**: "Laptop HP Pavilion"
- **Notes**: Must be unique and descriptive

#### **categoria** (Category)
- **Type**: Text
- **Max Length**: 100 characters
- **Example**: "Electrónicos"
- **Notes**: Must match an existing category in the system
- **Auto-sorting**: Categories are automatically sorted alphabetically

#### **codigo** (Product Code)
- **Type**: Text
- **Max Length**: 50 characters
- **Example**: "LAP001"
- **Notes**: Must be unique across all products

### **Optional Fields**

#### **stock** (Stock Quantity)
- **Type**: Number
- **Min Value**: 0
- **Example**: 10.00
- **Default**: 0

#### **precio_compra** (Purchase Price)
- **Type**: Number
- **Min Value**: 0
- **Example**: 800.00
- **Default**: 0

#### **precio_venta** (Sale Price)
- **Type**: Number
- **Min Value**: 0
- **Example**: 1200.00
- **Default**: 0

#### **descripcion** (Description)
- **Type**: Text
- **Max Length**: 500 characters
- **Example**: "Laptop HP Pavilion with Intel i5 processor"
- **Default**: Empty

#### **descuento** (Discount) ⭐ **NEW FORMAT**
- **Type**: Number (Percentage)
- **Range**: 0-100
- **Example**: 15.00 (for 15% discount)
- **Default**: 0
- **Notes**: Now percentage-based instead of flat amount

#### **iva** (IVA)
- **Type**: Number
- **Min Value**: 0
- **Example**: 21.00
- **Default**: 0

#### **tipo_producto** (Product Type)
- **Type**: Text
- **Max Length**: 100 characters
- **Example**: "Laptop"
- **Notes**: Must match an existing product type in the system

#### **ubicacion** (Location) ⭐ **NEW FIELD**
- **Type**: Text
- **Max Length**: 200 characters
- **Example**: "Estante A-1"
- **Default**: Empty
- **Notes**: Optional warehouse location for inventory management

#### **marca** (Brand) ⭐ **NEW FIELD**
- **Type**: Text
- **Max Length**: 100 characters
- **Example**: "HP"
- **Default**: Empty
- **Notes**: Optional product brand for categorization

## Validation Rules

### **Data Validation**
- **Required fields** must not be empty
- **Numeric fields** must contain valid numbers
- **Discount** must be between 0 and 100
- **Product codes** must be unique
- **Categories** must exist in the system
- **Product types** must exist in the system (if provided)

### **Error Handling**
- **Duplicate codes**: Products with existing codes are skipped
- **Invalid categories**: Rows with non-existent categories are skipped
- **Invalid data types**: Rows with wrong data types are skipped
- **Missing required fields**: Rows with missing required fields are skipped

## Upload Process

### **Step-by-Step Process**
1. **File Validation**: Check file format and size
2. **Data Parsing**: Read Excel/CSV data
3. **Row Processing**: Process each row individually
4. **Validation**: Validate each product's data
5. **Database Insert**: Insert valid products
6. **Error Collection**: Collect errors for reporting
7. **Results Display**: Show success/error statistics

### **Performance Features**
- **Batch Processing**: Processes 100 rows at a time
- **Memory Efficient**: Uses chunked reading for large files
- **Progress Tracking**: Real-time upload progress
- **Error Recovery**: Continues processing even if some rows fail

## Results and Reporting

### **Success Statistics**
- **Total Processed**: Number of rows in the file
- **Successfully Imported**: Number of products created
- **Errors**: Number of rows that failed

### **Error Details**
- **Row-specific errors**: Detailed error messages for each failed row
- **Error types**: Validation errors, duplicate codes, missing categories, etc.
- **Error location**: Row number and specific field causing the error

## Technical Implementation

### **Database Changes**
```sql
-- New fields added to productos table
ALTER TABLE productos 
ADD COLUMN ubicacion VARCHAR(200) NULL COMMENT 'Product location in warehouse',
ADD COLUMN marca VARCHAR(100) NULL COMMENT 'Product brand';

-- Modified discount field to be percentage-based
ALTER TABLE productos 
MODIFY COLUMN descuento DECIMAL(5,2) COMMENT 'Discount percentage (0-100)';
```

### **Files Created/Modified**
- **Migration**: `database/migrations/2024_12_19_000000_add_bulk_upload_fields_to_productos_table.php`
- **Model**: `app/Models/Articulo.php` (updated fillable fields)
- **Import Class**: `app/Imports/BulkProductImport.php`
- **Export Class**: `app/Exports/ProductTemplateExport.php`
- **Controller**: `app/Http/Controllers/Articulo/ArticuloController.php` (added methods)
- **View**: `resources/views/almacen/articulo/bulk_upload.blade.php`
- **Routes**: `routes/web.php` (added bulk upload routes)

### **Routes Added**
```php
Route::get('almacen/bulk-upload', [ArticuloController::class, 'showBulkUpload'])->name('bulk_upload');
Route::get('almacen/download-template', [ArticuloController::class, 'downloadTemplate'])->name('download_template');
Route::post('almacen/process-bulk-upload', [ArticuloController::class, 'processBulkUpload'])->name('process_bulk_upload');
Route::get('almacen/get-categories', [ArticuloController::class, 'getCategories'])->name('get_categories');
Route::get('almacen/get-product-types', [ArticuloController::class, 'getProductTypes'])->name('get_product_types');
Route::get('almacen/get-brands', [ArticuloController::class, 'getBrands'])->name('get_brands');
Route::get('almacen/get-locations', [ArticuloController::class, 'getLocations'])->name('get_locations');
```

## Best Practices

### **Data Preparation**
1. **Use the template**: Always start with the provided template
2. **Check categories**: Ensure categories exist in the system
3. **Unique codes**: Make sure product codes are unique
4. **Valid data**: Ensure all data is in the correct format
5. **Test with small files**: Test with a few products first

### **File Management**
1. **File size**: Keep files under 10MB for optimal performance
2. **Format**: Use .xlsx for best compatibility
3. **Backup**: Keep a backup of your original data
4. **Validation**: Review the template structure before filling

### **Error Handling**
1. **Review errors**: Always check the error details
2. **Fix and retry**: Correct errors and upload again
3. **Partial imports**: Valid products are imported even if some fail
4. **Duplicate handling**: Remove duplicate codes before uploading

## Troubleshooting

### **Common Issues**

#### **"Category not found" Error**
- **Cause**: Category name doesn't match existing categories
- **Solution**: Check available categories in the system
- **Prevention**: Use exact category names from the system

#### **"Product code already exists" Error**
- **Cause**: Product code is already in use
- **Solution**: Use unique product codes
- **Prevention**: Check existing products before uploading

#### **"Discount must be between 0 and 100" Error**
- **Cause**: Discount value is outside the valid range
- **Solution**: Use percentage values between 0 and 100
- **Prevention**: Remember discount is now percentage-based

#### **"File format not supported" Error**
- **Cause**: File is not .xlsx, .xls, or .csv
- **Solution**: Convert file to supported format
- **Prevention**: Use the provided template

### **Performance Issues**
- **Large files**: Break large files into smaller chunks
- **Slow uploads**: Check internet connection and server performance
- **Memory issues**: Use .xlsx format instead of .csv for large files

## Security Considerations

### **File Upload Security**
- **File size limit**: 10MB maximum
- **File type validation**: Only Excel and CSV files allowed
- **Content validation**: All data is validated before database insertion
- **SQL injection protection**: Uses Laravel's Eloquent ORM

### **Access Control**
- **Authentication required**: Must be logged in
- **Permission check**: Requires 'almacen_articulo.index' permission
- **CSRF protection**: All forms include CSRF tokens

## Support

### **Getting Help**
1. **Check the template**: Use the provided template as reference
2. **Review field reference**: Check the field reference table on the upload page
3. **Test with sample data**: Use the sample data in the template
4. **Check error details**: Review detailed error messages

### **Contact Information**
- **Technical issues**: Contact the development team
- **Data issues**: Review the validation rules and error messages
- **Feature requests**: Submit through the appropriate channels

## Conclusion

The bulk product upload feature provides a powerful and efficient way to import large numbers of products with comprehensive validation and error reporting. The new fields (Location and Brand) enhance inventory management, while the percentage-based discount system provides more flexibility for pricing strategies.

The feature is designed to be user-friendly while maintaining data integrity and providing detailed feedback for successful and failed imports. 