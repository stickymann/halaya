<?php
class SITEPDF extends TCPDF {

	//Page header
	public function Header() {
		// Logo
		//$image_file = K_PATH_IMAGES.'logo_example.jpg';
		$image_file = 'media/pdftemplate/images/modsys.96x48.png';
		$this->Image($image_file, 12, 12, 0, 0, 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);

		$this->SetFont('helvetica', '', 10);
		$html = '<span style="font-size: 20pt; font-weight: bold;">Model System.</span><br>Some Street, Somewhere <br>Trinidad, W.I.<br>Tel: 555-5555';
		$this->writeHTMLCell(60, 15, 55, 13, $html, 0, 0, 0, true, 'L', true);
		$html = ' <br>Email: modsys@mailserver.com<br>Website: www.modsys.com<br>Facebook: www.facebook.com/modsys<br>VAT Registration No.: xxxxxx';
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
		if ($this->pagegroups > 0) 
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
		$this->SetY(-5);
		// Set font
		$this->SetFont('helvetica', 'I', 8);
		// Page number
		$this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
	}
}

class HSIPDF extends TCPDF {

	//Page header
	public function Header() {
		$this->SetFont('helvetica', '', 10);
		$html = '<span style="font-weight: bold;">Shazam Enterprises and Investment Limited</span>';
		$this->writeHTMLCell(0, 0, 0, 0, $html, 0, 1, 0, true, 'C', true);
	}

	// Page footer
	public function Footer() {
		$this->SetY(-15);
		//$footer_text = sprintf("Printed : %s",date('g:ia \o\n l jS, F Y') );
		$footer_text = sprintf("Printed : %s",date('l jS, F Y \@ g:ia') );
		$this->Cell(0, 10, $footer_text, 0, false, 'L', 0, '', 0, false, 'T', 'M');
	}
}
?>
