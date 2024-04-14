<h4>Verbruik</h4>
<hr />
<a href="<?php echo base_url('reports/usage/' . $product['id']); ?>" class="btn btn-outline-info btn-sm mb-5">Details</a>
<br/>
<table class="table table-sm" id="monthlyUsageTable" width="100%">
    <thead>
        <tr>
            <th>Date</th>
            <th>total_volume</th>
        </tr>
    </thead>
    <tbody>
    </tbody>
</table>

<script>
const URL_USE = '<?php echo base_url('products/get_monthly_usage/' . $product['id']); ?>';
document.addEventListener("DOMContentLoaded", function(){
    $.ajax({
        url: URL_USE,
        type: 'GET',
        dataType: 'json',
        success: function (response) {
            $('#monthlyUsageTable').DataTable({
                data: response, 
                columns: [
                    { 
                        data: 'month_year',
                        render: function(data, type, row) {
                            // Split the string into month and year components
                            var components = data.split('/');
                            if (components.length !== 2) return data; // return the original data if format is invalid

                            // Parse month and year
                            var month = parseInt(components[0]);
                            var year = parseInt(components[1]);

                            // Validate month and year
                            if (isNaN(month) || isNaN(year)) return data; // return the original data if format is invalid

                            // Create a new Date object with the parsed components
                            var date = new Date(year, month - 1); // month is zero-based

                            // Format the date as "Month Year"
                            return date.toLocaleString('en-us', { month: 'long', year: 'numeric' });
                        }
                    },
                    { data: 'total_volume' }
                ]
            });
        },
        error: function (xhr, status, error) {
            console.error(xhr.responseText);
        }
    });

});
</script>

