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
                    { data: 'month_year', title: 'Month/Year' },
                    { data: 'total_volume', title: 'Total Volume' }
                ]
            });
        },
        error: function (xhr, status, error) {
            console.error(xhr.responseText);
        }
    });

});
</script>

