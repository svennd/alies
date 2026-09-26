#import "common.typ": *
#show: document
#let c = data.content
#branding()
#heading(c.title + " " + c.number)
#grid(columns: (1fr, 1fr), gutter: 12mm,
  [#text(weight: "bold", c.recipient)#linebreak()#paragraph(c.address)
   #linebreak()#l.client_id: #c.client_id
   #if c.vat_number != "" [#linebreak()#l.vat: #c.vat_number]],
  [#l.date: #c.date#linebreak()#l.due_date: #c.due
   #linebreak()#l.location: #c.location
   #linebreak()#c.payment_status],
)
#v(5mm)
#if c.message != "" { paragraph(c.message); v(3mm) }
#table(columns: (24mm, 1fr, 20mm, 27mm, 14mm, 24mm),
  table-head(l.pet, l.description, l.quantity, l.unit_price, l.vat, l.total),
  ..c.lines.map(row => (row.group, row.description, row.quantity, row.unit_price, row.tax, row.total)).flatten(),
)
#v(5mm)
#block(breakable: false)[
  #grid(columns: (1fr, 1fr), gutter: 10mm,
    table(columns: (1fr, 1fr, 1fr), table-head(l.vat, l.net, l.tax), ..c.tax_rows.flatten()),
    table(columns: (2fr, 1fr), l.net, c.net + " €", l.tax, c.tax_total + " €", text(weight: "bold", l.total), text(weight: "bold", c.total + " €")),
  )
]
#v(5mm)
#paragraph(l.cash + ": " + c.cash + " €  |  " + l.card + ": " + c.card + " €  |  " + l.transfer + ": " + c.transfer + " €")
#v(5mm)
#block(breakable: false)[
  #grid(columns: (1fr, auto), gutter: 8mm,
    [#paragraph(s.payment_text)
     #if c.bank_name != "" [#linebreak()#c.bank_name]
     #if c.iban != "" [#linebreak()IBAN: #c.iban]
     #if c.bic != "" [ BIC: #c.bic]
     #linebreak()#text(weight: "bold", c.reference)],
    if data.qr != "" { image(data.qr, width: 32mm) } else { [] },
  )
]
#v(5mm)
#paragraph(s.footer)
