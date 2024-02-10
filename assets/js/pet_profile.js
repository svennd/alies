function get_chip_info(chip) {
    if (!chip) {
      return false;
    }
  
	var clean_chip = chip.replace(/-/g, '');
	
    if (clean_chip.length !== 15) {
      $("#chip_info").html("Unrecognized code, not 15 numbers!");
      return;
    }
  
    if (clean_chip.startsWith('9')) {
      $("#chip_info").html("Manufacture code");
    } else {
      let countryCode = country_code[clean_chip.substr(0, 3)];
      if (typeof countryCode === 'undefined') {
		countryCode = country_code[clean_chip.substr(0, 2)];
		if (typeof countryCode === 'undefined') {
        $("#chip_info").html("Unknown country code");
      	} else {
			$("#chip_info").html("Country code: " + countryCode);
		}
      } else {
        $("#chip_info").html("Country code: " + countryCode);
      }
    }
  }
  
function createBreedSelect2(apiUrl) {
	return {
		ajax: {
		url: function (params) { 
			return apiUrl + ((params.term === undefined) ? '' : params.term); 
		},
		dataType: 'json',
		data: function (params) {
			return {
			type: $("input:radio[name ='type']:checked").val(),
			term: params.term || ''
			};
		},

		processResults: function (data) {
			const resultsArray = [{ id: '-1', text: '---' }, ...data.results];
			return { results: resultsArray };
		}
		}
	};
}

function make_date(date) {
    if ($("#dead").prop("checked")) {
        return false;
    }

    if (!date) {
        return false;
    }

    const today = new Date();
    const birthDate = new Date(date);

    const age = calculateAge(today, birthDate);
    if (isNaN(age)) {
        $("#birth_info").html("Wrong date!");
    } else {
        $("#birth_info").html(`${age} years old`);
    }
}

function calculateAge(today, birthDate) {
    let years = today.getFullYear() - birthDate.getFullYear();

    if (today.getMonth() < birthDate.getMonth() ||
        (today.getMonth() === birthDate.getMonth() && today.getDate() < birthDate.getDate())) {
        years--;
    }

    return years;
}

// based on ISO 11784
// info : https://www.icar.org/index.php/rfid-injectable/
// info : https://www.albertaanimalhealthsource.ca/sites/default/files/uploads/manufacturersisosandcountrycodes.pdf
// info : https://github.com/Proxmark/proxmark3/blob/master/client/cmdlffdx.c
// info : https://www.pet-detect.com/pages/Interpreting-microchip-numeric-codes.aspx?pageid=610 (france)
// country code:  https://nl.wikipedia.org/wiki/ISO_3166-1 ???
var country_code = {32: "Argentina",36: "Australia",40: "Austria",44: "Bahamas",52: "Barbados",56: "Belgium",60: "Bermuda",76: "Brazil",100: "Bulgaria",124: "Canada",152: "Chile",156: "China",158: "Taiwan",203: "Czech Republic",208: "Denmark",214: "Dominican Republic",246: "Finland",250: "France",276: "Germany",300: "Greece",348: "Hungary",356: "India",360: "Indonesia",372: "Ireland",376: "Israel",380: "Italy",392: "Japan",442: "Luxembourg",458: "Malaysia",484: "Mexico",492: "Monaco",528: "Netherlands",554: "New Zealand",578: "Norway",604: "Peru",608: "Philippines",616: "Poland",620: "Portugal",630: "Puerto Rico",642: "Romania",643: "Russian Federation",710: "South Africa",724: "Spain",752: "Sweden",756: "Switzerland",764: "Thailand",792: "Turkey",804: "Ukraine",818: "Egypt",826: "United Kingdom",840: "Unite States",858: "Uruguay",862: "Venezuela",891: "Yogoslavia"};

// most common colors in the database
// this is a non-restrictive select so they can add their own color
var simple_colors = [{text: 'abrikoos'},{text: 'albino'},{text: 'beige'},{text: 'beige bruin'},{text: 'beige grijs'},{text: 'beige langhaar'},{text: 'beige wit'},{text: 'beige zwart'},{text: 'black en tan'},{text: 'blauw'},{text: 'blauwgrijs'},{text: 'blauw wit'},{text: 'blond'},{text: 'bont'},{text: 'bruin'},{text: 'bruinachtig'},{text: 'bruin beige'},{text: 'bruin gestroomd'},{text: 'bruin gestroomd wit'},{text: 'bruin getijgerd'},{text: 'bruin getijgerd lang'},{text: 'bruin getijgerd wit'},{text: 'bruin grijs'},{text: 'bruin grijs tijger'},{text: 'bruin langhaar'},{text: 'bruin schimmel'},{text: 'bruin tabby'},{text: 'bruin tijger'},{text: 'bruin tijger wit'},{text: 'bruin wit'},{text: 'bruin wit zwart'},{text: 'bruin zwart'},{text: 'bruin zwart masker'},{text: 'calico'},{text: 'chocolade'},{text: 'creme'},{text: 'donkerblauw'},{text: 'donkerbruin'},{text: 'donkergrijs'},{text: 'driekleur'},{text: 'geel'},{text: 'gestroomd'},{text: 'gestroomd wit'},{text: 'getijgerd'},{text: 'getijgerd langhaar'},{text: 'getijgerd wit'},{text: 'goud'},{text: 'grijs'},{text: 'grijs beige'},{text: 'grijsblauw'},{text: 'grijs bruin'},{text: 'grijs bruin tijger'},{text: 'grijs gestreept'},{text: 'grijs getijgerd'},{text: 'grijs getijgerd wit'},{text: 'grijs langhaar'},{text: 'grijs tabby'},{text: 'grijs tijger'},{text: 'grijs tijger wit'},{text: 'grijs wit'},{text: 'grijs wit getijgerd'},{text: 'grijs wit tijger'},{text: 'grijs zwart'},{text: 'groen'},{text: 'kastanjebruin'},{text: 'lapjeskat'},{text: 'lichtbruin'},{text: 'lichtzilver'},{text: 'lila'},{text: 'lilac'},{text: 'oranje'},{text: 'peper en zout'},{text: 'reekalf'},{text: 'roest'},{text: 'rood'},{text: 'roodbruin'},{text: 'roomkleurig'},{text: 'ros'},{text: 'rosbruin'},{text: 'ros getijgerd'},{text: 'ros getijgerd wit'},{text: 'ros tijger'},{text: 'ros wit'},{text: 'ros wit getijgerd'},{text: 'ros wit tijger'},{text: 'schildpad'},{text: 'schimmel'},{text: 'seal point'},{text: 'tarwekleurig'},{text: 'tricolor'},{text: 'vos'},{text: 'wildkleur'},{text: 'wit'},{text: 'wit beige'},{text: 'wit bruin'},{text: 'wit bruin zwart'},{text: 'wit getijgerd'},{text: 'wit gevlekt'},{text: 'wit grijs'},{text: 'wit grijs getijgerd'},{text: 'wit grijs tijger'},{text: 'wit ros'},{text: 'wit zwart'},{text: 'zilver'},{text: 'zwart'},{text: 'zwart beige'},{text: 'zwart bruin'},{text: 'zwart bruin wit'},{text: 'zwart grijs'},{text: 'zwart halflang'},{text: 'zwart langhaar'},{text: 'zwart wit'},{text: 'zwart wit bruin'},{text: 'zwart wit langhaar'}];