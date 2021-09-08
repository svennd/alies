<div class="row">
      <div class="col-lg-12 mb-4">

      <div class="card shadow mb-4" id="merge_field" style="display:none;">
            <div class="card-header py-3">
              <h6 class="m-0 font-weight-bold text-primary">Merge breeds</h6>
            </div>
            <div class="card-body">
			<form method="post" action="<?php echo base_url(); ?>admin/breeds">
				<div class="form-group">
					<label for="old_breed">Old Breed</label>
					<input type="text" name="old_breed" class="form-control" id="old_breed" readonly>
				</div>
				<div class="form-group">
					<label for="new_breed">Select</label>
					<?php if ($breeds): ?>
					<select name="new_breed" class="form-control" id="new_breed">
						<?php foreach ($breeds as $breed): ?>
							<option value="<?php echo $breed['id']; ?>"><?php echo $breed['name']; ?></option>
						<?php endforeach; ?>
					</select>
					<?php endif; ?>
				</div>
				<input type="hidden" name="old_breed_id" id="old_breed_id" value="" />
				<button type="submit" name="submit" value="merge" class="btn btn-danger mb-2">Update</button>
			</form>
			</div>
	</div>
		
      <div class="card shadow mb-4">
            <div class="card-header py-3">
              <h6 class="m-0 font-weight-bold text-primary">Breeds List</h6>
            </div>
            <div class="card-body">
			<?php if ($breeds): ?>
				<table class="table" id="dataTable">
				<thead>
				<tr>
					<th>id</th>
					<th>Breed</th>
					<th>Pets in db</th>
					<th>edit</th>
				</tr>
				</thead>
				<tbody>
				</tbody>
				</table>
			<?php endif; ?>
                </div>
		</div>

	</div>
      
</div>

<script type="text/javascript">
function show_merge(id)
{
	var name = $("#name_" + id).html();
	$("#merge_field").show();
	$("#old_breed_id").val(id);
	$("#old_breed").val(name);
}

document.addEventListener("DOMContentLoaded", function(){
	$("#dataTable").DataTable({
			"ajax": "<?php echo base_url(); ?>admin/a_get_breeds",
			"pageLength": 50, 
			"lengthMenu": [[50, 100, -1], [50, 100, "All"]],
			"columnDefs": [ 
				{ "targets": 1, "data": null, "render": function ( data, type, row ) {
					var result = "<div id='name_"+ row[0] +"'>"+ row[1] +"</div><div id='edit_"+ row[0] +"' style='display:none;'><form method='post' action='<?php echo base_url(); ?>admin/breeds' class='form-inline'>";
					result += "<input type='text' class='form-control mb-2 mr-sm-2' name='name' value='"+ row[1] +"' />";
					result += "<input type='hidden' name='id' value='"+ row[0] +"' />";
					result += "<button type='submit' name='submit' value='edit' class='btn btn-primary mb-2'>Update</button></form></div>";
								return result;
					}
				},
				{ "targets": 2, "data": null, "render": function ( data, type, row ) {
								return "<a href='<?php echo base_url(); ?>admin/breeds/" + row[0] + "'>" + row[2] + "</a>";
					}
				},
				{ "targets": 3, "data": null, "render": function ( data, type, row ) {
					var result = "<a href='#' id='"+ row[0] +"' class='btn btn-outline-success btn-sm edit'><i class='fas fa-edit'></i></a>";
						result +="&nbsp;<a onclick='show_merge("+ row[0] +")' href='javascript:void(0);' id='"+ row[0] +"' class='btn btn-outline-danger btn-sm merge'><i class='fas fa-compress-alt'></i></a>";
								return result;
					}
				},
				{ "visible": false,  "targets": [ 0 ] }
				]
		});
	$("#adminmgm").show();
	$("#admin").addClass('active');
	$("#adminbreed").addClass('active');
		
	$("#dataTable").on('click', '.edit', function(){
		var id = $(this).attr('id');
		$("#name_" + id).hide();
		$("#edit_" + id).show();
		return false;
	});
	$("#new_breed").select2();
});
</script>
  