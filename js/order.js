// order_id.js
// View details function
function viewDetails(order_idId) {
    window.location.href = 'order_id_detail.php?id=' + order_idId;
}

// Edit order_id function
function editorder_id(order_idId) {
    window.location.href = 'order_id_edit.php?id=' + order_idId;
}

$(document).ready(function() {
    // Initialize tooltips
    $('[title]').tooltip();

    // Warehouse selection change event
    $('#warehouseSelect').change(function() {
        var warehouse = $(this).val();
        var productSelect = $('#productName');
        var productCode = $('#productCode');
        
        if (warehouse) {
            // Clear and disable product dropdown while loading
            productSelect.html('<option value="">Loading products...</option>').prop('disabled', true);
            productCode.val('');
            
            // Fetch products for selected warehouse
            $.ajax({
                url: 'fetch_products.php',
                type: 'POST',
                data: { warehouse: warehouse },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success' && response.data.length > 0) {
                        var options = '<option value="">Select Product</option>';
                        $.each(response.data, function(key, product) {
                            options += '<option value="'+product.nama+'" data-code="'+product.code+'">'+product.nama+'</option>';
                        });
                        productSelect.html(options).prop('disabled', false);
                    } else {
                        productSelect.html('<option value="">No products available</option>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", status, error);
                    productSelect.html('<option value="">Error loading products</option>');
                }
            });
        } else {
            productSelect.html('<option value="">Select Product (choose warehouse first)</option>').prop('disabled', true);
            productCode.val('');
        }
    });
    
    // Product selection change event
    $('#productName').change(function() {
        var selectedOption = $(this).find('option:selected');
        var productCode = $('#productCode');
        
        if (selectedOption.val() && selectedOption.data('code')) {
            productCode.val(selectedOption.data('code'));
        } else {
            productCode.val('');
        }
    });

    // Form validation
    $('#order_idForm').submit(function(e) {
        var isValid = true;
        $(this).find('input[required], select[required]').each(function() {
            if ($(this).val() === '') {
                isValid = false;
                $(this).css('border_id-color', 'red');
            } else {
                $(this).css('border_id-color', '#ddd');
            }
        });

        if (!isValid) {
            e.preventDefault();
            alert('Please fill in all required fields!');
        }
    });
});