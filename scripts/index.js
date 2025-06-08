// Uncomment if you want to use date-fns
// import { format } from 'https://cdn.jsdelivr.net/npm/date-fns@2.29.3/esm/index.js';

let display_values = document.getElementById('values'); // Output container



document.getElementById('fetch').addEventListener('click', function (event) {
    event.preventDefault();
    console.log('Fetch button clicked'); // Debug log
    
    const consumerName = document.getElementById('consumer-name').value;
    console.log('Searching for:', consumerName); // Debug log
    
    if (!consumerName) {
        alert('Please enter a consumer name');
        return;
    }

    fetch('consumerfetchdetails.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ cons_name: consumerName })
    })
    .then(response => {
        console.log('Response received'); // Debug log
        if (!response.ok) throw new Error('Network response was not ok');
        return response.json();
    })
    .then(data => {
        console.log('Data:', data); // Debug log
        if (data.error) {
            alert(data.error);
            return;
        }
        
        if (!data.data) {
            alert('No data received from server');
            return;
        }

        // Update form fields
    document.getElementById('consumer-name').value = data.data.name || '';
    document.getElementById('consumer-address').value = data.data.address || ''; // Changed to value
    document.getElementById('con-account-code').value = data.data.acc_code || ''; // Changed to value
    document.getElementById('con-type').value = data.data.type || ''; // Changed to value
    document.getElementById('con-size').value = data.data.size || ''; // Changed to value
    document.getElementById('con-pvr').value = data.data.prev_reading || '';
    document.getElementById('con-curred').value = data.data.current_reading || '';
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error fetching data. Please check console for details.');
    });
});


document.getElementById('calculate').addEventListener('click', function (event) {
    event.preventDefault(); // Prevent form submission
    
    let monthselect = document.getElementById('monthbill');
    let size = document.getElementById('con-size').value; // Size of Connection
    let pmrv = Number(document.getElementById('con-pvr').value); // Previous Month Meter Reading
    let cmcurred = Number(document.getElementById('con-curred').value); // Current Month Meter Reading
    let cons_name = document.getElementById('consumer-name').value; // Consumer name input
    let conslabel = document.getElementById('cons-label'); // Consumer name display label
    let date = document.getElementById('date'); // Date display element
    const con_account_code = document.getElementById('con-account-code').value;
    const con_address = document.getElementById('consumer-address').value;
    const con_type = document.getElementById('con-type').value;


    const month_of_bill = monthselect.value;

    if (!month_of_bill) {
            alert("Please select a billing month!");
        return;
        }

    if (isNaN(pmrv) || isNaN(cmcurred)) {
        alert("Please enter valid numeric values for meter readings!");
        return;
    }

    if (!["1/2", "1", "2", "3/8", "3/4", "4", "6", "8"].includes(size)) {
        alert("Please enter a valid size of connection!");
        return;
    }

    let cmp = pmrv - cmcurred;
    let w_fee = 0;

    // Main Calculation Function
    calculation(cmp, size);

    function calculation(cmp, size) {
        if (cmp < 0) cmp *= -1; // Handle negative consumption
        if (cmp > 10) {
            cmp -= 10;
            w_fee += Math.min(cmp, 10) * 16.80; // For first tier rate
            if (cmp > 10) {
                cmp -= 10;
                w_fee += Math.min(cmp, 10) * 19.77; // For second tier rate
                if (cmp > 10) {
                    w_fee += (cmp - 10) * 48.40; // For consumption above 30
                }
            }
        }
        sizecalculation(size);
        return w_fee;
    }

    function sizecalculation(size) {
        size = size.trim();
        switch (size) {
            case "1/2": w_fee += 152.00; break;
            case "1": w_fee += 1216.00; break;
            case "2": w_fee += 3040.00; break;
            case "3/8": w_fee += 152.00; break;
            case "3/4": w_fee += 243.00; break;
            case "4": w_fee += 10944.00; break;
            case "6": w_fee += 18240.00; break;
            case "8": w_fee += 29184.00; break;
            default:
                alert("Error: Invalid Size of Connection");
                console.log("ERROR: Invalid Size of Connection");
                break;
        }
    }

    // Additional charges
    let PCA = w_fee * 0.40;
    let PWA = w_fee * 0.10;
    let Fr_tax = w_fee * 0.02;
    let gross = w_fee + Fr_tax + PCA + PWA;
    gross = Math.round(gross * 100) / 100;
    // Display results
    conslabel.innerHTML = cons_name;
    let values = [cmp, w_fee, Fr_tax, PCA, PWA, gross];
    
    display_values.innerHTML = "";
    display_values.style.fontSize = "18px";

    values.forEach(value => {
        let h3 = document.createElement('h3');
        h3.textContent = value.toFixed(2);
        display_values.appendChild(h3);
    });

    if (isNaN(pmrv) || document.getElementById('con-pvr').value === '') {
    alert("Please enter a valid previous reading!");
    return;
    }
        const data2 = {
            acc_code: con_account_code,
            current_reading: cmcurred,
            previous_reading: pmrv.valueOf(),
            consume: cmp,
            water_fee: w_fee,
            ftax: Fr_tax,
            pca: PCA,
            pwa: PWA,
            gross_bill: gross,
            size: size,
            monthbill: month_of_bill
        };
        fetch('charges.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data2)
        })
        .then(async response => {
            const data = await response.json();
            if (!response.ok) {
                throw new Error(data.error || 'Server error');
            }
            return data;
        })
        .then(data2 => {
            console.log('Success:', data2);
            alert('Data saved successfully!');
        })
        .catch(error => {
            console.error('Full error:', error);
            alert(`Error: ${error.message}\nCheck console for details`);
        });

});