<link href="<?php echo base_url('assets/css/help.css'); ?>" rel="stylesheet">
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

    <!-- item -->
    <div class="accordion-item">
      <button id="accordion-button-1" aria-expanded="false"><span class="accordion-title">Welke stock errors zijn er ?</span><span class="icon" aria-hidden="true"></span></button>
      <div class="accordion-content">
        <p>Sommige problemen krijgen een info veld mee. Hieronder de gekende velden:<br/>
        <table class="table table-sm">
            <tr>
                <th>Info</th>
                <th>Probleem</th>
                <th>Oplossing</th>
            </tr>
            <tr>
                <td>NO_STOCK</td>
                <td>De dierenarts heeft een product verkocht waarvan geen stock aanwezig was. We kennen dus geen locatie, lotnr of vervaldatum.</td>
                <td>Indien dit product een virtueel product is. Kunt U best een procedure ervan maken, of een zeer groot -virtueel- volume toevoegen. Indien het een bestaand nog niet ingevoerd product is kunt U best de stock editeren.</td>
            </tr>
            <tr>
                <td>OVERDRAW_MOVE</td>
                <td>De dierenarts heeft een product in groter volume verplaats dan aanwezig/beschikbaar was op de locatie, van dit lotnr/vervaldatum dan beschikbaar.</td>
                <td>Ander lotnummer verminderen; of correctie op lot uitvoeren.</td>
            </tr>
        </table>
        </p>
      </div>
    </div>

    <!-- item -->
    <div class="accordion-item">
      <button id="accordion-button-1" aria-expanded="false"><span class="accordion-title">Wat is een gestructureerde mededeling ?</span><span class="icon" aria-hidden="true"></span></button>
      <div class="accordion-content">
        <p>
            Bij overschrijving wordt steeds een gestructureerde mededeling meegegeven. Deze wordt normaal opgebouwd uit het klant ID een uniek rekening id (niet factuur nummer). <br/>
            De gestructureerde mededeling is opgebouwd uit 10 getallen + 2 controle cijfers (modulo 97)<br/>
            Het is mogelijk deze aan te passen naar de volgende opties : 
            <table class="table table-sm">
            <tr>
                <th>Optie</th>
                <th>Beschrijving</th>
            </tr>
            <tr>
                <td>CLIENT ID only</td>
                <td>Enkel de klant nummer zal gebruikt worden om een gestructueerde mededeling te berekenen.<br/>
                  eg: klannummer 8312; zal een gestructueerde mededeling krijgen:<br/>
                  +++<b>831/2</b>000/00063+++.
                </td>
            </tr>
            <tr>
                <td>CLIENT ID + BILL ID</td>
                <td>De klant nummer en rekening id worden aan elkaar geplakt, <br/>met zoveel nullen ertussen als nodig om aan 10 getallen te komen<br/>
                  eg: klantnummer <span style="color:green;">8312</span> en rekening <span style="color:blue;">24123</span>; zal een gestructueerde mededeling krijgen:<br/>
                  +++<span style="color:green;">831/2</span>0<span style="color:blue;">24/123</span>33+++<br/>
                  <i>Mocht klantnummer en rekening nummer groter zijn dan 10 getallen, vallen we terug naar klant id alleen.<i>
                </td>
            </tr>
            <tr>
                <td>CLIENT ID + 3 last digits of BILL ID</td>
                <td>De verminderde weergave van client ID + bill ID. Enkel de laatste 3 getallen van de rekening worden aangevuld.
                  eg: klantnummer <span style="color:green;">8312</span> en rekening 24<span style="color:blue;">123</span>; zal een gestructueerde mededeling krijgen:<br/>
                  +++<span style="color:green;">831/2</span>000/<span style="color:blue;">123</span>89+++<br/>

                </td>
            </tr>
            </table>

            Het is steeds de bedoeling dat op deze manier facturen automatisch kunnen verwerkt worden.
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