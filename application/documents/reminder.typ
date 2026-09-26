#import "common.typ": *
#show: document
#let letters = data.content.letters
#for (i, row) in letters.enumerate() {
  if i > 0 { pagebreak() }
  let letter = [
    #branding()
    #v(5mm)
    #text(weight: "bold", row.recipient)
    #linebreak()#paragraph(row.address)
    #v(10mm)
    #heading(row.subject)
    #paragraph(row.greeting)
    #v(3mm)
    #paragraph(row.body)
    #v(5mm)
    #table(columns: (1fr, 2fr),
      l.pet, row.pet,
      l.vaccine, row.vaccine,
      l.due_date, row.due_date,
    )
    #v(8mm)
    #paragraph(row.closing)
    #linebreak()#text(weight: "bold", b.name)
  ]
  context {
    let available = 297mm - 2 * s.margin * 1mm - 2mm
    let size = measure(letter, width: 210mm - 2 * s.margin * 1mm)
    assert(size.height <= available, message: "LETTER_OVERFLOW_" + str(i + 1))
    block(breakable: false, letter)
    assert(here().page() == i + 1, message: "LETTER_OVERFLOW_" + str(i + 1))
  }
}
#context assert(counter(page).final().first() == letters.len(), message: "LETTER_OVERFLOW_0")
