<link href="<?php echo base_url('assets/css/help.css'); ?>" rel="stylesheet">

<div class="row">
      <div class="col-lg-12 mb-4">

      <div class="card shadow mb-4">
			<div class="card-header">
				Help / Dierenarts
			</div>
            <div class="card-body">
  <div class="accordion">

    <h5>Klanten</h5>
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
                    Enkel numerieke waarden worden gebruikt om te zoeken.<br/>012/34.56 zal dus 012 34 56 en 01234.56 vinden.</td>
            </tr>
            <tr>
                <td>Dier chip nr.</td>
                <td>input*</td>
                <td>Partiële chip is mogelijk (minimaal 10 cijfers), doorzoekt enkel lokale databank.<br/>Formatering toegelaten (***-***-***-***-***)</td>
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

    <div class="accordion-item">
      <button id="accordion-button-1" aria-expanded="false"><span class="accordion-title">Wat betekenen de kleuren bij kant opzoekingen ?</span><span class="icon" aria-hidden="true"></span></button>
      <div class="accordion-content">
        <p>Er zijn 4 mogelijke statussen voor klanten. Deze worden weergegeven met een kleur. <br/>
            <table class="table table-sm">
            <tr>
                <th>Kleur</th>
                <th>Beschrijving</th>
            </tr>
            <tr>
                <td><span class="badge badge-danger">rood</span></td>
                <td>De klant is aangeduid als, klant met schulden.</td>
            </tr>
            <tr>
                <td><span class="badge" style="color:#fff;background-color:#a77628;">bruin</span></td>
                <td>De klant is aangeduid als, klant met beperk/laag budget.</td>
            </tr>
            <tr>
                <td><s>Geschrapt</s></td>
                <td>Ongeldige klant, gelieve niet te gebruiken.</td>
            </tr>
            <tr>
                <td><span class="badge badge-primary">blauw</span></td>
                <td>Gewone klant</td>
            </tr>
            </table>
            Voorbeeld van deze statussen ziet U hieronder :<br/>
            <img src='<?php echo base_url('assets/promo/help_clients.png'); ?>' class='img-fluid' alt='client colors' />
		</p>
      </div>
    </div>

    <h5 class="pt-4">Varia</h5>
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
      <button id="accordion-button-1" aria-expanded="false"><span class="accordion-title">Welke symbolen worden er gebruikt voor betalingen ?</span><span class="icon" aria-hidden="true"></span></button>
      <div class="accordion-content">
        <p>
			Er worden drie icoontjes gebruikt voor betalingen. We verbergen waar mogelijk het icoontje voor betalingen per kaart. Gezien dit de standaart methode is. <br/>
            <table class="table table-sm">
            <tr>
                <th>Icoon</th>
                <th>Titel</th>
                <th>Beschrijving</th>
            </tr>
            <tr>
                <td><i class='fab fa-fw fa-cc-visa fa-lg' style='color:blue'></i></td>
                <td>Kaart</td>
                <td>Betaling per kaart, maestro, bankcontact, ...</td>
            </tr>
            <tr>
                <td><i class='fa-solid fa-fw fa-money-bill fa-lg' style='color:green'></i></td>
                <td>Contant</td>
                <td>Contant betalingen (munten en briefjes)</td>
            </tr>
            <tr>
                <td><i class='fa-solid fa-fw fa-money-bill-transfer fa-lg' style='color:tomato'></i></td>
                <td>Transfer</td>
                <td>Bank overschrijving of niet gevalideerde betaal methoden.</td>
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