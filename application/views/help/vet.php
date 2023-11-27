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
				Help / Dierenarts
			</div>
            <div class="card-body">
  <div class="accordion">

    <!-- item -->
    <div class="accordion-item">
      <button id="accordion-button-1" aria-expanded="false"><span class="accordion-title">Hoe hou ik de stock correct bij een verlies van (lot) product ?</span><span class="icon" aria-hidden="true"></span></button>
      <div class="accordion-content">
        <p>
			Wanneer een lot komt te vervallen zal het volledige lot te vinden zijn bij de <a href="<?php echo base_url('stock/expired_stock'); ?>">vervallen producten pagina</a>.<br/>
			Wanneer een product verloren gaat, bijvoorbeeld door een ongeval, kan je dit (deel van) een lot verwijderen uit de stock door naar het profiel van het product te gaan. (Producten -> Product opzoeken, product fiche -> tab blad stock)<br/>
			En vervolgens op dit icoontje klikken <a href="#" class="btn btn-outline-danger btn-sm"><i class="fa-solid fa-person-falling-burst"></i></a> om een (deel van) het lot te verwijderen.
		</p>
      </div>
    </div>

    <!-- item -->
    <div class="accordion-item">
      <button id="accordion-button-1" aria-expanded="false"><span class="accordion-title">Hoe kan ik een klant opzoeken ?</span><span class="icon" aria-hidden="true"></span></button>
      <div class="accordion-content">
        <p>
			U kunt klanten opzoeken via de <a href="<?php echo base_url('search') ?>">zoekfunctie</a>. De zoekfunctie zoekt steeds op alle onderstaande waarden.<br/>
            U kunt uw voorkeur zoekactie opgeven in uw <a href="<?php echo base_url('vet/profile'); ?>">profiel</a>. Deze wordt altijd geselecteerd als er resultaten zijn voor uw zoekopdracht.<br/> 
			Het zoekveld is niet hoofdlettergevoelig, dus "Tim" en "tIM" zullen beide overeenkomen met <b>"tim"</b><br/>
			<table class="table table-sm">
            <tr>
                <th>Veld</th>
                <th>Zoek methode</th>
                <th>Voorbeeld</th>
            </tr>
            <tr>
                <td>Voornaam</td>
                <td>input*</td>
                <td>Tim => <b>Tim</b>othy, <b>Tim</b>, ...</td>
            </tr>
            <tr>
                <td>Naam</td>
                <td>input*</td>
                <td>Jans => <b>Jans</b>sens, <b>Jans</b>was, ...</td>
            </tr>
            <tr>
                <td>Straat</td>
                <td>*input*</td>
                <td>beek => Klei<b>beek</b>straat, <b>Beek</b>straat, ...</td>
            </tr>
            <tr>
                <td>Telefoon</td>
                <td>input*</td>
                <td>0123 => <b>0123</b>456789 (alle telefoon velden)<br/>
                    Zoekt op exacte waarde, dus 0496/ zal niet 0496. niet vinden.</td>
            </tr>
            <tr>
                <td>Dier chip nr.</td>
                <td>input*</td>
                <td>Partiële chip is mogelijk (minimaal 10 cijfers), doorzoekt enkel lokale databank.</td>
            </tr>
            <tr>
                <td>Dier naam</td>
                <td>input*</td>
                <td>Tim => <b>Tim</b>othy, <b>Tim</b>, ... (max. 250 resultaten)</td>
            </tr>
            <tr>
                <td>Ras</td>
                <td>input*</td>
                <td>Labra => <b>Labra</b>dor, <b>Labra</b>doodle. (max. 50 resultaten)</td>
            </tr>
            <tr>
                <td>Dier ID</td>
                <td>input</td>
                <td>Volledig dier ID nodig, u krijgt dus steeds maar 1 resultaat</td>
            </tr>
            <tr>
                <td>Klant ID</td>
                <td>input</td>
                <td>Volledig klant ID nodig, u krijgt dus steeds maar 1 resultaat</td>
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