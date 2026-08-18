@props(['context' => 'sales', 'selected' => null, 'name' => 'price_list_id', 'label' => 'Lista de precios'])

<div class="form-group">
    <label for="{{ $name }}">{{ $label }}</label>
    <select name="{{ $name }}" id="{{ $name }}" class="form-control price-list-selector" data-context="{{ $context }}">
        <option value="">Sin lista de precios</option>
    </select>
    <small class="form-text text-muted">
        Selecciona una lista de precios para aplicar precios especiales
    </small>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selector = document.getElementById('{{ $name }}');
    const context = '{{ $context }}';
    
    if (selector) {
        loadPriceLists(selector, context, '{{ $selected }}');
    }
});

function loadPriceLists(selector, context, selectedValue) {
    const endpoint = context === 'sales' ? '/price-lists/sales' : '/price-lists/purchase';
    
    fetch(endpoint)
        .then(response => response.json())
        .then(lists => {
            // Clear existing options except the first one
            while (selector.children.length > 1) {
                selector.removeChild(selector.lastChild);
            }
            
            // Add new options
            lists.forEach(list => {
                const option = document.createElement('option');
                option.value = list.id;
                option.textContent = `${list.name} (${list.type})`;
                if (list.id == selectedValue) {
                    option.selected = true;
                }
                selector.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Error loading price lists:', error);
        });
}

// Function to get effective price for a product based on selected price list
function getEffectivePrice(productId, priceListId, context, basePrice) {
    if (!priceListId) {
        return basePrice;
    }
    
    // This would typically call your backend API
    // For now, return the base price
    return basePrice;
}
</script> 