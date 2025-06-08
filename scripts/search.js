document.getElementById('search').addEventListener('click', function (event) {
    event.preventDefault();
    console.log('Fetch button clicked'); // Debug log
    const search_by = document.getElementById('searchby');
    const search_term = document.getElementById('searchterm');
    const data_search_row = document.getElementById('data-search-row');
    const fetched_data = [];

    const data = {
        search_by : search_by,
        search_term : search_term,
        action: 'search'
    }
    if (!data) {
        alert('Please enter a data');
        return;
    }

    fetch('crud.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({data})
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
        
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error fetching data. Please check console for details.');
    });
});