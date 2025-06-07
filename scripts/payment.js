function getFormData() {
    return {
        cons_acc_code: document.getElementById('pay-cons-id').value.trim(),
        cons_charges_id: document.getElementById('pay-cons-charges-id').value.trim(),
        cons_month_bill: document.getElementById('pay-cons-month-bill').value.trim(),
        cons_amount: document.getElementById('pay-cons-amount').value.trim(),
        cons_change: document.getElementById('pay-cons-change').value.trim(),
        cons_charges: document.getElementById('pay-cons-charges').value.trim(),
        cons_payment_method: document.getElementById('pay-cons-payment-method').value.trim()
    };
}

// Change calculation for payments
function updateChange() {
    const amount = parseFloat(document.getElementById('pay-cons-amount').value) || 0;
    const charges = parseFloat(document.getElementById('pay-cons-charges').value) || 0;
    document.getElementById('pay-cons-change').value = (amount - charges).toFixed(2);
}

// Set up change calculation listeners
if (document.getElementById('pay-cons-amount')) {
    document.getElementById('pay-cons-amount').addEventListener('input', updateChange);
    document.getElementById('pay-cons-charges').addEventListener('input', updateChange);
}

// Handle payment submission
// Handle payment submission
const payBtn = document.getElementById('pay');
if (payBtn) {
    payBtn.addEventListener('click', async function (e) {
        e.preventDefault();
        const data = getFormData();
        data.action = 'pay';
        console.log('Processing payment with data:', data);

        try {
            const response = await fetch('crud.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });

            const text = await response.text();
            let result;
            try {
                result = JSON.parse(text);
            } catch (jsonError) {
                console.error('Response is not valid JSON:', text);
                alert('Server error: Response is not valid JSON. Check console for details.');
                throw jsonError;
            }

            console.log('Server response:', result);

            if (!response.ok || result.success === false) {
                throw new Error(result.error || 'Server error');
            }

            alert(result.message || 'Payment successful!');
            payBtn.closest('form').reset();
            updateChange();
        } catch (error) {
            console.error('Payment error:', error);
            alert(`Payment failed: ${error.message}`);
        }
    });
}