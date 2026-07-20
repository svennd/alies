<style>
.list-group-item {
    margin-bottom: 0;
}

.toggle-hide {
	background-color: #e7f5e7;
	display: none;
}

.toggle-hide:hover {
	background-color: #e7f5e7;
}


/*
new
*/
.page-header{
    background: linear-gradient(90deg,#1c64f2,#4f46e5);
    color:white;
    padding:25px 30px;
    margin-bottom:25px;
}

.page-title{
    font-size:1.8rem;
    font-weight:500;
    margin-bottom:8px;
}

.page-breadcrumb{
    background: rgba(255,255,255,0.12);
    border-radius:6px;
    padding:6px 12px;
    margin-bottom:0;
}

.page-breadcrumb .breadcrumb-item,
.page-breadcrumb .breadcrumb-item a{
    color: rgba(255,255,255,0.75);
}

.page-breadcrumb .breadcrumb-item.active{
    color: rgba(255,255,255,0.95);
}
.page-breadcrumb .breadcrumb-item a:hover{
    color: white;
    text-decoration: none;
}
.page-header{
    border-radius:6px;
}

.page-header{
    background: linear-gradient(120deg,#5c7cfa,#748ffc);
    color:white;
    padding:25px 30px 60px 30px;
    border-radius:6px;
}

.breadcrumb-item + .breadcrumb-item::before {
	content: "/";
	color: rgba(255,255,255,0.75);
}

.page-content{
    margin-top:-60px;
    position:relative;
    z-index:10;
	padding-left: 60px;
    padding-right: 60px;
}

</style>

<div class="page-header shadow-sm">
	<div class="container-fluid">
		<div class="d-flex justify-content-between align-items-center">
			<div>
				<h1 class="page-title"><i class="fas fa-box mr-2"></i> Product fiche</h1>
				<ol class="breadcrumb page-breadcrumb">
					<li class="breadcrumb-item"><a href="<?php echo base_url('products'); ?>">Products</a></li>
					<li class="breadcrumb-item active"><?php echo $product['name']; ?></li>
				</ol>
			</div>
		</div>
	</div>
</div>

<div class="page-content">
	<div class="card shadow mb-4">
		<div class="card-header">
			<ul class="nav nav-tabs card-header-tabs" id="productTabs">
				<li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#tab_product"><?php echo $this->lang->line('product_sheet'); ?></a></li>
				<li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab_transaction">Transaction</a></li>
				<li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab_limit"><?php echo $this->lang->line('limit'); ?></a></li>
				<li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab_advanced">Advanced</a></li>
			</ul>
		</div>

	<div class="card-body">
		<!-- modify success -->
		<?php if (isset($update) && $update) : ?>
			<div class="alert alert-success alert-dismissible fade show" role="alert">
			  Product updated! Want to :
				<ul>
					<li><a href="<?php echo base_url('stock/add_stock/' . $product['id']); ?>">add stock</a></li>
					<li><a href="<?php echo base_url('products/profile/' . $product['id']); ?>">see stock</a></li>
				</ul>
				<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			</div>
		<?php endif; ?>
		
		<form action="<?php echo base_url('products/product/' . $product['id']); ?>" method="post" autocomplete="off">

		<div class="tab-content">
			<!-- PRODUCT -->
			<div class="tab-pane fade show active" id="tab_product">
				<?php include 'block/edit_product_fiche.php'; ?>
			</div>

			<div class="tab-pane fade show" id="tab_limit">
				<?php include 'block/edit_limiet.php'; ?>
			</div>

			<div class="tab-pane fade show" id="tab_transaction">
				<?php include 'block/edit_transaction.php'; ?>
			</div>

			<div class="tab-pane fade show" id="tab_advanced">
				<?php include 'block/edit_advanced.php'; ?>
			</div>

		</div>


			<div class="float-right">
				<button type="submit" name="submit" value="edit" class="btn btn-outline-primary float-right"><i class="fa-solid fa-wrench"></i> <?php echo $this->lang->line('edit'); ?></button>
			</div>
		</form>
	</div>

</div>
	

</div>


<script type="text/javascript">

function process_datamatrix(barcode) {
	
	// GS1 data matrix 
	// 01 05420036903635 17 210400 10 111219
	// length : ~30 
	// 01 EAN/GTIN  (14 length)
	// 17 YY MM DD date (6 length)
	// 10 barcode (variable length)
	// 6 + 14 + 6 + x
	
	if (barcode.length > 26)
	{
		result = barcode.match(/01([0-9]{14})17([0-9]{6})10(.*)/);
		if(result)
		{
			// console.log(result);
			var input_barcode = result[1];
			var date = result[2];
			var day = (date.substr(4,2) == "00") ? "01" : date.substr(4,2);
			
			$("#input_barcode").val(result[1]);
			$("#extra_info").html("Scanned LotNR : " + "20" + date.substr(0, 2) + "-" + date.substr(2,2) + "-" + day + " lotnr :" + result[3]);
		}
	}
	else
	{
		console.log("code to short not recognized");
	}	
}

function toggleByCheckbox(checkbox, target) {
    if ($(checkbox).is(':checked')) {
        $(target).show();
    } else {
        $(target).hide();
    }

    $(checkbox).on('change', function () {
        $(this).is(':checked')
            ? $(target).slideDown()
            : $(target).slideUp();
    });
}

function diff(label, oldVal, newVal){
    oldVal = oldVal || "(empty)";
    newVal = newVal || "(empty)";
    return oldVal === newVal
        ? "<b>"+label+":</b> "+oldVal+" <span style='color:green'>(same)</span><br>"
        : "<b>"+label+":</b> "+oldVal+" → <b style='color:#d63384'>"+newVal+"</b><br>";
}

function buildDiff(map){
    var html = "";
    $.each(map,function(_,f){
        if(!f.new) return; // skip empty new values
        html += diff(f.label,f.old,f.new);
    });
    return html || "No changes detected.";
}
function applyChanges(map){
    $.each(map,function(_,f){
        if(f.new && f.old !== f.new){
            $(f.selector).val(f.new).addClass("is-valid");
        }
    });
}

document.addEventListener("DOMContentLoaded", function(){
	var _changeInterval = null;
	var barcode = null;
	$("#prd").show();
	$("#products").addClass('active');
	$("#product_list").addClass('active');
	
	$("#gs1_datamatrix").keyup(function(){
		barcode = this.value;
		clearInterval(_changeInterval)
		_changeInterval = setInterval(function() {
		clearInterval(_changeInterval)
			process_datamatrix(barcode);
		}, 500);
	});
	toggleByCheckbox('#vaccin', '.no_vaccin_hide');
	toggleByCheckbox('#is_antibiotic', '.no_antibiotic_hide');
	$("#product_labels").select2({
		theme: 'bootstrap4',
		placeholder: 'Select labels'
	});
/*
{
    "cti": "493600",
    "cti_extended": "493600-01",
    "cnk": "3383767",
    "fdm": null,
    "atc": "QM01AC06 Meloxicam",
    "geneesmiddel": "Meloxidyl 0.5 mg/ml or. susp. 5 ml",
    "werkzaam_bestanddeel": "Meloxicam",
    "status_melding": "STATUS_AVAILABLE",
    "is_veterinarian": "1",
    "tot": null,
    "reden": null,
    "impact": null,
    "VHB": "EU/2/06/070/010"
}
*/
$("#cnk_button").on("click", function(){

    var cnk = $("#cnk").val().trim();
    if(!cnk) return;

    var btn = $(this);
    var original = btn.html();

    btn.html('<i class="fa-solid fa-spinner fa-spin"></i>').prop("disabled",true);

    $.ajax({
        url:'/fagg/api/fagg/by-cnk/'+cnk,
        type:'GET',
        headers:{'X-API-Key':'<?php echo $index_api_key; ?>'},

        success:function(response){

            const item = response && response.data ? response.data : response;

            var map = {
                vhb:{
                    label:"VHB",
                    selector:"#vhbcode",
                    old:$("#vhbcode").val(),
                    new:item.VHB || ""
                },
                cti:{
                    label:"CTI Extended",
                    selector:"#cti-e",
                    old:$("#cti-e").val(),
                    new:item.cti_extended || ""
                },
                barcode:{
                    label:"Barcode",
                    selector:"#input_barcode",
                    old:$("#input_barcode").val(),
                    new:item.fdm || ""
                }
            };

            Swal.fire({
                title:"FAGG Update",
                html:buildDiff(map),
                showCancelButton:true,
                confirmButtonText:"Apply"
            }).then(function(r){
                if(r.isConfirmed) applyChanges(map);
            });

        },

        error:function(xhr,status,error){

            Swal.fire({
                icon:"error",
                title:"API Error",
                html:"<pre style='white-space:pre-wrap;text-align:left'>"+
                     JSON.stringify({status:status,error:error,responseText:xhr.responseText},null,2)+
                     "</pre>"
            });

        },

        complete:function(){
            btn.html(original).prop("disabled",false);
        }
    });

});



$("#wholesale_button").on("click", function(){

    Swal.fire({
        title:"Search article",
        html:'<select id="swal_wholesale" style="width:100%"></select>',
        showCancelButton:true,
        confirmButtonText:"Select",
        didOpen:function(){

            $('#swal_wholesale').select2({
                dropdownParent:$('.swal2-container'),
                theme:'bootstrap4',
                placeholder:'Select Article',
                ajax:{
                    url:'<?php echo base_url("wholesale/ajax_get_articles"); ?>',
                    dataType:'json'
                }
            });

        },
        preConfirm:function(){
            return $('#swal_wholesale').select2('data')[0];
        }

    }).then(function(result){

        if(!result.isConfirmed) return;

        var data = result.value;

        var map = {
            name:{
                label:"Article",
                selector:"#input_wh_name",
                old:$("#input_wh_name").val(),
                new:data.text || ""
            },
            vhb:{
                label:"VHB",
                selector:"#vhbcode",
                old:$("#vhbcode").val(),
                new:data.vhb || ""
            },
            cnk:{
                label:"CNK",
                selector:"#cnk",
                old:$("#cnk").val(),
                new:data.cnk || ""
            },
            producer:{
                label:"Producer",
                selector:"#input_producer",
                old:$("#input_producer").val(),
                new:data.distr || ""
            },
            id:{
                label:"Wholesale ID",
                selector:"#wholesale_id",
                old:$("#wholesale_id").val(),
                new:data.id || ""
            }
        };

        Swal.fire({
            title:"Confirm change",
            html:buildDiff(map),
            showCancelButton:true,
            confirmButtonText:"Apply"
        }).then(function(confirm){

            if(confirm.isConfirmed) applyChanges(map);

        });

    });

});

});
</script>
