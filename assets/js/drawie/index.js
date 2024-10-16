// source : https://www.codingnepalweb.com/build-drawing-app-html-canvas-javascript/
// adapted

const   canvas = document.querySelector("canvas"),
        toolBtns = document.querySelectorAll(".tool"),
        fillColor = document.querySelector("#fill-color"),
        sizeSlider = document.querySelector("#size-slider"),
        colorBtns = document.querySelectorAll(".colors .option"),
        colorPicker = document.querySelector("#color-picker"),
        clearCanvas = document.querySelector(".clear-canvas"),
        saveImg = document.querySelector(".save-img"),
        ctx = canvas.getContext("2d");

// global variables with default value
let prevMouseX, prevMouseY, snapshot,
isDrawing = false,
selectedTool = "brush",
brushWidth = 5,
selectedColor = "#000";

var mouse = {x: 0, y: 0};

const setCanvasBackground = () => {
    ctx.fillStyle = "#fff";
    ctx.fillRect(0, 0, canvas.width, canvas.height);
    ctx.fillStyle = selectedColor; // setting fillstyle back to the selectedColor, it'll be the brush color
}

window.addEventListener("load", () => {
    // setting canvas width/height.. offsetwidth/height returns viewable width/height of an element
    canvas.width = canvas.offsetWidth;
    canvas.height = canvas.offsetHeight;
    setCanvasBackground();
});

const drawRect = (e) => {
    // if fillColor isn't checked draw a rect with border else draw rect with background
    if(!fillColor.checked) {
        // creating circle according to the mouse pointer
        return ctx.strokeRect(e.offsetX, e.offsetY, prevMouseX - e.offsetX, prevMouseY - e.offsetY);
    }
    ctx.fillRect(e.offsetX, e.offsetY, prevMouseX - e.offsetX, prevMouseY - e.offsetY);
}


// changed it to ellipse
const drawCircle = (e) => {
    ctx.beginPath(); // creating new path to draw circle

    // getting radius for circle according to the mouse pointer
    let radiusX = Math.sqrt(Math.pow((prevMouseX - e.offsetX), 2) + Math.pow((prevMouseX - e.offsetX), 2));
    let radiusY = Math.sqrt(Math.pow((prevMouseY - e.offsetY), 2) + Math.pow((prevMouseY - e.offsetY), 2));

    // ctx.arc(prevMouseX, prevMouseY, radius, 0, 2 * Math.PI); // creating circle according to the mouse pointer
    ctx.ellipse(prevMouseX, prevMouseY, radiusX, radiusY, 0, 0, 2 * Math.PI);
    fillColor.checked ? ctx.fill() : ctx.stroke(); // if fillColor is checked fill circle else draw border circle
}

const drawTriangle = (e) => {
    ctx.beginPath(); // creating new path to draw circle
    ctx.moveTo(prevMouseX, prevMouseY); // moving triangle to the mouse pointer
    ctx.lineTo(e.offsetX, e.offsetY); // creating first line according to the mouse pointer
    ctx.lineTo(prevMouseX * 2 - e.offsetX, e.offsetY); // creating bottom line of triangle
    ctx.closePath(); // closing path of a triangle so the third line draw automatically
    fillColor.checked ? ctx.fill() : ctx.stroke(); // if fillColor is checked fill triangle else draw border
}

const startDraw = (e) => {
    isDrawing = true;
    prevMouseX = e.offsetX; // passing current mouseX position as prevMouseX value
    prevMouseY = e.offsetY; // passing current mouseY position as prevMouseY value
    ctx.beginPath(); // creating new path to draw
    ctx.lineWidth = brushWidth; // passing brushSize as line width
    ctx.strokeStyle = selectedColor; // passing selectedColor as stroke style
    ctx.fillStyle = selectedColor; // passing selectedColor as fill style
    // copying canvas data & passing as snapshot value.. this avoids dragging the image
    snapshot = ctx.getImageData(0, 0, canvas.width, canvas.height);
}

const drawing = (e) => {
    if(!isDrawing) return; // if isDrawing is false return from here
    ctx.putImageData(snapshot, 0, 0); // adding copied canvas data on to this canvas

    if(selectedTool === "brush" || selectedTool === "eraser") {
        // if selected tool is eraser then set strokeStyle to white 
        // to paint white color on to the existing canvas content else set the stroke color to selected color
        ctx.strokeStyle = selectedTool === "eraser" ? "#fff" : selectedColor;
        ctx.lineTo(e.offsetX, e.offsetY); // creating line according to the mouse pointer
        ctx.stroke(); // drawing/filling line with color
    } else if(selectedTool === "rectangle"){
        drawRect(e);
    } else if(selectedTool === "circle"){
        drawCircle(e);
    } else {
        drawTriangle(e);
    }
}

const loadImage = () => {
    let file = fileInput.files[0];
    if(!file) return;
    previewImg.src = URL.createObjectURL(file);
    previewImg.addEventListener("load", () => {
        resetFilterBtn.click();
        document.querySelector(".container").classList.remove("disable");
    });
}


toolBtns.forEach(a => {
    a.addEventListener("click", () => { // adding click event to all tool option
        // removing active class from the previous option and adding on current clicked option
        // document.querySelector(".option .active").classList.remove("active");
		// console.log(a.id);
        // a.classList.add("active");
        selectedTool = a.id;
    });
});

sizeSlider.addEventListener("change", () => brushWidth = sizeSlider.value); // passing slider value as brushSize

colorBtns.forEach(btn => {
    btn.addEventListener("click", () => { // adding click event to all color button
        // removing selected class from the previous option and adding on current clicked option
        document.querySelector(".options .selected").classList.remove("selected");
        btn.classList.add("selected");
        // passing selected btn background color as selectedColor value
        selectedColor = window.getComputedStyle(btn).getPropertyValue("background-color");
    });
});

colorPicker.addEventListener("change", () => {
    // passing picked color value from color picker to last color btn background
    colorPicker.parentElement.style.background = colorPicker.value;
    colorPicker.parentElement.click();
});

clearCanvas.addEventListener("click", () => {
    ctx.clearRect(0, 0, canvas.width, canvas.height); // clearing whole canvas
    setCanvasBackground();
    SendReset(document.getElementById("reset_url").value);
});

canvas.addEventListener("mousedown", startDraw);
canvas.addEventListener("mousemove", drawing);
canvas.addEventListener("mouseup", () => isDrawing = false);

document.addEventListener("DOMContentLoaded", function(){
    const event_id = $("#event_id").val();

    // templates
    $('#templates').on('click', function(event){
        // if an image was clicked add it as background
        if(event.target.src !== undefined){
            // clear canvas
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            
            // draw new background
            var background = new Image();
            background.src = event.target.src;
            background.onload = function() {
                // reset scope for different dimension
                // add some padding
                canvas.width = this.width+10;
                canvas.height = this.height+10;
                ctx.drawImage(background, 5, 5);
            }
        }
    });

    // draw restored images 
	const restored_image = $("#RestoreImg").attr('src');
	if(restored_image !== undefined){
		var background = new Image();
			background.src = restored_image;
			background.onload = function() {
				// reset scope for different dimension
				canvas.width = this.width;
   				canvas.height = this.height;
				ctx.drawImage(background, 0, 0);
			}
			$("#remark").html("Restored drawing");
	}

});


/*
Remove all files from server, reset page
don't do a reload but a "Save"
*/
function SendReset(reset_url) {
    $.ajax({
        method: 'POST',
        url: reset_url
    });
}
