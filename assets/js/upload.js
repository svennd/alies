/* if upload by click */
$("#my_old_browser")
	.on( "change", function(e) {

			$("#upload_field").addClass('drag-over').html("");

			var filelist = $(this)[0].files;
			var file;

			for(var i=0;i<filelist.length;i++){
					var file = filelist[i];
					var file_loader =
						'<h4 class="small font-weight-bold m-3">'+file.name+'<span class="float-right" id="text_status_' + file.name + '">&nbsp;</span></h4>' +
						'<div class="progress m-3"><div id="bar_' + file.name + '" class="progress-bar" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div></div>';
					$("#upload_field").append(file_loader);
			}

			// loop through files
			for (var i = 0; i < filelist.length; i++) {
			    file = filelist[i];
			    uploadFile(file);
			}

	});

/*
    behaviour of the elements
*/
function handleDragOver(e) {
    e.preventDefault();
    e.stopPropagation();
    $(this).addClass('drag-over');
  }
  
  function handleDragLeave(e) {
    e.preventDefault();
    e.stopPropagation();
    $(this).removeClass('drag-over');
  }
  
  function handleDrop(e, field) {
    e.preventDefault();
    e.stopPropagation();
    field.addClass('drag-over');
    field.empty();
  
    if (e.originalEvent.dataTransfer && e.originalEvent.dataTransfer.files.length) {
      var filelist = e.originalEvent.dataTransfer.files;
      processFiles(filelist, field);
    }
  }

/*
  process the uploaded files
*/
function processFiles(filelist, field) {
    for (var i = 0; i < filelist.length; i++) {
      var file = filelist[i];
      var fileLoader =
        `<h4 class="small font-weight-bold m-3">${file.name}<span class="float-right" id="text_status_${file.name}">&nbsp;</span></h4>` +
        `<div class="progress m-3"><div id="bar_${file.name}" class="progress-bar" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div></div>`;
  
      field.append(fileLoader);
      uploadFile(file, url, url_complete);
    }
}

/* add files to event 
*/
function handleFileUpload(field) {
    field.on("click", function() {
      $("#my_old_browser").click();
    });
  
    field.on("dragover dragenter", handleDragOver);
    field.on("dragleave", handleDragLeave);
    field.on("drop", function(e) {
      handleDrop(e, $(this));
    });
  }


/*
    upload file by splitting into chunks
    filechunk is the function that is sending piece by piece
    filecompletion is the function that sends once finished
*/
async function uploadFile(file, url, url_complete) {
    const chunkSize = 40000;
  
    const progress = $(`div#bar_${file.name}`);
    const text_status = $(`span#text_status_${file.name}`);
  
    try {
      await sendFileChunks(file, chunkSize, url + '?nocache=' + new Date().getTime(), progress);
      await sendFileCompletion(file, url_complete, progress, text_status);
    } catch (error) {
      text_status.html(error);
      progress.addClass('bg-danger');
    }
  }

  
async function sendFileChunks(file, chunkSize, url, progress) {
    const total = Math.ceil(file.size / chunkSize);
    let procent_value = 0;

    for (let start = 0; start < file.size; start += chunkSize) {
        const chunk = file.slice(start, start + chunkSize + 1);
        const fd = new FormData();
        fd.append('data', chunk);
        fd.append('file_name', file.name);
        fd.append('file_size', file.size);

        const response = await fetch(url, { method: 'POST', body: fd });
        if (!response.ok) {
        throw new Error('Error during file upload');
        }

        procent_value = Math.ceil(((start + chunkSize) / chunkSize / total) * 100);
        progress.attr('aria-valuenow', procent_value).css('width', `${procent_value}%`);
    }
}

async function sendFileCompletion(file, url_complete, progress, text_status) {
    const compl = new FormData();
    compl.append('file_name', file.name);
    compl.append('file_size', file.size);

    const response = await fetch(url_complete, { method: 'POST', body: compl });
    if (!response.ok) {
        throw new Error('Error during file completion');
    }

    const responseData = await response.json();
    if (responseData.success) {
        text_status.html('Complete!');
        progress.addClass('bg-success');
    } else {
        text_status.html(responseData.error);
        progress.addClass('bg-danger');
    }
}