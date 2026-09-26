#let data = json("data.json")
#let s = data.settings
#let b = data.branding
#let l = data.labels
#let accent = rgb(b.accent)
#let paragraph(value) = {
  for (i, line) in value.split("\n").enumerate() {
    if i > 0 { linebreak() }
    text(line)
  }
}
#let branding() = {
  grid(columns: (1fr, 62mm), gutter: 8mm, align: (left + horizon, right + horizon),
    if data.logo != "" {
      image(data.logo, width: 62mm, height: 15mm, fit: "contain")
    } else {
      text(size: 14pt, weight: "bold", fill: accent, b.name)
    },
    align(right)[
      #set text(size: 9pt, fill: accent)
      #set par(leading: 0.35em)
      #if data.logo != "" and b.name != "" [#text(b.name)]
      #if data.logo != "" and b.name != "" and (b.address != "" or b.contact != "") [#linebreak()]
      #if b.address != "" [#paragraph(b.address)]
      #if b.address != "" and b.contact != "" [#linebreak()]
      #if b.contact != "" [#paragraph(b.contact)]
    ],
  )
  v(7mm)
}
#let document(body) = {
  set page(paper: "a4", margin: s.margin * 1mm, numbering: "1 / 1", number-align: center)
  set text(font: "Noto Sans", size: s.font_size * 1pt, lang: data.language)
  set par(spacing: s.spacing * 1mm, leading: 0.65em)
  set table(inset: 5pt, stroke: 0.3pt + rgb("#dce3e5"))
  body
}
#let heading(value) = block(above: 4mm, below: 4mm)[#text(size: 17pt, weight: "bold", fill: accent, value)]
#let table-head(..cells) = table.header(..cells.pos().map(x => text(weight: "bold", fill: accent, x)))
