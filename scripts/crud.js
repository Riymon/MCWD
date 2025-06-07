document.addEventListener('DOMContentLoaded', function() {
    async function crud(action, data) {
        data.action = action;
        console.log('Sending data:', data);

        try {
            const response = await fetch('php/crud.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });

        let result;
        try {
            const text = await response.text();
            if (text.includes('<br />')) {
                throw new Error('PHP error detected. Check PHP error logs.');
            }
            try {
                result = JSON.parse(text);
            } catch (jsonError) {
                console.error('Response is not valid JSON:', text);
                throw new Error('Server returned invalid response. Check PHP error logs.');
            }
        } catch (error) {
            console.error('Error reading response:', error);
            throw error;
        }

        console.log('Server response:', result);
            console.log('Server response:', result);

            if (!response.ok) {
                throw new Error(result.error || 'Server error');
            }

            alert(result.message || `${action} successful!`);

            if (action === 'pay') {
                document.querySelector('#createcon form').reset();
            }

            return result;

        } catch (error) {
            console.error(`Error during ${action}:`, error);
            alert(`Error: ${error.message}`);
            throw error;
        }
    }

    document.getElementById('create').addEventListener('click', function (e) {
        e.preventDefault();
        crud('create', getFormData());
    });

    document.getElementById('update').addEventListener('click', function (e) {
        e.preventDefault();
        crud('update', getFormData());
    });

    document.getElementById('delete').addEventListener('click', function (e) {
        e.preventDefault();
        crud('delete', getFormData());
    });
});

function getFormData() {
    // Add your form data collection logic here
    return {
         consumer_name: document.getElementById('consumer-name2').value.trim(),
                consumer_add: document.getElementById('consumer-address2').value.trim(),
                account_code: document.getElementById('con-account-code2').value.trim(),
                type: document.getElementById('con-type2').value.trim(),
                consumer_size: document.getElementById('con-size2').value.trim()
    };
}