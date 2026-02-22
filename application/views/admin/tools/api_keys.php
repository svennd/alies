<div class="card shadow mb-4">

<div class="card-header d-flex flex-row align-items-center justify-content-between">
<div><a href="<?php echo base_url('tools'); ?>"><?php echo $this->lang->line('tools'); ?></a> / API Keys</div>
</div>

<div class="card-body">
    <!-- create new key -->
    <h5>New API Key</h5>
    <form method="post" class="form-inline" action="<?php echo base_url('tools/show_api_keys'); ?>">
        <input type="text" class="form-control form-control-sm mr-3" id="device" name="device" placeholder="Enter device description" required>
        <button type="submit" name="create_key" value="1" class="btn btn-sm btn-primary">Create API Key</button>
    </form>
      
    <br/>
    <h5>Existing API Keys</h5>
    <?php if (empty($api_keys)): ?>
        <p>No API keys found.</p>
    <?php else: ?>
    <table class="table table-sm" id="dataTable">

        <thead>
        <tr>
            <th>Key (hashed)</th>
            <th>Device</th>
            <th>Last Used At</th>
            <th>Operations</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($api_keys as $key): ?>
        <tr>
            <td><?php echo $key['key_hash']; ?></td>
            <td><?php echo htmlspecialchars($key['device']); ?></td>
            <td><?php echo $key['last_used_at']; ?></td>
            <td>
                <?php if ($key['active']): ?>
                <form method="post" style="display:inline;" action="<?php echo base_url('tools/show_api_keys'); ?>">
                    <input type="hidden" name="key_id" value="<?php echo $key['id']; ?>">
                    <button type="submit" name="revoke_key" value="1" class="btn btn-sm btn-danger">Revoke</button>
                </form>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

</div>
</div>
	
<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#dataTable").DataTable(
        {
            "order": [[ 1, "desc" ]]
        }
    );
});
</script>
