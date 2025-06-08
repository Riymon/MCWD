document.getElementById('search').addEventListener('click', function (event) {
    event.preventDefault();
    console.log('Fetch button clicked'); // Debug log
    const search_by = document.getElementById('searchby');
    const search_term = document.getElementById('searchterm');
    const data_search_row = document.getElementById('data-search-row');

    const data = {
        search_by: search_by.value,
        search_term: search_term.value,
        action: 'search'
    };

    if (!data.search_term) {
        alert('Please enter a search term');
        return;
    }

    console.log('search_by:', search_by.value, 'search_term:', search_term.value);

    fetch('crud.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => {
        console.log('Response received'); // Debug log
        if (!response.ok) throw new Error('Network response was not ok');
        return response.text();
    })
    .then(text => {
        if (!text) throw new Error('Empty response from server');
        let result;
        try {
            result = JSON.parse(text);
        } catch (e) {
            console.error('Invalid JSON:', text);
            throw e;
        }
        console.log('Data:', result); // Debug log
        if (result.error) {
            alert(result.error);
            return;
        }
        if (!result.data || !Array.isArray(result.data) || result.data.length === 0) {
            alert('No data received from server');
            return;
        }

        data_search_row.innerHTML = `<tr>
                        <th>Account Code</th>
                        <th>Consumer Name</th>
                        <th>Charges ID</th>
                        <th>Gross Current Bill</th>
                        <th>Month Bill</th>
                        <th>Payment ID</th>
                        <th>Amount</th>
                        <th>Change</th>
                        <th>Mode of Payment</th>
                        <th>Payment Date</th>
                        <th>Paid</th>
                    </tr>`;
        result.data.forEach(row => {
            const tr = document.createElement('tr');
            for (const key in row) {
                const td = document.createElement('td');
                td.textContent = row[key];
                tr.appendChild(td);

            }
            data_search_row.appendChild(tr);
        });
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error fetching data. Please check console for details.');
    });
});