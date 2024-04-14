<h4>Deliveries (automatic)</h4>
<hr />
<table class="table table-sm" id="deliveriesTable" width="100%">
    <thead>
        <tr>
            <th>Delivery Date</th>
            <th>Bruto</th>
            <th>Netto</th>
            <th>Amount</th>
            <th>Lot Number</th>
            <th>eol</th>
        </tr>
    </thead>
    <tbody>
    </tbody>
</table>

<script>
const URL_WHOLESALE = '<?php echo base_url('wholesale/ajax_get_history/' . $product['wholesale']); ?>';
document.addEventListener("DOMContentLoaded", function(){
    $.ajax({
        url: URL_WHOLESALE, // Your AJAX URL here
        type: 'GET',
        dataType: 'json',
        success: function (response) {
            // Populate DataTable with delivery data
            $('#deliveriesTable').DataTable({
                data: response.deliveries,
                columns: [
                    { data: 'delivery_date' },
                    { data: 'bruto_price' },
                    { data: 'netto_price' },
                    { data: 'amount' },
                    { data: 'lotnr' },
                    { data: 'due_date' }
                ]
            });
        },
        error: function (xhr, status, error) {
            console.error(xhr.responseText);
        }
    });
});
</script>