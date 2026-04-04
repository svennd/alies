
<strong>Integrations</strong>
<p>External data sources</p>

<div class="list-group mb-5 shadow">
    <div class="list-group-item list-group-item-action">
        <div class="row align-items-center">
            <div class="col">
                <strong class="mb-0">MediLab</strong>
                <p class="text-muted mb-0">Pulls <a href="https://www.medilab.be/" target="_blank">Medilab</a> results.</p>
            </div>
            <div class="col-auto">
                <div class="form-inline">
                    <div class="form-group">
                        <label for="staticEmail2" class="sr-only">User</label>
                        <input type="text" class="form-control" id="medilab_user" name="conf_medilab_user" value="<?php echo (isset($config['medilab_user'])) ? base64_decode($config['medilab_user']) : ''; ?>" autocomplete="medilabuser">
                    </div>
                    <div class="form-group mx-sm-3">
                        <label for="inputPassword2" class="sr-only">Password</label>
                        <input type="password" class="form-control" id="medilab_pasw" value="<?php echo (isset($config['medilab_pasw'])) ? base64_decode($config['medilab_pasw']) : ''; ?>" name="conf_medilab_pasw" autocomplete="medilabpasw">
                    </div>
                </div>
                <button type="button" name="test_covetrus" value="test" id="test_covetrus" class="mt-1 btn btn-sm btn-outline-primary"><i class="fa-solid fa-satellite-dish"></i> Test connection</button>
            </div>
        </div>
    </div>

    <div class="list-group-item list-group-item-action">
        <div class="row align-items-center">
            <div class="col">
                <strong class="mb-0">VamReg</strong>
                <p class="text-muted mb-0">Production</p>
            </div>
            <div class="col-auto">
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="vamreg_push" name="check_vamreg_push" <?php echo (isset($config['vamreg_push']) && base64_decode($config['vamreg_push'])) ? 'checked' : ''; ?>>
                    <label class="custom-control-label" for="vamreg_push"></label>
                </div>
            </div>
        </div>
    </div>
    <div class="list-group-item list-group-item-action">
        <div class="row align-items-center">
            <div class="col">
                <strong class="mb-0">VamReg API key</strong>
                <p class="text-muted mb-0">Found in vamreg under FAMHP connect -> API key</p>
            </div>
            <div class="col">
                <input type="text" class="form-control" id="vamreg_api_key" name="conf_vamreg_api_key" placeholder="********-****-****-****-************" value="<?php echo (isset($config['vamreg_api_key'])) ? base64_decode($config['vamreg_api_key']) : ''; ?>">
            </div>
        </div>
    </div>

</div>