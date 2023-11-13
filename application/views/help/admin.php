<style>
 .accordion .accordion-item {
	 border-bottom: 1px solid #e5e5e5;
}
 .accordion .accordion-item button[aria-expanded='true'] {
	 border-bottom: 1px solid #03b5d2;
}
 .accordion button {
	 position: relative;
	 display: block;
	 text-align: left;
	 width: 100%;
	 padding: 1em 0;
	 color: #7288a2;
	 font-size: 1.15rem;
	 font-weight: 400;
	 border: none;
	 background: none;
	 outline: none;
}
 .accordion button:hover, .accordion button:focus {
	 cursor: pointer;
	 color: #03b5d2;
}
 .accordion button:hover::after, .accordion button:focus::after {
	 cursor: pointer;
	 color: #03b5d2;
	 border: 1px solid #03b5d2;
}
 .accordion button .accordion-title {
	 padding: 1em 1.5em 1em 0;
}
 .accordion button .icon {
	 display: inline-block;
	 position: absolute;
	 top: 18px;
	 right: 0;
	 width: 22px;
	 height: 22px;
	 border: 1px solid;
	 border-radius: 22px;
}
 .accordion button .icon::before {
	 display: block;
	 position: absolute;
	 content: '';
	 top: 9px;
	 left: 5px;
	 width: 10px;
	 height: 2px;
	 background: currentColor;
}
 .accordion button .icon::after {
	 display: block;
	 position: absolute;
	 content: '';
	 top: 5px;
	 left: 9px;
	 width: 2px;
	 height: 10px;
	 background: currentColor;
}
 .accordion button[aria-expanded='true'] {
	 color: #03b5d2;
}
 .accordion button[aria-expanded='true'] .icon::after {
	 width: 0;
}
 .accordion button[aria-expanded='true'] + .accordion-content {
	 opacity: 1;
	 max-height: 35em;
	 transition: all 200ms linear;
	 will-change: opacity, max-height;
}
 .accordion .accordion-content {
	 opacity: 0;
	 max-height: 0;
	 overflow: hidden;
	 transition: opacity 200ms linear, max-height 200ms linear;
	 will-change: opacity, max-height;
}
 .accordion .accordion-content p {
	 font-size: 1rem;
	 font-weight: 300;
	 margin: 2em 0;
}

</style>

<div class="row">
      <div class="col-lg-12 mb-4">

      <div class="card shadow mb-4">
			<div class="card-header">
				Help / Admin
			</div>
            <div class="card-body">
  <div class="accordion">
    <div class="accordion-item">
      <button id="accordion-button-1" aria-expanded="false"><span class="accordion-title">Wat betekent xxx prijs ? (aankoop)</span><span class="icon" aria-hidden="true"></span></button>
      <div class="accordion-content">
        <p>Het programma kent verschillende aankoop prijzen. <br/>
        <table class="table table-sm">
            <tr>
                <td>Catalogus prijs</td>
                <td>Dit is de prijs die de groothandel aanbied zonder enige korting.<br/>Deze kan automatisch binnengetrokken worden bij:<br/>1) iedere leverbon en <br/>2) via de website van de groothandel (export prijzen).</td>
            </tr>
            <tr>
                <td>Handmatige prijs</td>
                <td>Dit is de prijs die door de admin is ingesteld. De datum van update kan worden bijgehouden.<br/><b>Dit is ook de prijs die voor de dierenarts wordt getoond als "catalogusprijs"</b></td>
            </tr>
            <tr>
                <td>Dag prijs</td>
                <td>Dit is de feitelijke prijs die betaald wordt voor een product. <br/>Te vinden op de leverbon als "netto prijs".<br/>Dit is inclusief tijdelijke acties en eventueel een vaste reductie bij groothandel.</td>
            </tr>
        </table>
        </p>
      </div>
    </div>

    <!-- item -->
    <div class="accordion-item">
      <button id="accordion-button-1" aria-expanded="false"><span class="accordion-title">Wat betekent xxx prijs ? (verkoop)</span><span class="icon" aria-hidden="true"></span></button>
      <div class="accordion-content">
        <p>
        Het programma kent verschillende verkoops prijzen. <br/>
        <table class="table table-sm">
            <tr>
                <td>Verkoops prijs of consumentenprijs</td>
                <td>
                    Dit is de prijs die automatisch gebruikt worden bij de berekening van een volume (product of handeling). <br/>
                    Het programma biedt de mogelijkheid om meerdere prijzen te gebruiken om bij hogere volumes een andere berekening toe te passen.<br/>
                    bv: product A, bij 1 ml kost 10 &euro; / ml. Bij 10 ml kost 9 &euro; / ml. Het programma zal steeds de hoogste prijs per eenheid selecteren wanneer er een overlap zou bestaan. 
                </td>
            </tr>
            <tr>
                <td>Groothandel suggestie</td>
                <td>Dit is de prijs die geadverteerd wordt door de groothandel, deze is enkel beschikbaar in het prijs-settings scherm. Dit wordt automatisch geupdate wanneer catalogus prijs geupdate wordt.</td>
            </tr>
        </table>
        </p>
      </div>
    </div>

  </div>
</div>


</div>
</div>
</div>
</div>
</div>
<script>
const items = document.querySelectorAll(".accordion button");

function toggleAccordion() {
const itemToggle = this.getAttribute('aria-expanded');

for (i = 0; i < items.length; i++) {
    items[i].setAttribute('aria-expanded', 'false');
}

    if (itemToggle == 'false') {
        this.setAttribute('aria-expanded', 'true');
    }
}

items.forEach(item => item.addEventListener('click', toggleAccordion));
</script>