/*

Try to extract most data from these scans


https://ref.gs1.org/standards/genspecs/

*/

// parse GS1 data matrix
function parse_gs1_data_matrix(barcode) {
  let gtin = false;
  let eol = false;
  let lotnr = false;
  let serial = false;
  const initial_barcode = barcode;

  // clean up incoming code
  // remove ()
  barcode = barcode.replace(/[()]/g, "");

  // split on control characters
  const pieces = barcode.split('$');
  pieces.forEach((part) => {

    // parts
    console.log(part);
    
    while (part.length > 0) {
      let current_length = part.length;  
      
      // get first 4 characters (or less at the end)
      let characters = part.slice(0, Math.min(2, part.length));

      // (01) GTIN
      if (characters === "01") {

        // we expect only GTIN-14
        let GTIN = part.match(/^01([0-9]{14})/);

        // check if mapped
        if (GTIN && GTIN.length > 1 && GtinCheck(GTIN[1]))
        {
          gtin = GTIN[1];
          part = part.replace(GTIN[0], "");
        }
      }
      // (10) batch/lotnr
      else if ( characters == "10" )
      {
        lotnr = part.slice(2);
        // this piece is finished
        break;
      }
      // (21) serial number
      else if ( characters == "21" )
      {
        serial = part.slice(2);
        // this piece is finished
        break;
      }
      // determing end of life - always 6 digits YYMMDD
      // (12) due date
      // (15) best before date
      // (16) sell by date
      // (17) expiry date
      else if (
              characters === "12" ||
              characters === "15" ||
              characters === "16" ||
              characters === "17"
          )
      {
        let EOL = part.match(/^\(?1[2567]\)?([0-9]{6})/);

        // check if mapped
        if (EOL && EOL.length > 1)
        {
          eol = EOL[1];
          part = part.replace(EOL[0], "");
        }

      }

      // no change in length, let's jump out
      if(current_length == barcode.length) { break; }
    }

  });

  
    return {
        original: initial_barcode,
        gtin: gtin,
        eol: eol,
        lotnr: lotnr,
        serial: serial,
    };
}


// verify the checksum of GTIN
function GtinCheck(gs1Data) {
    const reversedDigits = gs1Data.slice(0, -1).split('').reverse();
    let sum = 0;
  
    for (let i = 0; i < reversedDigits.length; i++) {
      const digit = parseInt(reversedDigits[i], 10);
  
      if (i % 2 === 0) {
        sum += digit * 3;
      } else {
        sum += digit;
      }
    }
  
    const mod10 = sum % 10;
    const checkDigit = (mod10 !== 0) ? (10 - mod10) : 0;
    
    return (checkDigit == gs1Data.slice(-1));
}