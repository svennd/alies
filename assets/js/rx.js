document.addEventListener("DOMContentLoaded", function() {

    const inversionBtn = document.getElementById('inversion');
    const download = document.getElementById('download');
    const previewImg = document.querySelector('#image');

    // Initial filter values
    let inversion = "0";

    // Apply the current filter settings to both the image 
    const applyFilter = () => {
        const filterStyle = `invert(${inversion}%)`;
        previewImg.style.filter = filterStyle;
    };


    const saveImage = () => {
      setTimeout(() => {
          const canvas = document.createElement("canvas");
          const ctx = canvas.getContext("2d");
          canvas.width = previewImg.naturalWidth;
          canvas.height = previewImg.naturalHeight;
          
          ctx.filter = `invert(${inversion}%)`;
          ctx.translate(canvas.width / 2, canvas.height / 2);
          ctx.drawImage(previewImg, -canvas.width / 2, -canvas.height / 2, canvas.width, canvas.height);
          
          const link = document.createElement("a");
          link.download = "rx_alies.png";
          link.href = canvas.toDataURL();
          link.click();
      });
  }

  
    // Toggle inversion between 0 and 100 when the button is clicked
    inversionBtn.addEventListener('click', function() {
        inversion = inversion === "0" ? "100" : "0";
        applyFilter();
    });
    
    download.addEventListener('click', function() {
        saveImage();
    });

  // jQuery-based image link click event
  $('.image-link').on('click', function(e) {
    e.preventDefault();
    const imgSrc = $(this).data('image');
    $('#image').attr('src', imgSrc); // Update the image source with jQuery
    previewImg.src = imgSrc; // Ensure the preview image source is also updated for vanilla JS
  });  

});

