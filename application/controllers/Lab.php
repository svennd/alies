<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Lab extends Vet_Controller
{

	# constructor
	public function __construct()
	{
		parent::__construct();

		$this->load->model('Lab_model', 'lab');
		$this->load->model('Lab_detail_model', 'lab_line');
	}

	public function index()
    {
    	$this->_render_page('lab/index', array(
			"data" => $this->lab->get_all(),
		));
	}


    public function detail(int $lab_id)
    {
		# update comment
		$comment_update = false;
		if ($this->input->post('submit')) {
			$this->lab
					->where(array(
									"id" 	=> (int) $lab_id
							))
					->update(array(
									"comment" => $this->input->post('message'),
							));
			$comment_update = true;
		}

    	$this->_render_page('lab/detail', array(
			"lab_info" => $this->lab->get($lab_id),
			"lab_details" => $this->lab_line->where(array('lab_id' => $lab_id))->get_all(),
            "comment_update" => $comment_update
		));
    }

    /*
        cron function for samples from online.medilab.be
    */
    public function cron_medilab($redirect = false, int $days = 14)
    {
        // static
        $url = "https://" . base64_decode($this->conf['medilab_user']['value']) . ":". base64_decode($this->conf['medilab_pasw']['value']) . "@online.medilab.be/dokter/";

        $test_id_to_names = array(
            // hematologie - guessed
            "11015" => "Hemoglobine",
            "11050" => "Hematocriet",
            "11052" => "Rode bloedcellen",
            "11060" => "MCV",
            "11061" => "MCH",
            "11062" => "MCHC",
            "11080" => "Witte bloedcellen",
            
            // formule
            "11101" => "Lymfocyten",
            "11103" => "Staafkernigen",
            "11105" => "Segmentkernigen",
            "11107" => "Eosinofielen",
            "11109" => "Monocyten",
            "11111" => "Basofielen",
            "11113" => "Abnormale vormen",
            "11126" => "Totale Lymfo s (ALC)",
            "11127" => "Totale Neutr.staven",
            "11128" => "Tot.neutrof.segm.(ANC)",
            "11129" => "Totale monocyten",
            "11134" => "Reticulocyten",
            "11135" => "Trombocyten",
            "11131" => "Totale eosinofielen",

            "12340" => "Ureum",
            "12345" => "Creatinine",
            "12385" => "IJzer",
            "12405" => "Totaal Eiwit",
            "12730" => "Bilirubine totaal",
            "12732" => "Bilirubine direct",
            "12734" => "Bilirubine indirect",

            // hemostase
            "11511" => "Protrombinetijd",
            "11530" => "Part.trombopl.tijd",
            "11540" => "Fibrinogeen",

            // urine
            "15741" => "Cortisol urine (random)",
            "15742" => "Cortisol creatinine ratio",
            "16226" => "Creatinine (random)", // double w 16290
            "16428" => "Eiwit creatinine ratio",

            // screening
            "16477" => "Glucose",
            "16478" => "Eiwit",
            "16479" => "Ketonen",
            "16480" => "pH",
            "16483" => "Densiteit (S.G.)",
            "16649" => "Bilirubine", // REPORT : N ???
            "16486" => "Bilirubine", // REPORT : J
            "16492" => "Urobiline", // based on req. form
            "16655" => "Kultuur",
            "16656" => "Identificatie",
            "16657" => "Antibiogram (urine)",
            
            // Microscopie/flow cytometrie
            "16645" => "Leukocyten",
            "16646" => "Erythrocyten",
            "16647" => "Plaveiselepitheel",
            "16648" => "Kleine ronde cellen",
            "16650" => "Gisten",
            "16651" => "Hyaliene cylinders", // guessed
            "16652" => "Pathologische cylinders", // guessed
            "16653" => "Kristallen",

            // FARMACA-TOXICOLOGIE-SPOORELEMENTEN
            "14705" => "Fenobarbital",

            // biochemie
            "12211" => "Fructosamine",
            "12260" => "Insuline",
            "12776" => "Gamma GT",
            "12780" => "Galzuren",

            // ANATOMOPATHOLOGIE
            "17809" => "oorsprong biopt",
            "17810" => "Histologie",

            // IMMUNOLOGIE
            "11852" => "As Leishmania",
            "11856" => "Anaplasma",
            "11857" => "Ehrlichia",
            "11858" => "Borrelia (Lyme)",
            "11859" => "Dirofilaria (hartworm)",

            // 
            "12750" => "Ammoniak (ext)",
            "12760" => "GOT (AST)",
            "12773" => "GPT (ALT)",
            "12361" => "SDMA",
            "16290" => "Creatinine (random)", // double w 16226
            "12805" => "Alkalische fosfatasen",

            // serum indices
            "15000" => "Hemolyse",
            "15001" => "Lipemie",
            "15002" => "Icterie",

            // faeces
            "16712" => "Parvovirus in faeces",
            "16704" => "Bloed in faeces",
            "16713" => "Giardia screening",
            "16807" => "Macroscopisch aspect",
            "16810" => "Kultuur",
            "16819" => "Campylobacter",
            "16825" => "Parasieten",
            "16830" => "Antibiogram",

            // vertering
            "16715" => "zetmeel",
            "16718" => "spierweefsel",
            "16721" => "vetten",

            // Niersteen
            "17874" => "Niersteen analyse",

            // extra? 
            "18070" => "Bijkomende bepaling"
        );

        // request
        $request = "stalen.json?days=" . $days;
        $json_response = $this->req_curl_json($url . $request);
        $stalen = json_decode($json_response, true);

        foreach($stalen as $staal)
        {
            $internal_id = $this->lab->add_sample(
                    $staal['id'],
                    array(
                            "lab_date"          => $staal['datum_opname'], 
                            "lab_patient_id"    => $staal['patient_id'], 
                            "lab_updated_at"    => $staal['updated_at'], 
                            "lab_created_at"    => $staal['tijd_opname'], 
                            "lab_comment"       => $staal['extra_gegevens']
                        ),
                    "medilab"
            );

            $detail_response = $this->req_curl_json($url . "staal/" . $staal['id'] . ".json");
            $staal = json_decode($detail_response, true);

            foreach($staal["resultaten"] as $line)
            {
                $this->lab_line->add_line(
                                            $internal_id,
                                            $staal['id'], // lab_id
                                            array(
                                                'resultaat'       => $line['resultaat'],  
                                                'sp_resultaat'    => (!empty($line['sp_resultaat']) ? $line['sp_resultaat'] : $line['tek_resultaat']),
                                                'bovenlimiet'     => $line['bovenlimiet'],  
                                                'onderlimiet'     => $line['onderlimiet'],  
                                                'rapport'         => ($line['rapport'] == "J") ? 1 : 0,  
                                                'eenheid'         => $line['eenheid'],  
                                                'updated_at'      => $line['updated_at'],  
                                                'tabulatie_code'  => $line['tabulatie_code'],  
                                                'lab_code_text'   => (isset($test_id_to_names[$line["tabulatie_code"]]) ? $test_id_to_names[$line["tabulatie_code"]] : '---'),  
                                                'commentaar'      => $line['commentaar'], 
                                            )
                                );
            }
    
        }

        if($redirect)
        {
            redirect('lab', 'refresh');
        }
    }

	// wrapper around some curl setup
	// may require a specific php extension : php-curl
	private function req_curl_json(string $url)
	{
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array("Accept: application/json"));
		$json_response = curl_exec($curl);
		curl_close($curl);

		return $json_response;
	}
}