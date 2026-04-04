<strong>Transfers</strong>
<p>These settings control the transfer settings.</p>

<div class="list-group mb-5 shadow">
    <div class="list-group-item list-group-item-action">
        <div class="row align-items-center">
            <div class="col">
                <strong class="mb-0">Name</strong>
                <p class="text-muted mb-0">Owner of the account.</p>
            </div>
            <div class="col-auto">
                <input type="text" class="form-control" id="nameiban" name="conf_nameiban" value="<?php echo (isset($config['nameiban'])) ? base64_decode($config['nameiban']) : ''; ?>" autocomplete="nameiban">
            </div>
        </div>
    </div>

    <div class="list-group-item list-group-item-action">
        <div class="row align-items-center">
            <div class="col">
                <strong class="mb-0">IBAN</strong>
                <p class="text-muted mb-0">IBAN of the account.</p>
            </div>
            <div class="col-auto">
                <input type="text" class="form-control" id="iban" name="conf_iban" value="<?php echo (isset($config['iban'])) ? base64_decode($config['iban']) : ''; ?>" autocomplete="iban">
            </div>
        </div>
    </div>

    <div class="list-group-item list-group-item-action">
        <div class="row align-items-center">
            <div class="col">
                <strong class="mb-0">BIC</strong>
                <p class="text-muted mb-0">BIC of the account.</p>
            </div>
            <div class="col-auto">
                <input type="text" class="form-control" id="bic" name="conf_bic" value="<?php echo (isset($config['bic'])) ? base64_decode($config['bic']) : ''; ?>" autocomplete="bic">
            </div>
        </div>
    </div>

</div>