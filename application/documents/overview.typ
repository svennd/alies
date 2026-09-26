#import "common.typ": *
#show: document
#let c = data.content
#branding()
#heading(s.title)
#paragraph(s.introduction)
#grid(columns: (1fr, 1fr), gutter: 10mm,
  [#text(weight: "bold", c.recipient)#linebreak()#paragraph(c.address)],
  [#text(weight: "bold", c.pet + " (#" + c.pet_id + ")")
   #linebreak()#c.type / #c.gender
   #linebreak()#l.birth: #c.birth
   #linebreak()#l.breed: #c.breed
   #linebreak()#l.chip: #c.chip
   #linebreak()#l.weight: #c.weight kg],
)
#v(5mm)
#let headers = (l.vaccine, l.injection)
#if s.show_vet { headers.push(l.vet) }
#if s.show_location { headers.push(l.location) }
#if s.show_due { headers.push(l.due_date) }
#let cells = ()
#for row in c.vaccines {
  cells += (row.vaccine, row.date)
  if s.show_vet { cells.push(row.vet) }
  if s.show_location { cells.push(row.location) }
  if s.show_due { cells.push(row.due) }
}
#if c.vaccines.len() == 0 {
  paragraph(l.no_vaccines)
} else {
  table(columns: headers.len(), table-head(..headers), ..cells)
}
#v(5mm)
#paragraph(s.footer)
