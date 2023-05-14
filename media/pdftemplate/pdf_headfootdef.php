<?php
class SITEPDF extends TCPDF {

	//Page header
	public function Header() {
		require_once(dirname(__FILE__).'/pdf_header_config.php');
        $cfg = get_pdf_header_config();
        
        // Logo
		//$image_file = K_PATH_IMAGES.'logo_example.jpg';
		$image_file = $cfg['logo'];
        $this->Image($image_file, 12, 12, 0, 0, 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);

		//organisation/company info
        $this->SetFont('helvetica', '', 10);
		$html = sprintf(
            '<span style="font-size: 20pt; font-weight: bold;">%s</span><br>%s, %s <br>%s<br>Tel: %s',
            $cfg['org'], 
            $cfg['street'], 
            $cfg['area'], 
            $cfg['country'],
            $cfg['tel']
        );
		$this->writeHTMLCell(60, 15, 55, 13, $html, 0, 0, 0, true, 'L', true);
		$html = sprintf('<br>Email: %s<br>Website: %s<br>Facebook: %s<br>VAT Registration No.: %s',
            $cfg['email'], 
            $cfg['website'], 
            $cfg['facebook'], 
            $cfg['taxreg']
        );
		$this->writeHTMLCell(0, 15, 130, 13, $html, 0, 1, 0, true, 'L', true);
		$html = '<hr style="border: black solid 0px;">';
		$this->writeHTMLCell(0, 20, 12, 35, $html, 0, 0, 0, true, 'L', true);
	}

	// Page footer
	public function Footer() {
		// Position at 15 mm from bottom
		$this->SetY(-12);
		// Set font
		$this->SetFont('helvetica', 'I', 8);
		// Page number
		if ( count($this->pagegroups) > 0) 
		{			
			$this->Cell(0, 10, 'Page '.$this->getPageNumGroupAlias().'/'.$this->getPageGroupAlias(), 0, false, 'R', 0, '', 0, false, 'T', 'M');
		} 
		else 
		{		
			$this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'T', 'M');	
		}
	}
}

class TESTPDF extends TCPDF {

	//Page header
	public function Header() {
		// Logo
		//$image_file = K_PATH_IMAGES.'logo_example.jpg';
		$image_file = 'media/pdftemplate/images/grllogo.96x48.png';
		$this->Image($image_file, 12, 12, 0, 0, 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);

		$this->SetFont('helvetica', '', 10);
		$html = '<span style="font-size: 30pt; font-weight: bold;">BIG FAT FRACKING TEST</span>';
		$this->writeHTMLCell(0, 15, 55, 13, $html, 0, 1, 0, true, 'L', true);
		$html = '<hr style="border: black solid 0px;">';
		$this->writeHTMLCell(0, 20, 12, 32, $html, 0, 0, 0, true, 'L', true);
	}

	// Page footer
	public function Footer() {
		// Position at 15 mm from bottom
		$this->SetY(-15);
		// Set font
		$this->SetFont('helvetica', 'I', 8);
		// Page number
		$this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
	
    }
}
?>
