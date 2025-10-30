

document.addEventListener('DOMContentLoaded', () => {
    const orderItemsList = document.getElementById('ordered-items-list');
    const orderTotalDisplay = document.getElementById('order-total');
    const cashTenderedInput = document.getElementById('cash-tendered');
    const payButton = document.getElementById('pay-button');
    const changeDisplay = document.getElementById('change-display');

    let currentOrder = {};
    let menuPrices = {}; // Stores prices keyed by product name for quick lookup


    document.querySelectorAll('.menu-item').forEach(item => {
        const name = item.querySelector('h3').textContent.trim();
        const priceText = item.querySelector('p').textContent.replace('PHP', '').trim();
        const price = parseFloat(priceText);
        const productId = item.getAttribute('data-product-id');

        menuPrices[name] = { id: productId, price: price };
    });


    const updatedOrder = {};
    for (const name in currentOrder) {
        const item = currentOrder[name];
        const productData = menuPrices[name];
        if (productData) {
            updatedOrder[productData.id] = item;
        }
    }
    currentOrder = updatedOrder;

    function updateOrderDisplay() {
        let total = 0;
        orderItemsList.innerHTML = '';

        for (const id in currentOrder) {
            const item = currentOrder[id];
            const subtotal = item.price * item.qty;
            total += subtotal;

            const itemHTML = `
                <div class="order-item">
                    <span class="item-name">${item.name}</span>
                    <span class="item-details">
                        <span class="item-price">${item.price.toFixed(2)} x</span>
                        <input type="number" class="item-qty-input" data-product-id="${id}" value="${item.qty}" min="1" style="width: 50px;">
                        <span class="item-subtotal">${subtotal.toFixed(2)} PHP</span>
                        <button class="remove-item-btn" data-product-id="${id}" style="font-size: 12px; padding: 2px 5px;">&times;</button>
                    </span>
                </div>
            `;
            orderItemsList.innerHTML += itemHTML;
        }

        orderTotalDisplay.textContent = total.toFixed(2) + ' PHP';
    }

    window.addToOrder = function(button) {
        const itemDiv = button.closest('.menu-item');
        const name = itemDiv.querySelector('h3').textContent.trim();
        const qtyInput = itemDiv.querySelector('.qty-input');
        let qty = parseInt(qtyInput.value);

        if (qty < 1) { qty = 1; }

        const productData = menuPrices[name];
        const productId = productData.id;

        if (currentOrder[productId]) {
            currentOrder[productId].qty += qty;
            Swal.fire({
                icon: 'success',
                title: 'Item Updated',
                text: `Added ${qty} more ${name}(s) to order.`,
                timer: 1500,
                showConfirmButton: false
            });
        } else {
            currentOrder[productId] = {
                name: name,
                price: productData.price,
                qty: qty
            };
            Swal.fire({
                icon: 'success',
                title: 'Item Added',
                text: `Added ${qty} x ${name} to order.`,
                timer: 1500,
                showConfirmButton: false
            });
        }

        qtyInput.value = 1;
        updateOrderDisplay();
    };

    orderItemsList.addEventListener('click', (e) => {
        if (e.target.classList.contains('remove-item-btn')) {
            const productId = e.target.getAttribute('data-product-id');
            const itemName = currentOrder[productId].name;
            delete currentOrder[productId];
            updateOrderDisplay();
            Swal.fire({
                icon: 'info',
                title: 'Item Removed',
                text: `${itemName} removed from order.`,
                timer: 1500,
                showConfirmButton: false
            });
        }
    });

    orderItemsList.addEventListener('input', (e) => {
        if (e.target.classList.contains('item-qty-input')) {
            const productId = e.target.getAttribute('data-product-id');
            const newQty = parseInt(e.target.value);
            if (newQty > 0 && currentOrder[productId]) {
                currentOrder[productId].qty = newQty;
                updateOrderDisplay();
            }
        }
    });


    payButton.addEventListener('click', () => {
        const total = parseFloat(orderTotalDisplay.textContent.replace(' PHP', ''));
        const cash = parseFloat(cashTenderedInput.value);

        if (isNaN(cash) || cash < total) {
            Swal.fire({
                icon: 'error',
                title: 'Insufficient Cash',
                text: 'Please enter an amount equal to or greater than the total.',
            });
            return;
        }

        const change = cash - total;
        changeDisplay.textContent = `Change: ${change.toFixed(2)} PHP`;


        Swal.fire({
            title: 'Processing Payment...',
            text: 'Please wait while we process your order.',
            allowOutsideClick: false,
            showConfirmButton: false,
            willOpen: () => {
                Swal.showLoading();
            }
        });


        const orderData = {
            items: currentOrder,
            total_amount: total,
            cash_tendered: cash,
            change_given: change
        };


        fetch('process_order.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(orderData)
        })
        .then(response => response.json())
        .then(data => {
            Swal.close(); // Close processing modal
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Payment Successful!',
                    html: `Order processed successfully!<br>Change: <strong>${data.change_given} PHP</strong>`,
                    confirmButtonText: 'OK'
                }).then(() => {
                    // Reset order
                    currentOrder = {};
                    updateOrderDisplay();
                    cashTenderedInput.value = '';
                    changeDisplay.textContent = '';
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Payment Failed',
                    text: data.error,
                });
            }
        })
        .catch(error => {
            Swal.close(); // Close processing modal
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Connection Error',
                text: 'An error occurred during payment processing. Please try again.',
            });
        });
    });
});
    // ---